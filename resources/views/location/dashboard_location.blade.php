@extends('layouts.layout')

@section('title', 'Dashboard Location')
@section('page_title', 'Dashboard Location')

@section('content')

{{-- ════════════════════════════════════════════════════════════════
     PAGE HEADER
════════════════════════════════════════════════════════════════ --}}
<div class="page-header">
  <div class="ph-left">
    <h2>Dashboard Location</h2>
    <p>Vue d'ensemble de l'activité locative — {{ now()->translatedFormat('F Y') }}</p>
  </div>
  <div class="ph-right">
    <a href="{{ route('contrats.index') }}" class="btn-outline-erp">
      <i class="fa-solid fa-file-contract"></i> Voir les contrats
    </a>
    <a href="{{ route('paiements-loyer.index') }}" class="btn-primary-erp">
      <i class="fa-solid fa-money-bill-wave"></i> Paiements loyers
    </a>
  </div>
</div>

{{-- ════════════════════════════════════════════════════════════════
     KPI CARDS — 5 cartes
════════════════════════════════════════════════════════════════ --}}
<div class="kpi-row" style="grid-template-columns: repeat(5, 1fr)">

  {{-- 1. Contrats actifs --}}
  <div class="kpi-card kpi-green" style="animation-delay:.05s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Contrats actifs</div>
        <div class="kpi-value">{{ $kpis['contratsActifs'] }}</div>
        <div class="kpi-sub">baux en cours</div>
      </div>
      <div class="kpi-icon green">
        <i class="fa-solid fa-file-contract"></i>
      </div>
    </div>
  </div>

  {{-- 2. Contrats en attente --}}
  <div class="kpi-card kpi-orange" style="animation-delay:.10s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">En attente</div>
        <div class="kpi-value">{{ $kpis['contratsEnAttente'] }}</div>
        <div class="kpi-sub">contrats à valider</div>
      </div>
      <div class="kpi-icon orange">
        <i class="fa-solid fa-hourglass-half"></i>
      </div>
    </div>
  </div>

  {{-- 3. Revenus du mois --}}
  <div class="kpi-card kpi-blue" style="animation-delay:.15s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Revenus du mois</div>
        <div class="kpi-value" style="font-size:18px">
          {{ number_format($kpis['revenusMois'], 0, ',', ' ') }}
        </div>
        <div class="kpi-sub">MAD encaissés</div>
      </div>
      <div class="kpi-icon blue">
        <i class="fa-solid fa-money-bill-trend-up"></i>
      </div>
    </div>
  </div>

  {{-- 4. Locataires actifs --}}
  <div class="kpi-card" style="animation-delay:.20s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Locataires actifs</div>
        <div class="kpi-value" style="color:var(--brand)">{{ $kpis['locatairesActifs'] }}</div>
        <div class="kpi-sub">comptes actifs</div>
      </div>
      <div class="kpi-icon" style="background:#eef2ff">
        <i class="fa-solid fa-users" style="color:var(--brand)"></i>
      </div>
    </div>
  </div>

  {{-- 5. Loyers partiels --}}
  <div class="kpi-card kpi-red" style="animation-delay:.25s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Loyers partiels</div>
        <div class="kpi-value">{{ $kpis['loyersPartiels'] }}</div>
        <div class="kpi-sub">paiements incomplets</div>
      </div>
      <div class="kpi-icon red">
        <i class="fa-solid fa-circle-half-stroke"></i>
      </div>
    </div>
  </div>

</div>

