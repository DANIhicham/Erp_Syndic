@extends('layouts.layout')

@section('title', 'Paiements Loyers')
@section('page_title', 'Paiements Loyers')

@section('content')

{{-- ════════════════════════════════════════════════════════════════
     PAGE HEADER
════════════════════════════════════════════════════════════════ --}}
<div class="page-header">
  <div class="ph-left">
    <h2>Paiements Loyers</h2>
    <p>Suivi des échéances, encaissements et historique des loyers</p>
  </div>
  <div class="ph-right">
    <button class="btn-outline-erp" onclick="showToast('Export en cours de développement.', 'warning')">
      <i class="fa-solid fa-download"></i> Exporter
    </button>
    <button class="btn-primary-erp" onclick="openEncaisserGlobal()">
      <i class="fa-solid fa-money-bill-wave"></i> Encaisser Paiement
    </button>
  </div>
</div>

{{-- ════════════════════════════════════════════════════════════════
     KPI CARDS
════════════════════════════════════════════════════════════════ --}}
<div class="kpi-row" style="grid-template-columns: repeat(5, 1fr)">

  <div class="kpi-card kpi-blue" style="animation-delay:.05s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Encaissés ce mois</div>
        <div class="kpi-value">{{ number_format($kpis['encaisses_mois'], 0, ',', ' ') }}</div>
        <div class="kpi-sub">MAD encaissés</div>
      </div>
      <div class="kpi-icon blue"><i class="fa-solid fa-money-bill-trend-up"></i></div>
    </div>
  </div>

  <div class="kpi-card kpi-red" style="animation-delay:.10s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Impayés</div>
        <div class="kpi-value">{{ number_format($kpis['impayes'], 0, ',', ' ') }}</div>
        <div class="kpi-sub">MAD en attente</div>
      </div>
      <div class="kpi-icon red"><i class="fa-solid fa-triangle-exclamation"></i></div>
    </div>
  </div>

  <div class="kpi-card" style="animation-delay:.15s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">En retard</div>
        <div class="kpi-value" style="color:var(--red-t)">{{ $kpis['en_retard'] }}</div>
        <div class="kpi-sub">échéances dépassées</div>
      </div>
      <div class="kpi-icon" style="background:var(--red-bg)">
        <i class="fa-solid fa-clock" style="color:var(--red-t)"></i>
      </div>
    </div>
  </div>

  <div class="kpi-card kpi-orange" style="animation-delay:.20s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Partiels</div>
        <div class="kpi-value">{{ $kpis['partiel'] }}</div>
        <div class="kpi-sub">paiements incomplets</div>
      </div>
      <div class="kpi-icon orange"><i class="fa-solid fa-circle-half-stroke"></i></div>
    </div>
  </div>

  <div class="kpi-card kpi-green" style="animation-delay:.25s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Payés</div>
        <div class="kpi-value">{{ $kpis['paye'] }}</div>
        <div class="kpi-sub">échéances soldées</div>
      </div>
      <div class="kpi-icon green"><i class="fa-solid fa-circle-check"></i></div>
    </div>
  </div>

</div>

{{-- ════════════════════════════════════════════════════════════════
     FILTER BAR
════════════════════════════════════════════════════════════════ --}}
<div class="filter-bar">

  <div class="filter-search">
    <i class="fa-solid fa-magnifying-glass"></i>
    <input type="text" id="searchInput" placeholder="Appartement, locataire, référence…" oninput="filterTable()">
  </div>

  <select id="filterStatut" class="filter-select" onchange="filterTable()">
    <option value="">Tous les statuts</option>
    <option value="paye">Payé</option>
    <option value="partiel">Partiel</option>
    <option value="en_attente">En attente</option>
    <option value="en_retard">En retard</option>
  </select>

  <select id="filterAnnee" class="filter-select" onchange="filterTable()">
    <option value="">Toutes années</option>
    @foreach($annees as $annee)
      <option value="{{ $annee }}" {{ $annee == now()->year ? 'selected' : '' }}>{{ $annee }}</option>
    @endforeach
  </select>

  <select id="filterMois" class="filter-select" onchange="filterTable()">
    <option value="">Tous mois</option>
    @foreach(['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'] as $i => $m)
      <option value="{{ $i + 1 }}" {{ ($i + 1) == now()->month ? 'selected' : '' }}>{{ $m }}</option>
    @endforeach
  </select>

  <select id="filterAppartement" class="filter-select" onchange="filterTable()">
    <option value="">Tous appartements</option>
    @foreach($appartements as $apt)
      <option value="{{ $apt->id }}">Apt. {{ $apt->numero }}</option>
    @endforeach
  </select>

  <select id="filterLocataire" class="filter-select" onchange="filterTable()">
    <option value="">Tous locataires</option>
    @foreach($locataires as $loc)
      <option value="{{ $loc->id }}">{{ $loc->prenom }} {{ $loc->nom }}</option>
    @endforeach
  </select>

  <div class="filter-divider"></div>

  <div class="filter-count">
    <strong id="countVisible">{{ $paiements->count() }}</strong>
    échéance{{ $paiements->count() !== 1 ? 's' : '' }}
  </div>

</div>

