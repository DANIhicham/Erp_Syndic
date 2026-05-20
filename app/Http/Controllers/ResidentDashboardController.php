<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Appartement;
use App\Models\PaiementCotisation;
use App\Models\TransactionPaiement;
use App\Models\Reclamation;
use App\Models\ConfigurationBudget;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ResidentDashboardController extends Controller
{
    // ═══════════════════════════════════════════════════════════════
    // INDEX — Dashboard principal du résident
    // ═══════════════════════════════════════════════════════════════
    public function index()
    {
        $user  = auth()->user();
        $annee = now()->year;

        // ── Appartement + résidence ──────────────────────────────
        $appartement = Appartement::with('residence')
            ->where('proprietaire_id', $user->id)
            ->firstOrFail();

        $residence = $appartement->residence;

        // ── Budget annuel (config de la résidence) ───────────────
        $config = ConfigurationBudget::where('residence_id', $residence->id)
            ->where('annee', $annee)
            ->first();

        $budgetAnnuel = $config ? (float) $config->montant_annuel_fixe : 0;

        // ── Cotisation de l'année en cours ───────────────────────
        $cotisationAnnee = PaiementCotisation::where('appartement_id', $appartement->id)
            ->where('annee_concernee', $annee)
            ->first();

        // montant_attendu vient de paiement_cotisations (par appartement)
        $montantAttendu = $cotisationAnnee
            ? (float) $cotisationAnnee->montant_attendu
            : $budgetAnnuel;

        // ── Total payé cette année (somme des transactions) ──────
        $montantPaye = TransactionPaiement::where('appartement_id', $appartement->id)
            ->where('annee', $annee)
            ->sum('montant');

        $montantPaye = (float) $montantPaye;
        $resteAPayer = max(0, $montantAttendu - $montantPaye);
        $pourcentage = $montantAttendu > 0
            ? min(100, round(($montantPaye / $montantAttendu) * 100))
            : 0;

        // ── Statut de l'année en cours ───────────────────────────
        $statutAnnee = $cotisationAnnee?->statut;
        // ── Date d'échéance ──────────────────────────────────────
        // 1ère année (année de signature) → à partir de date_signature_contrat
        $coverage = $this->calculateCoverage(
            $appartement,
            $annee,
            $montantAttendu,
            $montantPaye
        );

        $dateEcheance = $coverage['date_echeance'];

        // ── Historique annuel (toutes les années) ────────────────
        $historiques = PaiementCotisation::where('appartement_id', $appartement->id)
            ->orderBy('annee_concernee', 'desc')
            ->get()
            ->map(function ($cot) use ($appartement) {
                $paye    = (float) $cot->montant_paye;
                $attendu = (float) $cot->montant_attendu;
                $reste   = max(0, $attendu - $paye);
                return [
                    'annee'   => $cot->annee_concernee,
                    'paye'    => $paye,
                    'attendu' => $attendu,
                    'reste'   => $reste,
                    'statut'  => $cot->statut,
                ];
            });

        // ── Transactions détaillées (tableau historique) ─────────
        $transactions = TransactionPaiement::where('appartement_id', $appartement->id)
            ->orderBy('annee', 'desc')
            ->orderBy('date_paiement', 'desc')
            ->get()
            ->map(function ($t) use ($appartement) {
                // Récupérer le montant attendu de l'année correspondante
                $cot = PaiementCotisation::where('appartement_id', $appartement->id)
                    ->where('annee_concernee', $t->annee)
                    ->first();

                $attendu = $cot ? (float) $cot->montant_attendu : 0;
                $paye    = (float) $t->montant;

                // Calculer le reste à payer pour cette transaction
                $totalPayeAnnee = TransactionPaiement::where('appartement_id', $appartement->id)
                    ->where('annee', $t->annee)
                    ->sum('montant');

                $reste = max(0, $attendu - (float) $totalPayeAnnee);
                
                // Date échéance 
                $coverage = $this->calculateCoverage(
                    $appartement,
                    $t->annee,
                    $attendu,
                    (float) $totalPayeAnnee
                );

                $echeance = $coverage['date_echeance'];

                return [
                    'id'           => $t->id,
                    'annee'        => $t->annee,
                    'montant'      => $paye,
                    'attendu'      => $attendu,
                    'reste'        => $reste,
                    'date_paiement'=> $t->date_paiement
                        ? Carbon::parse($t->date_paiement)->format('d/m/Y')
                        : null,
                    'echeance' => $echeance
                        ? $echeance->format('d/m/Y')
                        : '—',
                    'mode'         => $t->mode_paiement,
                    'reference'    => $t->reference,
                    'commentaire'  => $t->commentaire,
                    'statut'       => $cot?->statut,
                ];
            });

        // ── Réclamations du résident ─────────────────────────────
        $reclamations = Reclamation::where('user_id', $user->id)
            ->orderBy('date_creation', 'desc')
            ->get();

        $nbReclamations        = $reclamations->count();
        $nbReclamationsOuvertes = $reclamations->where('statut', 'ouverte')->count();

        // ── Années disponibles pour le modal paiement ────────────
        $anneesDisponibles = collect(range(now()->year, max(2020, now()->year - 5)))
            ->values();

        return view('resident.resident_dash', compact(
            'user',
            'appartement',
            'residence',
            'annee',
            'budgetAnnuel',
            'montantAttendu',
            'montantPaye',
            'resteAPayer',
            'pourcentage',
            'statutAnnee',
            'dateEcheance',
            'historiques',
            'transactions',
            'reclamations',
            'nbReclamations',
            'nbReclamationsOuvertes',
            'anneesDisponibles',
        ));
    }


    // ═══════════════════════════════════════════════════════════════
    // STORE RÉCLAMATION — Créer une réclamation
    // ═══════════════════════════════════════════════════════════════
    public function storeReclamation(Request $request): JsonResponse
    {
        $request->validate([
            'titre'       => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'priorite'    => 'required|in:basse,moyenne,haute,urgente',
        ]);

        $user        = auth()->user();
        $appartement = Appartement::where('proprietaire_id', $user->id)->firstOrFail();

        $reclamation = Reclamation::create([
            'user_id'        => $user->id,
            'appartement_id' => $appartement->id,
            'titre'          => $request->titre,
            'description'    => $request->description,
            'priorite'       => $request->priorite,
            'statut'         => 'ouverte',
            'date_creation'  => now(),
        ]);

        return response()->json([
            'success'     => true,
            'message'     => 'Réclamation soumise avec succès !',
            'reclamation' => [
                'id'          => $reclamation->id,
                'titre'       => $reclamation->titre,
                'priorite'    => $reclamation->priorite,
                'description' => $reclamation->description,
                'statut'      => $reclamation->statut,
                'date'        => Carbon::parse($reclamation->date_creation)->format('d/m/Y'),
                'ref'         => '#' . str_pad($reclamation->id, 3, '0', STR_PAD_LEFT),
            ],
        ]);
    }

    private function calculateCoverage(
    Appartement $appartement,
    int $annee,
    float $montantAttendu,
    float $montantPaye
        ): array {

            $dateSignature = $appartement->date_signature_contrat
                ? Carbon::parse($appartement->date_signature_contrat)
                : Carbon::create($annee, 1, 1);

            $anneeSignature = $dateSignature->year;

            // Première année = commence à la signature
            $dateDebut = $annee == $anneeSignature
                ? $dateSignature->copy()
                : Carbon::create($annee, 1, 1);

            $dateFinTheorique = Carbon::create($annee, 12, 31);

            // nombre réel jours période
            $joursPeriode = $dateDebut->diffInDays($dateFinTheorique) + 1;

            // coût journalier réel
            $coutJournalier = $joursPeriode > 0
                ? $montantAttendu / $joursPeriode
                : 0;

            // jours couverts
            $joursCouverts = $coutJournalier > 0
                ? floor($montantPaye / $coutJournalier)
                : 0;

            $dateEcheance = null;

            if ($joursCouverts > 0) {

                $dateEcheance = $dateDebut->copy()
                    ->addDays($joursCouverts - 1);

                // ne jamais dépasser fin année
                if ($dateEcheance->gt($dateFinTheorique)) {
                    $dateEcheance = $dateFinTheorique->copy();
                }
            }

            return [
                'date_echeance' => $dateEcheance,
                'jours_couverts' => $joursCouverts,
            ];
        }

}