{{-- ════════════════════════════════════════════════════════════════
     LIGNE 1 — Graphique revenus + Répartition statuts
════════════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:16px">

  {{-- ── Graphique revenus par mois ── --}}
  <div class="table-card" style="padding:20px">
    <div class="table-card-header" style="padding:0 0 16px 0;border-bottom:1px solid var(--border-1);margin-bottom:20px">
      <div class="tch-left">
        <h5>Revenus loyers — 12 derniers mois</h5>
        <p>Encaissements mensuels en MAD</p>
      </div>
    </div>
    <div style="position:relative;height:220px">
      <canvas id="chartRevenus"></canvas>
    </div>
  </div>

  {{-- ── Répartition statuts paiements ── --}}
  <div class="table-card" style="padding:20px">
    <div class="table-card-header" style="padding:0 0 16px 0;border-bottom:1px solid var(--border-1);margin-bottom:20px">
      <div class="tch-left">
        <h5>Statuts paiements</h5>
        <p>Répartition des échéances loyer</p>
      </div>
    </div>

    @php
      $statutLabels = [
        'paye'       => ['label' => 'Payés',       'class' => 'active',   'color' => '#059669'],
        'partiel'    => ['label' => 'Partiels',    'class' => 'partiel',  'color' => '#d97706'],
        'en_attente' => ['label' => 'En attente',  'class' => 'late',     'color' => '#0891b2'],
        'en_retard'  => ['label' => 'En retard',   'class' => 'inactive', 'color' => '#dc2626'],
      ];
      $totalStatuts = array_sum($repartitionStatuts);
    @endphp

    <div style="display:flex;flex-direction:column;gap:10px">
      @foreach($statutLabels as $key => $cfg)
        @php
          $count = $repartitionStatuts[$key] ?? 0;
          $pct   = $totalStatuts > 0 ? round($count / $totalStatuts * 100) : 0;
        @endphp
        <div>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
            <div style="display:flex;align-items:center;gap:7px">
              <div style="width:10px;height:10px;border-radius:50%;background:{{ $cfg['color'] }}"></div>
              <span style="font-size:13px;color:var(--text-2);font-weight:600">{{ $cfg['label'] }}</span>
            </div>
            <div style="display:flex;align-items:center;gap:8px">
              <span style="font-size:13px;font-weight:700;color:var(--text-1)">{{ $count }}</span>
              <span style="font-size:11.5px;color:var(--text-4)">({{ $pct }}%)</span>
            </div>
          </div>
          <div style="height:6px;background:var(--border-1);border-radius:3px;overflow:hidden">
            <div style="width:{{ $pct }}%;height:100%;background:{{ $cfg['color'] }};border-radius:3px;transition:width .6s ease"></div>
          </div>
        </div>
      @endforeach

      @if($totalStatuts === 0)
        <div style="text-align:center;padding:24px;color:var(--text-4);font-size:13px">
          Aucune donnée disponible
        </div>
      @endif
    </div>

  </div>

</div>

{{-- ════════════════════════════════════════════════════════════════
     LIGNE 2 — Contrats expirant bientôt + Derniers paiements
════════════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">

  {{-- ── Contrats expirant dans 30 jours ── --}}
  <div class="table-card">
    <div class="table-card-header">
      <div class="tch-left">
        <h5><i class="fa-solid fa-calendar-xmark" style="color:var(--orange-t);margin-right:6px"></i>Contrats expirant bientôt</h5>
        <p>Dans les 30 prochains jours</p>
      </div>
    </div>

    @if($contratsExpirantBientot->isEmpty())
      <div style="text-align:center;padding:32px;color:var(--text-4);font-size:13.5px">
        <i class="fa-solid fa-circle-check" style="font-size:24px;color:var(--green-t);display:block;margin-bottom:8px"></i>
        Aucun contrat n'expire dans les 30 prochains jours
      </div>
    @else
      <div class="table-responsive">
        <table class="erp-table">
          <thead>
            <tr>
              <th>Appartement</th>
              <th>Locataire</th>
              <th>Date fin</th>
              <th>Jours restants</th>
            </tr>
          </thead>
          <tbody>
            @foreach($contratsExpirantBientot as $contrat)
              @php
                $jours     = \Carbon\Carbon::today()->diffInDays($contrat->date_fin);
                $urgence   = $jours <= 7 ? 'var(--red-t)' : ($jours <= 15 ? 'var(--orange-t)' : 'var(--text-3)');
              @endphp
              <tr>
                <td>
                  <span style="font-size:13.5px;font-weight:600;color:var(--text-1)">
                    Apt. {{ $contrat->appartement?->numero ?? '—' }}
                  </span>
                  @if($contrat->appartement?->residence)
                    <span style="display:block;font-size:11.5px;color:var(--text-4)">
                      {{ $contrat->appartement->residence->nom }}
                    </span>
                  @endif
                </td>
                <td>
                  @if($contrat->locataire)
                    <span style="font-size:13px;color:var(--text-2)">
                      {{ $contrat->locataire->prenom }} {{ $contrat->locataire->nom }}
                    </span>
                  @else
                    <span style="color:var(--text-4)">—</span>
                  @endif
                </td>
                <td>
                  <span class="date-text" style="color:{{ $urgence }}">
                    {{ \Carbon\Carbon::parse($contrat->date_fin)->format('d/m/Y') }}
                  </span>
                </td>
                <td>
                  <span style="font-weight:700;color:{{ $urgence }};font-size:13.5px">
                    {{ $jours }}j
                  </span>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>

  {{-- ── Derniers paiements encaissés ── --}}
  <div class="table-card">
    <div class="table-card-header">
      <div class="tch-left">
        <h5><i class="fa-solid fa-money-bill-wave" style="color:var(--green-t);margin-right:6px"></i>Derniers encaissements</h5>
        <p>8 dernières transactions loyer</p>
      </div>
    </div>

    @if($derniersPaiements->isEmpty())
      <div style="text-align:center;padding:32px;color:var(--text-4);font-size:13.5px">
        <i class="fa-solid fa-receipt" style="font-size:24px;display:block;margin-bottom:8px"></i>
        Aucun paiement enregistré
      </div>
    @else
      <div class="table-responsive">
        <table class="erp-table">
          <thead>
            <tr>
              <th>Locataire</th>
              <th>Appartement</th>
              <th>Montant</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            @foreach($derniersPaiements as $tx)
              @php
                $palette   = ['#4f46e5','#0891b2','#059669','#d97706','#7c3aed','#db2777'];
                $loc       = $tx->contrat?->locataire;
                $color     = $palette[($loc?->id ?? 0) % count($palette)];
                $initiales = $loc
                  ? strtoupper(substr($loc->prenom,0,1)).strtoupper(substr($loc->nom,0,1))
                  : '—';
              @endphp
              <tr>
                <td>
                  <div class="tenant-cell">
                    <div class="t-avatar" style="background:{{ $color }};width:30px;height:30px;font-size:11px;border-radius:8px">
                      {{ $initiales }}
                    </div>
                    <div>
                      <span class="t-name" style="font-size:13px">
                        {{ $loc ? $loc->prenom.' '.$loc->nom : '—' }}
                      </span>
                    </div>
                  </div>
                </td>
                <td>
                  <span style="font-size:13px;color:var(--text-3)">
                    Apt. {{ $tx->contrat?->appartement?->numero ?? '—' }}
                  </span>
                </td>
                <td>
                  <span style="font-weight:700;color:var(--green-t);font-size:13.5px">
                    {{ number_format($tx->montant, 0, ',', ' ') }} MAD
                  </span>
                </td>
                <td>
                  <span class="date-text">
                    {{ \Carbon\Carbon::parse($tx->date_paiement)->format('d/m/Y') }}
                  </span>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>

</div>

{{-- ════════════════════════════════════════════════════════════════
     LIGNE 3 — Loyers en retard (pleine largeur)
════════════════════════════════════════════════════════════════ --}}
<div class="table-card">
  <div class="table-card-header">
    <div class="tch-left">
      <h5>
        <i class="fa-solid fa-triangle-exclamation" style="color:var(--red-t);margin-right:6px"></i>
        Loyers en retard
      </h5>
      <p>Échéances dépassées non soldées</p>
    </div>
    <div class="tch-right">
      <a href="{{ route('paiements-loyer.index') }}" class="btn-outline-erp" style="font-size:12.5px;padding:7px 14px">
        Voir tout <i class="fa-solid fa-arrow-right" style="margin-left:4px"></i>
      </a>
    </div>
  </div>

  @if($loyersEnRetard->isEmpty())
    <div style="text-align:center;padding:32px;color:var(--text-4);font-size:13.5px">
      <i class="fa-solid fa-circle-check" style="font-size:24px;color:var(--green-t);display:block;margin-bottom:8px"></i>
      Aucun loyer en retard. Excellente situation !
    </div>
  @else
    <div class="table-responsive">
      <table class="erp-table">
        <thead>
          <tr>
            <th>Locataire</th>
            <th>Appartement</th>
            <th>Période</th>
            <th>Montant attendu</th>
            <th>Déjà payé</th>
            <th>Reste dû</th>
            <th>Échéance</th>
            <th>Retard</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach($loyersEnRetard as $p)
            @php
              $loc       = $p->contrat?->locataire;
              $apt       = $p->contrat?->appartement;
              $palette   = ['#4f46e5','#0891b2','#059669','#d97706','#7c3aed','#db2777'];
              $color     = $palette[($loc?->id ?? 0) % count($palette)];
              $initiales = $loc
                ? strtoupper(substr($loc->prenom,0,1)).strtoupper(substr($loc->nom,0,1))
                : '—';
              $joursRetard = \Carbon\Carbon::parse($p->date_echeance)->diffInDays(\Carbon\Carbon::today());
            @endphp
            <tr>
              <td>
                <div class="tenant-cell">
                  <div class="t-avatar" style="background:{{ $color }};width:30px;height:30px;font-size:11px;border-radius:8px">
                    {{ $initiales }}
                  </div>
                  <div>
                    <span class="t-name" style="font-size:13px">
                      {{ $loc ? $loc->prenom.' '.$loc->nom : '—' }}
                    </span>
                    <span class="t-type">{{ $loc?->telephone ?? '—' }}</span>
                  </div>
                </div>
              </td>
              <td>
                <span style="font-size:13px;color:var(--text-2)">
                  Apt. {{ $apt?->numero ?? '—' }}
                </span>
                @if($apt?->residence)
                  <span style="display:block;font-size:11.5px;color:var(--text-4)">
                    {{ $apt->residence->nom }}
                  </span>
                @endif
              </td>
              <td>
                <span style="font-size:12.5px;color:var(--text-3)">
                  {{ \Carbon\Carbon::parse($p->periode_debut)->format('d/m/Y') }}
                  →
                  {{ \Carbon\Carbon::parse($p->periode_fin)->format('d/m/Y') }}
                </span>
              </td>
              <td>
                <span style="font-weight:700;font-size:13.5px">
                  {{ number_format($p->montant, 0, ',', ' ') }} MAD
                </span>
              </td>
              <td>
                <span style="color:var(--green-t);font-weight:600;font-size:13.5px">
                  {{ number_format($p->montant_paye, 0, ',', ' ') }} MAD
                </span>
              </td>
              <td>
                <span style="color:var(--red-t);font-weight:700;font-size:13.5px">
                  {{ number_format($p->reste, 0, ',', ' ') }} MAD
                </span>
              </td>
              <td>
                <span class="date-text" style="color:var(--red-t)">
                  {{ \Carbon\Carbon::parse($p->date_echeance)->format('d/m/Y') }}
                </span>
              </td>
              <td>
                <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;font-size:12px;font-weight:700;background:var(--red-bg);color:var(--red-t);border:1px solid rgba(239,68,68,.2)">
                  <i class="fa-solid fa-clock" style="font-size:10px"></i>
                  {{ $joursRetard }}j
                </span>
              </td>
              <td>
                <a
                  href="{{ route('paiements-loyer.index') }}"
                  class="ra-btn encaisser"
                  title="Encaisser ce loyer"
                >
                  <i class="fa-solid fa-money-bill-wave"></i>
                </a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>

@endsection


@push('scripts')
{{-- Chart.js depuis CDN --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>

/* ── Données PHP → JS ────────────────────────────────────────────── */
const revenusData = @json($revenusParMois);

/* ── Graphique Revenus par mois (Chart.js barres) ───────────────── */
document.addEventListener('DOMContentLoaded', () => {

  const labels   = revenusData.map(r => r.mois);
  const montants = revenusData.map(r => r.montant);

  const ctx = document.getElementById('chartRevenus').getContext('2d');

  // Lire la couleur brand depuis les CSS vars
  const brand = getComputedStyle(document.documentElement)
    .getPropertyValue('--brand').trim() || '#4f46e5';

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Revenus loyers (MAD)',
        data: montants,
        backgroundColor: montants.map((v, i) =>
          i === montants.length - 1
            ? brand                       // mois courant : couleur pleine
            : brand + '55'               // autres mois : transparents
        ),
        borderColor: brand,
        borderWidth: 1.5,
        borderRadius: 6,
        borderSkipped: false,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => ' ' + ctx.parsed.y.toLocaleString('fr-FR') + ' MAD',
          },
          backgroundColor: '#fff',
          titleColor: '#1e293b',
          bodyColor: '#64748b',
          borderColor: '#e2e8f0',
          borderWidth: 1,
          padding: 10,
          cornerRadius: 8,
        },
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: {
            font: { size: 11 },
            color: '#94a3b8',
            maxRotation: 45,
          },
        },
        y: {
          beginAtZero: true,
          grid: {
            color: '#f1f5f9',
            drawBorder: false,
          },
          ticks: {
            font: { size: 11 },
            color: '#94a3b8',
            callback: v => v.toLocaleString('fr-FR') + ' MAD',
          },
        },
      },
    },
  });
});

</script>
@endpush