{{-- ════════════════════════════════════════════════════════════════
     TABLEAU PRINCIPAL
════════════════════════════════════════════════════════════════ --}}
<div class="table-card">

  <div class="table-card-header">
    <div class="tch-left">
      <h5>Échéances de Loyer</h5>
      <p>{{ $paiements->count() }} échéance{{ $paiements->count() !== 1 ? 's' : '' }} au total</p>
    </div>
  </div>

  <div class="table-responsive">
    <table class="erp-table">
      <thead>
        <tr>
          <th>Appartement</th>
          <th>Locataire</th>
          <th>Période</th>
          <th>Montant attendu</th>
          <th>Montant payé</th>
          <th>Reste</th>
          <th>Date échéance</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="paiementsTbody">

        @forelse($paiements as $p)
          @php
            $contrat   = $p->contrat;
            $apt       = $contrat?->appartement;
            $loc       = $contrat?->locataire;
            $palette   = ['#4f46e5','#0891b2','#059669','#d97706','#7c3aed','#db2777','#0f766e','#dc2626'];
            $color     = $palette[($loc?->id ?? 0) % count($palette)];
            $initiales = $loc
              ? strtoupper(substr($loc->prenom,0,1)).strtoupper(substr($loc->nom,0,1))
              : '—';
            $reste     = max(0, $p->montant - $p->montant_paye);
            $pct       = $p->montant > 0 ? min(100, round(($p->montant_paye / $p->montant) * 100)) : 0;

            $statutConfig = [
              'paye'       => ['label' => 'Payé',       'class' => 'active'],
              'partiel'    => ['label' => 'Partiel',    'class' => 'partiel'],
              'en_attente' => ['label' => 'En attente', 'class' => 'partiel'],
              'en_retard'  => ['label' => 'En retard',  'class' => 'late'],
            ];
            $sc = $statutConfig[$p->statut] ?? ['label' => $p->statut, 'class' => 'inactive'];

            $echeanceDt = \Carbon\Carbon::parse($p->date_echeance);
            $isRetard   = $echeanceDt->isPast() && $p->statut !== 'paye';

            $aptId  = $apt?->id ?? 0;
            $locId  = $loc?->id ?? 0;
            $moisEch = $echeanceDt->month;
            $anneeEch = $echeanceDt->year;
          @endphp

          <tr
            data-id="{{ $p->id }}"
            data-statut="{{ $p->statut }}"
            data-apt="{{ $aptId }}"
            data-loc="{{ $locId }}"
            data-mois="{{ $moisEch }}"
            data-annee="{{ $anneeEch }}"
          >

            {{-- Appartement --}}
            <td>
              <span style="font-size:13.5px;color:var(--text-2)">
                <i class="fa-solid fa-building" style="color:var(--text-4);margin-right:5px;font-size:11px"></i>
                {{ $apt?->numero ?? '—' }}
              </span>
              @if($apt?->residence)
                <span style="display:block;font-size:11.5px;color:var(--text-4);margin-left:16px">
                  {{ $apt->residence->nom }}
                </span>
              @endif
            </td>

            {{-- Locataire --}}
            <td>
              @if($loc)
                <div class="tenant-cell">
                  <div class="t-avatar" style="background:{{ $color }}">{{ $initiales }}</div>
                  <div>
                    <span class="t-name">{{ $loc->prenom }} {{ $loc->nom }}</span>
                    <span class="t-type">{{ $loc->telephone ?? $loc->email }}</span>
                  </div>
                </div>
              @else
                <span style="color:var(--text-4);font-size:13px">—</span>
              @endif
            </td>

            {{-- Période --}}
            <td>
              <span style="font-size:12.5px;color:var(--text-3)">
                {{ \Carbon\Carbon::parse($p->periode_debut)->format('d/m/Y') }}
              </span>
              <span style="display:block;font-size:11.5px;color:var(--text-4)">
                → {{ \Carbon\Carbon::parse($p->periode_fin)->format('d/m/Y') }}
              </span>
            </td>

            {{-- Montant attendu --}}
            <td>
              <span style="font-weight:700;color:var(--text-1);font-size:13.5px">
                {{ number_format($p->montant, 0, ',', ' ') }} MAD
              </span>
            </td>

            {{-- Montant payé + barre progression --}}
            <td>
              <span style="font-weight:700;color:var(--green-t);font-size:13.5px">
                {{ number_format($p->montant_paye, 0, ',', ' ') }} MAD
              </span>
              <div style="width:80px;height:4px;background:var(--border-1);border-radius:3px;margin-top:4px;overflow:hidden">
                <div style="width:{{ $pct }}%;height:100%;background:{{ $p->statut === 'paye' ? 'var(--green-t)' : ($p->statut === 'partiel' ? 'var(--orange-t)' : 'var(--red-t)') }};border-radius:3px"></div>
              </div>
              <span style="font-size:11px;color:var(--text-4)">{{ $pct }}%</span>
            </td>

            {{-- Reste --}}
            <td>
              <span style="font-weight:700;color:{{ $reste > 0 ? 'var(--red-t)' : 'var(--green-t)' }};font-size:13.5px">
                {{ number_format($reste, 0, ',', ' ') }} MAD
              </span>
            </td>

            {{-- Date échéance --}}
            <td>
              <span class="date-text {{ $isRetard ? 'style="color:var(--red-t)"' : '' }}">
                {{ $echeanceDt->format('d/m/Y') }}
              </span>
              @if($isRetard)
                <span style="display:block;font-size:11px;color:var(--red-t)">
                  <i class="fa-solid fa-clock" style="margin-right:3px"></i>
                  {{ $echeanceDt->diffForHumans() }}
                </span>
              @endif
            </td>

            {{-- Statut --}}
            <td>
              <span class="s-badge {{ $sc['class'] }}">{{ $sc['label'] }}</span>
            </td>

            {{-- Actions --}}
            <td>
              <div class="row-actions">

                {{-- Voir --}}
                <button class="ra-btn view" title="Voir détail" onclick="openDetail({{ $p->id }})">
                  <i class="fa-solid fa-eye"></i>
                </button>

                {{-- Encaisser (masqué si payé) --}}
                @if($p->statut !== 'paye')
                  <button
                    class="ra-btn encaisser"
                    title="Encaisser un paiement"
                    onclick="openEncaisser(
                      {{ $p->id }},
                      '{{ addslashes($apt?->numero ?? '—') }}',
                      '{{ $loc ? addslashes($loc->prenom.' '.$loc->nom) : '—' }}',
                      {{ $p->montant }},
                      {{ $p->montant_paye }},
                      '{{ \Carbon\Carbon::parse($p->periode_debut)->format('d/m/Y') }}',
                      '{{ \Carbon\Carbon::parse($p->periode_fin)->format('d/m/Y') }}'
                    )"
                  >
                    <i class="fa-solid fa-money-bill-wave"></i>
                  </button>
                @endif

                {{-- Historique --}}
                <button
                  class="ra-btn"
                  style="color:var(--brand)"
                  title="Historique du contrat"
                  onclick="openDetail({{ $p->id }}, 'tabHistorique')"
                >
                  <i class="fa-solid fa-clock-rotate-left"></i>
                </button>

              </div>
            </td>

          </tr>

        @empty
          <tr>
            <td colspan="9" style="text-align:center;padding:40px;color:var(--text-4);font-size:14px">
              <i class="fa-solid fa-money-bill" style="font-size:30px;margin-bottom:10px;display:block"></i>
              Aucune échéance trouvée.
            </td>
          </tr>
        @endforelse

      </tbody>
    </table>
  </div>

  <div class="table-footer">
    <span class="tf-info">
      <strong id="countFooter">{{ $paiements->count() }}</strong>
      échéance{{ $paiements->count() !== 1 ? 's' : '' }} affichée{{ $paiements->count() !== 1 ? 's' : '' }}
    </span>
  </div>

