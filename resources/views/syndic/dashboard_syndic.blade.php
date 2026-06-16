@extends('layouts.layout')

@section('title', 'Dashboard Syndic')
@section('page_title', 'Dashboard Syndic')

@section('content')

{{-- ══════════════════════════════════════════════════════════════════
     Préparation des données JSON pour Chart.js (côté PHP → JS)
     On encode ici pour éviter les répétitions dans le script.
     ══════════════════════════════════════════════════════════════════ --}}
@php
    // Données bar chart — encodées JSON pour injection directe dans JS
    $jsonCotisations = json_encode(array_values($cotisationsMensuelles));
    $jsonDepenses    = json_encode(array_values($depensesMensuelles));

    // Données doughnut
    $jsonDoughnut    = json_encode([$pctPartiel, $pctRetard, $pctPaye]);

    // Solde : positif = teal, négatif = rouge
    $soldePositif    = $soldeSyndic >= 0;
    $soldeCouleurCls = $soldePositif ? 'var(--teal-t)' : 'var(--red-t)';
    $soldeSigne      = $soldePositif ? '+' : '';
    $soldePctAffiche = $soldePositif ? min(100, $soldePct) : 0;
@endphp

<div id="page-dashboard">

  {{-- ── Header ──────────────────────────────────────────────── --}}
  <div class="page-header">
    <div class="ph-left">
      <h2>Dashboard Syndic</h2>
      {{-- Nom de la résidence dynamique  --}}
      <p>Vue d'ensemble de {{ $residence?->nom ?? 'la résidence' }} — {{ $annee }}</p>
    </div>
    <div class="ph-right">
      {{-- Filtre année dynamique --}}
      <select onchange="window.location.href='?annee='+this.value" class="filter-select">
        @foreach($anneesDisponibles as $a)
          <option value="{{ $a }}" @selected($a == $annee)>{{ $a }}</option>
        @endforeach
      </select>
      <!-- <button class="btn-outline-erp">
        <i class="fa-solid fa-print"></i> Rapport
      </button> -->
    </div>
  </div>

  {{-- ══════════════════════════════════════════════════════════
       KPI ROW 1 — Cotisations / Encaissé / Dépenses / Solde
  ══════════════════════════════════════════════════════════ --}}
  <div class="kpi-row" style="grid-template-columns:repeat(4,1fr)">

    {{-- Cotisations attendues --}}
    <div class="kpi-card kpi-green" style="animation-delay:.05s">
      <div class="kpi-top">
        <div>
          <div class="kpi-label">Total Cotisations</div>
          <div class="kpi-value">
            {{ number_format($totalCotisationsAttendu, 0, ',', ' ') }}
            <small style="font-size:14px;font-weight:500;color:var(--text-4)">MAD</small>
          </div>
          <div class="kpi-sub">Budget annuel {{ $annee }}</div>
        </div>
        <div class="kpi-icon green"><i class="fa-solid fa-coins"></i></div>
      </div>
      {{-- Barre = % encaissé vs attendu --}}
      <div class="kpi-progress">
        <div class="kpi-progress-bar" style="width:100%"></div>
      </div>
    </div>

    {{-- Encaissé --}}
    <div class="kpi-card kpi-blue" style="animation-delay:.1s">
      <div class="kpi-top">
        <div>
          <div class="kpi-label">Encaissé</div>
          <div class="kpi-value">
            {{ number_format($totalEncaisse, 0, ',', ' ') }}
            <small style="font-size:14px;font-weight:500;color:var(--text-4)">MAD</small>
          </div>
          <div class="kpi-sub">{{ $tauxCollecte }}% collecté</div>
        </div>
        <div class="kpi-icon blue"><i class="fa-solid fa-circle-check"></i></div>
      </div>
      <div class="kpi-progress">
        <div class="kpi-progress-bar" style="width:{{ $tauxCollecte }}%"></div>
      </div>
    </div>

    {{-- Dépenses payées --}}
    <div class="kpi-card kpi-orange" style="animation-delay:.15s">
      <div class="kpi-top">
        <div>
          <div class="kpi-label">Total Dépenses</div>
          <div class="kpi-value">
            {{ number_format($totalDepenses, 0, ',', ' ') }}
            <small style="font-size:14px;font-weight:500;color:var(--text-4)">MAD</small>
          </div>
          <div class="kpi-sub">
            @if($variationDepenses !== null)
              <span class="kpi-badge-alert">
                <i class="fa-solid fa-arrow-trend-{{ $variationDepenses >= 0 ? 'up' : 'down' }}"></i>
                {{ $variationDepenses >= 0 ? '+' : '' }}{{ $variationDepenses }}% vs {{ $annee - 1 }}
              </span>
            @else
              <span>Dépenses {{ $annee }}</span>
            @endif
          </div>
        </div>
        <div class="kpi-icon orange"><i class="fa-solid fa-receipt"></i></div>
      </div>
      <div class="kpi-progress">
        <div class="kpi-progress-bar" style="width:{{ $depensePct }}%"></div>
      </div>
    </div>

    {{-- Solde syndic --}}
    <div class="kpi-card kpi-teal" style="animation-delay:.2s">
      <div class="kpi-top">
        <div>
          <div class="kpi-label">Solde Syndic</div>
          <div class="kpi-value" style="color:{{ $soldeCouleurCls }}">
            {{ $soldeSigne }}{{ number_format($soldeSyndic, 0, ',', ' ') }}
            <small style="font-size:14px;font-weight:500;color:var(--text-4)">MAD</small>
          </div>
          <div class="kpi-sub">
            Bilan {{ $annee }} ({{ $soldePositif ? 'positif' : 'déficitaire' }})
          </div>
        </div>
        <div class="kpi-icon teal"><i class="fa-solid fa-scale-balanced"></i></div>
      </div>
      <div class="kpi-progress">
        <div class="kpi-progress-bar" style="width:{{ $soldePctAffiche }}%"></div>
      </div>
    </div>

  </div>{{-- /kpi-row 1 --}}

  {{-- ══════════════════════════════════════════════════════════
       KPI ROW 2 — Impayés / Copropriétaires / Réclamations / Résolues
  ══════════════════════════════════════════════════════════ --}}
  <div class="kpi-row" style="grid-template-columns:repeat(4,1fr);margin-top:0">

    {{-- Impayés --}}
    <div class="kpi-card kpi-red" style="animation-delay:.25s">
      <div class="kpi-top">
        <div>
          <div class="kpi-label">Impayés</div>
          <div class="kpi-value">
            {{ number_format($totalImpayes, 0, ',', ' ') }}
            <small style="font-size:14px;font-weight:500;color:var(--text-4)">MAD</small>
          </div>
          <div class="kpi-sub">
            @if($nbImpayes > 0)
              <span class="kpi-badge-alert">
                <i class="fa-solid fa-triangle-exclamation"></i>
                {{ $nbImpayes }} copropriétaire{{ $nbImpayes > 1 ? 's' : '' }}
              </span>
            @else
              <span>Aucun impayé </span>
            @endif
          </div>
        </div>
        <div class="kpi-icon red"><i class="fa-solid fa-triangle-exclamation"></i></div>
      </div>
      <div class="kpi-progress">
        <div class="kpi-progress-bar" style="width:{{ $impayePct }}%"></div>
      </div>
    </div>

    {{-- Copropriétaires --}}
    <div class="kpi-card kpi-purple" style="animation-delay:.3s">
      <div class="kpi-top">
        <div>
          <div class="kpi-label">Copropriétaires</div>
          <div class="kpi-value">{{ $nbCoproprietaires }}</div>
          <div class="kpi-sub">{{ $residence?->nom ?? 'Résidence' }}</div>
        </div>
        <div class="kpi-icon purple"><i class="fa-solid fa-users"></i></div>
      </div>
      <div class="kpi-progress">
        <div class="kpi-progress-bar" style="width:100%"></div>
      </div>
    </div>

    {{-- Réclamations actives --}}
    <div class="kpi-card kpi-orange" style="animation-delay:.35s">
      <div class="kpi-top">
        <div>
          <div class="kpi-label">Réclamations</div>
          <div class="kpi-value">{{ $nbReclamations }}</div>
          <div class="kpi-sub">
            @if($nbReclamationsUrgentes > 0)
              <span class="kpi-badge-alert">
                <i class="fa-solid fa-fire"></i>
                {{ $nbReclamationsUrgentes }} urgente{{ $nbReclamationsUrgentes > 1 ? 's' : '' }}
              </span>
            @else
              <span>Aucune urgente</span>
            @endif
          </div>
        </div>
        <div class="kpi-icon orange"><i class="fa-solid fa-wrench"></i></div>
      </div>
      <div class="kpi-progress">
        <div class="kpi-progress-bar" style="width:100%"></div>
      </div>
    </div>

    {{-- Réclamations résolues --}}
    <div class="kpi-card kpi-green" style="animation-delay:.15s">
      <div class="kpi-top">
        <div>
          <div class="kpi-label">Résolues</div>
          <div class="kpi-value">{{ $nbReclamationsResolues }}</div>
          <div class="kpi-sub">Cette année</div>
        </div>
        <div class="kpi-icon green"><i class="fa-solid fa-check-circle"></i></div>
      </div>
    </div>

  </div>{{-- /kpi-row 2 --}}

  {{-- ══════════════════════════════════════════════════════════
       ALERTES DYNAMIQUES
  ══════════════════════════════════════════════════════════ --}}
  @if(count($alertes) > 0)
  <div style="margin-bottom:24px">
    @foreach($alertes as $i => $alerte)
      @php
        $delay = 0.45 + ($i * 0.05);
        $clsMap = ['danger' => 'alert-danger', 'warn' => 'alert-warn', 'info' => 'alert-info'];
        $cls    = $clsMap[$alerte['type']] ?? 'alert-warn';
      @endphp
      <div class="alert-card {{ $cls }}" style="animation:fadeUp .4s ease {{ $delay }}s both">
        <div class="alert-icon"><i class="fa-solid {{ $alerte['icon'] }}"></i></div>
        <div>
          <div class="alert-title">{{ $alerte['titre'] }}</div>
          <div class="alert-msg">{{ $alerte['msg'] }}</div>
        </div>
      </div>
    @endforeach
  </div>
  @endif

  {{-- ══════════════════════════════════════════════════════════
       GRAPHIQUES
  ══════════════════════════════════════════════════════════ --}}
  <div class="row g-4">

    {{-- Bar chart : Cotisations vs Dépenses --}}
    <div class="col-12 col-xl-8">
      <div class="chart-card">
        <div class="cc-header">
          <div>
            <h5 class="cc-title">Cotisations vs Dépenses — {{ $annee }}</h5>
            <p class="cc-sub">Mensuel · {{ $residence?->nom ?? 'Résidence' }}</p>
          </div>
        </div>
        <canvas id="chartDashboard" height="80"></canvas>
      </div>
    </div>

    {{-- Doughnut : Répartition statuts cotisations --}}
    <div class="col-12 col-xl-4">
      <div class="chart-card" style="height:100%">
        <div class="cc-header">
          <h5 class="cc-title">Cotisations Résidents</h5>
          <p class="cc-sub">Par statut {{ $annee }}</p>
        </div>
        <canvas id="chartDep" height="180"></canvas>
        {{-- Légende dynamique --}}
        <div style="display:flex;flex-direction:column;gap:6px;margin-top:14px">
          <div style="display:flex;align-items:center;justify-content:space-between;font-size:12px">
            <span style="display:flex;align-items:center;gap:6px">
              <span style="width:10px;height:10px;border-radius:50%;background:#f59e0b;flex-shrink:0"></span>
              Partiel
            </span>
            <strong>{{ $pctPartiel }}%</strong>
          </div>
          <div style="display:flex;align-items:center;justify-content:space-between;font-size:12px">
            <span style="display:flex;align-items:center;gap:6px">
              <span style="width:10px;height:10px;border-radius:50%;background:#ef4444;flex-shrink:0"></span>
              En retard
            </span>
            <strong>{{ $pctRetard }}%</strong>
          </div>
          <div style="display:flex;align-items:center;justify-content:space-between;font-size:12px">
            <span style="display:flex;align-items:center;gap:6px">
              <span style="width:10px;height:10px;border-radius:50%;background:#22c55e;flex-shrink:0"></span>
              Payé
            </span>
            <strong>{{ $pctPaye }}%</strong>
          </div>
        </div>
      </div>
    </div>

  </div>{{-- /row --}}

