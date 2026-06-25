@extends('layouts.layout')

@section('title', 'Contrats')
@section('page_title', 'Contrats')

@section('content')

{{-- ════════════════════════════════════════════════════════════════
     PAGE HEADER
════════════════════════════════════════════════════════════════ --}}
<div class="page-header">
  <div class="ph-left">
    <h2>Contrats Locatifs</h2>
    <p>Gérez, suivez et renouvelez tous les baux de vos résidences.</p>
  </div>
  <div class="ph-right">
    <button class="btn-outline-erp" onclick="exportContrats()">
      <i class="fa-solid fa-download"></i> Exporter
    </button>
    <button class="btn-primary-erp" onclick="openModal('modalAjouter')">
      <i class="fa-solid fa-plus"></i> Nouveau Contrat
    </button>
  </div>
</div>

{{-- ════════════════════════════════════════════════════════════════
     KPI CARDS
════════════════════════════════════════════════════════════════ --}}
<div class="kpi-row" style="grid-template-columns: repeat(5, 1fr)">

  <div class="kpi-card kpi-green" style="animation-delay:.05s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Contrats actifs</div>
        <div class="kpi-value">{{ $kpis['actifs'] }}</div>
        <div class="kpi-sub">baux en cours</div>
      </div>
      <div class="kpi-icon green"><i class="fa-solid fa-file-contract"></i></div>
    </div>
  </div>

  <div class="kpi-card kpi-orange" style="animation-delay:.10s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">En attente</div>
        <div class="kpi-value">{{ $kpis['en_attente'] }}</div>
        <div class="kpi-sub">à valider</div>
      </div>
      <div class="kpi-icon orange"><i class="fa-solid fa-hourglass-half"></i></div>
    </div>
  </div>

  <div class="kpi-card kpi-red" style="animation-delay:.15s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Résiliés</div>
        <div class="kpi-value">{{ $kpis['resilies'] }}</div>
        <div class="kpi-sub">contrats clos</div>
      </div>
      <div class="kpi-icon red"><i class="fa-solid fa-ban"></i></div>
    </div>
  </div>

  <div class="kpi-card kpi-blue" style="animation-delay:.20s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Revenus mensuels</div>
        <div class="kpi-value">{{ number_format($kpis['revenus_mensuels'], 0, ',', ' ') }}</div>
        <div class="kpi-sub">MAD / mois</div>
      </div>
      <div class="kpi-icon blue"><i class="fa-solid fa-sack-dollar"></i></div>
    </div>
  </div>

  <div class="kpi-card" style="animation-delay:.25s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Impayés</div>
        <div class="kpi-value" style="color:var(--red-t)">{{ number_format($kpis['impayes'], 0, ',', ' ') }}</div>
        <div class="kpi-sub">MAD en retard</div>
      </div>
      <div class="kpi-icon" style="background:var(--red-bg)"><i class="fa-solid fa-triangle-exclamation" style="color:var(--red-t)"></i></div>
    </div>
  </div>

</div>

{{-- ════════════════════════════════════════════════════════════════
     FILTER BAR
════════════════════════════════════════════════════════════════ --}}
<div class="filter-bar">

  <div class="filter-search">
    <i class="fa-solid fa-magnifying-glass"></i>
    <input
      type="text"
      id="searchInput"
      placeholder="Rechercher par locataire, appartement, référence…"
      oninput="filterTable()"
    >
  </div>

  <select id="filterStatut" class="filter-select" onchange="filterTable()">
    <option value="">Tous les statuts</option>
    <option value="actif">Actifs</option>
    <option value="en_attente">En attente</option>
    <option value="termine">Terminés</option>
    <option value="resilie">Résiliés</option>
  </select>

  <div class="filter-divider"></div>

  <div class="filter-count">
    <strong id="countVisible">{{ $contrats->count() }}</strong>
    contrat{{ $contrats->count() !== 1 ? 's' : '' }} trouvé{{ $contrats->count() !== 1 ? 's' : '' }}
  </div>

</div>

