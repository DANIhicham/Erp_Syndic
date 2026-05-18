<?php

namespace App\Http\Controllers;
use App\Models\ConfigurationBudget;
use App\Models\PaiementCotisation;
use App\Models\DepenseResidence;
use App\Models\Appartement;
use Illuminate\Http\Request;

class BudgetController extends Controller
{

        public function index(Request $request)
        {
            $annee = $request->annee ?? date('Y');
            $residenceId = session('residence_id');

            // --- AJOUT : Récupérer toutes les années configurées pour cette résidence ---
            $anneesExistantes = ConfigurationBudget::where('residence_id', $residenceId)
            ->pluck('annee')
            ->toArray();

            // Budget config
            $budget = ConfigurationBudget::where('residence_id', $residenceId)
                ->where('annee', $annee)
                ->first();

            $montantAnnuel = $budget->montant_annuel_fixe ?? 0;

            $nbAppartements = Appartement::where('residence_id', $residenceId)
            ->whereHas('paiementCotisation', function ($query) use ($annee) {
                $query->where('annee_concernee', $annee);
            })
            ->count();

            // Total prévu
            $totalPrevu = $montantAnnuel * $nbAppartements;

            // Total encaissé
            $totalEncaisse = PaiementCotisation::where('annee_concernee', $annee)
                ->whereHas('appartement', fn($q) => $q->where('residence_id', $residenceId))
                ->sum('montant_paye');


            $nbPayes = PaiementCotisation::where('annee_concernee', $annee)
                ->where('statut', 'payé')
                ->whereHas('appartement', function ($q) use ($residenceId) {
                    $q->where('residence_id', $residenceId);
                })
                ->count();

            $nbPartiels = PaiementCotisation::where('annee_concernee', $annee)
                ->where('statut', 'partiel')
                ->whereHas('appartement', function ($q) use ($residenceId) {
                    $q->where('residence_id', $residenceId);
                })
                ->count();

            $nbImpayes = PaiementCotisation::where('annee_concernee', $annee)
                ->where('statut', 'en_retard')
                ->whereHas('appartement', function ($q) use ($residenceId) {
                    $q->where('residence_id', $residenceId);
                })
                ->count();

            // Dépenses
            $totalDepenses = DepenseResidence::where('residence_id', $residenceId)
                ->whereYear('date_debut', $annee)
                ->sum('montant');

            // Solde
            $solde = $totalEncaisse - $totalDepenses;

            // Taux
            $taux = $totalPrevu > 0 ? round(($totalEncaisse / $totalPrevu) * 100) : 0;

            return view('syndic.budget', compact(
                'annee',
                'anneesExistantes',
                'montantAnnuel',
                'nbAppartements',
                'totalPrevu',
                'totalEncaisse',
                'totalDepenses',
                'solde',
                'nbPayes',
                'nbPartiels',
                'nbImpayes',
                'taux'
            ));
        }
        public function store(Request $request)
            {
                ConfigurationBudget::updateOrCreate(
                    [
                        'residence_id' => session('residence_id'),
                        'annee' => $request->annee
                    ],
                    [
                        'montant_annuel_fixe' => $request->montant_annuel_fixe
                    ]
                );

                return back()->with('success', 'Budget enregistré');
            }
}
