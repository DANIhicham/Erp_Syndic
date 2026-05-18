<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Appartement;
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

        // ── Budget annuel configuré ──────────────────────────────
        $cotisationExistante = PaiementCotisation::where('appartement_id', $request->appartement_id)
            ->where('annee_concernee', $request->annee)
            ->first();

        $montantMax = (float) ($cotisationExistante?->montant_attendu ?? 0);

        // ── Appartements avec leurs données ──────────────────────
        $query = Appartement::with([
            'proprietaire',
            'paiementCotisation' => fn($q) => $q->where('annee_concernee', $annee),
            'transactions'       => fn($q) => $q->where('annee', $annee)->orderBy('date_paiement', 'desc'),
        ])
        ->where('residence_id', $residenceId)
        ->whereNotNull('proprietaire_id');

        // Filtre recherche
        if ($search) {
            $query->whereHas('proprietaire', fn($q) =>
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
            )->orWhere('numero', 'like', "%{$search}%");
        }

        $appartements = $query->get()->map(function ($apt) use ($annee) {
            return $this->buildApartmentRow($apt, $annee);
        })
        ->filter(fn($a) => $a['montant_annuel'] > 0)
        ->values();
        // Filtre statut (après mapping car calculé)
        if ($statut) {
            $appartements = $appartements->filter(fn($a) => $a['statut'] === $statut)->values();
        }

        $montantAnnuelFixe = ConfigurationBudget::where('residence_id', $residenceId)
        ->where('annee', $annee)
        ->value('montant_annuel_fixe') ?? 0;

        // ── KPIs ──────────────────────────────────────────────────


        $stats = $this->computeStats($appartements);
        // ── Années disponibles pour le filtre ─────────────────────
        $annees = ConfigurationBudget::where('residence_id', $residenceId)
        ->orderBy('annee', 'desc')
        ->pluck('annee')
        ->toArray();

        // Ajouter l'année courante si absente
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

            // ── Vérifier le montant déjà payé ────────────────────
            $dejaPayé = TransactionPaiement::where('appartement_id', $request->appartement_id)
                                           ->where('annee', $request->annee)
                                           ->sum('montant');

            $cotisationExistante = PaiementCotisation::where('appartement_id', $request->appartement_id)
                ->where('annee_concernee', $request->annee)
                ->first();

            $montantMax = (float) ($cotisationExistante?->montant_attendu ?? 0);

            if ($montantMax > 0 && ($dejaPayé + $request->montant) > $montantMax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le montant dépasse le budget annuel (' . number_format($montantMax, 0, ',', ' ') . ' MAD).',
                ], 422);
            }

            // ── Créer la transaction ──────────────────────────────
            $transaction = TransactionPaiement::create([
                'appartement_id' => $request->appartement_id,
                'user_id'        => auth()->id(),
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

            // ── Mettre à jour paiement_cotisations (résumé) ───────
            $cotisation = PaiementCotisation::firstOrNew([
                'appartement_id'  => $request->appartement_id,
                'annee_concernee' => $request->annee,
            ]);

            $totalPaye = TransactionPaiement::where('appartement_id', $request->appartement_id)
                                            ->where('annee', $request->annee)
                                            ->sum('montant');

            $cotisation->user_id         = auth()->id();
            $cotisation->montant_paye    = $totalPaye;
            $cotisation->montant_attendu = $montantMax;
            $cotisation->statut          = $this->computeStatut($totalPaye, $montantMax);
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

        // ── Transactions ───────────────────────────────────────────
        $transactions = TransactionPaiement::where('appartement_id', $appartementId)
            ->where('annee', $annee)
            ->with('documents')
            ->orderBy('date_paiement', 'desc')
            ->get();

        // ── Cotisation attendue ────────────────────────────────────
        $cotisation = PaiementCotisation::where('appartement_id', $appartementId)
            ->where('annee_concernee', $annee)
            ->first();

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
            $totalPaye   = TransactionPaiement::where('appartement_id', $aptId)
                                              ->where('annee', $annee)->sum('montant');

            PaiementCotisation::updateOrCreate(
                ['appartement_id' => $aptId, 'annee_concernee' => $annee],
                [
                    'montant_paye'    => $totalPaye,
                    'montant_attendu' => $montantMax,
                    'statut'          => $this->computeStatut($totalPaye, $montantMax),
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
    private function buildApartmentRow($apt, int $annee): array
    {
        $cotisation = PaiementCotisation::where('appartement_id', $apt->id)
            ->where('annee_concernee', $annee)
            ->first();

        $montantAttendu = $cotisation?->montant_attendu ?? 0;

        $montantPaye  = (float) $apt->transactions->sum('montant');
        $reste        = max(0, $montantAttendu - $montantPaye);

        // ── Date début couverture ─────────────────────────────
        $dateSignature = $apt->date_signature_contrat
            ? Carbon::parse($apt->date_signature_contrat)
            : Carbon::create($annee, 1, 1);

        $anneeSignature = $dateSignature->year;

        // Première année = commence à la date signature
        $dateDebut = $annee == $anneeSignature
            ? $dateSignature->copy()
            : Carbon::create($annee, 1, 1);

        // Fin période théorique
        $dateFinTheorique = Carbon::create($annee, 12, 31);

        // Nombre réel de jours à couvrir
        $joursPeriode = $dateDebut->diffInDays($dateFinTheorique) + 1;

        // Coût journalier réel
        $coutJournalier = $joursPeriode > 0
            ? $montantAttendu / $joursPeriode
            : 0;

        // Jours couverts par le paiement
        $joursCouverts = $coutJournalier > 0
            ? floor($montantPaye / $coutJournalier)
            : 0;

        // Date échéance
        $dateEcheance = null;

        if ($joursCouverts > 0) {

            $dateEcheance = $dateDebut->copy()
                ->addDays($joursCouverts - 1);

            // Ne jamais dépasser fin année
            if ($dateEcheance->gt($dateFinTheorique)) {
                $dateEcheance = $dateFinTheorique->copy();
            }

            $dateEcheance = $dateEcheance->format('d/m/Y');
        }

        $statut = $this->computeStatut(
            $montantPaye,
            $montantAttendu,
            $dateEcheance ? Carbon::createFromFormat('d/m/Y', $dateEcheance) : null
        );

        // Initiales avatar
        $proprietaire = $apt->proprietaire;
        $initiales    = $proprietaire
            ? mb_strtoupper(mb_substr($proprietaire->prenom, 0, 1) . mb_substr($proprietaire->nom, 0, 1))
            : '??';

        $lastTransaction = $apt->transactions->sortByDesc('date_paiement')->first(); 
        return [
            'id'              => $apt->id,
            'numero'          => $apt->numero,
            'nom'             => $proprietaire ? $proprietaire->prenom . ' ' . $proprietaire->nom : '—',
            'email'           => $proprietaire?->email,
            'telephone'       => $proprietaire?->telephone,
            'initiales'       => $initiales,
            'montant_annuel'  => $montantAttendu,
            'montant_paye'    => $montantPaye,
            'reste'           => $reste,
            'statut'          => $statut,
            'date_echeance' => $statut === 'payé'
            ? null
            : $dateEcheance,
            'jours_couverts' => $joursCouverts,
            'nb_transactions' => $apt->transactions->count(),
            'proprietaire_id' => $apt->proprietaire_id,
            'last_transaction_id' => $lastTransaction?->id,
        ];
    }

    private function computeStatut(
        float $paye,
        float $attendu,
        ?Carbon $dateEcheance = null
    ): string {

        // Aucun budget configuré
        if ($attendu <= 0) {
            return 'en_attente';
        }

        // Totalement payé
        if ($paye >= $attendu) {
            return 'payé';
        }

        // Rien payé
        if ($paye <= 0) {

            // Si date dépassée => retard
            if ($dateEcheance && now()->gt($dateEcheance)) {
                return 'en_retard';
            }

            return 'en_retard';
        }

        // Paiement partiel avec couverture encore valide
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

        // ── Cotisation ─────────────────────────────
        $cotisation = PaiementCotisation::where('appartement_id', $appartement->id)
            ->where('annee_concernee', $annee)
            ->first();

        $montantAttendu = (float) ($cotisation?->montant_attendu ?? 0);

        // ── Total payé ─────────────────────────────
        $totalPaye = TransactionPaiement::where('appartement_id', $appartement->id)
            ->where('annee', $annee)
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