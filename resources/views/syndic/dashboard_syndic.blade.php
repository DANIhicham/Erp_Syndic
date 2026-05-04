@extends('layouts.layout')

@section('title', 'Dashboard Syndic')
@section('page_title', 'Dashboard Syndic')

@section('content')

    <div id="page-dashboard">

      <div class="page-header">
        <div class="ph-left">
          <h2>Dashboard Syndic</h2>
          <p>Vue d'ensemble de la Bliving Office — Exercice 2026</p>
        </div>
        <div class="ph-right">
          <button class="btn-outline-erp"><i class="fa-solid fa-print"></i> Rapport</button>

        </div>
      </div>

      <!-- KPI Row 1 -->
      <div class="kpi-row" style="grid-template-columns:repeat(4,1fr)">
        <div class="kpi-card kpi-green" style="animation-delay:.05s">
          <div class="kpi-top">
            <div><div class="kpi-label">Total Cotisations</div><div class="kpi-value">61 440 <small style="font-size:14px;font-weight:500;color:var(--text-4)">MAD</small></div><div class="kpi-sub">Budget annuel 2026</div></div>
            <div class="kpi-icon green"><i class="fa-solid fa-coins"></i></div>
          </div>
          <div class="kpi-progress"><div class="kpi-progress-bar" style="width:78%"></div></div>
        </div>
        <div class="kpi-card kpi-blue" style="animation-delay:.1s">
          <div class="kpi-top">
            <div><div class="kpi-label">Encaissé</div><div class="kpi-value">47 900 <small style="font-size:14px;font-weight:500;color:var(--text-4)">MAD</small></div><div class="kpi-sub">78% collecté</div></div>
            <div class="kpi-icon blue"><i class="fa-solid fa-circle-check"></i></div>
          </div>
          <div class="kpi-progress"><div class="kpi-progress-bar" style="width:78%"></div></div>
        </div>
        <div class="kpi-card kpi-orange" style="animation-delay:.15s">
          <div class="kpi-top">
            <div><div class="kpi-label">Total Dépenses</div><div class="kpi-value">39 200 <small style="font-size:14px;font-weight:500;color:var(--text-4)">MAD</small></div><div class="kpi-sub"><span class="kpi-badge-alert"><i class="fa-solid fa-arrow-trend-up"></i> +12% vs 2025</span></div></div>
            <div class="kpi-icon orange"><i class="fa-solid fa-receipt"></i></div>
          </div>
          <div class="kpi-progress"><div class="kpi-progress-bar" style="width:64%"></div></div>
        </div>
        <div class="kpi-card kpi-teal" style="animation-delay:.2s">
          <div class="kpi-top">
            <div><div class="kpi-label">Solde Syndic</div><div class="kpi-value" style="color:var(--teal-t)">+8 700 <small style="font-size:14px;font-weight:500;color:var(--text-4)">MAD</small></div><div class="kpi-sub">Bilan 2026 (positif)</div></div>
            <div class="kpi-icon teal"><i class="fa-solid fa-scale-balanced"></i></div>
          </div>
          <div class="kpi-progress"><div class="kpi-progress-bar" style="width:100%"></div></div>
        </div>
      </div>

      <!-- KPI Row 2 -->
      <div class="kpi-row" style="grid-template-columns:repeat(4,1fr);margin-top:0">
        <div class="kpi-card kpi-red" style="animation-delay:.25s">
          <div class="kpi-top">
            <div><div class="kpi-label">Impayés</div><div class="kpi-value">13 540 <small style="font-size:14px;font-weight:500;color:var(--text-4)">MAD</small></div><div class="kpi-sub"><span class="kpi-badge-alert"><i class="fa-solid fa-triangle-exclamation"></i> 3 copropriétaires</span></div></div>
            <div class="kpi-icon red"><i class="fa-solid fa-triangle-exclamation"></i></div>
          </div>
          <div class="kpi-progress"><div class="kpi-progress-bar" style="width:22%"></div></div>
        </div>
        <div class="kpi-card kpi-purple" style="animation-delay:.3s">
          <div class="kpi-top">
            <div><div class="kpi-label">Copropriétaires</div><div class="kpi-value">8</div><div class="kpi-sub">Bliving Office</div></div>
            <div class="kpi-icon purple"><i class="fa-solid fa-users"></i></div>
          </div>
          <div class="kpi-progress"><div class="kpi-progress-bar" style="width:100%"></div></div>
        </div>
        <div class="kpi-card kpi-orange" style="animation-delay:.35s">
          <div class="kpi-top">
            <div><div class="kpi-label">Réclamations</div><div class="kpi-value">5</div><div class="kpi-sub"><span class="kpi-badge-alert"><i class="fa-solid fa-fire"></i> 2 urgentes</span></div></div>
            <div class="kpi-icon orange"><i class="fa-solid fa-wrench"></i></div>
          </div>
          <div class="kpi-progress"><div class="kpi-progress-bar" style="width:40%"></div></div>
        </div>
        <div class="kpi-card kpi-blue" style="animation-delay:.4s">
          <div class="kpi-top">
            <div><div class="kpi-label">Documents</div><div class="kpi-value">24</div><div class="kpi-sub">PV, factures, contrats</div></div>
            <div class="kpi-icon blue"><i class="fa-solid fa-folder-open"></i></div>
          </div>
          <div class="kpi-progress"><div class="kpi-progress-bar" style="width:100%"></div></div>
        </div>
      </div>

      <!-- Alertes -->
      <div style="margin-bottom:24px">
        <div class="alert-card alert-danger" style="animation:fadeUp .4s ease .45s both">
          <div class="alert-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
          <div><div class="alert-title">3 cotisations impayées — Relance requise</div><div class="alert-msg">Apt. 12, 15, 22 n'ont pas réglé leur part annuelle (total : 13 540 MAD)</div></div>
        </div>
        <div class="alert-card alert-warn" style="animation:fadeUp .4s ease .5s both">
          <div class="alert-icon"><i class="fa-solid fa-fire"></i></div>
          <div><div class="alert-title">2 réclamations urgentes en attente</div><div class="alert-msg">Problème ascenseur (Apt. 07) + Fuite toiture (Apt. 18) — créées il y a plus de 7 jours</div></div>
        </div>

      </div>

      <!-- Graphiques + activité -->
      <div class="row g-4">
        <div class="col-12 col-xl-8">
          <div class="chart-card">
            <div class="cc-header">
              <div><h5 class="cc-title">Cotisations vs Dépenses — 2026</h5><p class="cc-sub">Mensuel · Bliving Office</p></div>
              <button class="btn-outline-erp" style="font-size:12px;padding:6px 12px">Voir tout</button>
            </div>
            <canvas id="chartDashboard" height="80"></canvas>
          </div>
        </div>
        <div class="col-12 col-xl-4">
          <div class="chart-card" style="height:100%">
            <div class="cc-header"><h5 class="cc-title">Cautisation Résidents</h5><p class="cc-sub">Par catégorie 2026</p></div>
            <canvas id="chartDep" height="180"></canvas>
            <div style="display:flex;flex-direction:column;gap:6px;margin-top:14px">
              <div style="display:flex;align-items:center;justify-content:space-between;font-size:12px"><span style="display:flex;align-items:center;gap:6px"><span style="width:10px;height:10px;border-radius:50%;background:#f59e0b;flex-shrink:0"></span>Partiel</span><strong>20%</strong></div>
              <div style="display:flex;align-items:center;justify-content:space-between;font-size:12px"><span style="display:flex;align-items:center;gap:6px"><span style="width:10px;height:10px;border-radius:50%;background:#ef4444;flex-shrink:0"></span>En retard</span><strong>30%</strong></div>
              <div style="display:flex;align-items:center;justify-content:space-between;font-size:12px"><span style="display:flex;align-items:center;gap:6px"><span style="width:10px;height:10px;border-radius:50%;background:#22c55e;flex-shrink:0"></span>Payé</span><strong>50%</strong></div>
            </div>
          </div>
        </div>
      </div>

    </div>