</div>{{-- /page-dashboard --}}

@push('scripts')
<script>
/*
 * ══════════════════════════════════════════════════════════════════
 *  CHART.JS — Données injectées depuis PHP (variables Blade → JS)
 * ══════════════════════════════════════════════════════════════════
 *
 *  Les tableaux PHP sont encodés en JSON et injectés directement.
 *  Cela évite tout appel AJAX et rend les graphiques immédiatement
 *  disponibles au chargement de la page.
 */

// ── Données reçues du controller ──────────────────────────────────
const DATA_COTISATIONS = {!! $jsonCotisations !!}; // [jan, fév, …, déc] — null si mois futur
const DATA_DEPENSES    = {!! $jsonDepenses !!};    // idem
const DATA_DOUGHNUT    = {!! $jsonDoughnut !!};    // [pctPartiel, pctRetard, pctPaye]

let dashChartInst = null;
let depChartInst  = null;
let budgetChartInst = null;

// ── Bar chart : Cotisations vs Dépenses ───────────────────────────
function initDashboardCharts() {
    const ctx = document.getElementById('chartDashboard');
    if (!ctx || dashChartInst) return;

    dashChartInst = new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'],
            datasets: [
                {
                    label: 'Cotisations encaissées',
                    // DATA_COTISATIONS : tableau PHP → JSON → JS
                    data: DATA_COTISATIONS,
                    backgroundColor: 'rgba(99,102,241,.75)',
                    borderRadius: 6,
                },
                {
                    label: 'Dépenses',
                    // DATA_DEPENSES : tableau PHP → JSON → JS
                    data: DATA_DEPENSES,
                    backgroundColor: 'rgba(239,68,68,.5)',
                    borderRadius: 6,
                }
            ]
        },
        options: {
            responsive: true,
            spanGaps: false, // les null (mois futurs) créent une rupture visuelle
            plugins: {
                legend: {
                    position: 'top',
                    labels: { font: { family: 'DM Sans', size: 12 }, padding: 16 }
                },
                tooltip: {
                    backgroundColor: '#1e1e2e',
                    titleColor: '#a5b4fc',
                    bodyColor: '#e2e8f0',
                    padding: 12,
                    cornerRadius: 10,
                    callbacks: {
                        label: ctx => ` ${(ctx.parsed.y ?? 0).toLocaleString('fr-FR')} MAD`
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(148,163,184,.07)' },
                    ticks: { color: '#94a3b8', font: { family: 'DM Sans', size: 12 } }
                },
                y: {
                    grid: { color: 'rgba(148,163,184,.07)' },
                    ticks: {
                        color: '#94a3b8',
                        font: { family: 'DM Sans', size: 12 },
                        callback: v => v.toLocaleString('fr-FR')
                    }
                }
            }
        }
    });

    // ── Doughnut : Statuts cotisations ────────────────────────────
    const ctx2 = document.getElementById('chartDep');
    if (!ctx2 || depChartInst) return;

    depChartInst = new Chart(ctx2.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Partiel', 'En retard', 'Payé'],
            // DATA_DOUGHNUT = [pctPartiel, pctRetard, pctPaye] depuis PHP
            datasets: [{
                data: DATA_DOUGHNUT,
                backgroundColor: ['#f59e0b', '#ef4444', '#22c55e'],
                borderColor: '#fff',
                borderWidth: 3,
                hoverOffset: 6
            }]
        },
        options: {
            cutout: '72%',
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e1e2e',
                    bodyColor: '#e2e8f0',
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: c => ` ${c.label} : ${c.parsed}%`
                    }
                }
            }
        }
    });
}