</div>


{{-- ════════════════════════════════════════════════════════════════
     MODAL — ENCAISSER PAIEMENT
════════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalEncaisser">
  <div class="modal-panel" style="max-width:540px">

    <div class="modal-head">
      <div>
        <h3 class="mh-title">Encaisser un paiement</h3>
        <p class="mh-sub" id="encSubtitle">—</p>
      </div>
      <div class="mh-right">
        <button class="modal-close" onclick="closeModal('modalEncaisser')">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>

    <div class="modal-body">
      <input type="hidden" id="encId">

      {{-- Récapitulatif --}}
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:18px">
        <div class="detail-card">
          <div class="dc-label">Montant attendu</div>
          <div class="dc-value" id="encMontantAttendu">—</div>
        </div>
        <div class="detail-card">
          <div class="dc-label">Déjà payé</div>
          <div class="dc-value" style="color:var(--green-t)" id="encDejaPayé">—</div>
        </div>
        <div class="detail-card">
          <div class="dc-label">Reste à payer</div>
          <div class="dc-value" style="color:var(--red-t)" id="encReste">—</div>
        </div>
      </div>

      <div class="form-section-label">
        <i class="fa-solid fa-money-bill-wave" style="color:var(--brand)"></i>
        Détails du versement
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="form-group">
          <label class="form-label">Montant versé (MAD) <span style="color:var(--red-t)">*</span></label>
          <input type="number" id="encMontant" class="form-control" min="0.01" step="0.01" placeholder="Ex : 3800">
          <div id="errEncMontant" class="form-err"></div>
        </div>

        <div class="form-group">
          <label class="form-label">Date du paiement <span style="color:var(--red-t)">*</span></label>
          <input type="date" id="encDate" class="form-control">
          <div id="errEncDate" class="form-err"></div>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Mode de paiement <span style="color:var(--red-t)">*</span></label>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px" id="modePaiementCards">

          <label class="role-card" data-mode="espece" onclick="selectMode('espece')">
            <input type="radio" name="modePaiement" value="espece" style="display:none">
            <div class="rc-icon" style="background:#f0fdf4;color:#059669">
              <i class="fa-solid fa-money-bill"></i>
            </div>
            <div class="rc-label">Espèces</div>
          </label>

          <label class="role-card" data-mode="virement" onclick="selectMode('virement')">
            <input type="radio" name="modePaiement" value="virement" style="display:none">
            <div class="rc-icon" style="background:#eef2ff;color:#4f46e5">
              <i class="fa-solid fa-building-columns"></i>
            </div>
            <div class="rc-label">Virement</div>
          </label>

          <label class="role-card" data-mode="cheque" onclick="selectMode('cheque')">
            <input type="radio" name="modePaiement" value="cheque" style="display:none">
            <div class="rc-icon" style="background:#ecfeff;color:#0891b2">
              <i class="fa-solid fa-money-check"></i>
            </div>
            <div class="rc-label">Chèque</div>
          </label>

        </div>
        <div id="errEncMode" class="form-err"></div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="form-group">
          <label class="form-label">Référence</label>
          <input type="text" id="encReference" class="form-control" placeholder="N° virement / chèque">
        </div>
        <div class="form-group">
          <label class="form-label">Commentaire</label>
          <input type="text" id="encCommentaire" class="form-control" placeholder="Remarque éventuelle">
        </div>
        {{-- ── Justificatif (nouveau) ── --}}
      <div class="form-section-label" style="margin-top:6px">
        <i class="fa-solid fa-paperclip" style="color:var(--brand)"></i>
        Justificatif de paiement
      </div>

      <div class="form-group">
        <label class="form-label">
          Joindre un document
          <span style="font-size:11.5px;color:var(--text-4)">(optionnel — JPG, PNG ou PDF · max 5 Mo)</span>
        </label>
        <div class="upload-zone" onclick="document.getElementById('encJustificatif').click()" id="encUploadZone">
          <i class="fa-solid fa-upload" style="font-size:20px;color:var(--text-4)"></i>
          <div style="font-size:12.5px;color:var(--text-3);margin-top:6px" id="encJustificatifLabel">
            Cliquer pour uploader un reçu ou justificatif
          </div>
          <div style="font-size:11px;color:var(--text-4)">JPG, PNG ou PDF · max 5 Mo</div>
        </div>
        <input
          type="file"
          id="encJustificatif"
          accept=".jpg,.jpeg,.png,.pdf"
          style="display:none"
          onchange="previewJustificatif()"
        >
        <div id="errEncJustificatif" class="form-err"></div>
      </div>
      </div>

    </div>{{-- /modal-body --}}

    <div class="modal-foot">
      <div class="mf-left"></div>
      <div class="mf-right">
        <button class="btn-outline-erp" onclick="closeModal('modalEncaisser')">Annuler</button>
        <button class="btn-primary-erp" id="btnEncaisser" onclick="submitEncaisser()">
          <i class="fa-solid fa-circle-check"></i> Enregistrer
        </button>
      </div>
    </div>

  </div>
</div>


{{-- ════════════════════════════════════════════════════════════════
     MODAL — DÉTAIL PAIEMENT (4 onglets)
════════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalDetail">
  <div class="modal-panel" style="max-width:780px">

    <div class="modal-head">
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <h3 class="mh-title" id="dTitre">Détail</h3>
        <span class="s-badge" id="dBadge"></span>
      </div>
      <div class="mh-right">
        <button class="modal-close" onclick="closeModal('modalDetail')">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>

    {{-- ONGLETS --}}
    <div style="display:flex;gap:0;border-bottom:2px solid var(--border-1);padding:0 24px;background:var(--surface-1)">
      <button class="tab-btn active" onclick="switchTab('tabInfos')" id="tbInfos">
        <i class="fa-solid fa-circle-info" style="margin-right:5px"></i>Informations
      </button>
      <button class="tab-btn" onclick="switchTab('tabTransactions')" id="tbTransactions">
        <i class="fa-solid fa-money-bill-transfer" style="margin-right:5px"></i>Transactions
      </button>
      <button class="tab-btn" onclick="switchTab('tabDocuments')" id="tbDocuments">
        <i class="fa-solid fa-folder-open" style="margin-right:5px"></i>Documents
      </button>
      <button class="tab-btn" onclick="switchTab('tabHistorique')" id="tbHistorique">
        <i class="fa-solid fa-clock-rotate-left" style="margin-right:5px"></i>Historique
      </button>
    </div>

    <div class="modal-body">

      {{-- ── ONGLET INFORMATIONS ── --}}
      <div id="tabInfos" class="tab-panel">

        <div class="form-section-label">
          <i class="fa-solid fa-file-invoice" style="color:var(--brand)"></i>
          Résumé de l'échéance
        </div>

        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px">
          <div class="detail-card">
            <div class="dc-label">Appartement</div>
            <div class="dc-value" id="dAppt">—</div>
          </div>
          <div class="detail-card">
            <div class="dc-label">Résidence</div>
            <div class="dc-value" id="dResidence">—</div>
          </div>
          <div class="detail-card">
            <div class="dc-label">Loyer contractuel</div>
            <div class="dc-value" id="dLoyer">—</div>
          </div>
          <div class="detail-card">
            <div class="dc-label">Période</div>
            <div class="dc-value" id="dPeriode">—</div>
          </div>
          <div class="detail-card">
            <div class="dc-label">Date échéance</div>
            <div class="dc-value" id="dEcheance">—</div>
          </div>
          <div class="detail-card">
            <div class="dc-label">Statut</div>
            <div class="dc-value"><span class="s-badge" id="dStatutBadge"></span></div>
          </div>
          <div class="detail-card">
            <div class="dc-label">Montant attendu</div>
            <div class="dc-value" id="dMontantAttendu">—</div>
          </div>
          <div class="detail-card">
            <div class="dc-label">Montant payé</div>
            <div class="dc-value" style="color:var(--green-t)" id="dMontantPaye">—</div>
          </div>
          <div class="detail-card">
            <div class="dc-label">Reste à payer</div>
            <div class="dc-value" style="color:var(--red-t)" id="dReste">—</div>
          </div>
        </div>

        <div class="form-section-label">
          <i class="fa-solid fa-user" style="color:var(--brand)"></i>
          Locataire
        </div>

        <div style="background:var(--surface-1);border:1.5px solid var(--border-2);border-radius:var(--r-md);padding:14px">
          <div class="tenant-cell">
            <div class="t-avatar" id="dLocAvatar" style="background:#4f46e5">—</div>
            <div>
              <span class="t-name" id="dLocNom">—</span>
              <span class="t-type" id="dLocEmail">—</span>
              <span class="t-type" id="dLocTel">—</span>
            </div>
          </div>
        </div>

      </div>

      {{-- ── ONGLET TRANSACTIONS ── --}}
      <div id="tabTransactions" class="tab-panel" style="display:none">

        <div class="form-section-label">
          <i class="fa-solid fa-money-bill-transfer" style="color:var(--brand)"></i>
          Transactions enregistrées
        </div>

        <div id="transactionsContent">
          <div class="tab-loading"><i class="fa-solid fa-spinner fa-spin"></i></div>
        </div>

      </div>

      {{-- ── ONGLET DOCUMENTS ── --}}
      <div id="tabDocuments" class="tab-panel" style="display:none">

        <div class="form-section-label">
          <i class="fa-solid fa-folder-open" style="color:var(--brand)"></i>
          Reçus &amp; justificatifs
        </div>

        <div id="documentsContent">
          <div class="tab-loading"><i class="fa-solid fa-spinner fa-spin"></i></div>
        </div>

      </div>

      {{-- ── ONGLET HISTORIQUE ── --}}
      <div id="tabHistorique" class="tab-panel" style="display:none">

        <div class="form-section-label">
          <i class="fa-solid fa-clock-rotate-left" style="color:var(--brand)"></i>
          Toutes les périodes du contrat
        </div>

        <div id="historiqueContent">
          <div class="tab-loading"><i class="fa-solid fa-spinner fa-spin"></i></div>
        </div>

      </div>

    </div>{{-- /modal-body --}}

    <div class="modal-foot">
      <div class="mf-left">
        <button class="btn-outline-erp" id="dBtnEncaisser" onclick="encaisserDepuisDetail()">
          <i class="fa-solid fa-money-bill-wave"></i> Encaisser
        </button>
      </div>
      <div class="mf-right">
        <button class="btn-outline-erp" onclick="closeModal('modalDetail')">Fermer</button>
      </div>
    </div>

  </div>
</div>

@endsection


{{-- ════════════════════════════════════════════════════════════════
     JAVASCRIPT
════════════════════════════════════════════════════════════════ --}}
@push('scripts')
<script>

/* ── Config ──────────────────────────────────────────────────────── */
const CSRF    = '{{ csrf_token() }}';
const URL_PAY = '{{ url("paiements-loyer") }}';

/* ── Modals ──────────────────────────────────────────────────────── */
function openModal(id) {
  document.getElementById(id).classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeModal(id) {
  document.getElementById(id).classList.remove('open');
  document.body.style.overflow = '';
}
document.querySelectorAll('.modal-overlay').forEach(o => {
  o.addEventListener('click', e => { if (e.target === o) closeModal(o.id); });
});
document.addEventListener('keydown', e => {
  if (e.key === 'Escape')
    document.querySelectorAll('.modal-overlay.open').forEach(m => closeModal(m.id));
});

/* ── Onglets ─────────────────────────────────────────────────────── */
const TAB_BTN = {
  tabInfos:        'tbInfos',
  tabTransactions: 'tbTransactions',
  tabDocuments:    'tbDocuments',
  tabHistorique:   'tbHistorique',
};
function switchTab(tabId) {
  document.querySelectorAll('#modalDetail .tab-panel').forEach(p => p.style.display = 'none');
  document.querySelectorAll('#modalDetail .tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById(tabId).style.display = 'block';
  document.getElementById(TAB_BTN[tabId]).classList.add('active');
}

/* ── Filtre tableau ──────────────────────────────────────────────── */
function filterTable() {
  const q      = document.getElementById('searchInput').value.toLowerCase();
  const statut = document.getElementById('filterStatut').value;
  const annee  = document.getElementById('filterAnnee').value;
  const mois   = document.getElementById('filterMois').value;
  const apt    = document.getElementById('filterAppartement').value;
  const loc    = document.getElementById('filterLocataire').value;
  let visible  = 0;

  document.querySelectorAll('#paiementsTbody tr[data-id]').forEach(tr => {
    const mQ = !q      || tr.textContent.toLowerCase().includes(q);
    const mS = !statut || tr.dataset.statut === statut;
    const mA = !annee  || tr.dataset.annee === annee;
    const mM = !mois   || tr.dataset.mois === mois;
    const mP = !apt    || tr.dataset.apt === apt;
    const mL = !loc    || tr.dataset.loc === loc;

    const show = mQ && mS && mA && mM && mP && mL;
    tr.style.display = show ? '' : 'none';
    if (show) visible++;
  });

  const c = document.getElementById('countVisible');
  if (c) c.textContent = visible;
  const cf = document.getElementById('countFooter');
  if (cf) cf.textContent = visible;
}

/* ── Mode paiement (cartes visuelles) ───────────────────────────── */
function selectMode(mode) {
  document.querySelectorAll('#modePaiementCards .role-card').forEach(card => {
    const sel = card.dataset.mode === mode;
    card.style.borderColor = sel ? 'var(--brand)'   : 'var(--border-2)';
    card.style.background  = sel ? '#eef2ff'        : 'var(--surface-1)';
    card.style.boxShadow   = sel ? '0 0 0 3px rgba(99,102,241,.15)' : 'none';
    card.querySelector('input').checked = sel;
  });
}
function getMode() {
  const r = document.querySelector('#modePaiementCards input:checked');
  return r ? r.value : '';
}

/* ── Helpers ─────────────────────────────────────────────────────── */
function clearErrors(ids) {
  ids.forEach(id => {
    const el = document.getElementById(id);
    if (el) { el.textContent = ''; el.style.display = 'none'; }
  });
}
function showError(id, msg) {
  const el = document.getElementById(id);
  if (el) { el.textContent = msg; el.style.display = 'block'; }
}
function setLoading(btnId, loading, html) {
  const btn = document.getElementById(btnId);
  if (!btn) return;
  btn.disabled  = loading;
  btn.innerHTML = loading
    ? '<i class="fa-solid fa-spinner fa-spin" style="margin-right:6px"></i>Chargement…'
    : html;
}
function fmtDate(str) {
  if (!str) return '—';
  const d = new Date(str);
  return isNaN(d) ? str : d.toLocaleDateString('fr-FR');
}
function fmtMontant(v) {
  return Number(v || 0).toLocaleString('fr-FR') + ' MAD';
}

/* ── Toast ───────────────────────────────────────────────────────── */
function showToast(msg, type = 'success') {
  const map = {
    success: { icon: 'fa-circle-check',        color: 'var(--green-t)',  bg: 'var(--green-bg)',  title: 'Succès'    },
    error:   { icon: 'fa-circle-xmark',         color: 'var(--red-t)',   bg: 'var(--red-bg)',    title: 'Erreur'    },
    warning: { icon: 'fa-triangle-exclamation', color: 'var(--orange-t)',bg: 'var(--orange-bg)', title: 'Attention' },
  };
  const { icon, color, bg, title } = map[type] ?? map.success;
  const el = document.createElement('div');
  el.style.cssText = `
    position:fixed;top:22px;right:22px;z-index:9999;
    display:flex;align-items:center;gap:11px;
    padding:12px 16px;min-width:280px;max-width:380px;
    background:#fff;border-radius:12px;
    box-shadow:0 10px 40px rgba(0,0,0,.13);
    border:1px solid var(--border-2);
    animation:_toastIn .3s ease;font-family:var(--font-b)`;
  el.innerHTML = `
    <div style="width:34px;height:34px;border-radius:8px;flex-shrink:0;
      display:flex;align-items:center;justify-content:center;
      background:${bg};color:${color};font-size:15px">
      <i class="fa-solid ${icon}"></i>
    </div>
    <div style="flex:1;min-width:0">
      <div style="font-family:var(--font-h);font-size:13px;font-weight:700;color:var(--text-1)">${title}</div>
      <div style="font-size:12px;color:var(--text-3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${msg}</div>
    </div>`;
  document.body.appendChild(el);
  setTimeout(() => {
    el.style.transition = 'opacity .3s,transform .3s';
    el.style.opacity    = '0';
    el.style.transform  = 'translateX(20px)';
    setTimeout(() => el.remove(), 300);
  }, 4000);
}

/* ── Fetch wrapper ───────────────────────────────────────────────── */
async function apiFetch(url, method = 'GET', body = null) {
  const res = await fetch(url, {
    method,
    headers: {
      'Content-Type': 'application/json',
      'Accept':       'application/json',
      'X-CSRF-TOKEN': CSRF,
    },
    body: body ? JSON.stringify(body) : undefined,
  });
  return res.json();
}

/* ════════════════════════════════════════════════════════
   OPEN — ENCAISSER (depuis tableau)
════════════════════════════════════════════════════════ */
function openEncaisserGlobal() {
  showToast('Sélectionnez une échéance dans le tableau pour encaisser.', 'warning');
}

function openEncaisser(id, apt, locataire, montant, montantPaye, periodeDebut, periodeFin) {
  document.getElementById('encId').value = id;

  const reste = Math.max(0, montant - montantPaye);

  document.getElementById('encSubtitle').textContent =
    'Apt. ' + apt + ' — ' + locataire + ' · ' + periodeDebut + ' → ' + periodeFin;

  document.getElementById('encMontantAttendu').textContent = fmtMontant(montant);
  document.getElementById('encDejaPayé').textContent       = fmtMontant(montantPaye);
  document.getElementById('encReste').textContent          = fmtMontant(reste);

  // Pré-remplir avec le reste à payer
  document.getElementById('encMontant').value     = reste > 0 ? reste.toFixed(2) : '';
  document.getElementById('encDate').value        = new Date().toISOString().slice(0, 10);
  document.getElementById('encReference').value   = '';
  document.getElementById('encCommentaire').value = '';

  // Reset mode paiement
  selectMode('');
  document.querySelectorAll('#modePaiementCards input').forEach(i => i.checked = false);
  document.querySelectorAll('#modePaiementCards .role-card').forEach(c => {
    c.style.borderColor = 'var(--border-2)';
    c.style.background  = 'var(--surface-1)';
    c.style.boxShadow   = 'none';
  });

  clearErrors(['errEncMontant', 'errEncDate', 'errEncMode']);
  openModal('modalEncaisser');
}

/* ════════════════════════════════════════════════════════
   SUBMIT — ENCAISSER
════════════════════════════════════════════════════════ */
async function submitEncaisser() {
  clearErrors(['errEncMontant','errEncDate','errEncMode','errEncJustificatif']);

  const id          = document.getElementById('encId').value;
  const montant     = parseFloat(document.getElementById('encMontant').value);
  const date        = document.getElementById('encDate').value;
  const mode        = getMode();
  const reference   = document.getElementById('encReference').value.trim();
  const commentaire = document.getElementById('encCommentaire').value.trim();
  const fichier     = document.getElementById('encJustificatif').files[0];

  let ok = true;
  if (!montant || montant <= 0) { showError('errEncMontant','Le montant doit être > 0.');         ok = false; }
  if (!date)                    { showError('errEncDate',   'La date de paiement est requise.');  ok = false; }
  if (!mode)                    { showError('errEncMode',   'Sélectionnez un mode de paiement.'); ok = false; }
  if (!ok) return;

  // ── FormData (nécessaire pour l'upload fichier) ──
  const fd = new FormData();
  fd.append('_token',        CSRF);
  fd.append('montant',       montant);
  fd.append('date_paiement', date);
  fd.append('mode_paiement', mode);
  if (reference)   fd.append('reference',   reference);
  if (commentaire) fd.append('commentaire', commentaire);
  if (fichier)     fd.append('justificatif', fichier);

  setLoading('btnEncaisser', true, '<i class="fa-solid fa-circle-check"></i> Enregistrer');

  try {
    const res  = await fetch(URL_PAY + '/' + id + '/encaisser', {
      method:  'POST',
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body:    fd,
    });
    const data = await res.json();
    setLoading('btnEncaisser', false, '<i class="fa-solid fa-circle-check"></i> Enregistrer');

    if (data.success) {
      closeModal('modalEncaisser');
      showToast('Paiement de ' + fmtMontant(montant) + ' enregistré. Statut : ' + data.statut + '.');
      setTimeout(() => location.reload(), 950);
    } else {
      if (data.errors) {
        if (data.errors.montant)        showError('errEncMontant',      data.errors.montant[0]);
        if (data.errors.date_paiement)  showError('errEncDate',         data.errors.date_paiement[0]);
        if (data.errors.mode_paiement)  showError('errEncMode',         data.errors.mode_paiement[0]);
        if (data.errors.justificatif)   showError('errEncJustificatif', data.errors.justificatif[0]);
      }
      showToast(data.message || 'Erreur.', 'error');
    }
  } catch {
    setLoading('btnEncaisser', false, '<i class="fa-solid fa-circle-check"></i> Enregistrer');
    showToast('Erreur réseau.', 'error');
  }
}

// Preview du fichier sélectionné
function previewJustificatif() {
  const f    = document.getElementById('encJustificatif').files[0];
  const lbl  = document.getElementById('encJustificatifLabel');
  const zone = document.getElementById('encUploadZone');
  if (f) {
    lbl.textContent  = f.name;
    lbl.style.color  = 'var(--green-t)';
    zone.style.borderColor = 'var(--green-t)';
    zone.style.background  = 'var(--green-bg)';
  }
}

/* ════════════════════════════════════════════════════════
   OPEN — DÉTAIL
════════════════════════════════════════════════════════ */
let _curPaiement = null;

async function openDetail(id, tabId = 'tabInfos') {
  _curPaiement = null;

  // Reset contenu onglets dynamiques
  ['transactionsContent','documentsContent','historiqueContent'].forEach(sid => {
    document.getElementById(sid).innerHTML =
      '<div class="tab-loading"><i class="fa-solid fa-spinner fa-spin"></i></div>';
  });

  document.getElementById('dTitre').textContent  = 'Chargement…';
  document.getElementById('dBadge').textContent  = '';
  document.getElementById('dBadge').className    = 's-badge';
  switchTab('tabInfos');
  openModal('modalDetail');

  try {
    const data = await apiFetch(URL_PAY + '/' + id);
    if (!data.success) { showToast('Impossible de charger les données.', 'error'); return; }

    const p    = data.paiement;
    const c    = p.contrat;
    const apt  = c?.appartement;
    const loc  = c?.locataire;
    const reste = Math.max(0, p.montant - p.montant_paye);

    _curPaiement = p;

    // ── Titre + badge ──
    document.getElementById('dTitre').textContent =
      'Apt. ' + (apt?.numero ?? '—') + ' — ' + (loc ? loc.prenom + ' ' + loc.nom : '—');

    const sc = {
      paye:'active', partiel:'partiel', en_attente:'partiel', en_retard:'late'
    };
    const sl = { paye:'Payé', partiel:'Partiel', en_attente:'En attente', en_retard:'En retard' };
    const badge = document.getElementById('dBadge');
    badge.textContent = sl[p.statut] ?? p.statut;
    badge.className   = 's-badge ' + (sc[p.statut] ?? 'inactive');

    // ── Infos ──
    document.getElementById('dAppt').textContent         = apt?.numero ?? '—';
    document.getElementById('dResidence').textContent    = apt?.residence?.nom ?? '—';
    document.getElementById('dLoyer').textContent        = fmtMontant(c?.loyer);
    document.getElementById('dPeriode').textContent      = fmtDate(p.periode_debut) + ' → ' + fmtDate(p.periode_fin);
    document.getElementById('dEcheance').textContent     = fmtDate(p.date_echeance);
    document.getElementById('dMontantAttendu').textContent = fmtMontant(p.montant);
    document.getElementById('dMontantPaye').textContent  = fmtMontant(p.montant_paye);
    document.getElementById('dReste').textContent        = fmtMontant(reste);

    const statBadge = document.getElementById('dStatutBadge');
    statBadge.textContent = sl[p.statut] ?? p.statut;
    statBadge.className   = 's-badge ' + (sc[p.statut] ?? 'inactive');

    // ── Locataire ──
    if (loc) {
      const init = ((loc.prenom ?? '')[0] ?? '').toUpperCase() + ((loc.nom ?? '')[0] ?? '').toUpperCase();
      document.getElementById('dLocAvatar').textContent = init;
      document.getElementById('dLocNom').textContent    = loc.prenom + ' ' + loc.nom;
      document.getElementById('dLocEmail').textContent  = loc.email ?? '—';
      document.getElementById('dLocTel').textContent    = loc.telephone ?? '—';
    }

    // ── Bouton encaisser footer ──
    document.getElementById('dBtnEncaisser').style.display =
      p.statut === 'paye' ? 'none' : '';

    // ── Transactions ──
    renderTransactions(data.transactions);

    // ── Documents ──
    renderDocuments(data.documents);

    // ── Historique ──
    renderHistorique(data.historique);

    // Ouvrir l'onglet demandé (ex. Historique si venu du bouton horloge)
    if (tabId !== 'tabInfos') switchTab(tabId);

  } catch (err) {
    showToast('Erreur réseau.', 'error');
  }
}

function encaisserDepuisDetail() {
  if (!_curPaiement) return;
  const p   = _curPaiement;
  const apt = p.contrat?.appartement;
  const loc = p.contrat?.locataire;
  closeModal('modalDetail');
  openEncaisser(
    p.id,
    apt?.numero ?? '—',
    loc ? loc.prenom + ' ' + loc.nom : '—',
    p.montant,
    p.montant_paye,
    fmtDate(p.periode_debut),
    fmtDate(p.periode_fin)
  );
}

/* ── Renderers ───────────────────────────────────────────────────── */
function renderTransactions(transactions) {
  const el = document.getElementById('transactionsContent');
  if (!transactions || transactions.length === 0) {
    el.innerHTML = '<div class="tab-empty"><i class="fa-solid fa-receipt"></i><p>Aucune transaction.</p></div>';
    return;
  }
  const modeIcon = { espece:'fa-money-bill', virement:'fa-building-columns', cheque:'fa-money-check' };
  el.innerHTML = `
    <table class="erp-table">
      <thead>
        <tr>
          <th>Date</th><th>Montant</th><th>Mode</th><th>Référence</th><th>Commentaire</th>
        </tr>
      </thead>
      <tbody>
        ${transactions.map(t => `
          <tr>
            <td><span class="date-text">${fmtDate(t.date_paiement)}</span></td>
            <td style="font-weight:700;color:var(--green-t)">${fmtMontant(t.montant)}</td>
            <td>
              <span style="display:flex;align-items:center;gap:5px;font-size:13px;text-transform:capitalize">
                <i class="fa-solid ${modeIcon[t.mode_paiement] ?? 'fa-money-bill'}" style="color:var(--text-4);font-size:12px"></i>
                ${t.mode_paiement ?? '—'}
              </span>
            </td>
            <td><span class="ref-code" style="font-size:11px">${t.reference ?? '—'}</span></td>
            <td style="font-size:12.5px;color:var(--text-3)">${t.commentaire ?? '—'}</td>
          </tr>`).join('')}
      </tbody>
    </table>`;
}

function renderDocuments(documents) {
  const el = document.getElementById('documentsContent');
  if (!documents || documents.length === 0) {
    el.innerHTML = '<div class="tab-empty"><i class="fa-solid fa-folder-open"></i><p>Aucun document.</p></div>';
    return;
  }
  el.innerHTML = `
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px">
      ${documents.map(d => {
        const ext   = (d.nom_fichier ?? '').split('.').pop().toLowerCase();
        const isImg = ['jpg','jpeg','png','gif','webp'].includes(ext);
        return `
          <div style="background:var(--surface-1);border:1.5px solid var(--border-2);border-radius:var(--r-md);padding:14px;
                      display:flex;flex-direction:column;align-items:center;gap:8px;text-align:center">
            ${isImg
              ? `<img src="${d.url}" style="width:100%;height:80px;object-fit:cover;border-radius:6px;border:1px solid var(--border-1)">`
              : `<div style="width:100%;height:80px;background:var(--surface-2);border-radius:6px;display:flex;align-items:center;justify-content:center">
                   <i class="fa-solid fa-file-pdf" style="font-size:28px;color:var(--red-t)"></i>
                 </div>`}
            <div style="font-size:12px;color:var(--text-2);font-weight:600;word-break:break-all">${d.nom_fichier}</div>
            <div style="font-size:11px;color:var(--text-4)">${d.type_document}</div>
            <a href="${d.url}" target="_blank" download
               class="btn-outline-erp" style="font-size:12px;padding:5px 12px">
              <i class="fa-solid fa-download" style="margin-right:4px"></i>Télécharger
            </a>
          </div>`;
      }).join('')}
    </div>`;
}

function renderHistorique(historique) {
  const el = document.getElementById('historiqueContent');
  if (!historique || historique.length === 0) {
    el.innerHTML = '<div class="tab-empty"><i class="fa-solid fa-clock-rotate-left"></i><p>Aucun historique.</p></div>';
    return;
  }
  const sc = { paye:'active', partiel:'partiel', en_attente:'partiel', en_retard:'late' };
  const sl = { paye:'Payé',   partiel:'Partiel', en_attente:'En attente', en_retard:'En retard' };
  el.innerHTML = `
    <table class="erp-table">
      <thead>
        <tr>
          <th>Période</th><th>Échéance</th>
          <th>Attendu</th><th>Payé</th><th>Reste</th><th>Statut</th>
        </tr>
      </thead>
      <tbody>
        ${historique.map(h => {
          const reste = Math.max(0, h.montant - h.montant_paye);
          const pct   = h.montant > 0 ? Math.min(100, Math.round((h.montant_paye / h.montant) * 100)) : 0;
          return `
            <tr>
              <td style="font-size:12.5px;color:var(--text-3)">
                ${fmtDate(h.periode_debut)} → ${fmtDate(h.periode_fin)}
              </td>
              <td><span class="date-text">${fmtDate(h.date_echeance)}</span></td>
              <td style="font-weight:700">${fmtMontant(h.montant)}</td>
              <td>
                <span style="color:var(--green-t);font-weight:600">${fmtMontant(h.montant_paye)}</span>
                <div style="width:60px;height:4px;background:var(--border-1);border-radius:2px;margin-top:3px;overflow:hidden">
                  <div style="width:${pct}%;height:100%;background:var(--green-t);border-radius:2px"></div>
                </div>
              </td>
              <td style="color:${reste > 0 ? 'var(--red-t)' : 'var(--green-t)'};font-weight:600">
                ${fmtMontant(reste)}
              </td>
              <td><span class="s-badge ${sc[h.statut] ?? 'inactive'}">${sl[h.statut] ?? h.statut}</span></td>
            </tr>`;
        }).join('')}
      </tbody>
    </table>`;
}

/* ── Styles injectés ─────────────────────────────────────────────── */
const _s = document.createElement('style');
_s.textContent = `
  @keyframes _toastIn { from { opacity:0;transform:translateX(30px) } to { opacity:1;transform:none } }

  .form-err { display:none;font-size:12px;color:var(--red-t);margin-top:4px }

  .tab-btn {
    padding:11px 16px;font-size:13px;font-weight:600;
    font-family:var(--font-b);color:var(--text-3);
    background:transparent;border:none;
    border-bottom:2.5px solid transparent;
    cursor:pointer;transition:all .18s;margin-bottom:-2px;
  }
  .tab-btn:hover { color:var(--brand); }
  .tab-btn.active { color:var(--brand);border-bottom-color:var(--brand); }

  .tab-loading { text-align:center;padding:32px;font-size:22px;color:var(--text-4); }
  .tab-empty   { text-align:center;padding:32px;color:var(--text-4);font-size:14px; }
  .tab-empty i { font-size:28px;display:block;margin-bottom:8px; }

  .detail-card {
    background:var(--surface-1);border:1px solid var(--border-1);
    border-radius:var(--r-md);padding:12px;
  }
  .dc-label {
    font-size:11.5px;color:var(--text-4);font-weight:600;
    text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px;
  }
  .dc-value { font-size:14px;font-weight:700;color:var(--text-1); }

  .role-card {
    display:flex;flex-direction:column;align-items:center;gap:6px;
    padding:12px 8px;border:1.5px solid var(--border-2);
    border-radius:var(--r-md);cursor:pointer;background:var(--surface-1);
    transition:all .18s;text-align:center;user-select:none;
  }
  .role-card:hover { border-color:var(--brand);background:#eef2ff; }
  .rc-icon {
    width:36px;height:36px;border-radius:9px;
    display:flex;align-items:center;justify-content:center;font-size:15px;
  }
  .rc-label { font-size:12.5px;font-weight:700;color:var(--text-1);font-family:var(--font-h); }

  .s-badge.partiel {
    background:#fffbeb;color:#d97706;
    border:1px solid rgba(217,119,6,.2);
  }
`;
document.head.appendChild(_s);

/* ── Appliquer filtres au chargement (année et mois courants pré-selectionnés) ── */
document.addEventListener('DOMContentLoaded', () => filterTable());
</script>
@endpush
