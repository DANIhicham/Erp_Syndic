<?php

namespace App\Http\Controllers;

use App\Models\TransactionPaiement;
use App\Models\ConfigurationBudget;
use App\Models\PaiementCotisation;
use App\Models\PaiementDepense;
use App\Models\DepenseResidence;
use App\Models\Appartement;
use Illuminate\Http\Request;
use Carbon\Carbon;

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

            $cotisationsMensuelles = $this->calculerCotisationsMensuelles(
                $residenceId,
                $annee
            );

            $depensesMensuelles = $this->calculerDepensesMensuelles(
                $residenceId,
                $annee
            );

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
                'cotisationsMensuelles',
                'depensesMensuelles',
                'taux'
            ));
        }

        public function store(Request $request)
        {
            $request->validate([
                'annee' => 'required|integer',
                'montant_annuel_fixe' => 'required|numeric|min:0'
            ]);

            $residenceId = session('residence_id');

            // ── Sauvegarde budget ─────────────────────────────
            $budget = ConfigurationBudget::updateOrCreate(
                [
                    'residence_id' => $residenceId,
                    'annee' => $request->annee
                ],
                [
                    'montant_annuel_fixe' => $request->montant_annuel_fixe
                ]
            );

            // ─────────────────────────────────────────────
            // CRÉER COTISATIONS SI ANNÉE N'EXISTE PAS
            // ─────────────────────────────────────────────

            $appartements = Appartement::where('residence_id', $residenceId)->get();

            foreach ($appartements as $appartement) {

                if (!$appartement->date_signature_contrat) {
                    continue;
                }

                $dateSignature = Carbon::parse($appartement->date_signature_contrat);

                // Ne pas créer avant signature contrat
                if ($request->annee < $dateSignature->year) {
                    continue;
                }

                // Vérifier existence
                $cotisation = PaiementCotisation::where('appartement_id', $appartement->id)
                    ->where('annee_concernee', $request->annee)
                    ->first();

                // Déjà existe
                if ($cotisation) {
                    continue;
                }

                // ─────────────────────────────────────────
                // PRORATA
                // ─────────────────────────────────────────

                $montantAnnuel = (float) $budget->montant_annuel_fixe;

                if ($request->annee == $dateSignature->year) {

                    $debut = $dateSignature->copy();
                    $fin = Carbon::create($request->annee, 12, 31);

                    $joursRestants = $debut->diffInDays($fin) + 1;
                    $joursAnnee = $fin->dayOfYear;

                    $montant = $joursAnnee > 0
                        ? ($montantAnnuel / $joursAnnee) * $joursRestants
                        : 0;

                } else {

                    $montant = $montantAnnuel;
                }

                // ─────────────────────────────────────────
                // STATUT INITIAL
                // ─────────────────────────────────────────

                $statut = $request->annee > now()->year
                    ? 'en_attente'
                    : 'en_retard';

                // ─────────────────────────────────────────
                // CREATE
                // ─────────────────────────────────────────

                PaiementCotisation::create([
                    'appartement_id'   => $appartement->id,
                    'user_id'          => $appartement->proprietaire_id,
                    'annee_concernee' => $request->annee,
                    'montant_attendu' => round($montant, 2),
                    'montant_paye'    => 0,
                    'statut'          => $statut,
                    'commentaire'     => 'Créé automatiquement depuis budget',
                ]);
            }

            // ── Cotisations de la résidence pour cette année ─
            $cotisations = PaiementCotisation::where('annee_concernee', $request->annee)
                ->whereHas('appartement', function ($q) use ($residenceId) {
                    $q->where('residence_id', $residenceId);
                })
                ->get();

            foreach ($cotisations as $cotisation) {

                $appartement = Appartement::find($cotisation->appartement_id);

                if (!$appartement) {
                    continue;
                }

                $montantAnnuel = (float) $budget->montant_annuel_fixe;

                // ─────────────────────────────────────────────
                // PRORATA
                // ─────────────────────────────────────────────

                if ($appartement->date_signature_contrat) {

                    $dateSignature = Carbon::parse($appartement->date_signature_contrat);

                    $anneeSignature = $dateSignature->year;

                    // Première année
                    if ($cotisation->annee_concernee == $anneeSignature) {

                        $dateDebut = $dateSignature->copy();
                        $dateFin   = Carbon::create($anneeSignature, 12, 31);

                        $joursPeriode = $dateDebut->diffInDays($dateFin) + 1;

                        $montant = $joursPeriode > 0
                            ? ($montantAnnuel / $dateFin->dayOfYear) * $joursPeriode
                            : 0;

                    } else {

                        $montant = $montantAnnuel;
                    }

                } else {

                    $montant = $montantAnnuel;
                }

                $montantAttendu = round($montant, 2);

                // ─────────────────────────────────────────────
                // TOTAL PAYÉ
                // ─────────────────────────────────────────────

                $montantPaye = (float) TransactionPaiement::where('appartement_id', $appartement->id)
                    ->where('annee', $request->annee)
                    ->sum('montant');

                // ─────────────────────────────────────────────
                // CALCUL COUVERTURE
                // ─────────────────────────────────────────────

                $dateSignature = $appartement->date_signature_contrat
                    ? Carbon::parse($appartement->date_signature_contrat)
                    : Carbon::create($request->annee, 1, 1);

                $anneeSignature = $dateSignature->year;

                $dateDebut = $request->annee == $anneeSignature
                    ? $dateSignature->copy()
                    : Carbon::create($request->annee, 1, 1);

                $dateFinTheorique = Carbon::create($request->annee, 12, 31);

                $joursPeriode = $dateDebut->diffInDays($dateFinTheorique) + 1;

                $coutJournalier = $joursPeriode > 0
                    ? $montantAttendu / $joursPeriode
                    : 0;

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

                // ─────────────────────────────────────────────
                // CALCUL STATUT
                // ─────────────────────────────────────────────

                $anneeCourante = now()->year;

                // Aucun budget
                if ($montantAttendu <= 0) {

                    $statut = 'en_attente';

                }
                // Totalement payé
                elseif ($montantPaye >= $montantAttendu) {

                    $statut = 'payé';

                }
                // ── ANNÉE FUTURE ─────────────────────────────
                elseif ($request->annee > $anneeCourante) {

                    if ($montantPaye > 0) {
                        $statut = 'partiel';
                    } else {
                        $statut = 'en_attente';
                    }

                }
                // ── ANNÉE COURANTE / PASSÉE ─────────────────
                elseif ($montantPaye <= 0) {

                    $statut = 'en_retard';

                }
                elseif ($dateEcheance && now()->lte($dateEcheance)) {

                    $statut = 'partiel';

                }
                else {

                    $statut = 'en_retard';
                }
                // ─────────────────────────────────────────────
                // UPDATE
                // ─────────────────────────────────────────────

                $cotisation->montant_attendu = $montantAttendu;
                $cotisation->montant_paye    = $montantPaye;
                $cotisation->statut          = $statut;

                $cotisation->save();
            }

            return back()->with(
                'success',
                'Budget enregistré et cotisations recalculées avec prorata.'
            );
        }

        private function calculerCotisationsMensuelles($residenceId, $annee)
        {
            $mois = array_fill(1, 12, 0);

            $cotisations = PaiementCotisation::with('appartement')
                ->where('annee_concernee', $annee)
                ->whereHas('appartement', function ($q) use ($residenceId) {
                    $q->where('residence_id', $residenceId);
                })
                ->get();

            foreach ($cotisations as $cotisation) {

                $appartement = $cotisation->appartement;

                // mois début selon date signature
                $moisDebut = 1;

                if ($appartement->date_signature_contrat) {

                    $dateSignature = Carbon::parse(
                        $appartement->date_signature_contrat
                    );

                    // seulement si la signature est dans la même année
                    if ($dateSignature->year == $annee) {
                        $moisDebut = $dateSignature->month;
                    }
                }

                // nombre de mois restants
                $nbMois = 13 - $moisDebut;

                if ($nbMois <= 0) {
                    continue;
                }

                // montant mensuel réparti
                $mensuel = $cotisation->montant_attendu / $nbMois;

                $reste = $cotisation->montant_paye;

                for ($m = $moisDebut; $m <= 12; $m++) {

                    if ($reste <= 0) {
                        break;
                    }

                    $valeur = min($mensuel, $reste);

                    $mois[$m] += round($valeur, 2);

                    $reste -= $valeur;
                }
            }

            return array_values($mois);
        }

        private function calculerDepensesMensuelles($residenceId, $annee)
        {
            $mois = array_fill(1, 12, 0);

            $paiements = PaiementDepense::with('depense')
                ->whereHas('depense', function ($q) use ($residenceId) {
                    $q->where('residence_id', $residenceId);
                })
                ->whereYear('periode_debut', $annee)
                ->get();

            foreach ($paiements as $paiement) {

                $depense = $paiement->depense;

                // uniquement les paiements réellement payés
                if ($paiement->statut !== 'paye') {
                    continue;
                }

                $moisPaiement = Carbon::parse(
                    $paiement->periode_fin
                )->month;

                // =========================
                // MENSUEL
                // =========================
                if ($depense->type === 'mensuel') {

                    $mois[$moisPaiement] += $paiement->montant;
                }

                // =========================
                // TRIMESTRIEL
                // =========================
                elseif ($depense->type === 'trimestriel') {

                    $mois[$moisPaiement] += $paiement->montant;
                }

                // =========================
                // UNIQUE
                // =========================
                elseif ($depense->type === 'unique') {

                    $montant = $paiement->montant_paye > 0
                        ? $paiement->montant_paye
                        : $paiement->montant;

                    $mois[$moisPaiement] += $montant;
                }

                // =========================
                // VARIABLE
                // =========================
                elseif ($depense->type === 'variable') {

                    $mois[$moisPaiement] += $paiement->montant;
                }
            }

            return array_values($mois);
        }
}
