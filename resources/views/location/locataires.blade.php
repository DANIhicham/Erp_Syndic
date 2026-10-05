@extends('layouts.layout')

@section('title', 'Locataires')
@section('page_title', 'Locataires')

@section('content')

{{-- ════════════════════════════════════════════════════════════════
     PAGE HEADER
════════════════════════════════════════════════════════════════ --}}
<div class="page-header">
  <div class="ph-left">
    <h2>Locataires</h2>
    <p>Gestion des locataires et de leurs documents</p>
  </div>
  <div class="ph-right">
    <button class="btn-outline-erp" onclick="showToast('Export en cours de développement.', 'warning')">
      <i class="fa-solid fa-download"></i> Exporter
    </button>
    <button class="btn-primary-erp" onclick="openModal('modalAjouter')">
      <i class="fa-solid fa-user-plus"></i> Nouveau locataire
    </button>
  </div>
</div>

{{-- ════════════════════════════════════════════════════════════════
     KPI CARDS
════════════════════════════════════════════════════════════════ --}}
<div class="kpi-row" style="grid-template-columns: repeat(4, 1fr)">

  <div class="kpi-card kpi-blue" style="animation-delay:.05s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Total locataires</div>
        <div class="kpi-value">{{ $kpis['total'] }}</div>
        <div class="kpi-sub">enregistrés</div>
      </div>
      <div class="kpi-icon blue"><i class="fa-solid fa-users"></i></div>
    </div>
  </div>

  <div class="kpi-card kpi-green" style="animation-delay:.10s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Actifs</div>
        <div class="kpi-value">{{ $kpis['actifs'] }}</div>
        <div class="kpi-sub">comptes actifs</div>
      </div>
      <div class="kpi-icon green"><i class="fa-solid fa-user-check"></i></div>
    </div>
  </div>

  <div class="kpi-card kpi-red" style="animation-delay:.15s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Inactifs</div>
        <div class="kpi-value">{{ $kpis['inactifs'] }}</div>
        <div class="kpi-sub">comptes suspendus</div>
      </div>
      <div class="kpi-icon red"><i class="fa-solid fa-user-slash"></i></div>
    </div>
  </div>

  <div class="kpi-card kpi-orange" style="animation-delay:.20s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Contrats actifs</div>
        <div class="kpi-value">{{ $kpis['contrats_actifs'] }}</div>
        <div class="kpi-sub">baux en cours</div>
      </div>
      <div class="kpi-icon orange"><i class="fa-solid fa-file-contract"></i></div>
    </div>
  </div>

</div>

{{-- ════════════════════════════════════════════════════════════════
     FILTER BAR
════════════════════════════════════════════════════════════════ --}}
<div class="filter-bar">
  <div class="filter-search">
    <i class="fa-solid fa-magnifying-glass"></i>
    <input type="text" id="searchInput" placeholder="Nom, CIN, téléphone, email…" oninput="filterTable()">
  </div>
  <select id="filterEtat" class="filter-select" onchange="filterTable()">
    <option value="">Tous</option>
    <option value="active">Actifs</option>
    <option value="desactive">Inactifs</option>
  </select>
  <div class="filter-divider"></div>
  <div class="filter-count">
    <strong id="countVisible">{{ $locataires->count() }}</strong>
    locataire{{ $locataires->count() !== 1 ? 's' : '' }}
  </div>
</div>

