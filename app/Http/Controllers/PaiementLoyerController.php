<?php

namespace App\Http\Controllers;

use App\Models\PaiementLoyer;
use App\Models\TransactionLoyer;
use App\Models\Contrat;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PaiementLoyerController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    // INDEX
    // ══════════════════════════════════════════════════════════════
    public function index()
    {
        $residenceId   = session('residence_id');
        $moisCourant   = Carbon::now()->month;
        $anneeCourante = Carbon::now()->year;

        // Auto-mise à jour des statuts expirés
        $this->autoUpdateStatuts($residenceId);

        $paiements = PaiementLoyer::with([
                'contrat.appartement.residence',
                'contrat.locataire',
                'contrat.proprietaire',
                'transactions',
                'documents',              // ← chargement des justificatifs
            ])
            ->when($residenceId, function ($q) use ($residenceId) {
                $q->whereHas('contrat.appartement', fn($a) =>
                    $a->where('residence_id', $residenceId)
                );
            })
            ->orderBy('date_echeance', 'desc')
            ->get();

        $annees       = $paiements->map(fn($p) => Carbon::parse($p->date_echeance)->year)->unique()->sortDesc()->values();
        $appartements = $paiements->map(fn($p) => $p->contrat?->appartement)->filter()->unique('id')->values();
        $locataires   = $paiements->map(fn($p) => $p->contrat?->locataire)->filter()->unique('id')->values();

        $kpis = [
            'encaisses_mois' => TransactionLoyer::whereHas('contrat.appartement', function ($q) use ($residenceId) {
                    if ($residenceId) $q->where('residence_id', $residenceId);
                })
                ->whereMonth('date_paiement', $moisCourant)
                ->whereYear('date_paiement',  $anneeCourante)
                ->sum('montant'),
            'impayes'   => $paiements->whereIn('statut', ['en_retard','en_attente'])
                                     ->sum(fn($p) => max(0, $p->montant - $p->montant_paye)),
            'en_retard' => $paiements->where('statut', 'en_retard')->count(),
            'partiel'   => $paiements->where('statut', 'partiel')->count(),
            'paye'      => $paiements->where('statut', 'paye')->count(),
        ];

        return view('location.paiements_loyer', compact(
            'paiements', 'kpis', 'annees', 'appartements', 'locataires'
        ));
    }

    // ══════════════════════════════════════════════════════════════
    // SHOW
    // ══════════════════════════════════════════════════════════════
    public function show(PaiementLoyer $paiementLoyer): JsonResponse
    {
        $paiementLoyer->load([
            'contrat.appartement.residence',
            'contrat.locataire',
            'contrat.proprietaire',
            'transactions',
            'documents',              // ← justificatifs
        ]);

        $historique = PaiementLoyer::where('contrat_id', $paiementLoyer->contrat_id)
            ->orderBy('date_echeance')
            ->get();

        // Enrichir les documents avec leur URL publique
        $documents = $paiementLoyer->documents->map(function ($doc) {
            $doc->url      = Storage::url($doc->chemin_stockage);
            $doc->is_image = in_array(
                strtolower(pathinfo($doc->nom_fichier, PATHINFO_EXTENSION)),
                ['jpg','jpeg','png','gif','webp']
            );
            return $doc;
        });

        return response()->json([
            'success'      => true,
            'paiement'     => $paiementLoyer,
            'transactions' => $paiementLoyer->transactions,
            'historique'   => $historique,
            'documents'    => $documents,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // ENCAISSER — Paiement + justificatif optionnel
    // ══════════════════════════════════════════════════════════════
    public function encaisser(Request $request, PaiementLoyer $paiementLoyer): JsonResponse
    {
        $request->validate([
            'montant'        => 'required|numeric|gt:0',
            'date_paiement'  => 'required|date',
            'mode_paiement'  => 'required|in:espece,virement,cheque',
            'reference'      => 'nullable|string|max:255',
            'commentaire'    => 'nullable|string',
            'justificatif'   => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'montant.gt'             => 'Le montant doit être supérieur à 0.',
            'mode_paiement.in'       => 'Mode de paiement invalide.',
            'justificatif.mimes'     => 'Le justificatif doit être une image (jpg, png) ou un PDF.',
            'justificatif.max'       => 'Le justificatif ne doit pas dépasser 5 Mo.',
        ]);

        // Protection contre sur-paiement
        $resteAPayer = (float)$paiementLoyer->montant - (float)$paiementLoyer->montant_paye;
        if ((float)$request->montant > $resteAPayer + 0.01) {
            return response()->json([
                'success' => false,
                'message' => 'Le montant versé (' . number_format($request->montant, 2) . ' MAD) '
                           . 'dépasse le reste à payer (' . number_format($resteAPayer, 2) . ' MAD).',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // ── 1. Créer la transaction loyer ──────────────────────
            $transaction = TransactionLoyer::create([
                'paiement_loyer_id' => $paiementLoyer->id,
                'contrat_id'        => $paiementLoyer->contrat_id,
                'montant'           => $request->montant,
                'date_paiement'     => $request->date_paiement,
                'mode_paiement'     => $request->mode_paiement,
                'reference'         => $request->reference,
                'commentaire'       => $request->commentaire,
            ]);

            // ── 2. Upload justificatif si fourni ───────────────────
            if ($request->hasFile('justificatif')) {
                $file       = $request->file('justificatif');
                $nomFichier = 'justif_loyer_'
                    . $paiementLoyer->id . '_'
                    . $transaction->id . '_'
                    . time() . '.'
                    . $file->getClientOriginalExtension();

                $chemin = $file->storeAs(
                    'documents/loyer/' . $paiementLoyer->contrat_id,
                    $nomFichier,
                    'public'
                );

                Document::create([
                    'paiement_loyer_id'       => $paiementLoyer->id,
                    'transaction_paiement_id' => null,
                    'user_id'                 => $paiementLoyer->contrat?->locataire?->id,
                    'residence_id'            => $paiementLoyer->contrat?->appartement?->residence_id,
                    'appartement_id'          => $paiementLoyer->contrat?->appartement_id,
                    'nom_fichier'             => $nomFichier,
                    'chemin_stockage'         => $chemin,
                    'type_document'           => 'recu',
                    'date_upload'             => now(),
                ]);
            }

            // ── 3. Recalculer montant_paye = SUM(transactions) ─────
            $montantPaye = TransactionLoyer::where('paiement_loyer_id', $paiementLoyer->id)
                ->sum('montant');

            // ── 4. Recalculer statut (logique identique cotisations) ──
            $statut = $this->calculerStatut(
                (float) $montantPaye,
                (float) $paiementLoyer->montant,
                Carbon::parse($paiementLoyer->date_echeance)
            );

            // ── 5. Sauvegarder ────────────────────────────────────
            $paiementLoyer->update([
                'montant_paye' => $montantPaye,
                'statut'       => $statut,
            ]);

            DB::commit();

            return response()->json([
                'success'      => true,
                'message'      => 'Paiement encaissé avec succès.',
                'montant_paye' => $montantPaye,
                'statut'       => $statut,
                'paiement'     => $paiementLoyer->fresh([
                    'contrat.appartement',
                    'contrat.locataire',
                    'transactions',
                    'documents',
                ]),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // PRIVATE — Logique statut (identique CotisationController)
    // ══════════════════════════════════════════════════════════════
    private function calculerStatut(float $montantPaye, float $montant, Carbon $dateEcheance): string
    {
        $aujourd_hui = Carbon::today();

        if ($montantPaye >= $montant && $montant > 0) {
            return 'paye';
        }
        if ($montantPaye > 0 && $montantPaye < $montant) {
            return $dateEcheance->lessThan($aujourd_hui) ? 'en_retard' : 'partiel';
        }
        return $dateEcheance->lessThan($aujourd_hui) ? 'en_retard' : 'en_attente';
    }

    // ══════════════════════════════════════════════════════════════
    // PRIVATE — Auto-update statuts expirés
    // ══════════════════════════════════════════════════════════════
    private function autoUpdateStatuts(?int $residenceId): void
    {
        PaiementLoyer::whereIn('statut', ['en_attente','partiel'])
            ->where('date_echeance', '<', Carbon::today())
            ->when($residenceId, fn($q) =>
                $q->whereHas('contrat.appartement', fn($a) =>
                    $a->where('residence_id', $residenceId)
                )
            )
            ->chunk(100, fn($items) =>
                $items->each->update(['statut' => 'en_retard'])
            );
    }
}