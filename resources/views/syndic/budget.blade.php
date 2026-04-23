@extends('layouts.layout')

@section('title', 'Budget Annuel')
@section('page_title', 'Budget Annuel')

@section('content')
      <div class="page-header">
        <div class="ph-left">
          <h2>Budget Annuel</h2>
          <p>Bilan financier — Résidence Atlas · 2026</p>
        </div>
        <div class="ph-right">
          <select class="filter-select"><option>2026</option><option>2025</option><option>2024</option></select>
          <button class="btn-outline-erp"><i class="fa-solid fa-print"></i> Imprimer bilan</button>
          <button class="btn-primary-erp" onclick="openModal('modalBudget')"><i class="fa-solid fa-gear"></i> Configurer budget</button>
        </div>
      </div>

      <!-- Bilan résumé (🆕 bilan-row) -->
      <div class="bilan-row">
        <div class="bilan-block" style="border-top:3px solid var(--green)">
          <div class="bilan-icon" style="background:var(--green-bg);color:var(--green-t)"><i class="fa-solid fa-arrow-trend-up"></i></div>
          <div class="bilan-val" style="color:var(--green-t)">61 440 MAD</div>
          <div class="bilan-lbl">Total Cotisations Prévu</div>
        </div>
        <div class="bilan-block" style="border-top:3px solid var(--red)">
          <div class="bilan-icon" style="background:var(--red-bg);color:var(--red-t)"><i class="fa-solid fa-arrow-trend-down"></i></div>
          <div class="bilan-val" style="color:var(--red-t)">39 200 MAD</div>
          <div class="bilan-lbl">Total Dépenses Réelles</div>
        </div>
        <div class="bilan-block" style="border-top:3px solid var(--teal)">
          <div class="bilan-icon" style="background:var(--teal-bg);color:var(--teal-t)"><i class="fa-solid fa-scale-balanced"></i></div>
          <div class="bilan-val" style="color:var(--teal-t)">+8 700 MAD</div>
          <div class="bilan-lbl">Solde (Encaissé − Dépenses)</div>
        </div>
      </div>

      <div class="row g-4 mb-4">
        <div class="col-12 col-xl-8">
          <div class="chart-card">
            <div class="cc-header"><h5 class="cc-title">Évolution mensuelle — Cotisations vs Dépenses</h5><p class="cc-sub">2026 · en MAD</p></div>
            <canvas id="chartBudget" height="90"></canvas>
          </div>
        </div>
        <div class="col-12 col-xl-4">
          <div class="chart-card" style="height:100%">
            <div class="cc-header"><h5 class="cc-title">Taux de collecte</h5><p class="cc-sub">Cotisations encaissées / prévues</p></div>
            <div class="budget-ring-wrap" style="margin-top:16px">
              <div class="budget-ring">
                <svg viewBox="0 0 36 36" width="100" height="100">
                  <circle cx="18" cy="18" r="15.9" fill="none" stroke="#f1f5f9" stroke-width="3"/>
                  <circle cx="18" cy="18" r="15.9" fill="none" stroke="#6366f1" stroke-width="3" stroke-dasharray="78 22" stroke-linecap="round"/>
                </svg>
                <div class="budget-ring-pct">78%</div>
              </div>
              <div class="budget-ring-label">Cotisations collectées</div>
            </div>
            <div style="margin-top:20px">
              <div class="stat-row"><span class="stat-label"><i class="fa-solid fa-circle-check" style="color:var(--green-t)"></i>Payé intégralement</span><span class="stat-value">5 / 8</span></div>
              <div class="stat-row"><span class="stat-label"><i class="fa-solid fa-code-branch" style="color:var(--amber-t)"></i>Partiel</span><span class="stat-value">1 / 8</span></div>
              <div class="stat-row"><span class="stat-label"><i class="fa-solid fa-clock" style="color:var(--red-t)"></i>Impayé</span><span class="stat-value">2 / 8</span></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Détail budget par catégorie -->
      <div class="table-card">
        <div class="table-card-header">
          <div class="tch-left"><h5>Détail par Catégorie</h5><p>Dépenses réelles vs budget prévisionnel 2026</p></div>
        </div>
        <div class="table-responsive">
          <table class="erp-table">
            <thead><tr><th>Catégorie</th><th>Montant Prévu</th><th>Dépensé</th><th>Solde</th><th>Avancement</th></tr></thead>
            <tbody>
              <tr><td><span class="cat-badge maintenance" style="font-size:13px;padding:5px 12px">Maintenance</span></td><td><span class="amount-main">15 000 MAD</span></td><td><span style="font-family:var(--font-h);font-weight:700;color:var(--brand)">12 400 MAD</span></td><td><span style="color:var(--green-t);font-weight:700">+2 600</span></td><td><div style="width:140px"><div class="progress-mini"><div class="progress-mini-fill pfill-blue" style="width:83%"></div></div><span style="font-size:11px;color:var(--text-4)">83%</span></div></td></tr>
              <tr><td><span class="cat-badge securite" style="font-size:13px;padding:5px 12px">Sécurité</span></td><td><span class="amount-main">10 000 MAD</span></td><td><span style="font-family:var(--font-h);font-weight:700;color:var(--brand)">9 400 MAD</span></td><td><span style="color:var(--green-t);font-weight:700">+600</span></td><td><div style="width:140px"><div class="progress-mini"><div class="progress-mini-fill pfill-orange" style="width:94%"></div></div><span style="font-size:11px;color:var(--text-4)">94%</span></div></td></tr>
              <tr><td><span class="cat-badge travaux" style="font-size:13px;padding:5px 12px">Travaux</span></td><td><span class="amount-main">12 000 MAD</span></td><td><span style="font-family:var(--font-h);font-weight:700;color:var(--red-t)">14 800 MAD</span></td><td><span style="color:var(--red-t);font-weight:700">-2 800</span></td><td><div style="width:140px"><div class="progress-mini"><div class="progress-mini-fill pfill-red" style="width:100%"></div></div><span style="font-size:11px;color:var(--red-t)">123% ⚠</span></div></td></tr>
              <tr><td><span class="cat-badge admin" style="font-size:13px;padding:5px 12px">Administration</span></td><td><span class="amount-main">5 000 MAD</span></td><td><span style="font-family:var(--font-h);font-weight:700;color:var(--brand)">2 600 MAD</span></td><td><span style="color:var(--green-t);font-weight:700">+2 400</span></td><td><div style="width:140px"><div class="progress-mini"><div class="progress-mini-fill pfill-green" style="width:52%"></div></div><span style="font-size:11px;color:var(--text-4)">52%</span></div></td></tr>
            </tbody>
          </table>
        </div>
      </div>