{{-- ════════════════════════════════════════════════════════════════
     TABLEAU PRINCIPAL
════════════════════════════════════════════════════════════════ --}}
<div class="table-card">

  <div class="table-card-header">
    <div class="tch-left">
      <h5>Liste des Contrats</h5>
      <p>Cliquez sur une ligne pour voir les détails complets</p>
    </div>
  </div>

  <div class="table-responsive">
    <table class="erp-table">
      <thead>
        <tr>
          <th>Référence</th>
          <th>Appartement</th>
          <th>Locataire</th>
          <th>Loyer</th>
          <th>Date début</th>
          <th>Date fin</th>
          <th>Type paiement</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="contratsTbody">

        @forelse($contrats as $contrat)
          @php
            $palette   = ['#4f46e5','#0891b2','#059669','#d97706','#7c3aed','#db2777','#0f766e','#dc2626'];
            $color     = $palette[$contrat->id % count($palette)];
            $locataire = $contrat->locataire;
            $initiales = $locataire
                ? strtoupper(substr($locataire->prenom,0,1)).strtoupper(substr($locataire->nom,0,1))
                : '—';

            $statutConfig = [
              'actif'      => ['label' => 'Actif',      'class' => 'active'],
              'en_attente' => ['label' => 'En attente', 'class' => 'late'],
              'termine'    => ['label' => 'Terminé',    'class' => 'inactive'],
              'resilie'    => ['label' => 'Résilié',    'class' => 'inactive'],
            ];
            $sc = $statutConfig[$contrat->statut] ?? ['label' => $contrat->statut, 'class' => 'inactive'];

            $ref = 'LVC-' . $contrat->created_at->format('Y') . '-' . str_pad($contrat->id, 3, '0', STR_PAD_LEFT);
          @endphp

          <tr
            data-id="{{ $contrat->id }}"
            data-statut="{{ $contrat->statut }}"
          >

            {{-- Référence --}}
            <td><span class="ref-code">{{ $ref }}</span></td>

            {{-- Appartement --}}
            <td>
              <span style="font-size:13.5px;color:var(--text-2)">
                <i class="fa-solid fa-building" style="color:var(--text-4);margin-right:5px;font-size:11px"></i>
                {{ $contrat->appartement->numero ?? '—' }}
                @if($contrat->appartement && $contrat->appartement->residence)
                  <span style="display:block;font-size:11.5px;color:var(--text-4);margin-left:16px">{{ $contrat->appartement->residence->nom }}</span>
                @endif
              </span>
            </td>

            {{-- Locataire --}}
            <td>
              @if($locataire)
                <div class="tenant-cell">
                  <div class="t-avatar" style="background:{{ $color }}">{{ $initiales }}</div>
                  <div>
                    <span class="t-name">{{ $locataire->prenom }} {{ $locataire->nom }}</span>
                    <span class="t-type">{{ $locataire->telephone ?? $locataire->email }}</span>
                  </div>
                </div>
              @else
                <span style="color:var(--text-4);font-size:13px">—</span>
              @endif
            </td>

            {{-- Loyer --}}
            <td>
              <span style="font-weight:700;color:var(--text-1);font-size:13.5px">
                {{ number_format($contrat->loyer, 0, ',', ' ') }} MAD
              </span>
              <span style="display:block;font-size:11.5px;color:var(--text-4)">
                Caution : {{ number_format($contrat->caution, 0, ',', ' ') }} MAD
              </span>
            </td>

            {{-- Date début --}}
            <td><span class="date-text">{{ $contrat->date_debut->format('d/m/Y') }}</span></td>

            {{-- Date fin --}}
            <td>
              <span class="date-text">
                {{ $contrat->date_fin ? $contrat->date_fin->format('d/m/Y') : '—' }}
              </span>
            </td>

            {{-- Type paiement --}}
            <td>
              <span style="font-size:12.5px;color:var(--text-3);text-transform:capitalize">
                {{ $contrat->type_paiement }}
              </span>
            </td>

            {{-- Statut --}}
            <td><span class="s-badge {{ $sc['class'] }}">{{ $sc['label'] }}</span></td>

            {{-- Actions --}}
            <td>
              <div class="row-actions">

                {{-- Voir --}}
                <button class="ra-btn view" title="Voir détail" onclick="openDetail({{ $contrat->id }})">
                  <i class="fa-solid fa-eye"></i>
                </button>

                {{-- Modifier --}}
                <button
                  class="ra-btn edit"
                  title="Modifier"
                  onclick="openModifier(
                    {{ $contrat->id }},
                    {{ $contrat->loyer }},
                    {{ $contrat->caution }},
                    '{{ $contrat->date_fin ? $contrat->date_fin->format('Y-m-d') : '' }}'
                  )"
                >
                  <i class="fa-solid fa-pen-to-square"></i>
                </button>

                {{-- Résilier (caché si déjà résilié/terminé) --}}
                @if(!in_array($contrat->statut, ['resilie', 'termine']))
                  <button
                    class="ra-btn delete"
                    title="Résilier"
                    onclick="openResilier({{ $contrat->id }}, '{{ $ref }}')"
                  >
                    <i class="fa-solid fa-ban"></i>
                  </button>
                @endif

              </div>
            </td>

          </tr>

        @empty
          <tr>
            <td colspan="9" style="text-align:center;padding:40px;color:var(--text-4);font-size:14px">
              <i class="fa-solid fa-file-contract" style="font-size:30px;margin-bottom:10px;display:block"></i>
              Aucun contrat trouvé.
            </td>
          </tr>
        @endforelse

      </tbody>
    </table>
  </div>

  <div class="table-footer">
    <span class="tf-info">
      <strong>{{ $contrats->count() }}</strong>
      contrat{{ $contrats->count() !== 1 ? 's' : '' }} affiché{{ $contrats->count() !== 1 ? 's' : '' }}
    </span>
  </div>

</div>


