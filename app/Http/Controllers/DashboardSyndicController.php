<?php

namespace App\Http\Controllers;

use App\Models\Appartement;
use App\Models\BudgetAnnuelResume;
use App\Models\ConfigurationBudget;
use App\Models\PaiementCotisation;
use App\Models\PaiementDepense;
use App\Models\Reclamation;
use App\Models\TransactionPaiement;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardSyndicController extends Controller
{
    public function index(Request $request): View
    {
        // ══════════════════════════════════════════════════════════════
        //  CONTEXTE : résidence connectée + année sélectionnée
        // ══════════════════════════════════════════════════════════════

        /** @var int $residenceId Résidence de la session courante */
        $residenceId = (int) session('residence_id');

        /** @var int $annee Année filtrée, défaut = année en cours */
        $annee = (int) request('annee', now()->year);

        // Toutes les années disponibles (pour le <select>)
        $anneesDisponibles = $this->getAnneesDisponibles($residenceId);

        // Nom de la résidence pour le header
        $residence = \App\Models\Residence::find($residenceId);

        // ══════════════════════════════════════════════════════════════
        //  1. KPI — COTISATIONS
        // ══════════════════════════════════════════════════════════════

        /**
         * Total cotisations attendues pour l'année.
         * Source : paiement_cotisations.montant_attendu
         * Filtre  : appartements.residence_id + annee_concernee
         */
        $totalCotisationsAttendu = PaiementCotisation::whereHas('appartement', fn($q) =>
                $q->where('residence_id', $residenceId))
            ->where('annee_concernee', $annee)
            ->sum('montant_attendu');

        /**
         * Total encaissé pour l'année.
         * Source : transactions_paiements.montant
         * Filtre  : appartements.residence_id + annee
         */
        $totalEncaisse = TransactionPaiement::whereHas('appartement', fn($q) =>
                $q->where('residence_id', $residenceId))
            ->where('annee', $annee)
            ->sum('montant');

        /**
         * Taux de collecte (%) : encaissé / attendu
         */
        $tauxCollecte = $totalCotisationsAttendu > 0
            ? min(100, round(($totalEncaisse / $totalCotisationsAttendu) * 100))
            : 0;

        /**
         * Impayés = somme des montants attendus non couverts par les paiements.
         * Calculé directement depuis paiement_cotisations pour les statuts
         * « en_retard » ou « partiel ».
         */
        $totalImpayes = PaiementCotisation::whereHas('appartement', fn($q) =>
                $q->where('residence_id', $residenceId))
            ->where('annee_concernee', $annee)
            ->whereIn('statut', ['en_retard', 'partiel'])
            ->selectRaw('SUM(montant_attendu - montant_paye) as total')
            ->value('total') ?? 0;

        /**
         * Nombre de copropriétaires en retard (comptage distinct).
         */
        $nbImpayes = PaiementCotisation::whereHas('appartement', fn($q) =>
                $q->where('residence_id', $residenceId))
            ->where('annee_concernee', $annee)
            ->whereIn('statut', ['en_retard', 'partiel'])
            ->distinct('user_id')
            ->count('user_id');

        // ══════════════════════════════════════════════════════════════
        //  2. KPI — DÉPENSES
        // ══════════════════════════════════════════════════════════════

        /**
         * Total dépenses payées sur l'année.
         * Source : paiements_depenses.montant WHERE statut = 'paye'
         * Filtre  : dépenses liées à la résidence + année de la période
         */
        $totalDepenses = PaiementDepense::whereHas('depense', fn($q) =>
                $q->where('residence_id', $residenceId))
            ->where('statut', 'paye')
            ->whereYear('periode_debut', $annee)
            ->sum('montant');

        /**
         * Variation dépenses vs année précédente (en %).
         */
        $depensesAnPrec = PaiementDepense::whereHas('depense', fn($q) =>
                $q->where('residence_id', $residenceId))
            ->where('statut', 'paye')
            ->whereYear('periode_debut', $annee - 1)
            ->sum('montant');

        $variationDepenses = $depensesAnPrec > 0
            ? round((($totalDepenses - $depensesAnPrec) / $depensesAnPrec) * 100, 1)
            : null;

        /**
         * Dépenses en attente ou en retard sur l'année (alertes).
         */
        $depensesEnRetard = PaiementDepense::with(['depense'])
            ->whereHas('depense', fn($q) =>
                $q->where('residence_id', $residenceId)->where('is_active', true))
            ->where('statut', 'en_retard')
            ->whereYear('periode_debut', $annee)
            ->orderBy('periode_fin')
            // ->take(5)
            ->get();

        // ══════════════════════════════════════════════════════════════
        //  3. KPI — SOLDE SYNDIC
        // ══════════════════════════════════════════════════════════════

        /**
         * Solde = Encaissé - Dépenses payées
         * Positif = excédent, Négatif = déficit
         */
        $soldeSyndic = $totalEncaisse - $totalDepenses;

        /**
         * Pourcentage de la barre de progression du solde.
         * 100% si solde ≥ 0, proportion sinon.
         */
        $soldePct = $totalCotisationsAttendu > 0
            ? min(100, max(0, round(($soldeSyndic / $totalCotisationsAttendu) * 100)))
            : 0;

        // // Aussi disponible depuis budget_annuel_resumes si calculé périodiquement
        // $budgetResume = BudgetAnnuelResume::where('residence_id', $residenceId)
        //     ->where('annee', $annee)
        //     ->first();

        // ══════════════════════════════════════════════════════════════
        //  4. KPI — COPROPRIÉTAIRES
        // ══════════════════════════════════════════════════════════════

        /**
         * Nombre de copropriétaires distincts dans la résidence.
         * Source : appartements.proprietaire_id DISTINCT
         */
        $nbCoproprietaires = Appartement::where('residence_id', $residenceId)
            ->distinct('proprietaire_id')
            ->count('proprietaire_id');

        // ══════════════════════════════════════════════════════════════
        //  5. KPI — RÉCLAMATIONS
        // ══════════════════════════════════════════════════════════════

        /**
         * Toutes les réclamations actives (non résolues) de la résidence.
         * On filtre via appartement.residence_id.
         */
        $nbReclamations = Reclamation::whereHas('appartement', fn($q) =>
                $q->where('residence_id', $residenceId))
            ->whereIn('statut', ['ouverte', 'en_cours'])
            ->count();

        $nbReclamationsUrgentes = Reclamation::whereHas('appartement', fn($q) =>
                $q->where('residence_id', $residenceId))
            ->where('priorite', 'urgente')
            ->whereIn('statut', ['ouverte', 'en_cours'])
            ->count();

        $nbReclamationsResolues = Reclamation::whereHas('appartement', fn($q) =>
                $q->where('residence_id', $residenceId))
            ->where('statut', 'resolue')
            ->whereYear('date_creation', $annee)
            ->count();

        /**
         * Réclamations urgentes détaillées pour les alertes.
         */
        $reclamationsUrgentes = Reclamation::with(['appartement', 'user'])
            ->whereHas('appartement', fn($q) =>
                $q->where('residence_id', $residenceId))
            ->where('priorite', 'urgente')
            ->whereIn('statut', ['ouverte', 'en_cours'])
            ->orderBy('date_creation')
            ->take(5)
            ->get();

        // ══════════════════════════════════════════════════════════════
        //  6. ALERTES DYNAMIQUES
        // ══════════════════════════════════════════════════════════════

        $alertes = $this->buildAlertes(
            residenceId       : $residenceId,
            annee             : $annee,
            nbImpayes         : $nbImpayes,
            totalImpayes      : $totalImpayes,
            reclamationsUrg   : $reclamationsUrgentes,
            depensesEnRetard  : $depensesEnRetard,
            soldeSyndic       : $soldeSyndic,
        );

        // ══════════════════════════════════════════════════════════════
        //  7. CHART.JS — DONNÉES MENSUELLES (Bar chart)
        // ══════════════════════════════════════════════════════════════


        $cotisationsMensuelles = $this->calculerCotisationsMensuelles(
            $residenceId,
            $annee
        );

        $depensesMensuelles = $this->calculerDepensesMensuelles(
            $residenceId,
            $annee
        );

        // Masquer les mois futurs
        $moisCourant = now()->year == $annee
            ? now()->month
            : 12;

        for ($m = 1; $m <= 12; $m++) {

            if ($m > $moisCourant) {

                $cotisationsMensuelles[$m - 1] = null;
                $depensesMensuelles[$m - 1] = null;
            }
        }

        // ══════════════════════════════════════════════════════════════
        //  8. CHART.JS — DOUGHNUT (statuts cotisations)
        // ══════════════════════════════════════════════════════════════

        /**
         * Répartition des statuts de paiement_cotisations pour l'année.
         * Labels : Payé / Partiel / En retard
         */
        $statutsCotisations = PaiementCotisation::whereHas('appartement', fn($q) =>
                $q->where('residence_id', $residenceId))
            ->where('annee_concernee', $annee)
            ->selectRaw('statut, COUNT(*) as nb')
            ->groupBy('statut')
            ->pluck('nb', 'statut'); // ['paye' => 4, 'partiel' => 2, 'en_retard' => 3]

        $totalStatuts = $statutsCotisations->sum() ?: 1; // éviter division par 0

        $pctPaye    = round(($statutsCotisations->get('payé',    0) / $totalStatuts) * 100);
        $pctPartiel = round(($statutsCotisations->get('partiel', 0) / $totalStatuts) * 100);
        $pctRetard  = round(($statutsCotisations->get('en_retard', 0) / $totalStatuts) * 100);

        // ══════════════════════════════════════════════════════════════
        //  9. PROGRESSION DES BARRES KPI
        // ══════════════════════════════════════════════════════════════

        $depensePct = $totalCotisationsAttendu > 0
            ? min(100, round(($totalDepenses / $totalCotisationsAttendu) * 100))
            : 0;

        $impayePct = $totalCotisationsAttendu > 0
            ? min(100, round(($totalImpayes / $totalCotisationsAttendu) * 100))
            : 0;

        $reclamPct = $nbCoproprietaires > 0
            ? min(100, round(($nbReclamations / $nbCoproprietaires) * 100))
            : 0;

        // ══════════════════════════════════════════════════════════════
        //  10. PASSAGE À LA VUE
        // ══════════════════════════════════════════════════════════════

        return view('syndic.dashboard_syndic', compact(
            // Contexte
            'residence',
            'annee',
            'anneesDisponibles',

            // KPI cotisations
            'totalCotisationsAttendu',
            'totalEncaisse',
            'tauxCollecte',
            'totalImpayes',
            'nbImpayes',

            // KPI dépenses
            'totalDepenses',
            'variationDepenses',
            'depensesEnRetard',

            // KPI solde
            'soldeSyndic',
            'soldePct',
            

            // KPI copropriétaires
            'nbCoproprietaires',

            // KPI réclamations
            'nbReclamations',
            'nbReclamationsUrgentes',
            'nbReclamationsResolues',
            'reclamationsUrgentes',

            // Alertes
            'alertes',

            // Chart.js — données mensuelles
            // 'cotisationsMensuelles',
            // 'depensesMensuelles',
            'cotisationsMensuelles',
            'depensesMensuelles',

            // Chart.js — doughnut statuts
            'pctPaye',
            'pctPartiel',
            'pctRetard',

            // Progressions barres
            'depensePct',
            'impayePct',
            'reclamPct',
        ));
    }

    // ══════════════════════════════════════════════════════════════════
    //  PRIVÉ — Construction des alertes dynamiques
    // ══════════════════════════════════════════════════════════════════

    private function buildAlertes(
        int   $residenceId,
        int   $annee,
        int   $nbImpayes,
        float $totalImpayes,
        mixed $reclamationsUrg,
        mixed $depensesEnRetard,
        float $soldeSyndic,
    ): array {
        $alertes = [];

        // ── Alerte 1 : Impayés cotisations ───────────────────────────
        if ($nbImpayes > 0) {
            // Récupérer les numéros d'appartements concernés
            $aptsImpayes = PaiementCotisation::with('appartement')
                ->whereHas('appartement', fn($q) => $q->where('residence_id', $residenceId))
                ->where('annee_concernee', $annee)
                ->whereIn('statut', ['en_retard', 'partiel'])
                ->get()
                ->map(fn($p) => 'Apt. ' . $p->appartement?->numero)
                ->filter()
                ->join(', ');

            $alertes[] = [
                'type'  => 'danger',
                'icon'  => 'fa-triangle-exclamation',
                'titre' => "{$nbImpayes} cotisation(s) impayée(s) — Relance requise",
                'msg'   => "{$aptsImpayes} n'ont pas réglé leur part annuelle "
                         . "(total : " . number_format($totalImpayes, 0, ',', ' ') . " MAD)",
            ];
        }

        // ── Alerte 2 : Réclamations urgentes ─────────────────────────
        if ($reclamationsUrg->count() > 0) {
            $details = $reclamationsUrg->map(function ($r) {
                $apt   = $r->appartement ? 'Apt. ' . $r->appartement->numero : '?';
                $jours = (int) Carbon::parse($r->date_creation)->diffInDays(now());
                return "{$r->titre} ({$apt}) — il y a {$jours} j";
            })->join(' · ');

            $alertes[] = [
                'type'  => 'warn',
                'icon'  => 'fa-fire',
                'titre' => $reclamationsUrg->count() . " réclamation(s) urgente(s) en attente",
                'msg'   => $details,
            ];
        }

        // ── Alerte 3 : Dépenses en retard ────────────────────────────
        if ($depensesEnRetard->count() > 0) {
            $montantRetard = $depensesEnRetard->sum('montant');
            $titres = $depensesEnRetard->map(fn($p) => $p->depense?->titre)->filter()->join(', ');

            $alertes[] = [
                'type'  => 'warn',
                'icon'  => 'fa-receipt',
                'titre' => $depensesEnRetard->count() . " dépense(s) en retard de paiement",
                'msg'   => "{$titres} — total : " . number_format($montantRetard, 0, ',', ' ') . " MAD",
            ];
        }

        // ── Alerte 4 : Budget déficitaire ────────────────────────────
        if ($soldeSyndic < 0) {
            $alertes[] = [
                'type'  => 'danger',
                'icon'  => 'fa-scale-unbalanced',
                'titre' => "Solde syndic négatif — déficit budgétaire",
                'msg'   => "Le budget est déficitaire de "
                         . number_format(abs($soldeSyndic), 0, ',', ' ')
                         . " MAD. Action corrective requise.",
            ];
        }

        return $alertes;
    }

    // ══════════════════════════════════════════════════════════════════
    //  PRIVÉ — Années disponibles pour le filtre
    // ══════════════════════════════════════════════════════════════════

    private function getAnneesDisponibles(int $residenceId): array
    {
        $fromCotisations = PaiementCotisation::whereHas('appartement', fn($q) =>
                $q->where('residence_id', $residenceId))
            ->distinct()
            ->pluck('annee_concernee');

        $fromDepenses = PaiementDepense::whereHas('depense', fn($q) =>
                $q->where('residence_id', $residenceId))
            ->selectRaw('YEAR(periode_debut) as annee')
            ->distinct()
            ->pluck('annee');

        return $fromCotisations
            ->merge($fromDepenses)
            ->push(now()->year)
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();
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

            $moisDebut = 1;

            if ($appartement->date_signature_contrat) {

                $dateSignature = Carbon::parse(
                    $appartement->date_signature_contrat
                );

                if ($dateSignature->year == $annee) {
                    $moisDebut = $dateSignature->month;
                }
            }

            $nbMois = 13 - $moisDebut;

            if ($nbMois <= 0) {
                continue;
            }

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
            ->where('statut', 'paye')
            ->whereYear('periode_debut', $annee)
            ->get();

        foreach ($paiements as $paiement) {

            $depense = $paiement->depense;

            // =========================
            // MENSUEL
            // =========================
            if ($depense->type === 'mensuel') {

                $dateDebut = \Carbon\Carbon::parse(
                    $paiement->periode_debut
                );

                $dateFin = Carbon::parse(
                    $paiement->periode_fin
                );

                $nbMois = $dateDebut->diffInMonths($dateFin) + 1;

                if ($nbMois <= 0) {
                    continue;
                }

                $mensuel = $paiement->montant / $nbMois;

                for ($m = $dateDebut->month; $m <= $dateFin->month; $m++) {

                    $mois[$m] += round($mensuel, 2);
                }
            }

            // =========================
            // TRIMESTRIEL
            // =========================
            elseif ($depense->type === 'trimestriel') {

                $dateDebut = Carbon::parse(
                    $paiement->periode_debut
                );

                $dateFin = Carbon::parse(
                    $paiement->periode_fin
                );

                $nbTrimestres = ceil(
                    ($dateDebut->diffInMonths($dateFin) + 1) / 3
                );

                if ($nbTrimestres <= 0) {
                    continue;
                }

                $montantTrim = $paiement->montant / $nbTrimestres;

                for ($m = $dateDebut->month; $m <= $dateFin->month; $m += 3) {

                    $mois[$m] += round($montantTrim, 2);
                }
            }

            // =========================
            // UNIQUE
            // =========================
            elseif ($depense->type === 'unique') {

                if (!$paiement->date_paiement) {
                    continue;
                }

                $moisPaiement = Carbon::parse(
                    $paiement->periode_fin
                )->month;

                $montant = $paiement->montant_paye > 0
                    ? $paiement->montant_paye
                    : $paiement->montant;

                $mois[$moisPaiement] += $montant;
            }

            // =========================
            // VARIABLE
            // =========================
            elseif ($depense->type === 'variable') {

                if (!$paiement->date_paiement) {
                    continue;
                }

                $moisPaiement = Carbon::parse(
                    $paiement->periode_fin
                )->month;

                $mois[$moisPaiement] += $paiement->montant;
            }
        }

        return array_values($mois);
    }

}