{{-- ════════════════════════════════════════════════════════════════
     TABLEAU PRINCIPAL
════════════════════════════════════════════════════════════════ --}}
<div class="table-card">
  <div class="table-card-header">
    <div class="tch-left">
      <h5>Liste des Locataires</h5>
      <p>{{ $locataires->count() }} locataire{{ $locataires->count() !== 1 ? 's' : '' }} enregistré{{ $locataires->count() !== 1 ? 's' : '' }}</p>
    </div>
  </div>

  <div class="table-responsive">
    <table class="erp-table">
      <thead>
        <tr>
          <th>Nom complet</th>
          <th>CIN</th>
          <th>Téléphone</th>
          <th>Email</th>
          <th>Contrats</th>
          <th>État</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="locatairesTbody">

        @forelse($locataires as $loc)
          @php
            $palette   = ['#4f46e5','#0891b2','#059669','#d97706','#7c3aed','#db2777','#0f766e','#dc2626'];
            $color     = $loc->etat === 'desactive' ? '#94a3b8' : $palette[$loc->id % count($palette)];
            $initiales = strtoupper(substr($loc->prenom,0,1)).strtoupper(substr($loc->nom,0,1));
            $nbContrats = $loc->contratsLocataire->count();
            $contratActif = $loc->contratsLocataire->where('statut','actif')->first();
          @endphp
          <tr data-id="{{ $loc->id }}" data-etat="{{ $loc->etat }}">

            {{-- Nom complet --}}
            <td>
              <div class="tenant-cell">
                <div class="t-avatar" style="background:{{ $color }}">{{ $initiales }}</div>
                <div>
                  <span class="t-name">{{ $loc->prenom }} {{ $loc->nom }}</span>
                  @if($contratActif && $contratActif->appartement)
                    <span class="t-type">
                      <i class="fa-solid fa-building" style="font-size:10px;margin-right:3px"></i>
                      Apt. {{ $contratActif->appartement->numero }}
                      @if($contratActif->appartement->residence)
                        — {{ $contratActif->appartement->residence->nom }}
                      @endif
                    </span>
                  @endif
                </div>
              </div>
            </td>

            {{-- CIN --}}
            <td><span class="ref-code" style="font-size:12px">{{ $loc->cin ?? '—' }}</span></td>

            {{-- Téléphone --}}
            <td><span style="font-size:13px;color:var(--text-3)">{{ $loc->telephone ?? '—' }}</span></td>

            {{-- Email --}}
            <td><span style="font-size:13px;color:var(--text-3)">{{ $loc->email }}</span></td>

            {{-- Nombre de contrats --}}
            <td>
              @if($nbContrats > 0)
                <span class="s-badge {{ $contratActif ? 'active' : 'inactive' }}" style="font-size:12px">
                  {{ $nbContrats }} contrat{{ $nbContrats > 1 ? 's' : '' }}
                  {{ $contratActif ? '· Actif' : '' }}
                </span>
              @else
                <span style="color:var(--text-4);font-size:13px">Aucun</span>
              @endif
            </td>

            {{-- État --}}
            <td>
              @if($loc->etat === 'active')
                <span class="s-badge active">
                  <i class="fa-solid fa-circle" style="font-size:7px;margin-right:4px"></i>Actif
                </span>
              @else
                <span class="s-badge inactive">
                  <i class="fa-regular fa-circle" style="font-size:7px;margin-right:4px"></i>Inactif
                </span>
              @endif
            </td>

            {{-- Actions --}}
            <td>
              <div class="row-actions">

                <button class="ra-btn view" title="Voir le détail" onclick="openDetail({{ $loc->id }})">
                  <i class="fa-solid fa-eye"></i>
                </button>

                <button
                  class="ra-btn edit"
                  title="Modifier"
                  onclick="openModifier(
                    {{ $loc->id }},
                    '{{ addslashes($loc->nom) }}',
                    '{{ addslashes($loc->prenom) }}',
                    '{{ $loc->telephone ?? '' }}',
                    '{{ $loc->email }}',
                    '{{ $loc->etat }}'
                  )"
                >
                  <i class="fa-solid fa-pen-to-square"></i>
                </button>

                @if($loc->etat === 'active')
                  <button class="ra-btn" style="color:var(--orange-t)" title="Désactiver"
                    onclick="openToggle({{ $loc->id }}, 'desactiver', '{{ addslashes($loc->prenom) }} {{ addslashes($loc->nom) }}')">
                    <i class="fa-solid fa-user-minus"></i>
                  </button>
                @else
                  <button class="ra-btn" style="color:var(--green-t)" title="Réactiver"
                    onclick="openToggle({{ $loc->id }}, 'reactiver', '{{ addslashes($loc->prenom) }} {{ addslashes($loc->nom) }}')">
                    <i class="fa-solid fa-user-check"></i>
                  </button>
                @endif

              </div>
            </td>

          </tr>
        @empty
          <tr>
            <td colspan="7" style="text-align:center;padding:40px;color:var(--text-4);font-size:14px">
              <i class="fa-solid fa-users" style="font-size:30px;margin-bottom:10px;display:block"></i>
              Aucun locataire trouvé.
            </td>
          </tr>
        @endforelse

      </tbody>
    </table>
  </div>

  <div class="table-footer">
    <span class="tf-info">
      <strong>{{ $locataires->count() }}</strong>
      locataire{{ $locataires->count() !== 1 ? 's' : '' }} affiché{{ $locataires->count() !== 1 ? 's' : '' }}
    </span>
  </div>
</div>