// ── initBudgetCharts : graphe line (inchangé, conservé tel quel) ──
function initBudgetCharts() {
    const ctx = document.getElementById('chartBudget');
    if (!ctx || budgetChartInst) return;

    const g1 = ctx.getContext('2d').createLinearGradient(0, 0, 0, 250);
    g1.addColorStop(0, 'rgba(34,197,94,.25)');
    g1.addColorStop(1, 'rgba(34,197,94,0)');

    const g2 = ctx.getContext('2d').createLinearGradient(0, 0, 0, 250);
    g2.addColorStop(0, 'rgba(239,68,68,.2)');
    g2.addColorStop(1, 'rgba(239,68,68,0)');

    budgetChartInst = new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'],
            datasets: [
                {
                    label: 'Cotisations',
                    // Réutilise les mêmes données dynamiques
                    data: DATA_COTISATIONS,
                    borderColor: '#22c55e',
                    backgroundColor: g1,
                    fill: true, tension: .45, borderWidth: 2.5,
                    pointBackgroundColor: '#22c55e', pointBorderColor: '#fff',
                    pointBorderWidth: 2, pointRadius: 4
                },
                {
                    label: 'Dépenses',
                    data: DATA_DEPENSES,
                    borderColor: '#ef4444',
                    backgroundColor: g2,
                    fill: true, tension: .45, borderWidth: 2.5,
                    pointBackgroundColor: '#ef4444', pointBorderColor: '#fff',
                    pointBorderWidth: 2, pointRadius: 4
                }
            ]
        },
        options: {
            responsive: true, spanGaps: false,
            plugins: {
                legend: { position: 'top', labels: { font: { family: 'DM Sans', size: 12 }, padding: 16 } },
                tooltip: {
                    backgroundColor: '#1e1e2e', titleColor: '#a5b4fc', bodyColor: '#e2e8f0',
                    padding: 12, cornerRadius: 10,
                    callbacks: { label: c => ` ${c.parsed.y?.toLocaleString('fr-FR') || '—'} MAD` }
                }
            },
            scales: {
                x: { grid: { color: 'rgba(148,163,184,.07)' }, ticks: { color: '#94a3b8', font: { family: 'DM Sans', size: 12 } } },
                y: { grid: { color: 'rgba(148,163,184,.07)' }, ticks: { color: '#94a3b8', font: { family: 'DM Sans', size: 12 }, callback: v => v.toLocaleString('fr-FR') } }
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    initDashboardCharts();
});
</script>
@endpush
@endsection