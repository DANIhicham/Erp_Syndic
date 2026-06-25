<?php

namespace App\Http\Controllers;

use App\Models\Contrat;
use App\Models\PaiementLoyer;
use App\Models\TransactionLoyer;
use App\Models\User;
use App\Models\Appartement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardLocationController extends Controller
{
    public function index()
    {
        $residenceId   = session('residence_id');
        $moisCourant   = Carbon::now()->month;
        $anneeCourante = Carbon::now()->year;

        // ── Scope résidence ────────────────────────────────────────
        $contratScope = function ($q) use ($residenceId) {
            if ($residenceId) {
                $q->whereHas('appartement', fn($a) => $a->where('residence_id', $residenceId));
            }
        };

        // ══════════════════════════════════════════════════════════
        // KPIs
        // ══════════════════════════════════════════════════════════

        // 1. Contrats actifs
        $contratsActifs = Contrat::where('statut', 'actif')
            ->when($residenceId, $contratScope)
            ->count();

        // 2. Contrats en attente
        $contratsEnAttente = Contrat::where('statut', 'en_attente')
            ->when($residenceId, $contratScope)
            ->count();

        // 3. Revenus loyers du mois courant (SUM des transactions ce mois)
        $revenusMois = TransactionLoyer::whereHas('contrat', $contratScope)
            ->whereMonth('date_paiement', $moisCourant)
            ->whereYear('date_paiement', $anneeCourante)
            ->sum('montant');

        // 4. Locataires actifs
        $locatairesActifs = User::where('role', 'locataire')
            ->where('etat', 'active')
            ->when($residenceId, function ($q) use ($residenceId) {
                $q->whereHas('contratsLocataire.appartement', fn($a) =>
                    $a->where('residence_id', $residenceId)
                );
            })
            ->count();

        // 5. Loyers partiels en cours
        $loyersPartiels = PaiementLoyer::where('statut', 'partiel')
            ->whereHas('contrat', $contratScope)
            ->count();

        $kpis = compact(
            'contratsActifs',
            'contratsEnAttente',
            'revenusMois',
            'locatairesActifs',
            'loyersPartiels'
        );

        // ══════════════════════════════════════════════════════════
        // GRAPHIQUE — Revenus loyers par mois (12 derniers mois)
        // ══════════════════════════════════════════════════════════
        $revenusParMois = [];
        for ($i = 11; $i >= 0; $i--) {
            $date  = Carbon::now()->subMonths($i);
            $total = TransactionLoyer::whereHas('contrat', $contratScope)
                ->whereMonth('date_paiement', $date->month)
                ->whereYear('date_paiement',  $date->year)
                ->sum('montant');

            $revenusParMois[] = [
                'mois'   => $date->translatedFormat('M Y'),
                'montant'=> (float) $total,
            ];
        }

        // ══════════════════════════════════════════════════════════
        // WIDGET 1 — Contrats expirant dans 30 jours
        // ══════════════════════════════════════════════════════════
        $contratsExpirantBientot = Contrat::with(['appartement.residence', 'locataire'])
            ->where('statut', 'actif')
            ->whereNotNull('date_fin')
            ->whereBetween('date_fin', [
                Carbon::today(),
                Carbon::today()->addDays(30),
            ])
            ->when($residenceId, $contratScope)
            ->orderBy('date_fin')
            ->limit(5)
            ->get();

        // ══════════════════════════════════════════════════════════
        // WIDGET 2 — Derniers paiements encaissés (5 derniers)
        // ══════════════════════════════════════════════════════════
        $derniersPaiements = TransactionLoyer::with([
                'contrat.appartement',
                'contrat.locataire',
            ])
            ->whereHas('contrat', $contratScope)
            ->orderByDesc('date_paiement')
            ->limit(8)
            ->get();

        // ══════════════════════════════════════════════════════════
        // WIDGET 3 — Loyers en retard (top impayés)
        // ══════════════════════════════════════════════════════════
        $loyersEnRetard = PaiementLoyer::with([
                'contrat.appartement.residence',
                'contrat.locataire',
            ])
            ->where('statut', 'en_retard')
            ->whereHas('contrat', $contratScope)
            ->orderBy('date_echeance')
            ->limit(8)
            ->get()
            ->map(function ($p) {
                $p->reste = max(0, $p->montant - $p->montant_paye);
                return $p;
            });

        // ══════════════════════════════════════════════════════════
        // WIDGET 4 — Répartition statuts paiements loyer
        // ══════════════════════════════════════════════════════════
        $repartitionStatuts = PaiementLoyer::whereHas('contrat', $contratScope)
            ->select('statut', DB::raw('count(*) as total'))
            ->groupBy('statut')
            ->pluck('total', 'statut')
            ->toArray();

        return view('location.dashboard_location', compact(
            'kpis',
            'revenusParMois',
            'contratsExpirantBientot',
            'derniersPaiements',
            'loyersEnRetard',
            'repartitionStatuts'
        ));
    }
}