@push('scripts')

<script>
    let dashChartInst = null, budgetChartInst = null, depChartInst = null;

    function initDashboardCharts() {
    /* Bar chart : Cotisations vs Dépenses */
    const ctx = document.getElementById('chartDashboard');
    if (!ctx || dashChartInst) return;
    dashChartInst = new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
        labels: ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'],
        datasets: [
            {
            label: 'Cotisations encaissées',
            data: [8200, 7800, 9100, 7900, 0, 0, 0, 0, 0, 0, 0, 0],
            backgroundColor: 'rgba(99,102,241,.75)',
            borderRadius: 6,
            },
            {
            label: 'Dépenses',
            data: [4800, 3200, 12400, 5100, 0, 0, 0, 0, 0, 0, 0, 0],
            backgroundColor: 'rgba(239,68,68,.5)',
            borderRadius: 6,
            }
        ]
        },
        options: {
        responsive: true,
        plugins: { legend: { position: 'top', labels: { font: { family:'DM Sans', size:12 }, padding:16 } },
            tooltip: { backgroundColor:'#1e1e2e', titleColor:'#a5b4fc', bodyColor:'#e2e8f0', padding:12, cornerRadius:10,
            callbacks: { label: ctx => ` ${ctx.parsed.y.toLocaleString('fr-FR')} MAD` }
            }
        },
        scales: {
            x: { grid: { color:'rgba(148,163,184,.07)' }, ticks: { color:'#94a3b8', font:{family:'DM Sans',size:12} } },
            y: { grid: { color:'rgba(148,163,184,.07)' }, ticks: { color:'#94a3b8', font:{family:'DM Sans',size:12}, callback: v => v.toLocaleString('fr-FR') } }
        }
        }
    });

    /* Doughnut : Répartition dépenses */
    const ctx2 = document.getElementById('chartDep');
    if (!ctx2 || depChartInst) return;
    depChartInst = new Chart(ctx2.getContext('2d'), {
        type: 'doughnut',
        data: {
        labels: ['Partiel','En retard','Payé'],
        datasets: [{ data:[20,30,50], backgroundColor:['#f59e0b','#ef4444','#22c55e'], borderColor:'#fff', borderWidth:3, hoverOffset:6 }]
        },
        options: {
        cutout:'72%', responsive:true,
        plugins: { legend:{display:false}, tooltip:{backgroundColor:'#1e1e2e',bodyColor:'#e2e8f0',padding:10,cornerRadius:8, callbacks:{label:c=>` ${c.label}: ${c.parsed}%`}} }
        }
    });
    }

    function initBudgetCharts() {
    const ctx = document.getElementById('chartBudget');
    if (!ctx || budgetChartInst) return;
    const g1 = ctx.getContext('2d').createLinearGradient(0,0,0,250);
    g1.addColorStop(0,'rgba(34,197,94,.25)'); g1.addColorStop(1,'rgba(34,197,94,0)');
    const g2 = ctx.getContext('2d').createLinearGradient(0,0,0,250);
    g2.addColorStop(0,'rgba(239,68,68,.2)'); g2.addColorStop(1,'rgba(239,68,68,0)');
    budgetChartInst = new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
        labels: ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'],
        datasets: [
            { label:'Cotisations', data:[8200,7800,9100,7900,null,null,null,null,null,null,null,null], borderColor:'#22c55e', backgroundColor:g1, fill:true, tension:.45, borderWidth:2.5, pointBackgroundColor:'#22c55e', pointBorderColor:'#fff', pointBorderWidth:2, pointRadius:4 },
            { label:'Dépenses',    data:[4800,3200,12400,5100,null,null,null,null,null,null,null,null], borderColor:'#ef4444', backgroundColor:g2, fill:true, tension:.45, borderWidth:2.5, pointBackgroundColor:'#ef4444', pointBorderColor:'#fff', pointBorderWidth:2, pointRadius:4 }
        ]
        },
        options: {
        responsive:true, spanGaps:false,
        plugins: { legend:{position:'top',labels:{font:{family:'DM Sans',size:12},padding:16}}, tooltip:{backgroundColor:'#1e1e2e',titleColor:'#a5b4fc',bodyColor:'#e2e8f0',padding:12,cornerRadius:10,callbacks:{label:c=>` ${c.parsed.y?.toLocaleString('fr-FR')||'—'} MAD`}} },
        scales: {
            x:{grid:{color:'rgba(148,163,184,.07)'},ticks:{color:'#94a3b8',font:{family:'DM Sans',size:12}}},
            y:{grid:{color:'rgba(148,163,184,.07)'},ticks:{color:'#94a3b8',font:{family:'DM Sans',size:12},callback:v=>v.toLocaleString('fr-FR')}}
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