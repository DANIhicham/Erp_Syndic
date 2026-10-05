<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Appartement;
use App\Models\User;
use App\Models\TransactionPaiement;
use App\Models\PaiementCotisation;
use App\Models\ConfigurationBudget;
use App\Models\Document;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class CotisationController extends Controller
{
    // ═══════════════════════════════════════════════════════════════
    // INDEX — Page principale avec toutes les cotisations
    // ═══════════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        $residenceId = session('residence_id');
        $annee       = (int) ($request->annee ?? date('Y'));
        $statut      = $request->statut ?? '';
        $search      = $request->search ?? '';

        if (!$residenceId) {
            return back()->with('error', 'Veuillez sélectionner une résidence.');
        }

        // ══════════════════════════════════════════════════════════
        // NOUVELLE LOGIQUE : on part de paiement_cotisations
        // pour capturer TOUS les propriétaires d'une année donnée
        // (y compris les anciens après transfert de propriété)
        // ══════════════════════════════════════════════════════════

        // ── Étape 1 : collecter tous les (appartement_id, user_id)
        // ayant une cotisation cette année pour cette résidence ────
        $cotisationsAnnee = PaiementCotisation::with([
                'appartement',
                'user',
            ])
            ->where('annee_concernee', $annee)
            ->whereHas('appartement', fn($q) =>
                $q->where('residence_id', $residenceId)
            )
            ->get();

        // ── Étape 2 : compléter avec les appartements qui ont un
        // propriétaire actuel MAIS pas encore de cotisation cette
        // année (nouveaux propriétaires sans cotisation générée) ──
        $aptIdsDejaCouverts = $cotisationsAnnee
            ->pluck('appartement_id')
            ->unique();

        $appartementsSansCotisation = Appartement::with(['proprietaire'])
            ->where('residence_id', $residenceId)
            ->whereNotNull('proprietaire_id')
            ->whereNotIn('id', $aptIdsDejaCouverts)
            ->get();

        // ── Étape 3 : construire les lignes du tableau ─────────
        $lignesCotisations = collect();

        // 3a. Lignes issues de paiement_cotisations (anciens + actuels)
        foreach ($cotisationsAnnee as $cot) {
            $apt = $cot->appartement;
            if (!$apt) continue;

            // Charger les transactions filtrées par user_id du propriétaire
            // lié à cette cotisation (évite le mélange avec l'autre propriétaire)
            $apt->setRelation('transactions',
                TransactionPaiement::where('appartement_id', $apt->id)
                    ->where('annee', $annee)
                    ->where('user_id', $cot->user_id)
                    ->orderBy('date_paiement', 'desc')
                    ->get()
            );

            $lignesCotisations->push(
                $this->buildApartmentRowFromCotisation($apt, $cot, $annee)
            );
        }

        // 3b. Lignes des appartements sans cotisation générée
        foreach ($appartementsSansCotisation as $apt) {
            $apt->setRelation('transactions', collect());
            $lignesCotisations->push(
                $this->buildApartmentRow($apt, $annee)
            );
        }

        // ── Filtre recherche (client-side prioritaire, mais on
        // garde le filtre serveur pour les gros volumes) ──────────
        if ($search) {
            $lignesCotisations = $lignesCotisations->filter(function ($a) use ($search) {
                $s = mb_strtolower($search);
                return str_contains(mb_strtolower($a['nom']), $s)
                    || str_contains(mb_strtolower($a['numero']), $s);
            });
        }

        $appartements = $lignesCotisations
            ->filter(fn($a) => $a['montant_annuel'] > 0)
            ->values();

        // Filtre statut
        if ($statut) {
            $appartements = $appartements->filter(fn($a) => $a['statut'] === $statut)->values();
        }

        $montantAnnuelFixe = ConfigurationBudget::where('residence_id', $residenceId)
            ->where('annee', $annee)
            ->value('montant_annuel_fixe') ?? 0;

        $stats = $this->computeStats($appartements);

        $annees = ConfigurationBudget::where('residence_id', $residenceId)
            ->orderBy('annee', 'desc')
            ->pluck('annee')
            ->toArray();

        if (!in_array(date('Y'), $annees)) array_unshift($annees, (int) date('Y'));

        return view('syndic.cotisations', compact(
            'appartements',
            'annee',
            'annees',
            'stats',
            'statut',
            'search',
            'residenceId',
            'montantAnnuelFixe'
        ));
    }

    // ═══════════════════════════════════════════════════════════════
    // PAYER — Enregistrer un paiement (avec upload document)
    // ═══════════════════════════════════════════════════════════════
    public function payer(Request $request): JsonResponse
    {
        $request->validate([
            'appartement_id' => 'required|exists:appartements,id',
            'user_id'        => 'required|exists:users,id',  // ← propriétaire de la cotisation cliquée
            'annee'          => 'required|integer|min:2000|max:2100',
            'montant'        => 'required|numeric|min:0.01',
            'date_paiement'  => 'required|date',
            'mode_paiement'  => 'required|in:virement,especes,cheque',
            'reference'      => 'nullable|string|max:255',
            'commentaire'    => 'nullable|string',
            'document'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $residenceId = session('residence_id');

            $appartement = Appartement::findOrFail($request->appartement_id);

            // ── Utiliser le user_id envoyé par le blade ────────────
            // C'est le propriétaire de la cotisation cliquée
            // (peut être l'ancien propriétaire après un transfert)
            $proprietaireId = (int) $request->user_id;

            // ── Vérifier que ce user a bien une cotisation pour cet apt/année ──
            $cotisationExistante = PaiementCotisation::where('appartement_id', $request->appartement_id)
                ->where('annee_concernee', $request->annee)
                ->where('user_id', $proprietaireId)
                ->first();

            if (!$cotisationExistante) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune cotisation trouvée pour ce propriétaire sur cette année.',
                ], 422);
            }

            $montantMax = (float) ($cotisationExistante->montant_attendu ?? 0);

            // ── Montant déjà payé par CE propriétaire pour cet apt/année ──
            $dejaPayé = TransactionPaiement::where('appartement_id', $request->appartement_id)
                                           ->where('annee', $request->annee)
                                           ->where('user_id', $proprietaireId)
                                           ->sum('montant');

            if ($montantMax > 0 && ($dejaPayé + $request->montant) > $montantMax) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Le montant dépasse le budget annuel (' . number_format($montantMax, 0, ',', ' ') . ' MAD).',
                ], 422);
            }

            // ── Créer la transaction liée au bon propriétaire ─────
            $transaction = TransactionPaiement::create([
                'appartement_id' => $request->appartement_id,
                'user_id'        => $proprietaireId,
                'annee'          => $request->annee,
                'montant'        => $request->montant,
                'date_paiement'  => $request->date_paiement,
                'mode_paiement'  => $request->mode_paiement,
                'reference'      => $request->reference,
                'commentaire'    => $request->commentaire,
            ]);

            // ── Upload document justificatif ──────────────────────
            if ($request->hasFile('document')) {
                $path = $request->file('document')->store(
                    "syndic/cotisations/{$request->appartement_id}/{$request->annee}",
                    'public'
                );
                Document::create([
                    'nom_fichier'              => $request->file('document')->getClientOriginalName(),
                    'chemin_stockage'          => $path,
                    'type_document'            => 'reçu',
                    'residence_id'             => $residenceId,
                    'appartement_id'           => $request->appartement_id,
                    'transaction_paiement_id'  => $transaction->id,
                    'date_upload'              => now(),
                ]);
            }

            // ── Mettre à jour paiement_cotisations du BON propriétaire ──
            $cotisation = PaiementCotisation::firstOrNew([
                'appartement_id'  => $request->appartement_id,
                'annee_concernee' => $request->annee,
                'user_id'         => $proprietaireId,  // ← scope complet
            ]);

            // totalPaye filtré par CE propriétaire uniquement
            $totalPaye = TransactionPaiement::where('appartement_id', $request->appartement_id)
                                            ->where('annee', $request->annee)
                                            ->where('user_id', $proprietaireId)
                                            ->sum('montant');

            $cotisation->user_id         = $proprietaireId;
            $cotisation->montant_paye    = $totalPaye;
            $cotisation->montant_attendu = $montantMax;

            $coverage = $this->calculateCoverage(
                $appartement,
                $request->annee,
                $montantMax,
                $totalPaye
            );

            $cotisation->statut = $this->computeStatut(
                $totalPaye,
                $montantMax,
                $coverage['date_echeance'],
                $request->annee
            );
            $cotisation->save();

            DB::commit();

            // ── Réponse avec données à jour ───────────────────────
            $appartement = Appartement::with(['proprietaire', 'transactions' => fn($q) => $q->where('annee', $request->annee)])
                                      ->find($request->appartement_id);
            $row = $this->buildApartmentRow($appartement, $request->annee);

            return response()->json([
                'success'     => true,
                'message'     => 'Paiement enregistré avec succès.',
                'row'         => $row,
                'transaction' => $transaction,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // HISTORIQUE — Transactions d'un appartement pour une année
    // ═══════════════════════════════════════════════════════════════
    public function historique($appartementId, Request $request): JsonResponse
    {
        $annee = (int) ($request->annee ?? date('Y'));

        $appartement = Appartement::with('proprietaire')->findOrFail($appartementId);

        // ── Récupérer la cotisation du propriétaire concerné ─────
        // (peut être l'ancien ou le nouveau selon qui on consulte)
        $cotisation = PaiementCotisation::where('appartement_id', $appartementId)
            ->where('annee_concernee', $annee)
            ->where('user_id', $appartement->proprietaire_id)
            ->first();

        // Fallback : si pas de cotisation pour le propriétaire actuel,
        // prendre la première disponible (cas historique)
        if (!$cotisation) {
            $cotisation = PaiementCotisation::where('appartement_id', $appartementId)
                ->where('annee_concernee', $annee)
                ->orderByDesc('created_at')
                ->first();
        }

        $proprietaireId = $cotisation?->user_id ?? $appartement->proprietaire_id;

        // ── Transactions filtrées par propriétaire de la cotisation ──
        $transactions = TransactionPaiement::where('appartement_id', $appartementId)
            ->where('annee', $annee)
            ->where('user_id', $proprietaireId)
            ->with('documents')
            ->orderBy('date_paiement', 'asc')
            ->get();

        $montantAttendu = (float) ($cotisation?->montant_attendu ?? 0);

        // ── Totaux ────────────────────────────────────────────────
        $totalPaye = (float) $transactions->sum('montant');

        $reste = max(0, $montantAttendu - $totalPaye);


        // ── Formatage transactions ────────────────────────────────
        $transactionsFormatees = $transactions->map(function ($t) {

            $document = $t->documents->first();

            return [
                'id'            => $t->id,
                'montant'       => (float) $t->montant,
                'date_paiement' => Carbon::parse($t->date_paiement)->format('d/m/Y'),
                'mode_paiement' => $t->mode_paiement,
                'reference'     => $t->reference,
                'commentaire'   => $t->commentaire,

                'document_url'  => $document
                    ? asset('storage/' . $document->chemin_stockage)
                    : null,
            ];
        });

        // ── Réponse JSON ──────────────────────────────────────────
        return response()->json([
            'success'        => true,

            'proprietaire'   => $appartement->proprietaire
                ? $appartement->proprietaire->prenom . ' ' . $appartement->proprietaire->nom
                : '—',

            'email'          => $appartement->proprietaire?->email,
            'telephone'      => $appartement->proprietaire?->telephone,

            'numero_apt'     => $appartement->numero,

            'montant_annuel' => $montantAttendu,
            'total_paye'     => $totalPaye,
            'reste'          => $reste,
            

            'transactions'   => $transactionsFormatees,
        ]);
    }
    // ═══════════════════════════════════════════════════════════════
    // SUPPRIMER TRANSACTION — Annuler un paiement
    // ═══════════════════════════════════════════════════════════════
    public function supprimerTransaction($id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $transaction = TransactionPaiement::with('documents')->findOrFail($id);

            // Supprimer les fichiers liés
            foreach ($transaction->documents as $doc) {
                Storage::disk('public')->delete($doc->chemin_stockage);
                $doc->delete();
            }
            $transaction->delete();

            // Recalculer le résumé paiement_cotisations
            $residenceId = session('residence_id');
            $annee       = $transaction->annee;
            $aptId       = $transaction->appartement_id;

            $config      = ConfigurationBudget::where('residence_id', $residenceId)
                                              ->where('annee', $annee)->first();
            $montantMax  = $config ? (float) $config->montant_annuel_fixe : 0;
            $appartement    = Appartement::findOrFail($aptId);
            // user_id de la transaction supprimée = propriétaire concerné
            $proprietaireId = $transaction->user_id;

            $totalPaye = TransactionPaiement::where('appartement_id', $aptId)
                ->where('annee', $annee)
                ->where('user_id', $proprietaireId)
                ->sum('montant');

            $coverage = $this->calculateCoverage(
                $appartement,
                $annee,
                $montantMax,
                $totalPaye
            );

            PaiementCotisation::updateOrCreate(
                [
                    'appartement_id'  => $aptId,
                    'annee_concernee' => $annee,
                    'user_id'         => $proprietaireId,
                ],
                [
                    'montant_paye'    => $totalPaye,
                    'montant_attendu' => $montantMax,
                    'statut' => $this->computeStatut(
                        $totalPaye,
                        $montantMax,
                        $coverage['date_echeance']
                    ),
                ]
            );

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Transaction supprimée.']);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // EXPORT CSV
    // ═══════════════════════════════════════════════════════════════
    public function exportCsv(Request $request)
    {
        $residenceId = session('residence_id');
        $annee       = (int) ($request->annee ?? date('Y'));

        $config            = ConfigurationBudget::where('residence_id', $residenceId)->where('annee', $annee)->first();

        $appartements = Appartement::with([
            'proprietaire',
            'transactions' => fn($q) => $q->where('annee', $annee),
        ])
        ->where('residence_id', $residenceId)
        ->whereNotNull('proprietaire_id')
        ->get()
        ->map(fn($a) => $this->buildApartmentRow($a, $annee));

        return response()->streamDownload(function () use ($appartements, $annee) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($handle, ['Appartement', 'Copropriétaire', 'Montant Annuel', 'Payé', 'Reste', 'Statut', 'Date Échéance'], ';');
            foreach ($appartements as $a) {
                fputcsv($handle, [
                    $a['numero'],
                    $a['nom'],
                    number_format($a['montant_annuel'], 2, ',', ' '),
                    number_format($a['montant_paye'], 2, ',', ' '),
                    number_format($a['reste'], 2, ',', ' '),
                    $a['statut'],
                    $a['date_echeance'] ?? '',
                ], ';');
            }
            fclose($handle);
        }, "cotisations_{$annee}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ═══════════════════════════════════════════════════════════════
    // RELANCER — Envoi d'un email de relance (stub)
    // ═══════════════════════════════════════════════════════════════
    public function relancer($appartementId): JsonResponse
    {
        $appartement = Appartement::with('proprietaire')->findOrFail($appartementId);
        $email       = $appartement->proprietaire?->email;

        if (!$email) {
            return response()->json(['success' => false, 'message' => 'Aucun email associé à ce propriétaire.'], 422);
        }

        // TODO: Mail::to($email)->send(new RelanceCotisationMail($appartement));
        // Pour l'instant on simule l'envoi
        return response()->json([
            'success' => true,
            'message' => "Email de relance envoyé à {$email}.",
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // HELPERS PRIVÉS
    // ═══════════════════════════════════════════════════════════════
    // ═══════════════════════════════════════════════════════════════
    // HELPER — Construire une ligne depuis une cotisation existante
    // Utilisé pour afficher les anciens propriétaires après transfert
    // ═══════════════════════════════════════════════════════════════
    private function buildApartmentRowFromCotisation(
        Appartement $apt,
        PaiementCotisation $cot,
        int $annee
    ): array {
        $montantAttendu = (float) ($cot->montant_attendu ?? 0);

        // Transactions de l'appartement pour cette année
        $montantPaye = (float) $apt->transactions->sum('montant');
        $reste       = max(0, $montantAttendu - $montantPaye);

        $coverage = $this->calculateCoverage($apt, $annee, $montantAttendu, $montantPaye);

        $statut = $this->computeStatut(
            $montantPaye,
            $montantAttendu,
            $coverage['date_echeance'],
            $annee
        );

        // Synchronisation DB si statut changé
        if ($cot->statut !== $statut) {
            $cot->update(['statut' => $statut, 'montant_paye' => $montantPaye]);
        }

        // Le "propriétaire" affiché est celui lié à la cotisation (user_id)
        // et non forcément le proprietaire_id actuel de l'appartement
        $proprietaire = $cot->user;
        $initiales    = $proprietaire
            ? mb_strtoupper(mb_substr($proprietaire->prenom, 0, 1) . mb_substr($proprietaire->nom, 0, 1))
            : '??';

        // Détecter si c'est un ancien propriétaire (après transfert)
        $estAncienProp = $proprietaire && $apt->proprietaire_id !== $proprietaire->id;

        $lastTransaction = $apt->transactions->sortByDesc('date_paiement')->first();

        return [
            'id'               => $apt->id,
            'numero'           => $apt->numero,
            'nom'              => $proprietaire
                ? $proprietaire->prenom . ' ' . $proprietaire->nom
                : '—',
            'email'            => $proprietaire?->email,
            'telephone'        => $proprietaire?->telephone,
            'initiales'        => $initiales,
            'montant_annuel'   => $montantAttendu,
            'montant_paye'     => $montantPaye,
            'reste'            => $reste,
            'statut'           => $statut,
            'date_echeance'    => $statut === 'payé'
                ? null
                : ($coverage['date_echeance'] ? $coverage['date_echeance']->format('d/m/Y') : null),
            'jours_couverts'   => $coverage['jours_couverts'],
            'nb_transactions'  => $apt->transactions->count(),
            'proprietaire_id'  => $proprietaire?->id,
            'last_transaction_id' => $lastTransaction?->id,
            // Label visuel pour distinguer les anciens propriétaires
            'est_ancien_prop'  => $estAncienProp,
            'label_transfert'  => $estAncienProp
                ? 'Ancien prop ' . $annee
                : null,
        ];
    }

    private function buildApartmentRow($apt, int $annee): array
    {
        $cotisation = PaiementCotisation::where('appartement_id', $apt->id)
            ->where('annee_concernee', $annee)
            ->first();

        $montantAttendu = $cotisation?->montant_attendu ?? 0;

        $montantPaye  = (float) $apt->transactions->sum('montant');
        $reste        = max(0, $montantAttendu - $montantPaye);

        $coverage = $this->calculateCoverage(
            $apt,
            $annee,
            $montantAttendu,
            $montantPaye
        );

        $dateEcheance = $coverage['date_echeance'];
        $joursCouverts = $coverage['jours_couverts'];

        $statut = $this->computeStatut(
            $montantPaye,
            $montantAttendu,
            $dateEcheance,
            $annee
        );

        // Synchronisation DB
        if ($cotisation && $cotisation->statut !== $statut) {
            $cotisation->update([
                'statut' => $statut,
                'montant_paye' => $montantPaye
            ]);
            }

        // Initiales avatar
        $proprietaire = $apt->proprietaire;
        $initiales    = $proprietaire
            ? mb_strtoupper(mb_substr($proprietaire->prenom, 0, 1) . mb_substr($proprietaire->nom, 0, 1))
            : '??';

        $lastTransaction = $apt->transactions->sortByDesc('date_paiement')->first();
        return [
            'id'                   => $apt->id,
            'numero'               => $apt->numero,
            'nom'                  => $proprietaire ? $proprietaire->prenom . ' ' . $proprietaire->nom : '—',
            'email'                => $proprietaire?->email,
            'telephone'            => $proprietaire?->telephone,
            'initiales'            => $initiales,
            'montant_annuel'       => $montantAttendu,
            'montant_paye'         => $montantPaye,
            'reste'                => $reste,
            'statut'               => $statut,
            'date_echeance'        => $statut === 'payé'
                ? null
                : ($dateEcheance ? $dateEcheance->format('d/m/Y') : null),
            'jours_couverts'       => $joursCouverts,
            'nb_transactions'      => $apt->transactions->count(),
            'proprietaire_id'      => $apt->proprietaire_id,
            'last_transaction_id'  => $lastTransaction?->id,
            'est_ancien_prop'      => false,
            'label_transfert'      => null,
        ];
    }

    private function calculateCoverage(
    Appartement $apt,
    int $annee,
    float $montantAttendu,
    float $montantPaye
    ): array {

        // ── Date signature ─────────────────────────
        $dateSignature = $apt->date_signature_contrat
            ? Carbon::parse($apt->date_signature_contrat)
            : Carbon::create($annee, 1, 1);

        $anneeSignature = $dateSignature->year;

        // ── Début période ──────────────────────────
        $dateDebut = $annee == $anneeSignature
            ? $dateSignature->copy()
            : Carbon::create($annee, 1, 1);

        $dateFinTheorique = Carbon::create($annee, 12, 31);

        // ── Nombre jours réels ─────────────────────
        $joursPeriode = $dateDebut->diffInDays($dateFinTheorique) + 1;

        // ── Coût journalier ────────────────────────
        $coutJournalier = $joursPeriode > 0
            ? $montantAttendu / $joursPeriode
            : 0;

        // ── Jours couverts ─────────────────────────
        $joursCouverts = $coutJournalier > 0
            ? floor($montantPaye / $coutJournalier)
            : 0;

        $dateEcheance = null;

        if ($joursCouverts > 0) {

            $dateEcheance = $dateDebut->copy()
                ->addDays($joursCouverts - 1);

            if ($dateEcheance->gt($dateFinTheorique)) {
                $dateEcheance = $dateFinTheorique->copy();
            }
        }

        return [
            'date_echeance' => $dateEcheance,
            'jours_couverts' => $joursCouverts,
        ];
    }

    private function computeStatut(
        float $paye,
        float $attendu,
        ?Carbon $dateEcheance = null,
        ?int $annee = null
    ): string {

        $anneeCourante = now()->year;

        // Aucun budget
        if ($attendu <= 0) {
            return 'en_attente';
        }

        // Totalement payé
        if ($paye >= $attendu) {
            return 'payé';
        }

        // ── ANNÉE FUTURE ─────────────────────────
        if ($annee && $annee > $anneeCourante) {

            if ($paye > 0) {
                return 'partiel';
            }

            return 'en_attente';
        }

        // ── ANNÉE COURANTE / PASSÉE ──────────────

        // Aucun paiement
        if ($paye <= 0) {
            return 'en_retard';
        }

        // Couverture encore valide
        if ($dateEcheance && now()->lte($dateEcheance)) {
            return 'partiel';
        }

        // Couverture expirée
        return 'en_retard';
    }

    private function computeStats($appartements): array
    {
        $total = $appartements->count();
        return [
            'total_apparts' => $total,
            'total_budget'  => $appartements->sum('montant_annuel'),
            'encaissé'      => $appartements->sum('montant_paye'),
            'reste'         => $appartements->sum('reste'),
            'payes'         => $appartements->where('statut', 'payé')->count(),
            'partiels'      => $appartements->where('statut', 'partiel')->count(),
            'retards'       => $appartements->where('statut', 'en_retard')->count(),
        ];
    }
    public function recu($transactionId)
    {
        $transaction = TransactionPaiement::with([
            'appartement.proprietaire',
            'appartement.residence',
            'documents'
        ])->findOrFail($transactionId);

        $appartement  = $transaction->appartement;
        $proprietaire = $appartement->proprietaire;
        $residence    = $appartement->residence;
        $annee        = $transaction->annee;

        // ── Cotisation du propriétaire concerné ────────────────────
        $cotisation = PaiementCotisation::where('appartement_id', $appartement->id)
            ->where('annee_concernee', $annee)
            ->where('user_id', $appartement->proprietaire_id)
            ->first();

        $montantAttendu = (float) ($cotisation?->montant_attendu ?? 0);

        // ── Total payé filtré par propriétaire actuel ───────────────
        $totalPaye = TransactionPaiement::where('appartement_id', $appartement->id)
            ->where('annee', $annee)
            ->where('user_id', $appartement->proprietaire_id)
            ->sum('montant');

        $reste = max(0, $montantAttendu - $totalPaye);

        // ── Calcul période ─────────────────────────
        $dateSignature = $appartement->date_signature_contrat
            ? Carbon::parse($appartement->date_signature_contrat)
            : Carbon::create($annee, 1, 1);

        $anneeSignature = $dateSignature->year;

        $dateDebut = $annee == $anneeSignature
            ? $dateSignature->copy()
            : Carbon::create($annee, 1, 1);

        $joursAnnee = $dateDebut->isLeapYear() ? 366 : 365;

        $coutJournalier = $montantAttendu > 0
            ? $montantAttendu / $joursAnnee
            : 0;

        $joursCouverts = $coutJournalier > 0
            ? floor($transaction->montant / $coutJournalier)
            : 0;

        $dateFin = $joursCouverts > 0
            ? $dateDebut->copy()->addDays($joursCouverts - 1)
            : $dateDebut;

        // ── Génération PDF ─────────────────────────
        $pdf = Pdf::loadView('syndic.recu-cotisation', [
            'transaction'      => $transaction,
            'appartement'      => $appartement,
            'residence'        => $residence,
            'proprietaire'     => $proprietaire,
            'montantAttendu'   => $montantAttendu,
            'totalPaye'        => $totalPaye,
            'reste'            => $reste,
            'dateDebut'        => $dateDebut,
            'dateFin'          => $dateFin,
        ])->setPaper('A4', 'portrait');

        return $pdf->download('recu-'.$transaction->id.'.pdf');
    }
}