<!-- Modal Configurer Budget -->
<div class="modal-overlay" id="modalBudget">
  <div class="modal-panel">
    <div class="modal-head">
      <div><h3 class="mh-title">Configurer Budget</h3><p class="mh-sub">Définir le montant annuel de cotisation par appartement</p></div>
      <div class="mh-right"><button class="modal-close" onclick="closeModal('modalBudget')"><i class="fa-solid fa-xmark"></i></button></div>
    </div>
    <div class="modal-body">
      <div class="form-grid">
        <div class="form-group"><label class="form-label">Résidence</label><select class="form-control-erp"><option>Résidence Atlas</option><option>Résidence Palmeraie</option></select></div>
        <div class="form-group"><label class="form-label">Année <span class="req">*</span></label><select class="form-control-erp"><option>2026</option><option>2027</option></select></div>
        <div class="form-group" style="grid-column:1/-1"><label class="form-label">Montant annuel fixe par appartement (MAD) <span class="req">*</span></label><input type="number" class="form-control-erp" value="7680" placeholder="ex: 7680"></div>
      </div>
      <div class="info-grid" style="margin-top:16px;margin-bottom:0">
        <div class="info-block"><div class="ib-label">Nbre appartements</div><div class="ib-value accent">8</div></div>
        <div class="info-block"><div class="ib-label">Budget total calculé</div><div class="ib-value green">61 440 MAD</div></div>
        <div class="info-block"><div class="ib-label">Montant / mois</div><div class="ib-value">640 MAD</div></div>
      </div>
    </div>
    <div class="modal-foot">
      <div class="mf-left"><button class="btn-outline-erp" onclick="closeModal('modalBudget')">Annuler</button></div>
      <div class="mf-right"><button class="btn-primary-erp" onclick="submitForm('modalBudget','Budget configuré','Configuration du budget 2026 sauvegardée.')"><i class="fa-solid fa-floppy-disk"></i> Sauvegarder</button></div>
    </div>
  </div>
</div>
@push('scripts')

<script>
let budgetChartInst = null;
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
        initBudgetCharts();
    });
</script>
@endpush 
@endsection