{{-- ════════════════════════════════════════════════════════════════
     MODAL — AJOUTER CONTRAT
════════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalAjouter">
  <div class="modal-panel">

    <div class="modal-head">
      <div>
        <h3 class="mh-title">Nouveau Contrat</h3>
        <p class="mh-sub">Remplissez les informations du bail à créer</p>
      </div>
      <div class="mh-right">
        <button class="modal-close" onclick="closeModal('modalAjouter')">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>

    <div class="modal-body">

      <div class="form-section-label">
        <i class="fa-solid fa-building" style="color:var(--brand)"></i>
        Unité &amp; Parties
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="form-group">
          <label class="form-label">Appartement <span style="color:var(--red-t)">*</span></label>
          <select id="addAppartement" class="form-control">
            <option value="">— Sélectionner —</option>
            @foreach($appartementsDisponibles as $apt)
              <option value="{{ $apt->id }}">
                Apt. {{ $apt->numero }}
                @if($apt->residence) — {{ $apt->residence->nom }} @endif
                @if($apt->etage !== null) (Étage {{ $apt->etage }}) @endif
              </option>
            @endforeach
          </select>
          <div id="errAddAppartement" class="form-err"></div>
        </div>

        <div class="form-group">
          <label class="form-label">Type de contrat <span style="color:var(--red-t)">*</span></label>
          <select id="addTypeContrat" class="form-control">
            <option value="">— Sélectionner —</option>
            <option value="bail_residentiel">Bail résidentiel</option>
            <option value="bail_commercial">Bail commercial</option>
          </select>
          <div id="errAddTypeContrat" class="form-err"></div>
        </div>

        <div class="form-group">
          <label class="form-label">Locataire <span style="color:var(--red-t)">*</span></label>
          <select id="addLocataire" class="form-control">
            <option value="">— Sélectionner —</option>
            @foreach($locataires as $loc)
              <option value="{{ $loc->id }}">{{ $loc->prenom }} {{ $loc->nom }}</option>
            @endforeach
          </select>
          <div id="errAddLocataire" class="form-err"></div>
        </div>

        <div class="form-group">
          <label class="form-label">Type de paiement <span style="color:var(--red-t)">*</span></label>
          <select id="addTypePaiement" class="form-control">
            <option value="">— Sélectionner —</option>
            <option value="mensuel">Mensuel</option>
            <option value="trimestriel">Trimestriel</option>
            <option value="total">Total (paiement unique)</option>
          </select>
          <div id="errAddTypePaiement" class="form-err"></div>
        </div>
      </div>

      <div class="form-section-label" style="margin-top:6px">
        <i class="fa-solid fa-calendar-days" style="color:var(--brand)"></i>
        Période &amp; Financier
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="form-group">
          <label class="form-label">Date de début <span style="color:var(--red-t)">*</span></label>
          <input type="date" id="addDateDebut" class="form-control">
          <div id="errAddDateDebut" class="form-err"></div>
        </div>

        <div class="form-group">
          <label class="form-label">Date de fin</label>
          <input type="date" id="addDateFin" class="form-control">
          <div id="errAddDateFin" class="form-err"></div>
          <div style="font-size:11.5px;color:var(--text-4);margin-top:4px">
            <i class="fa-solid fa-circle-info" style="margin-right:3px"></i>
            Laisser vide pour un contrat à durée indéterminée
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Loyer (MAD) <span style="color:var(--red-t)">*</span></label>
          <input type="number" id="addLoyer" class="form-control" placeholder="ex : 3800" min="1" step="0.01">
          <div id="errAddLoyer" class="form-err"></div>
        </div>

        <div class="form-group">
          <label class="form-label">Caution (MAD) <span style="color:var(--red-t)">*</span></label>
          <input type="number" id="addCaution" class="form-control" placeholder="ex : 7600" min="0" step="0.01">
          <div id="errAddCaution" class="form-err"></div>
        </div>
      </div>

      <div class="form-group" style="margin-top:6px">
        <label class="form-label">Commentaire</label>
        <textarea id="addCommentaire" class="form-control" rows="2" placeholder="Remarques éventuelles…"></textarea>
      </div>

    </div>{{-- /modal-body --}}

    <div class="modal-foot">
      <div class="mf-left"></div>
      <div class="mf-right">
        <button class="btn-outline-erp" onclick="closeModal('modalAjouter')">Annuler</button>
        <button class="btn-primary-erp" id="btnAjouter" onclick="submitAjouter()">
          <i class="fa-solid fa-check"></i> Créer le contrat
        </button>
      </div>
    </div>

  </div>
</div>


{{-- ════════════════════════════════════════════════════════════════
     MODAL — DÉTAIL CONTRAT (avec onglets)
════════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalDetail">
  <div class="modal-panel" style="max-width:760px">

    <div class="modal-head">
      <div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px">
          <span class="ref-code" id="dRef">—</span>
          <span class="s-badge" id="dBadge">—</span>
        </div>
        <h3 class="mh-title" id="dTitre">Contrat</h3>
        <p class="mh-sub" id="dSub">—</p>
      </div>
      <div class="mh-right">
        <button class="modal-close" onclick="closeModal('modalDetail')">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>

    {{-- Onglets --}}
    <div style="display:flex;gap:0;border-bottom:2px solid var(--border-1);padding:0 24px;background:var(--surface-1)">
      <button class="tab-btn active" onclick="switchTab('tabInfos')" id="tbInfos">
        <i class="fa-solid fa-circle-info" style="margin-right:5px"></i>Informations
      </button>
      <button class="tab-btn" onclick="switchTab('tabPaiements')" id="tbPaiements">
        <i class="fa-solid fa-calendar-check" style="margin-right:5px"></i>Échéances
      </button>
      <button class="tab-btn" onclick="switchTab('tabTransactions')" id="tbTransactions">
        <i class="fa-solid fa-money-bill-transfer" style="margin-right:5px"></i>Transactions
      </button>
    </div>

    <div class="modal-body">

      {{-- ── Onglet Informations ── --}}
      <div id="tabInfos" class="tab-panel">

        <div class="form-section-label">
          <i class="fa-solid fa-file-contract" style="color:var(--brand)"></i>
          Détails du contrat
        </div>

        {{-- Info grid --}}
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:18px">
          <div style="background:var(--surface-1);border:1px solid var(--border-1);border-radius:var(--r-md);padding:12px">
            <div style="font-size:11.5px;color:var(--text-4);font-weight:600;text-transform:uppercase;letter-spacing:.04em">Loyer</div>
            <div style="font-size:17px;font-weight:800;color:var(--text-1);margin-top:3px" id="dLoyer">—</div>
          </div>
          <div style="background:var(--surface-1);border:1px solid var(--border-1);border-radius:var(--r-md);padding:12px">
            <div style="font-size:11.5px;color:var(--text-4);font-weight:600;text-transform:uppercase;letter-spacing:.04em">Caution</div>
            <div style="font-size:17px;font-weight:800;color:var(--text-1);margin-top:3px" id="dCaution">—</div>
          </div>
          <div style="background:var(--surface-1);border:1px solid var(--border-1);border-radius:var(--r-md);padding:12px">
            <div style="font-size:11.5px;color:var(--text-4);font-weight:600;text-transform:uppercase;letter-spacing:.04em">Paiement</div>
            <div style="font-size:15px;font-weight:700;color:var(--text-1);margin-top:3px;text-transform:capitalize" id="dTypePaiement">—</div>
          </div>
          <div style="background:var(--surface-1);border:1px solid var(--border-1);border-radius:var(--r-md);padding:12px">
            <div style="font-size:11.5px;color:var(--text-4);font-weight:600;text-transform:uppercase;letter-spacing:.04em">Date début</div>
            <div style="font-size:14px;font-weight:700;color:var(--brand);margin-top:3px" id="dDateDebut">—</div>
          </div>
          <div style="background:var(--surface-1);border:1px solid var(--border-1);border-radius:var(--r-md);padding:12px">
            <div style="font-size:11.5px;color:var(--text-4);font-weight:600;text-transform:uppercase;letter-spacing:.04em">Date fin</div>
            <div style="font-size:14px;font-weight:700;color:var(--orange-t);margin-top:3px" id="dDateFin">—</div>
          </div>
          <div style="background:var(--surface-1);border:1px solid var(--border-1);border-radius:var(--r-md);padding:12px">
            <div style="font-size:11.5px;color:var(--text-4);font-weight:600;text-transform:uppercase;letter-spacing:.04em">Type bail</div>
            <div style="font-size:14px;font-weight:700;color:var(--text-1);margin-top:3px;text-transform:capitalize" id="dTypeContrat">—</div>
          </div>
        </div>

        <div class="form-section-label">
          <i class="fa-solid fa-users" style="color:var(--brand)"></i>
          Parties prenantes
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          {{-- Locataire --}}
          <div style="background:var(--surface-1);border:1.5px solid var(--border-2);border-radius:var(--r-md);padding:14px">
            <div style="font-size:11px;color:var(--text-4);font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">Locataire</div>
            <div class="tenant-cell">
              <div class="t-avatar" id="dLocataireAvatar" style="background:#4f46e5">—</div>
              <div>
                <span class="t-name" id="dLocataireName">—</span>
                <span class="t-type" id="dLocataireEmail">—</span>
                <span class="t-type" id="dLocataireTel">—</span>
              </div>
            </div>
          </div>
          {{-- Propriétaire --}}
          <div style="background:var(--surface-1);border:1.5px solid var(--border-2);border-radius:var(--r-md);padding:14px">
            <div style="font-size:11px;color:var(--text-4);font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">Propriétaire</div>
            <div class="tenant-cell">
              <div class="t-avatar" id="dPropAvatar" style="background:#059669">—</div>
              <div>
                <span class="t-name" id="dPropName">—</span>
                <span class="t-type" id="dPropEmail">—</span>
              </div>
            </div>
          </div>
        </div>

        {{-- Appartement --}}
        <div style="margin-top:12px;background:var(--surface-1);border:1.5px solid var(--border-2);border-radius:var(--r-md);padding:14px;display:flex;align-items:center;gap:12px">
          <div style="width:40px;height:40px;border-radius:10px;background:#eef2ff;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="fa-solid fa-building" style="color:var(--brand);font-size:16px"></i>
          </div>
          <div>
            <div style="font-size:13.5px;font-weight:700;color:var(--text-1)" id="dAppartement">—</div>
            <div style="font-size:12px;color:var(--text-4)" id="dResidence">—</div>
          </div>
        </div>

      </div>

      {{-- ── Onglet Échéances ── --}}
      <div id="tabPaiements" class="tab-panel" style="display:none">

        <div class="form-section-label">
          <i class="fa-solid fa-calendar-check" style="color:var(--brand)"></i>
          Échéances de loyer
        </div>

        <div id="paiementsLoading" style="text-align:center;padding:30px;color:var(--text-4)">
          <i class="fa-solid fa-spinner fa-spin" style="font-size:22px"></i>
        </div>

        <div id="paiementsList" style="display:none">
          <table class="erp-table" style="margin-top:4px">
            <thead>
              <tr>
                <th>Période</th>
                <th>Échéance</th>
                <th>Montant</th>
                <th>Payé</th>
                <th>Statut</th>
              </tr>
            </thead>
            <tbody id="paiementsTbody"></tbody>
          </table>
        </div>

        <div id="paiementsEmpty" style="display:none;text-align:center;padding:30px;color:var(--text-4);font-size:14px">
          <i class="fa-solid fa-calendar-xmark" style="font-size:24px;display:block;margin-bottom:8px"></i>
          Aucune échéance générée.
        </div>

      </div>

      {{-- ── Onglet Transactions ── --}}
      <div id="tabTransactions" class="tab-panel" style="display:none">

        <div class="form-section-label">
          <i class="fa-solid fa-money-bill-transfer" style="color:var(--brand)"></i>
          Paiements effectués
        </div>

        <div id="transactionsLoading" style="text-align:center;padding:30px;color:var(--text-4)">
          <i class="fa-solid fa-spinner fa-spin" style="font-size:22px"></i>
        </div>

        <div id="transactionsList" style="display:none">
          <table class="erp-table" style="margin-top:4px">
            <thead>
              <tr>
                <th>Date</th>
                <th>Montant</th>
                <th>Mode</th>
                <th>Référence</th>
                <th>Commentaire</th>
              </tr>
            </thead>
            <tbody id="transactionsTbody"></tbody>
          </table>
        </div>

        <div id="transactionsEmpty" style="display:none;text-align:center;padding:30px;color:var(--text-4);font-size:14px">
          <i class="fa-solid fa-receipt" style="font-size:24px;display:block;margin-bottom:8px"></i>
          Aucune transaction enregistrée.
        </div>

      </div>

    </div>{{-- /modal-body --}}

    <div class="modal-foot">
      <div class="mf-left">
        <button
          id="dBtnResilier"
          class="btn-outline-erp"
          style="color:var(--red-t);border-color:var(--red-bg)"
          onclick="openResilierDepuisDetail()"
        >
          <i class="fa-solid fa-ban"></i> Résilier
        </button>
      </div>
      <div class="mf-right">
        <button class="btn-outline-erp" onclick="closeModal('modalDetail')">Fermer</button>
        <button class="btn-primary-erp" id="dBtnModifier" onclick="openModifierDepuisDetail()">
          <i class="fa-solid fa-pen"></i> Modifier
        </button>
      </div>
    </div>

  </div>
</div>


{{-- ════════════════════════════════════════════════════════════════
     MODAL — MODIFIER CONTRAT
════════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalModifier">
  <div class="modal-panel" style="max-width:500px">

    <div class="modal-head">
      <div>
        <h3 class="mh-title">Modifier le contrat</h3>
        <p class="mh-sub" id="modSubtitle">—</p>
      </div>
      <div class="mh-right">
        <button class="modal-close" onclick="closeModal('modalModifier')">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>

    <div class="modal-body">
      <input type="hidden" id="modId">

      <div style="display:flex;align-items:flex-start;gap:10px;background:#eef2ff;border:1.5px solid rgba(99,102,241,.25);border-radius:var(--r-md);padding:12px 14px;margin-bottom:18px">
        <i class="fa-solid fa-circle-info" style="color:var(--brand);font-size:14px;flex-shrink:0;margin-top:1px"></i>
        <div style="font-size:12.5px;color:var(--text-2);line-height:1.6">
          Seuls le <strong>loyer</strong>, la <strong>caution</strong> et la <strong>date de fin</strong> peuvent être modifiés.
        </div>
      </div>

      <div class="form-section-label">
        <i class="fa-solid fa-pen-to-square" style="color:var(--brand)"></i>
        Informations modifiables
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="form-group">
          <label class="form-label">Loyer (MAD) <span style="color:var(--red-t)">*</span></label>
          <input type="number" id="modLoyer" class="form-control" min="1" step="0.01">
          <div id="errModLoyer" class="form-err"></div>
        </div>

        <div class="form-group">
          <label class="form-label">Caution (MAD) <span style="color:var(--red-t)">*</span></label>
          <input type="number" id="modCaution" class="form-control" min="0" step="0.01">
          <div id="errModCaution" class="form-err"></div>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Date de fin</label>
        <input type="date" id="modDateFin" class="form-control">
        <div id="errModDateFin" class="form-err"></div>
      </div>

      <div class="form-group">
        <label class="form-label">Commentaire</label>
        <textarea id="modCommentaire" class="form-control" rows="2" placeholder="Remarques éventuelles…"></textarea>
      </div>

    </div>{{-- /modal-body --}}

    <div class="modal-foot">
      <div class="mf-left"></div>
      <div class="mf-right">
        <button class="btn-outline-erp" onclick="closeModal('modalModifier')">Annuler</button>
        <button class="btn-primary-erp" id="btnModifier" onclick="submitModifier()">
          <i class="fa-solid fa-circle-check"></i> Enregistrer
        </button>
      </div>
    </div>

  </div>
</div>


{{-- ════════════════════════════════════════════════════════════════
     MODAL — RÉSILIER CONTRAT
════════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalResilier">
  <div class="modal-panel" style="max-width:500px">

    <div class="modal-head">
      <div>
        <h3 class="mh-title" style="color:var(--red-t)">
          <i class="fa-solid fa-ban" style="margin-right:6px"></i>Résilier le contrat
        </h3>
        <p class="mh-sub" id="resSub">—</p>
      </div>
      <div class="mh-right">
        <button class="modal-close" onclick="closeModal('modalResilier')">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>

    <div class="modal-body">
      <input type="hidden" id="resId">

      <div style="background:var(--red-bg);border:1px solid rgba(239,68,68,.2);border-radius:var(--r-md);padding:14px;margin-bottom:18px">
        <p style="font-size:13.5px;color:var(--text-1);margin:0 0 5px 0;font-weight:600">
          <i class="fa-solid fa-triangle-exclamation" style="color:var(--red-t);margin-right:5px"></i>
          Action irréversible
        </p>
        <p style="font-size:12.5px;color:var(--text-3);margin:0">
          Le contrat sera marqué comme <strong>résilié</strong> et l'appartement
          redeviendra <strong>Disponible</strong>. Le contrat ne sera pas supprimé.
        </p>
      </div>

      <div class="form-section-label">
        <i class="fa-solid fa-calendar-xmark" style="color:var(--red-t)"></i>
        Informations de résiliation
      </div>

      <div class="form-group">
        <label class="form-label">Date de résiliation <span style="color:var(--red-t)">*</span></label>
        <input type="date" id="resDate" class="form-control">
        <div id="errResDate" class="form-err"></div>
      </div>

      <div class="form-group">
        <label class="form-label">Motif <span style="color:var(--red-t)">*</span></label>
        <input type="text" id="resMotif" class="form-control" placeholder="Ex : Départ volontaire du locataire">
        <div id="errResMotif" class="form-err"></div>
      </div>

      <div class="form-group">
        <label class="form-label">Commentaire</label>
        <textarea id="resCommentaire" class="form-control" rows="2" placeholder="Détails supplémentaires…"></textarea>
      </div>

    </div>{{-- /modal-body --}}

    <div class="modal-foot">
      <div class="mf-left"></div>
      <div class="mf-right">
        <button class="btn-outline-erp" onclick="closeModal('modalResilier')">Annuler</button>
        <button
          id="btnResilier"
          onclick="submitResilier()"
          style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:var(--r-md);background:var(--red-t);color:#fff;font-size:13px;font-weight:700;border:none;cursor:pointer;font-family:var(--font-b);transition:opacity .2s"
          onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'"
        >
          <i class="fa-solid fa-ban"></i> Confirmer la résiliation
        </button>
      </div>
    </div>

  </div>
</div>

@endsection


@push('scripts')
<script>

/* ══════════════════════════════════════════════════════════
   CONFIG
══════════════════════════════════════════════════════════ */
const CSRF        = '{{ csrf_token() }}';
const URL_CONTRAT = '{{ url("contrats") }}';

/* ══════════════════════════════════════════════════════════
   HELPERS — MODALS
══════════════════════════════════════════════════════════ */
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

/* ══════════════════════════════════════════════════════════
   HELPERS — ONGLETS MODAL DETAIL
══════════════════════════════════════════════════════════ */
function switchTab(tabId) {
  document.querySelectorAll('.tab-panel').forEach(p => p.style.display = 'none');
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById(tabId).style.display = 'block';

  const btnMap = { tabInfos: 'tbInfos', tabPaiements: 'tbPaiements', tabTransactions: 'tbTransactions' };
  document.getElementById(btnMap[tabId]).classList.add('active');
}

/* ══════════════════════════════════════════════════════════
   HELPERS — VALIDATION INLINE
══════════════════════════════════════════════════════════ */
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

/* ══════════════════════════════════════════════════════════
   TOAST ERP
══════════════════════════════════════════════════════════ */
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
    padding:12px 16px;min-width:280px;max-width:360px;
    background:#fff;border-radius:12px;
    box-shadow:0 10px 40px rgba(0,0,0,.13);
    border:1px solid var(--border-2);
    animation:_toastIn .3s ease;
    font-family:var(--font-b)`;
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

/* ══════════════════════════════════════════════════════════
   FETCH WRAPPER
══════════════════════════════════════════════════════════ */
async function apiFetch(url, method, body) {
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

/* ══════════════════════════════════════════════════════════
   FILTRE TABLEAU (client-side)
══════════════════════════════════════════════════════════ */
function filterTable() {
  const q      = document.getElementById('searchInput').value.toLowerCase();
  const statut = document.getElementById('filterStatut').value;
  let visible  = 0;

  document.querySelectorAll('#contratsTbody tr[data-id]').forEach(tr => {
    const mQ = !q      || tr.textContent.toLowerCase().includes(q);
    const mS = !statut || tr.dataset.statut === statut;
    const show = mQ && mS;
    tr.style.display = show ? '' : 'none';
    if (show) visible++;
  });

  const c = document.getElementById('countVisible');
  if (c) c.textContent = visible;
}

/* ══════════════════════════════════════════════════════════
   EXPORT (ouverture simple dans un onglet)
══════════════════════════════════════════════════════════ */
function exportContrats() {
  showToast('Export en cours de développement.', 'warning');
}

/* ══════════════════════════════════════════════════════════
   SUBMIT — AJOUTER
══════════════════════════════════════════════════════════ */
async function submitAjouter() {
  clearErrors(['errAddAppartement','errAddLocataire','errAddTypeContrat',
               'errAddTypePaiement','errAddDateDebut','errAddDateFin',
               'errAddLoyer','errAddCaution']);

  const appartement_id  = document.getElementById('addAppartement').value;
  const locataire_id    = document.getElementById('addLocataire').value;
  const type_contrat    = document.getElementById('addTypeContrat').value;
  const type_paiement   = document.getElementById('addTypePaiement').value;
  const date_debut      = document.getElementById('addDateDebut').value;
  const date_fin        = document.getElementById('addDateFin').value;
  const loyer           = parseFloat(document.getElementById('addLoyer').value);
  const caution         = parseFloat(document.getElementById('addCaution').value);
  const commentaire     = document.getElementById('addCommentaire').value;

  let ok = true;
  if (!appartement_id)    { showError('errAddAppartement',  'Sélectionnez un appartement.');    ok = false; }
  if (!locataire_id)      { showError('errAddLocataire',    'Sélectionnez un locataire.');      ok = false; }
  if (!type_contrat)      { showError('errAddTypeContrat',  'Sélectionnez le type de bail.');   ok = false; }
  if (!type_paiement)     { showError('errAddTypePaiement', 'Sélectionnez la fréquence.');      ok = false; }
  if (!date_debut)        { showError('errAddDateDebut',    'La date de début est requise.');   ok = false; }
  if (date_fin && date_fin < date_debut) {
    showError('errAddDateFin', 'La date de fin doit être ≥ à la date de début.'); ok = false;
  }
  if (!loyer || loyer <= 0)      { showError('errAddLoyer',   'Le loyer doit être > 0.');   ok = false; }
  if (isNaN(caution) || caution < 0) { showError('errAddCaution','La caution doit être ≥ 0.'); ok = false; }
  if (!ok) return;

  setLoading('btnAjouter', true, '<i class="fa-solid fa-check"></i> Créer le contrat');

  try {
    const data = await apiFetch(URL_CONTRAT, 'POST', {
      appartement_id, locataire_id, type_contrat, type_paiement,
      date_debut, date_fin: date_fin || null,
      loyer, caution, commentaire: commentaire || null,
    });
    setLoading('btnAjouter', false, '<i class="fa-solid fa-check"></i> Créer le contrat');

    if (data.success) {
      closeModal('modalAjouter');
      showToast('Contrat créé avec succès.');
      setTimeout(() => location.reload(), 900);
    } else {
      if (data.errors) {
        Object.entries(data.errors).forEach(([field, msgs]) => {
          const map = {
            appartement_id: 'errAddAppartement', locataire_id: 'errAddLocataire',
            type_contrat: 'errAddTypeContrat',   type_paiement: 'errAddTypePaiement',
            date_debut: 'errAddDateDebut',       date_fin: 'errAddDateFin',
            loyer: 'errAddLoyer',                caution: 'errAddCaution',
          };
          if (map[field]) showError(map[field], msgs[0]);
        });
      }
      showToast(data.message || 'Erreur lors de la création.', 'error');
    }
  } catch {
    setLoading('btnAjouter', false, '<i class="fa-solid fa-check"></i> Créer le contrat');
    showToast('Erreur réseau.', 'error');
  }
}

/* ══════════════════════════════════════════════════════════
   OPEN — DETAIL
══════════════════════════════════════════════════════════ */
let _currentContratId = null;
let _currentContratData = null;

async function openDetail(id) {
  _currentContratId = id;
  switchTab('tabInfos');

  document.getElementById('dRef').textContent    = '…';
  document.getElementById('dTitre').textContent  = 'Chargement…';
  document.getElementById('dSub').textContent    = '';
  document.getElementById('dBadge').className    = 's-badge';
  document.getElementById('dBadge').textContent  = '';

  openModal('modalDetail');

  try {
    const data = await apiFetch(URL_CONTRAT + '/' + id, 'GET');
    if (!data.success) { showToast('Impossible de charger le contrat.', 'error'); return; }

    const c = data.contrat;
    _currentContratData = c;

    // ── Référence & badge ──
    const ref = 'CTR-' + c.created_at.substring(0,4) + '-' + String(c.id).padStart(3,'0');
    document.getElementById('dRef').textContent = ref;

    const statutLabel = { actif:'Actif', en_attente:'En attente', termine:'Terminé', resilie:'Résilié' };
    const statutClass = { actif:'active', en_attente:'late', termine:'inactive', resilie:'inactive' };
    const badge = document.getElementById('dBadge');
    badge.textContent = statutLabel[c.statut] ?? c.statut;
    badge.className   = 's-badge ' + (statutClass[c.statut] ?? 'inactive');

    // ── Titre & sous-titre ──
    const loc = c.locataire;
    document.getElementById('dTitre').textContent = loc
      ? `Contrat — ${loc.prenom} ${loc.nom}`
      : 'Contrat';
    document.getElementById('dSub').textContent = c.appartement
      ? `Apt. ${c.appartement.numero}${c.appartement.residence ? ' · ' + c.appartement.residence.nom : ''}`
      : '—';

    // ── Infos grid ──
    document.getElementById('dLoyer').textContent       = Number(c.loyer).toLocaleString('fr-FR') + ' MAD';
    document.getElementById('dCaution').textContent     = Number(c.caution).toLocaleString('fr-FR') + ' MAD';
    document.getElementById('dTypePaiement').textContent= c.type_paiement;
    document.getElementById('dTypeContrat').textContent = c.type_contrat.replace('_', ' ');
    document.getElementById('dDateDebut').textContent   = formatDate(c.date_debut);
    document.getElementById('dDateFin').textContent     = c.date_fin ? formatDate(c.date_fin) : 'Indéterminée';

    // ── Locataire ──
    if (loc) {
      const init = (loc.prenom[0] ?? '') + (loc.nom[0] ?? '');
      document.getElementById('dLocataireAvatar').textContent = init.toUpperCase();
      document.getElementById('dLocataireName').textContent   = loc.prenom + ' ' + loc.nom;
      document.getElementById('dLocataireEmail').textContent  = loc.email ?? '—';
      document.getElementById('dLocataireTel').textContent    = loc.telephone ?? '—';
    }

    // ── Propriétaire ──
    const prop = c.proprietaire;
    if (prop) {
      const initP = ((prop.prenom ?? '')[0] ?? '') + ((prop.nom ?? '')[0] ?? '');
      document.getElementById('dPropAvatar').textContent = initP.toUpperCase();
      document.getElementById('dPropName').textContent   = (prop.prenom ?? '') + ' ' + (prop.nom ?? '');
      document.getElementById('dPropEmail').textContent  = prop.email ?? '—';
    }

    // ── Appartement ──
    if (c.appartement) {
      document.getElementById('dAppartement').textContent = `Apt. ${c.appartement.numero}`;
      document.getElementById('dResidence').textContent   = c.appartement.residence?.nom ?? '—';
    }

    // ── Boutons footer : cacher si résilié/terminé ──
    const peut = !['resilie','termine'].includes(c.statut);
    document.getElementById('dBtnResilier').style.display = peut ? '' : 'none';
    document.getElementById('dBtnModifier').style.display = peut ? '' : 'none';

    // ── Pré-charger paiements & transactions ──
    renderPaiements(data.paiements);
    renderTransactions(data.transactions);

  } catch (err) {
    showToast('Erreur réseau lors du chargement.', 'error');
  }
}

function renderPaiements(paiements) {
  document.getElementById('paiementsLoading').style.display = 'none';

  if (!paiements || paiements.length === 0) {
    document.getElementById('paiementsEmpty').style.display = 'block';
    return;
  }

  const statutPay = { en_attente:'late', partiel:'warning', paye:'active', en_retard:'inactive' };
  const statutLbl = { en_attente:'En attente', partiel:'Partiel', paye:'Payé', en_retard:'En retard' };

  const rows = paiements.map(p => `
    <tr>
      <td style="font-size:12.5px;color:var(--text-3)">${formatDate(p.periode_debut)} → ${formatDate(p.periode_fin)}</td>
      <td><span class="date-text">${formatDate(p.date_echeance)}</span></td>
      <td style="font-weight:700">${Number(p.montant).toLocaleString('fr-FR')} MAD</td>
      <td style="color:var(--green-t);font-weight:600">${Number(p.montant_paye).toLocaleString('fr-FR')} MAD</td>
      <td><span class="s-badge ${statutPay[p.statut] ?? 'inactive'}">${statutLbl[p.statut] ?? p.statut}</span></td>
    </tr>`).join('');

  document.getElementById('paiementsTbody').innerHTML = rows;
  document.getElementById('paiementsList').style.display = 'block';
}

function renderTransactions(transactions) {
  document.getElementById('transactionsLoading').style.display = 'none';

  if (!transactions || transactions.length === 0) {
    document.getElementById('transactionsEmpty').style.display = 'block';
    return;
  }

  const rows = transactions.map(t => `
    <tr>
      <td><span class="date-text">${formatDate(t.date_paiement)}</span></td>
      <td style="font-weight:700;color:var(--green-t)">${Number(t.montant).toLocaleString('fr-FR')} MAD</td>
      <td style="font-size:12.5px;text-transform:capitalize">${t.mode_paiement ?? '—'}</td>
      <td><span class="ref-code" style="font-size:11px">${t.reference ?? '—'}</span></td>
      <td style="font-size:12px;color:var(--text-3)">${t.commentaire ?? '—'}</td>
    </tr>`).join('');

  document.getElementById('transactionsTbody').innerHTML = rows;
  document.getElementById('transactionsList').style.display = 'block';
}

/* ══════════════════════════════════════════════════════════
   OPEN — MODIFIER
══════════════════════════════════════════════════════════ */
function openModifier(id, loyer, caution, dateFin) {
  _currentContratId = id;
  document.getElementById('modId').value         = id;
  document.getElementById('modLoyer').value      = loyer;
  document.getElementById('modCaution').value    = caution;
  document.getElementById('modDateFin').value    = dateFin || '';
  document.getElementById('modCommentaire').value= '';
  document.getElementById('modSubtitle').textContent = 'Contrat #' + id;
  clearErrors(['errModLoyer','errModCaution','errModDateFin']);
  openModal('modalModifier');
}

function openModifierDepuisDetail() {
  if (!_currentContratData) return;
  const c = _currentContratData;
  closeModal('modalDetail');
  openModifier(c.id, c.loyer, c.caution, c.date_fin ? c.date_fin.substring(0,10) : '');
}

/* ══════════════════════════════════════════════════════════
   SUBMIT — MODIFIER
══════════════════════════════════════════════════════════ */
async function submitModifier() {
  clearErrors(['errModLoyer','errModCaution','errModDateFin']);

  const id      = document.getElementById('modId').value;
  const loyer   = parseFloat(document.getElementById('modLoyer').value);
  const caution = parseFloat(document.getElementById('modCaution').value);
  const dateFin = document.getElementById('modDateFin').value;

  let ok = true;
  if (!loyer || loyer <= 0)       { showError('errModLoyer',   'Le loyer doit être > 0.');   ok = false; }
  if (isNaN(caution) || caution < 0) { showError('errModCaution','La caution doit être ≥ 0.'); ok = false; }
  if (!ok) return;

  setLoading('btnModifier', true, '<i class="fa-solid fa-circle-check"></i> Enregistrer');

  try {
    const data = await apiFetch(URL_CONTRAT + '/' + id, 'PUT', {
      loyer, caution,
      date_fin: dateFin || null,
      commentaire: document.getElementById('modCommentaire').value || null,
    });
    setLoading('btnModifier', false, '<i class="fa-solid fa-circle-check"></i> Enregistrer');

    if (data.success) {
      closeModal('modalModifier');
      showToast('Contrat modifié avec succès.');
      setTimeout(() => location.reload(), 900);
    } else {
      if (data.errors) {
        if (data.errors.loyer)    showError('errModLoyer',   data.errors.loyer[0]);
        if (data.errors.caution)  showError('errModCaution', data.errors.caution[0]);
        if (data.errors.date_fin) showError('errModDateFin', data.errors.date_fin[0]);
      }
      showToast(data.message || 'Erreur.', 'error');
    }
  } catch {
    setLoading('btnModifier', false, '<i class="fa-solid fa-circle-check"></i> Enregistrer');
    showToast('Erreur réseau.', 'error');
  }
}

/* ══════════════════════════════════════════════════════════
   OPEN — RÉSILIER
══════════════════════════════════════════════════════════ */
function openResilier(id, ref) {
  _currentContratId = id;
  document.getElementById('resId').value = id;
  document.getElementById('resSub').textContent = ref || 'Contrat #' + id;
  document.getElementById('resDate').value       = new Date().toISOString().slice(0,10);
  document.getElementById('resMotif').value      = '';
  document.getElementById('resCommentaire').value= '';
  clearErrors(['errResDate','errResMotif']);
  openModal('modalResilier');
}

function openResilierDepuisDetail() {
  if (!_currentContratData) return;
  const c   = _currentContratData;
  const ref = 'CTR-' + c.created_at.substring(0,4) + '-' + String(c.id).padStart(3,'0');
  closeModal('modalDetail');
  openResilier(c.id, ref);
}

/* ══════════════════════════════════════════════════════════
   SUBMIT — RÉSILIER
══════════════════════════════════════════════════════════ */
async function submitResilier() {
  clearErrors(['errResDate','errResMotif']);

  const id          = document.getElementById('resId').value;
  const date        = document.getElementById('resDate').value;
  const motif       = document.getElementById('resMotif').value.trim();
  const commentaire = document.getElementById('resCommentaire').value;

  let ok = true;
  if (!date)  { showError('errResDate',  'La date de résiliation est requise.'); ok = false; }
  if (!motif) { showError('errResMotif', 'Le motif est requis.');               ok = false; }
  if (!ok) return;

  setLoading('btnResilier', true, '<i class="fa-solid fa-ban"></i> Confirmer la résiliation');

  try {
    const data = await apiFetch(URL_CONTRAT + '/' + id + '/resilier', 'POST', {
      date_resiliation: date,
      motif,
      commentaire: commentaire || null,
    });
    setLoading('btnResilier', false, '<i class="fa-solid fa-ban"></i> Confirmer la résiliation');

    if (data.success) {
      closeModal('modalResilier');
      showToast('Contrat résilié. Appartement libéré.', 'warning');
      setTimeout(() => location.reload(), 900);
    } else {
      showToast(data.message || 'Erreur.', 'error');
    }
  } catch {
    setLoading('btnResilier', false, '<i class="fa-solid fa-ban"></i> Confirmer la résiliation');
    showToast('Erreur réseau.', 'error');
  }
}

/* ══════════════════════════════════════════════════════════
   UTILS
══════════════════════════════════════════════════════════ */
function formatDate(str) {
  if (!str) return '—';
  const d = new Date(str);
  if (isNaN(d)) return str;
  return d.toLocaleDateString('fr-FR', { day:'2-digit', month:'2-digit', year:'numeric' });
}

/* ── Styles injectés ─────────────────────────────────────── */
const _s = document.createElement('style');
_s.textContent = `
  @keyframes _toastIn { from { opacity:0;transform:translateX(30px) } to { opacity:1;transform:none } }

  .form-err {
    display: none;
    font-size: 12px;
    color: var(--red-t);
    margin-top: 4px;
  }

  /* Onglets dans le modal détail */
  .tab-btn {
    padding: 11px 18px;
    font-size: 13px;
    font-weight: 600;
    font-family: var(--font-b);
    color: var(--text-3);
    background: transparent;
    border: none;
    border-bottom: 2.5px solid transparent;
    cursor: pointer;
    transition: all .18s;
    margin-bottom: -2px;
  }
  .tab-btn:hover  { color: var(--brand); }
  .tab-btn.active { color: var(--brand); border-bottom-color: var(--brand); }
`;
document.head.appendChild(_s);

</script>
@endpush