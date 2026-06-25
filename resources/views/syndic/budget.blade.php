@extends('layouts.layout')

@section('title', 'Budget Annuel')
@section('page_title', 'Budget Annuel')

@section('content')
      <div class="page-header">
        <div class="ph-left">
          <h2>Budget Annuel</h2>
          <p>Bilan financier —  {{$annee}}</p>
        </div>
        <div class="ph-right">
          <select onchange="window.location.href='?annee='+this.value" class="filter-select"><option>Année</option><option>2026</option><option>2025</option><option>2024</option></select>

          <button class="btn-primary-erp" onclick="openModal('modalBudget')"><i class="fa-solid fa-gear"></i> Configurer budget</button>
        </div>
      </div>

      <!-- Bilan résumé (🆕 bilan-row) -->
      <div class="bilan-row">
        <div class="bilan-block" style="border-top:3px solid var(--green)">
          <div class="bilan-icon" style="background:var(--green-bg);color:var(--green-t)"><i class="fa-solid fa-arrow-trend-up"></i></div>
          <div class="bilan-val" style="color:var(--green-t)">{{ number_format($totalEncaisse, 0, ',', ' ') }} MAD</div>
          <div class="bilan-lbl">Total Cotisations Prévu</div>
        </div>
        <div class="bilan-block" style="border-top:3px solid var(--red)">
          <div class="bilan-icon" style="background:var(--red-bg);color:var(--red-t)"><i class="fa-solid fa-arrow-trend-down"></i></div>
          <div class="bilan-val" style="color:var(--red-t)">{{ number_format($totalDepenses, 0, ',', ' ') }} MAD</div>
          <div class="bilan-lbl">Total Dépenses Réelles</div>
        </div>
        <div class="bilan-block" style="border-top:3px solid var(--teal)">
          <div class="bilan-icon" style="background:var(--teal-bg);color:var(--teal-t)"><i class="fa-solid fa-scale-balanced"></i></div>
          <div class="bilan-val" style="color:var(--teal-t)">{{ $solde >= 0 ? '+' : '' }}{{ number_format($solde, 0, ',', ' ') }} MAD</div>
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
                  <circle cx="18" cy="18" r="15.9" fill="none" stroke="#6366f1" stroke-width="3" stroke-dasharray="{{ $taux }} {{ 100 - $taux }}" stroke-linecap="round"/>
                </svg>
                <div class="budget-ring-pct">{{ $taux }}%</div>
              </div>
              <div class="budget-ring-label">Cotisations collectées</div>
            </div>
            <div style="margin-top:20px">
              <div class="stat-row"><span class="stat-label"><i class="fa-solid fa-circle-check" style="color:var(--green-t)"></i>Payé intégralement</span><span class="stat-value">{{ $nbPayes }} / {{ $nbAppartements }}</span></div>
              <div class="stat-row"><span class="stat-label"><i class="fa-solid fa-code-branch" style="color:var(--amber-t)"></i>Partiel</span><span class="stat-value">{{ $nbPartiels }} / {{ $nbAppartements }}</span></div>
              <div class="stat-row"><span class="stat-label"><i class="fa-solid fa-clock" style="color:var(--red-t)"></i>Impayé</span><span class="stat-value">{{ $nbImpayes }} / {{ $nbAppartements }}</span></div>
            </div>
          </div>
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
        <!-- <div class="form-group"><label class="form-label">Résidence</label><select class="form-control-erp"><option>Résidence Atlas</option><option>Résidence Palmeraie</option></select></div> -->
         <form id="budgetForm" class="form-group" method="POST" action="{{ route('budget.store') }}">
              @csrf
              <label class="form-label">Année <span class="req">*</span></label>
              <select id="anneeSelect" class="form-control-erp" name="annee" class="form-control-erp">
                  @for($i = date('Y'); $i <= date('Y') + 2; $i++)
                      <option value="{{ $i }}">{{ $i }}</option>
                  @endfor
              </select>
              <div class="form-group" style="grid-column:1/-1"><label class="form-label">Montant annuel fixe par appartement (MAD) <span class="req">*</span></label>
              <input type="number" name="montant_annuel_fixe"
                    class="form-control-erp"
                    value="{{ $montantAnnuel }}"
                    required>
              </div>
             
         
        <!-- <div class="form-group"><label class="form-label">Année <span class="req">*</span></label><select class="form-control-erp"><option>2026</option><option>2027</option></select></div>
        <div class="form-group" style="grid-column:1/-1"><label class="form-label">Montant annuel fixe par appartement (MAD) <span class="req">*</span></label><input type="number" class="form-control-erp" value="7680" placeholder="ex: 7680"></div> -->
      </div>
      <div class="info-grid" style="margin-top:16px;margin-bottom:0">
        <div class="info-block"><div class="ib-label">Nbre appartements</div><div class="ib-value accent">{{ $nbAppartements }}</div></div>
        <div class="info-block"><div class="ib-label">Budget total calculé</div><div class="ib-value green">{{ number_format($totalPrevu, 0, ',', ' ') }} MAD</div></div>
        <div class="info-block"><div class="ib-label">Montant / mois</div><div class="ib-value">{{ number_format($montantAnnuel / 12, 0, ',', ' ') }} MAD</div></div>
      </div>
    </div>
    <div class="modal-foot">
      <div class="mf-left"><button class="btn-outline-erp" onclick="closeModal('modalBudget')">Annuler</button></div>
      <div class="mf-right"><button class="btn-primary-erp" type="submit"><i class="fa-solid fa-floppy-disk"></i> Sauvegarder</button></div>
    </div> </form>
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
        { label:'Cotisations', data: @json($cotisationsMensuelles), borderColor:'#22c55e', backgroundColor:g1, fill:true, tension:.45, borderWidth:2.5, pointBackgroundColor:'#22c55e', pointBorderColor:'#fff', pointBorderWidth:2, pointRadius:4 },
        { label:'Dépenses',    data: @json($depensesMensuelles), borderColor:'#ef4444', backgroundColor:g2, fill:true, tension:.45, borderWidth:2.5, pointBackgroundColor:'#ef4444', pointBorderColor:'#fff', pointBorderWidth:2, pointRadius:4 }
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

    // Récupération des années déjà existantes envoyées par le contrôleur
    const anneesExistantes = @json($anneesExistantes ?? []);

    document.getElementById('budgetForm').addEventListener('submit', function(e) {
        // Récupérer l'année sélectionnée dans le menu déroulant
        const anneeSelectionnee = document.getElementById('anneeSelect').value;

        // Vérifier si cette année est déjà dans la base de données
        if (anneesExistantes.includes(parseInt(anneeSelectionnee))) {
            const confirmation = confirm("Un budget existe déjà pour l'année " + anneeSelectionnee + ". Voulez-vous vraiment écraser le montant actuel ?");
            
            if (!confirmation) {
                e.preventDefault(); // Annule l'envoi du formulaire si l'utilisateur clique sur "Annuler"
            }
        }
    });
</script>
@endpush 
@endsection