{{-- ════════════════════════════════════════════════════════════════
     MODAL — AJOUTER LOCATAIRE
════════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalAjouter">
  <div class="modal-panel">
    <div class="modal-head">
      <div>
        <h3 class="mh-title">Nouveau Locataire</h3>
        <p class="mh-sub">Le mot de passe sera généré automatiquement.</p>
      </div>
      <div class="mh-right">
        <button class="modal-close" onclick="closeModal('modalAjouter')"><i class="fa-solid fa-xmark"></i></button>
      </div>
    </div>

    <div class="modal-body">

      <div class="form-section-label">
        <i class="fa-solid fa-user" style="color:var(--brand)"></i>
        Informations personnelles
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="form-group">
          <label class="form-label">Nom <span style="color:var(--red-t)">*</span></label>
          <input type="text" id="addNom" class="form-control" placeholder="Ex : Benali">
          <div id="errAddNom" class="form-err"></div>
        </div>
        <div class="form-group">
          <label class="form-label">Prénom <span style="color:var(--red-t)">*</span></label>
          <input type="text" id="addPrenom" class="form-control" placeholder="Ex : Karim">
          <div id="errAddPrenom" class="form-err"></div>
        </div>
        <div class="form-group">
          <label class="form-label">CIN <span style="color:var(--red-t)">*</span></label>
          <input type="text" id="addCin" class="form-control" placeholder="Ex : AB123456">
          <div id="errAddCin" class="form-err"></div>
        </div>
        <div class="form-group">
          <label class="form-label">Téléphone <span style="color:var(--red-t)">*</span></label>
          <input type="text" id="addTel" class="form-control" placeholder="Ex : 06 00 00 00 00">
          <div id="errAddTel" class="form-err"></div>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Email <span style="color:var(--red-t)">*</span></label>
        <input type="email" id="addEmail" class="form-control" placeholder="karim@email.ma">
        <div id="errAddEmail" class="form-err"></div>
      </div>

      <div class="form-section-label" style="margin-top:6px">
        <i class="fa-solid fa-id-card" style="color:var(--brand)"></i>
        Documents CIN
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="form-group">
          <label class="form-label">CIN Recto</label>
          <div class="upload-zone" onclick="document.getElementById('addCinRecto').click()">
            <i class="fa-solid fa-upload" style="font-size:20px;color:var(--text-4)"></i>
            <div style="font-size:12.5px;color:var(--text-3);margin-top:6px" id="addCinRectoLabel">
              Cliquer pour uploader
            </div>
            <div style="font-size:11px;color:var(--text-4)">JPG, PNG ou PDF · max 5 Mo</div>
          </div>
          <input type="file" id="addCinRecto" accept=".jpg,.jpeg,.png,.pdf" style="display:none"
            onchange="previewFile('addCinRecto','addCinRectoLabel')">
          <div id="errAddCinRecto" class="form-err"></div>
        </div>

        <div class="form-group">
          <label class="form-label">CIN Verso</label>
          <div class="upload-zone" onclick="document.getElementById('addCinVerso').click()">
            <i class="fa-solid fa-upload" style="font-size:20px;color:var(--text-4)"></i>
            <div style="font-size:12.5px;color:var(--text-3);margin-top:6px" id="addCinVersoLabel">
              Cliquer pour uploader
            </div>
            <div style="font-size:11px;color:var(--text-4)">JPG, PNG ou PDF · max 5 Mo</div>
          </div>
          <input type="file" id="addCinVerso" accept=".jpg,.jpeg,.png,.pdf" style="display:none"
            onchange="previewFile('addCinVerso','addCinVersoLabel')">
          <div id="errAddCinVerso" class="form-err"></div>
        </div>
      </div>

    </div>

    <div class="modal-foot">
      <div class="mf-left"></div>
      <div class="mf-right">
        <button class="btn-outline-erp" onclick="closeModal('modalAjouter')">Annuler</button>
        <button class="btn-primary-erp" id="btnAjouter" onclick="submitAjouter()">
          <i class="fa-solid fa-user-plus"></i> Créer le locataire
        </button>
      </div>
    </div>
  </div>
</div>


{{-- ════════════════════════════════════════════════════════════════
     MODAL — DÉTAIL LOCATAIRE (5 onglets)
════════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalDetail">
  <div class="modal-panel" style="max-width:800px">
    <div class="modal-head">
      <div style="display:flex;align-items:center;gap:12px">
        <div class="t-avatar" id="dAvatar" style="background:#4f46e5;width:44px;height:44px;font-size:16px;border-radius:12px">—</div>
        <div>
          <h3 class="mh-title" id="dNom">—</h3>
          <p class="mh-sub" id="dSub">—</p>
        </div>
        <span class="s-badge" id="dBadge" style="margin-left:6px"></span>
      </div>
      <div class="mh-right">
        <button class="modal-close" onclick="closeModal('modalDetail')"><i class="fa-solid fa-xmark"></i></button>
      </div>
    </div>

    {{-- ONGLETS --}}
    <div style="display:flex;gap:0;border-bottom:2px solid var(--border-1);padding:0 24px;background:var(--surface-1)">
      <button class="tab-btn active" onclick="switchTab('dTabInfos')" id="tbDInfos">
        <i class="fa-solid fa-circle-info" style="margin-right:5px"></i>Informations
      </button>
      <button class="tab-btn" onclick="switchTab('dTabContrats')" id="tbDContrats">
        <i class="fa-solid fa-file-contract" style="margin-right:5px"></i>Contrats
      </button>
      <button class="tab-btn" onclick="switchTab('dTabPaiements')" id="tbDPaiements">
        <i class="fa-solid fa-calendar-check" style="margin-right:5px"></i>Paiements
      </button>
      <!-- <button class="tab-btn" onclick="switchTab('dTabReclamations')" id="tbDReclamations">
        <i class="fa-solid fa-flag" style="margin-right:5px"></i>Réclamations
      </button> -->
      <button class="tab-btn" onclick="switchTab('dTabDocuments')" id="tbDDocuments">
        <i class="fa-solid fa-folder-open" style="margin-right:5px"></i>Documents
      </button>
    </div>

    <div class="modal-body">

      {{-- ── ONGLET INFORMATIONS ── --}}
      <div id="dTabInfos" class="tab-panel">
        <div class="form-section-label">
          <i class="fa-solid fa-user" style="color:var(--brand)"></i>Identité
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px">
          <div class="detail-card">
            <div class="dc-label">Nom complet</div>
            <div class="dc-value" id="dFullName">—</div>
          </div>
          <div class="detail-card">
            <div class="dc-label">CIN</div>
            <div class="dc-value"><span class="ref-code" id="dCin">—</span></div>
          </div>
          <div class="detail-card">
            <div class="dc-label">Téléphone</div>
            <div class="dc-value" id="dTel">—</div>
          </div>
          <div class="detail-card" style="grid-column:span 2">
            <div class="dc-label">Email</div>
            <div class="dc-value" id="dEmail">—</div>
          </div>
          <div class="detail-card">
            <div class="dc-label">Membre depuis</div>
            <div class="dc-value" id="dCreatedAt">—</div>
          </div>
        </div>
      </div>

      {{-- ── ONGLET CONTRATS ── --}}
      <div id="dTabContrats" class="tab-panel" style="display:none">
        <div class="form-section-label">
          <i class="fa-solid fa-file-contract" style="color:var(--brand)"></i>Historique des contrats
        </div>
        <div id="dContratsList">
          <div class="tab-loading"><i class="fa-solid fa-spinner fa-spin"></i></div>
        </div>
      </div>

      {{-- ── ONGLET PAIEMENTS ── --}}
      <div id="dTabPaiements" class="tab-panel" style="display:none">
        <div class="form-section-label">
          <i class="fa-solid fa-calendar-check" style="color:var(--brand)"></i>Échéances &amp; Transactions
        </div>
        <div id="dPaiementsList">
          <div class="tab-loading"><i class="fa-solid fa-spinner fa-spin"></i></div>
        </div>
      </div>

      {{-- ── ONGLET RÉCLAMATIONS ── --}}
      <div id="dTabReclamations" class="tab-panel" style="display:none">
        <div class="form-section-label">
          <i class="fa-solid fa-flag" style="color:var(--brand)"></i>Réclamations
        </div>
        <div id="dReclamationsList">
          <div class="tab-loading"><i class="fa-solid fa-spinner fa-spin"></i></div>
        </div>
      </div>

      {{-- ── ONGLET DOCUMENTS ── --}}
      <div id="dTabDocuments" class="tab-panel" style="display:none">
        <div class="form-section-label">
          <i class="fa-solid fa-folder-open" style="color:var(--brand)"></i>Documents CIN &amp; autres
        </div>
        <div id="dDocumentsList">
          <div class="tab-loading"><i class="fa-solid fa-spinner fa-spin"></i></div>
        </div>
      </div>

    </div>{{-- /modal-body --}}

    <div class="modal-foot">
      <div class="mf-left">
        <button id="dBtnToggle" class="btn-outline-erp" style="color:var(--orange-t);border-color:var(--orange-bg)"
          onclick="openToggleDepuisDetail()">
          <i class="fa-solid fa-user-minus"></i> Désactiver
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
     MODAL — MODIFIER LOCATAIRE
════════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalModifier">
  <div class="modal-panel" style="max-width:560px">
    <div class="modal-head">
      <div>
        <h3 class="mh-title">Modifier le locataire</h3>
        <p class="mh-sub" id="modSubtitle">—</p>
      </div>
      <div class="mh-right">
        <button class="modal-close" onclick="closeModal('modalModifier')"><i class="fa-solid fa-xmark"></i></button>
      </div>
    </div>

    <div class="modal-body">
      <input type="hidden" id="modId">

      <div class="form-section-label">
        <i class="fa-solid fa-pen-to-square" style="color:var(--brand)"></i>
        Informations modifiables
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="form-group">
          <label class="form-label">Nom <span style="color:var(--red-t)">*</span></label>
          <input type="text" id="modNom" class="form-control">
          <div id="errModNom" class="form-err"></div>
        </div>
        <div class="form-group">
          <label class="form-label">Prénom <span style="color:var(--red-t)">*</span></label>
          <input type="text" id="modPrenom" class="form-control">
          <div id="errModPrenom" class="form-err"></div>
        </div>
        <div class="form-group">
          <label class="form-label">Téléphone <span style="color:var(--red-t)">*</span></label>
          <input type="text" id="modTel" class="form-control">
          <div id="errModTel" class="form-err"></div>
        </div>
        <div class="form-group">
          <label class="form-label">État</label>
          <select id="modEtat" class="form-control">
            <option value="active">Actif</option>
            <option value="desactive">Inactif</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Email <span style="color:var(--red-t)">*</span></label>
        <input type="email" id="modEmail" class="form-control">
        <div id="errModEmail" class="form-err"></div>
      </div>

      <div class="form-section-label" style="margin-top:6px">
        <i class="fa-solid fa-id-card" style="color:var(--brand)"></i>
        Mettre à jour les documents CIN
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="form-group">
          <label class="form-label">CIN Recto <span style="font-size:11px;color:var(--text-4)">(optionnel)</span></label>
          <div class="upload-zone" onclick="document.getElementById('modCinRecto').click()">
            <i class="fa-solid fa-arrow-up-from-bracket" style="font-size:18px;color:var(--text-4)"></i>
            <div style="font-size:12px;color:var(--text-3);margin-top:5px" id="modCinRectoLabel">Remplacer le fichier</div>
          </div>
          <input type="file" id="modCinRecto" accept=".jpg,.jpeg,.png,.pdf" style="display:none"
            onchange="previewFile('modCinRecto','modCinRectoLabel')">
        </div>
        <div class="form-group">
          <label class="form-label">CIN Verso <span style="font-size:11px;color:var(--text-4)">(optionnel)</span></label>
          <div class="upload-zone" onclick="document.getElementById('modCinVerso').click()">
            <i class="fa-solid fa-arrow-up-from-bracket" style="font-size:18px;color:var(--text-4)"></i>
            <div style="font-size:12px;color:var(--text-3);margin-top:5px" id="modCinVersoLabel">Remplacer le fichier</div>
          </div>
          <input type="file" id="modCinVerso" accept=".jpg,.jpeg,.png,.pdf" style="display:none"
            onchange="previewFile('modCinVerso','modCinVersoLabel')">
        </div>
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
     MODAL — ACTIVER / DÉSACTIVER
════════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalToggle">
  <div class="modal-panel" style="max-width:460px">
    <div class="modal-head">
      <div>
        <h3 class="mh-title" id="toggleTitle">Désactiver le locataire</h3>
        <p class="mh-sub" id="toggleSubtitle">—</p>
      </div>
      <div class="mh-right">
        <button class="modal-close" onclick="closeModal('modalToggle')"><i class="fa-solid fa-xmark"></i></button>
      </div>
    </div>
    <div class="modal-body">
      <input type="hidden" id="toggleId">
      <div id="toggleAlertBox" style="border-radius:var(--r-md);padding:16px">
        <p style="font-size:14px;color:var(--text-1);margin:0 0 6px 0" id="toggleMsg"></p>
        <p style="font-size:12.5px;color:var(--text-3);margin:0">
          Un locataire désactivé ne peut plus se connecter. Ses données sont conservées.
        </p>
      </div>
    </div>
    <div class="modal-foot">
      <div class="mf-left"></div>
      <div class="mf-right">
        <button class="btn-outline-erp" onclick="closeModal('modalToggle')">Annuler</button>
        <button
          id="btnToggle"
          onclick="submitToggle()"
          style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:var(--r-md);color:#fff;font-size:13px;font-weight:700;border:none;cursor:pointer;font-family:var(--font-b);transition:opacity .2s"
          onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'"
        >Confirmer</button>
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
const URL_LOC = '{{ url("locataires") }}';

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
const TAB_BTN_MAP = {
  dTabInfos:        'tbDInfos',
  dTabContrats:     'tbDContrats',
  dTabPaiements:    'tbDPaiements',
  dTabReclamations: 'tbDReclamations',
  dTabDocuments:    'tbDDocuments',
};
function switchTab(tabId) {
  document.querySelectorAll('#modalDetail .tab-panel').forEach(p => p.style.display = 'none');
  document.querySelectorAll('#modalDetail .tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById(tabId).style.display = 'block';
  document.getElementById(TAB_BTN_MAP[tabId]).classList.add('active');
}

/* ── Filtre tableau ──────────────────────────────────────────────── */
function filterTable() {
  const q    = document.getElementById('searchInput').value.toLowerCase();
  const etat = document.getElementById('filterEtat').value;
  let visible = 0;
  document.querySelectorAll('#locatairesTbody tr[data-id]').forEach(tr => {
    const mQ = !q    || tr.textContent.toLowerCase().includes(q);
    const mE = !etat || tr.dataset.etat === etat;
    const show = mQ && mE;
    tr.style.display = show ? '' : 'none';
    if (show) visible++;
  });
  const c = document.getElementById('countVisible');
  if (c) c.textContent = visible;
}

/* ── Preview upload ──────────────────────────────────────────────── */
function previewFile(inputId, labelId) {
  const f   = document.getElementById(inputId).files[0];
  const lbl = document.getElementById(labelId);
  if (f) {
    lbl.textContent = f.name;
    lbl.style.color = 'var(--green-t)';
  }
}

/* ── Helpers validation ──────────────────────────────────────────── */
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
    <div style="width:34px;height:34px;border-radius:8px;flex-shrink:0;display:flex;align-items:center;justify-content:center;background:${bg};color:${color};font-size:15px">
      <i class="fa-solid ${icon}"></i></div>
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

/* ── Format date ─────────────────────────────────────────────────── */
function fmtDate(str) {
  if (!str) return '—';
  const d = new Date(str);
  return isNaN(d) ? str : d.toLocaleDateString('fr-FR');
}
function fmtMontant(v) {
  return Number(v || 0).toLocaleString('fr-FR') + ' MAD';
}

/* ════════════════════════════════════════════════════════
   SUBMIT — AJOUTER (multipart/form-data pour les fichiers)
════════════════════════════════════════════════════════ */
async function submitAjouter() {
  clearErrors(['errAddNom','errAddPrenom','errAddCin','errAddTel','errAddEmail']);

  const nom    = document.getElementById('addNom').value.trim();
  const prenom = document.getElementById('addPrenom').value.trim();
  const cin    = document.getElementById('addCin').value.trim();
  const tel    = document.getElementById('addTel').value.trim();
  const email  = document.getElementById('addEmail').value.trim();

  let ok = true;
  if (!nom)    { showError('errAddNom',    'Le nom est requis.');       ok = false; }
  if (!prenom) { showError('errAddPrenom', 'Le prénom est requis.');    ok = false; }
  if (!cin)    { showError('errAddCin',    'Le CIN est requis.');       ok = false; }
  if (!tel)    { showError('errAddTel',    'Le téléphone est requis.'); ok = false; }
  if (!email)  { showError('errAddEmail',  'L\'email est requis.');     ok = false; }
  if (!ok) return;

  const fd = new FormData();
  fd.append('_token',    CSRF);
  fd.append('nom',       nom);
  fd.append('prenom',    prenom);
  fd.append('cin',       cin);
  fd.append('telephone', tel);
  fd.append('email',     email);

  const recto = document.getElementById('addCinRecto').files[0];
  const verso = document.getElementById('addCinVerso').files[0];
  if (recto) fd.append('cin_recto', recto);
  if (verso) fd.append('cin_verso', verso);

  setLoading('btnAjouter', true, '<i class="fa-solid fa-user-plus"></i> Créer le locataire');

  try {
    const res  = await fetch(URL_LOC, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: fd });
    const data = await res.json();
    setLoading('btnAjouter', false, '<i class="fa-solid fa-user-plus"></i> Créer le locataire');

    if (data.success) {
      closeModal('modalAjouter');
      showToast(data.message);
      setTimeout(() => location.reload(), 900);
    } else {
      if (data.errors) {
        const m = { nom:'errAddNom', prenom:'errAddPrenom', cin:'errAddCin', telephone:'errAddTel', email:'errAddEmail' };
        Object.entries(data.errors).forEach(([k,v]) => { if (m[k]) showError(m[k], v[0]); });
      }
      showToast(data.message || 'Erreur.', 'error');
    }
  } catch {
    setLoading('btnAjouter', false, '<i class="fa-solid fa-user-plus"></i> Créer le locataire');
    showToast('Erreur réseau.', 'error');
  }
}

/* ════════════════════════════════════════════════════════
   OPEN — DÉTAIL
════════════════════════════════════════════════════════ */
let _curLoc = null;

async function openDetail(id) {
  _curLoc = null;
  switchTab('dTabInfos');

  document.getElementById('dNom').textContent = 'Chargement…';
  document.getElementById('dSub').textContent = '';
  document.getElementById('dBadge').textContent = '';
  document.getElementById('dAvatar').textContent = '…';

  // Reset des contenus dynamiques
  ['dContratsList','dPaiementsList','dReclamationsList','dDocumentsList'].forEach(id => {
    document.getElementById(id).innerHTML = '<div class="tab-loading"><i class="fa-solid fa-spinner fa-spin"></i></div>';
  });

  openModal('modalDetail');

  try {
    const res  = await fetch(URL_LOC + '/' + id, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } });
    const data = await res.json();
    if (!data.success) { showToast('Impossible de charger les données.', 'error'); return; }

    const l = data.locataire;
    _curLoc = l;

    // ── Header ──
    const init = (l.prenom[0] ?? '').toUpperCase() + (l.nom[0] ?? '').toUpperCase();
    document.getElementById('dAvatar').textContent = init;
    document.getElementById('dNom').textContent    = l.prenom + ' ' + l.nom;
    document.getElementById('dSub').textContent    = l.email;

    const badge = document.getElementById('dBadge');
    badge.className   = 's-badge ' + (l.etat === 'active' ? 'active' : 'inactive');
    badge.textContent = l.etat === 'active' ? 'Actif' : 'Inactif';

    // ── Infos ──
    document.getElementById('dFullName').textContent  = l.prenom + ' ' + l.nom;
    document.getElementById('dCin').textContent       = l.cin ?? '—';
    document.getElementById('dTel').textContent       = l.telephone ?? '—';
    document.getElementById('dEmail').textContent     = l.email;
    document.getElementById('dCreatedAt').textContent = fmtDate(l.created_at);

    // ── Bouton toggle footer ──
    const btnTog = document.getElementById('dBtnToggle');
    if (l.etat === 'active') {
      btnTog.innerHTML = '<i class="fa-solid fa-user-minus"></i> Désactiver';
      btnTog.style.color = 'var(--orange-t)';
    } else {
      btnTog.innerHTML = '<i class="fa-solid fa-user-check"></i> Réactiver';
      btnTog.style.color = 'var(--green-t)';
    }

    // ── Contrats ──
    renderContrats(data.contrats);

    // ── Paiements ──
    renderPaiements(data.paiements, data.transactions);

    // ── Réclamations ──
    renderReclamations(data.reclamations);

    // ── Documents ──
    renderDocuments(data.documents);

  } catch (err) {
    showToast('Erreur réseau.', 'error');
  }
}

function renderContrats(contrats) {
  const el = document.getElementById('dContratsList');
  if (!contrats || contrats.length === 0) {
    el.innerHTML = '<div class="tab-empty"><i class="fa-solid fa-file-circle-xmark"></i><p>Aucun contrat.</p></div>';
    return;
  }
  const sc = { actif:'active', en_attente:'late', termine:'inactive', resilie:'inactive' };
  const sl = { actif:'Actif', en_attente:'En attente', termine:'Terminé', resilie:'Résilié' };
  el.innerHTML = `
    <table class="erp-table" style="margin-top:4px">
      <thead><tr><th>Appartement</th><th>Loyer</th><th>Début</th><th>Fin</th><th>Type paiement</th><th>Statut</th></tr></thead>
      <tbody>${contrats.map(c => `
        <tr>
          <td><span style="font-size:13px">Apt. ${c.appartement?.numero ?? '—'}${c.appartement?.residence ? ' · ' + c.appartement.residence.nom : ''}</span></td>
          <td style="font-weight:700">${fmtMontant(c.loyer)}</td>
          <td><span class="date-text">${fmtDate(c.date_debut)}</span></td>
          <td><span class="date-text">${c.date_fin ? fmtDate(c.date_fin) : 'Indét.'}</span></td>
          <td style="font-size:12.5px;text-transform:capitalize">${c.type_paiement}</td>
          <td><span class="s-badge ${sc[c.statut] ?? 'inactive'}">${sl[c.statut] ?? c.statut}</span></td>
        </tr>`).join('')}
      </tbody>
    </table>`;
}

function renderPaiements(paiements, transactions) {
  const el = document.getElementById('dPaiementsList');
  const sp = { en_attente:'late', partiel:'late', paye:'active', en_retard:'inactive' };
  const lp = { en_attente:'En attente', partiel:'Partiel', paye:'Payé', en_retard:'En retard' };

  let html = '';

  if (paiements && paiements.length > 0) {
    html += `<p style="font-size:12.5px;font-weight:700;color:var(--text-3);margin:0 0 8px 0">ÉCHÉANCES</p>
    <table class="erp-table">
      <thead><tr><th>Période</th><th>Échéance</th><th>Montant</th><th>Payé</th><th>Reste</th><th>Statut</th></tr></thead>
      <tbody>${paiements.map(p => `
        <tr>
          <td style="font-size:12px;color:var(--text-3)">${fmtDate(p.periode_debut)} → ${fmtDate(p.periode_fin)}</td>
          <td><span class="date-text">${fmtDate(p.date_echeance)}</span></td>
          <td style="font-weight:700">${fmtMontant(p.montant)}</td>
          <td style="color:var(--green-t);font-weight:600">${fmtMontant(p.montant_paye)}</td>
          <td style="color:var(--red-t);font-weight:600">${fmtMontant(p.reste)}</td>
          <td><span class="s-badge ${sp[p.statut] ?? 'inactive'}">${lp[p.statut] ?? p.statut}</span></td>
        </tr>`).join('')}
      </tbody>
    </table>`;
  } else {
    html += '<div class="tab-empty"><i class="fa-solid fa-calendar-xmark"></i><p>Aucune échéance.</p></div>';
  }

  if (transactions && transactions.length > 0) {
    html += `<p style="font-size:12.5px;font-weight:700;color:var(--text-3);margin:18px 0 8px 0">TRANSACTIONS RÉELLES</p>
    <table class="erp-table">
      <thead><tr><th>Date</th><th>Montant</th><th>Mode</th><th>Référence</th></tr></thead>
      <tbody>${transactions.map(t => `
        <tr>
          <td><span class="date-text">${fmtDate(t.date_paiement)}</span></td>
          <td style="font-weight:700;color:var(--green-t)">${fmtMontant(t.montant)}</td>
          <td style="font-size:12.5px;text-transform:capitalize">${t.mode_paiement ?? '—'}</td>
          <td><span class="ref-code" style="font-size:11px">${t.reference ?? '—'}</span></td>
        </tr>`).join('')}
      </tbody>
    </table>`;
  }

  el.innerHTML = html || '<div class="tab-empty"><i class="fa-solid fa-receipt"></i><p>Aucune transaction.</p></div>';
}

function renderReclamations(reclamations) {
  const el = document.getElementById('dReclamationsList');
  if (!reclamations || reclamations.length === 0) {
    el.innerHTML = '<div class="tab-empty"><i class="fa-solid fa-flag"></i><p>Aucune réclamation.</p></div>';
    return;
  }
  const sp = { ouverte:'late', en_cours:'warning', resolue:'active' };
  const sl = { ouverte:'Ouverte', en_cours:'En cours', resolue:'Résolue' };
  const pp = { basse:'', moyenne:'warning', haute:'late', urgente:'inactive' };
  el.innerHTML = `
    <table class="erp-table">
      <thead><tr><th>Titre</th><th>Appartement</th><th>Date</th><th>Priorité</th><th>Statut</th></tr></thead>
      <tbody>${reclamations.map(r => `
        <tr>
          <td style="font-size:13.5px;font-weight:600">${r.titre}</td>
          <td style="font-size:12.5px;color:var(--text-3)">${r.appartement ? 'Apt. ' + r.appartement.numero : '—'}</td>
          <td><span class="date-text">${fmtDate(r.date_creation)}</span></td>
          <td><span class="s-badge ${pp[r.priorite] ?? ''}" style="text-transform:capitalize">${r.priorite}</span></td>
          <td><span class="s-badge ${sp[r.statut] ?? 'inactive'}">${sl[r.statut] ?? r.statut}</span></td>
        </tr>`).join('')}
      </tbody>
    </table>`;
}

function renderDocuments(documents) {
  const el = document.getElementById('dDocumentsList');
  if (!documents || documents.length === 0) {
    el.innerHTML = '<div class="tab-empty"><i class="fa-solid fa-folder-open"></i><p>Aucun document.</p></div>';
    return;
  }
  const icons = { carte_nationale: 'fa-id-card', bail: 'fa-file-contract', facture: 'fa-file-invoice', recu: 'fa-receipt' };
  el.innerHTML = `<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px">
    ${documents.map(d => {
      const ext = d.nom_fichier.split('.').pop().toLowerCase();
      const isImg = ['jpg','jpeg','png','gif','webp'].includes(ext);
      return `
        <div style="background:var(--surface-1);border:1.5px solid var(--border-2);border-radius:var(--r-md);padding:14px;display:flex;flex-direction:column;align-items:center;gap:8px;text-align:center">
          ${isImg
            ? `<img src="${d.url}" style="width:100%;height:90px;object-fit:cover;border-radius:6px;border:1px solid var(--border-1)">`
            : `<div style="width:100%;height:90px;background:var(--surface-2);border-radius:6px;display:flex;align-items:center;justify-content:center"><i class="fa-solid fa-file-pdf" style="font-size:32px;color:var(--red-t)"></i></div>`}
          <div style="font-size:12px;color:var(--text-2);font-weight:600;word-break:break-all">${d.nom_fichier}</div>
          <div style="font-size:11px;color:var(--text-4)">${d.type_document}</div>
          <a href="${d.url}" target="_blank" download class="btn-outline-erp" style="font-size:12px;padding:5px 12px;margin-top:2px">
            <i class="fa-solid fa-download" style="margin-right:4px"></i>Télécharger
          </a>
        </div>`
    }).join('')}
  </div>`;
}

/* ════════════════════════════════════════════════════════
   OPEN — MODIFIER
════════════════════════════════════════════════════════ */
function openModifier(id, nom, prenom, tel, email, etat) {
  document.getElementById('modId').value       = id;
  document.getElementById('modNom').value      = nom;
  document.getElementById('modPrenom').value   = prenom;
  document.getElementById('modTel').value      = tel;
  document.getElementById('modEmail').value    = email;
  document.getElementById('modEtat').value     = etat;
  document.getElementById('modSubtitle').textContent = prenom + ' ' + nom;
  document.getElementById('modCinRecto').value = '';
  document.getElementById('modCinVerso').value = '';
  document.getElementById('modCinRectoLabel').textContent = 'Remplacer le fichier';
  document.getElementById('modCinRectoLabel').style.color = '';
  document.getElementById('modCinVersoLabel').textContent = 'Remplacer le fichier';
  document.getElementById('modCinVersoLabel').style.color = '';
  clearErrors(['errModNom','errModPrenom','errModTel','errModEmail']);
  openModal('modalModifier');
}

function openModifierDepuisDetail() {
  if (!_curLoc) return;
  const l = _curLoc;
  closeModal('modalDetail');
  openModifier(l.id, l.nom, l.prenom, l.telephone ?? '', l.email, l.etat);
}

/* ════════════════════════════════════════════════════════
   SUBMIT — MODIFIER (multipart pour fichiers)
════════════════════════════════════════════════════════ */
async function submitModifier() {
  clearErrors(['errModNom','errModPrenom','errModTel','errModEmail']);

  const id     = document.getElementById('modId').value;
  const nom    = document.getElementById('modNom').value.trim();
  const prenom = document.getElementById('modPrenom').value.trim();
  const tel    = document.getElementById('modTel').value.trim();
  const email  = document.getElementById('modEmail').value.trim();
  const etat   = document.getElementById('modEtat').value;

  let ok = true;
  if (!nom)    { showError('errModNom',    'Le nom est requis.');       ok = false; }
  if (!prenom) { showError('errModPrenom', 'Le prénom est requis.');    ok = false; }
  if (!tel)    { showError('errModTel',    'Le téléphone est requis.'); ok = false; }
  if (!email)  { showError('errModEmail',  'L\'email est requis.');     ok = false; }
  if (!ok) return;

  const fd = new FormData();
  fd.append('_token',    CSRF);
  fd.append('_method',   'PUT');
  fd.append('nom',       nom);
  fd.append('prenom',    prenom);
  fd.append('telephone', tel);
  fd.append('email',     email);
  fd.append('etat',      etat);

  const recto = document.getElementById('modCinRecto').files[0];
  const verso = document.getElementById('modCinVerso').files[0];
  if (recto) fd.append('cin_recto', recto);
  if (verso) fd.append('cin_verso', verso);

  setLoading('btnModifier', true, '<i class="fa-solid fa-circle-check"></i> Enregistrer');

  try {
    const res  = await fetch(URL_LOC + '/' + id, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: fd });
    const data = await res.json();
    setLoading('btnModifier', false, '<i class="fa-solid fa-circle-check"></i> Enregistrer');

    if (data.success) {
      closeModal('modalModifier');
      showToast('Locataire modifié avec succès.');
      setTimeout(() => location.reload(), 900);
    } else {
      if (data.errors) {
        const m = { nom:'errModNom', prenom:'errModPrenom', telephone:'errModTel', email:'errModEmail' };
        Object.entries(data.errors).forEach(([k,v]) => { if (m[k]) showError(m[k], v[0]); });
      }
      showToast(data.message || 'Erreur.', 'error');
    }
  } catch {
    setLoading('btnModifier', false, '<i class="fa-solid fa-circle-check"></i> Enregistrer');
    showToast('Erreur réseau.', 'error');
  }
}

/* ════════════════════════════════════════════════════════
   OPEN / SUBMIT — TOGGLE ÉTAT
════════════════════════════════════════════════════════ */
function openToggle(id, action, nomComplet) {
  document.getElementById('toggleId').value = id;
  const isDesact = action === 'desactiver';

  document.getElementById('toggleTitle').textContent    = isDesact ? 'Désactiver le locataire' : 'Réactiver le locataire';
  document.getElementById('toggleSubtitle').textContent = nomComplet;
  document.getElementById('toggleMsg').textContent      = isDesact
    ? 'Êtes-vous sûr de vouloir désactiver ' + nomComplet + ' ?'
    : 'Voulez-vous réactiver ' + nomComplet + ' ?';

  const box = document.getElementById('toggleAlertBox');
  const btn = document.getElementById('btnToggle');

  if (isDesact) {
    box.style.cssText    = 'border-radius:var(--r-md);padding:16px;background:var(--orange-bg,#fffbeb);border:1px solid rgba(234,179,8,.2)';
    btn.style.background = 'var(--orange-t,#d97706)';
    btn.innerHTML        = '<i class="fa-solid fa-user-minus"></i> Désactiver';
  } else {
    box.style.cssText    = 'border-radius:var(--r-md);padding:16px;background:var(--green-bg);border:1px solid rgba(34,197,94,.2)';
    btn.style.background = 'var(--green-t)';
    btn.innerHTML        = '<i class="fa-solid fa-user-check"></i> Réactiver';
  }

  openModal('modalToggle');
}

function openToggleDepuisDetail() {
  if (!_curLoc) return;
  const l      = _curLoc;
  const action = l.etat === 'active' ? 'desactiver' : 'reactiver';
  closeModal('modalDetail');
  openToggle(l.id, action, l.prenom + ' ' + l.nom);
}

async function submitToggle() {
  const id = document.getElementById('toggleId').value;
  setLoading('btnToggle', true, 'Chargement…');

  try {
    const res  = await fetch(URL_LOC + '/' + id + '/toggle-etat', {
      method: 'PATCH',
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
    });
    const data = await res.json();
    setLoading('btnToggle', false, 'Confirmer');

    if (data.success) {
      closeModal('modalToggle');
      showToast(data.message, data.etat === 'active' ? 'success' : 'warning');
      setTimeout(() => location.reload(), 900);
    } else {
      showToast(data.message || 'Erreur.', 'error');
    }
  } catch {
    setLoading('btnToggle', false, 'Confirmer');
    showToast('Erreur réseau.', 'error');
  }
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

  .tab-loading {
    text-align:center;padding:32px;
    font-size:22px;color:var(--text-4);
  }
  .tab-empty {
    text-align:center;padding:32px;
    color:var(--text-4);font-size:14px;
  }
  .tab-empty i { font-size:28px;display:block;margin-bottom:8px; }

  .upload-zone {
    border:1.5px dashed var(--border-2);border-radius:var(--r-md);
    padding:16px;text-align:center;cursor:pointer;
    background:var(--surface-1);transition:all .18s;
  }
  .upload-zone:hover { border-color:var(--brand);background:#eef2ff; }

  .detail-card {
    background:var(--surface-1);border:1px solid var(--border-1);
    border-radius:var(--r-md);padding:12px;
  }
  .dc-label {
    font-size:11.5px;color:var(--text-4);
    font-weight:600;text-transform:uppercase;letter-spacing:.04em;
    margin-bottom:4px;
  }
  .dc-value { font-size:14px;font-weight:700;color:var(--text-1); }

  .s-badge.inactive {
    background:var(--surface-2);color:var(--text-3);border-color:var(--border-2);
  }
`;
document.head.appendChild(_s);
</script>
@endpush
