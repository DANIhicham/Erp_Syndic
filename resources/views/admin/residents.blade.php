@extends('layouts.layout')

@section('title', 'Résidents')
@section('page_title', 'Résidents')

@section('content')

{{-- ════════════════════════════════════════════════════════════════
     PAGE HEADER
════════════════════════════════════════════════════════════════ --}}
<div class="page-header">
  <div class="ph-left">
    <h2>Résidents</h2>
    <p>Gestion des copropriétaires — {{ session('residence_nom', 'Toutes résidences') }}</p>
  </div>
  <div class="ph-right">
    <button class="btn-primary-erp" onclick="openModal('modalAjouter')">
      <i class="fa-solid fa-user-plus"></i> Ajouter résident
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
        <div class="kpi-label">Total résidents</div>
        <div class="kpi-value" id="statTotal">{{ $residents->count() }}</div>
        <div class="kpi-sub">copropriétaires</div>
      </div>
      <div class="kpi-icon blue"><i class="fa-solid fa-users"></i></div>
    </div>
  </div>

  <div class="kpi-card kpi-green" style="animation-delay:.10s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Actifs</div>
        <div class="kpi-value" id="statActif">{{ $residents->where('etat','active')->count() }}</div>
        <div class="kpi-sub">comptes actifs</div>
      </div>
      <div class="kpi-icon green"><i class="fa-solid fa-user-check"></i></div>
    </div>
  </div>

  <div class="kpi-card kpi-red" style="animation-delay:.15s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Désactivés</div>
        <div class="kpi-value" id="statInactif">{{ $residents->where('etat','desactive')->count() }}</div>
        <div class="kpi-sub">comptes suspendus</div>
      </div>
      <div class="kpi-icon red"><i class="fa-solid fa-user-slash"></i></div>
    </div>
  </div>

  <div class="kpi-card" style="animation-delay:.20s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Sans appartement</div>
        <div class="kpi-value" id="statSansApt" style="color:var(--text-3)">
          {{$residents->filter(fn($r) => $r->appartements->isEmpty())->count()}}
        </div>  
        <div class="kpi-sub">à associer</div>
      </div>
      <div class="kpi-icon" style="background:var(--surface-2)">
        <i class="fa-solid fa-building-circle-xmark" style="color:var(--text-3)"></i>
      </div>
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
      placeholder="Nom, email, CIN…"
      oninput="filterTable()"
    >
  </div>

  <select id="filterEtat" class="filter-select" onchange="filterTable()">
    <option value="">Tous les états</option>
    <option value="active">Actif</option>
    <option value="desactive">Désactivé</option>
  </select>

  <select id="filterApt" class="filter-select" onchange="filterTable()">
    <option value="">Tous</option>
    <option value="avec">Avec appartement</option>
    <option value="sans">Sans appartement</option>
  </select>

  <div class="filter-divider"></div>

  <div class="filter-count">
    <strong id="countVisible">{{ $residents->count() }}</strong>
    résident{{ $residents->count() !== 1 ? 's' : '' }}
  </div>

</div>

{{-- ════════════════════════════════════════════════════════════════
     TABLEAU PRINCIPAL
════════════════════════════════════════════════════════════════ --}}
<div class="table-card">

  <div class="table-card-header">
    <div class="tch-left">
      <h5>Liste des résidents</h5>
      <p>{{ $residents->count() }} résident{{ $residents->count() !== 1 ? 's' : '' }} enregistré{{ $residents->count() !== 1 ? 's' : '' }}</p>
    </div>
  </div>

  <div class="table-responsive">
    <table class="erp-table">
      <thead>
        <tr>
          <th>Résident</th>
          <th>Contact</th>
          <th>CIN</th>
          <th>Appartement</th>
          <!-- <th>Cotisation {{ now()->year }}</th> -->
          <th>État</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="resTbody">

        @forelse($residents as $res)
          @php
            $apts       = $res->appartements;
            $initiales = strtoupper(substr($res->prenom, 0, 1)) . strtoupper(substr($res->nom, 0, 1));

            $palette = ['#4f46e5','#0891b2','#059669','#d97706','#7c3aed','#db2777','#0f766e','#dc2626'];
            $color   = $res->etat === 'desactive'
                         ? '#94a3b8'
                         : $palette[$res->id % count($palette)];

            $cotisation = $res->paiementCotisations
                ? $res->paiementCotisations->firstWhere('annee_concernee', now()->year)
                : null;
            $paye    = $cotisation ? (float) $cotisation->montant_paye    : 0;
            $attendu = $cotisation ? (float) $cotisation->montant_attendu : 0;
            $pct     = $attendu > 0 ? min(100, round($paye / $attendu * 100)) : 0;

            $cotStatut = $cotisation?->statut;
            $barColor  = match($cotStatut) {
              'payé'      => 'var(--green-t)',
              'partiel'   => 'var(--orange-t)',
              'en_retard' => 'var(--red-t)',
              default     => 'var(--red-t)',
            };
          @endphp

          <tr
            data-id="{{ $res->id }}"
            data-etat="{{ $res->etat }}"
            data-apt="{{ $apts->count() ? 'avec' : 'sans' }}"
          >

            {{-- Résident --}}
            <td>
              <div class="tenant-cell">
                <div class="t-avatar" style="background:{{ $color }}">{{ $initiales }}</div>
                <div>
                  <span class="t-name">{{ $res->prenom }} {{ $res->nom }}</span>
                  <span class="t-type">{{ $res->email }}</span>
                </div>
              </div>
            </td>

            {{-- Contact --}}
            <td>
              <span style="font-size:13px;color:var(--text-3)">
                {{ $res->telephone ?? '—' }}
              </span>
            </td>

            {{-- CIN --}}
            <td>
              <span class="ref-code" style="font-size:12px">{{ $res->cin ?? '—' }}</span>
            </td>

            {{-- Appartement --}}
            <td>
              @if($apts->count())
                @foreach($apts as $apt)
                <span class="s-badge active" style="font-size:12px">
                  <i class="fa-solid fa-building" style="margin-right:4px"></i>
                  Apt. {{ $apt->numero }}
                  @if($apt->residence) · {{ $apt->residence->nom }} @endif
                </span>
                @endforeach
              @else
                <span style="color:var(--text-4);font-size:13px">
                  <i class="fa-solid fa-circle-minus" style="margin-right:4px"></i>Non associé
                </span>
              @endif
            </td>

            <!-- {{-- Cotisation année en cours --}}
            <td>
              @if($cotisation && $attendu > 0)
                <div style="display:flex;align-items:center;gap:8px">
                  <div style="width:72px;height:5px;background:var(--border-1);border-radius:3px;overflow:hidden;flex-shrink:0">
                    <div style="width:{{ $pct }}%;height:100%;background:{{ $barColor }};border-radius:3px"></div>
                  </div>
                  <span style="font-size:12px;color:var(--text-3);font-family:var(--font-mono,monospace)">{{ $pct }}%</span>
                </div>
                <div style="font-size:11.5px;color:var(--text-4);margin-top:3px">
                  {{ number_format($paye,0,',',' ') }} / {{ number_format($attendu,0,',',' ') }} MAD
                </div>
              @elseif($apts->count())
                <span style="font-size:12.5px;color:var(--text-4)">
                  <i class="fa-solid fa-clock" style="margin-right:3px"></i>En attente
                </span>
              @else
                <span style="color:var(--text-4);font-size:13px">—</span>
              @endif
            </td> -->

            {{-- État --}}
            <td>
              @if($res->etat === 'active')
                <span class="s-badge active">
                  <i class="fa-solid fa-circle" style="font-size:7px;margin-right:4px"></i>Actif
                </span>
              @else
                <span class="s-badge inactive">
                  <i class="fa-regular fa-circle" style="font-size:7px;margin-right:4px"></i>Désactivé
                </span>
              @endif
            </td>

            {{-- Actions --}}
            <td>
              <div class="row-actions">

                {{-- Modifier --}}
                <button
                  class="ra-btn edit"
                  title="Modifier"
                  onclick="openModifier(
                    {{ $res->id }},
                    '{{ addslashes($res->nom) }}',
                    '{{ addslashes($res->prenom) }}',
                    '{{ $res->email }}',
                    '{{ $res->telephone ?? '' }}',
                    '{{ $res->cin ?? '' }}'
                  )"
                >
                  <i class="fa-solid fa-pen-to-square"></i>
                </button>

                {{-- Associer appartement (uniquement si aucun appartement associé) --}}
                @if($apts->isEmpty())
                  <button
                    class="ra-btn encaisser"
                    title="Associer un appartement"
                    onclick="openAssocier(
                                {{ $res->id }},
                                '{{ addslashes($res->prenom) }} {{ addslashes($res->nom) }}'
                            )"
                  >
                    <i class="fa-solid fa-building-user"></i>
                  </button>
                @endif

                {{-- Désactiver / Réactiver --}}
                @if($res->etat === 'active')
                  <button
                    class="ra-btn"
                    style="color:var(--orange-t)"
                    title="Désactiver"
                    onclick="toggleEtat({{ $res->id }}, 'desactiver', '{{ addslashes($res->prenom) }} {{ addslashes($res->nom) }}')"
                  >
                    <i class="fa-solid fa-user-minus"></i>
                  </button>
                @else
                  <button
                    class="ra-btn"
                    style="color:var(--green-t)"
                    title="Réactiver"
                    onclick="toggleEtat({{ $res->id }}, 'reactiver', '{{ addslashes($res->prenom) }} {{ addslashes($res->nom) }}')"
                  >
                    <i class="fa-solid fa-user-check"></i>
                  </button>
                @endif
              {{-- Supprimer --}}
                <button
                  class="ra-btn delete"
                  title="Supprimer"
                  onclick="openSupprimer({{ $res->id }}, '{{ addslashes($res->prenom) }} {{ addslashes($res->nom) }}')"
                >
                  <i class="fa-solid fa-trash"></i>
                </button>

              </div>
              </div>
            </td>

          </tr>

        @empty
          <tr>
            <td colspan="7" style="text-align:center;padding:40px;color:var(--text-4);font-size:14px">
              <i class="fa-solid fa-users" style="font-size:30px;margin-bottom:10px;display:block"></i>
              Aucun résident trouvé.
            </td>
          </tr>
        @endforelse

      </tbody>
    </table>
  </div>

  <div class="table-footer">
    <span class="tf-info">
      <strong>{{ $residents->count() }}</strong>
      résident{{ $residents->count() !== 1 ? 's' : '' }} affiché{{ $residents->count() !== 1 ? 's' : '' }}
    </span>
  </div>

</div>{{-- /table-card --}}


{{-- ════════════════════════════════════════════════════════════════
     MODAL — AJOUTER RÉSIDENT
════════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalAjouter">
  <div class="modal-panel">

    <div class="modal-head">
      <div>
        <h3 class="mh-title">Ajouter un résident</h3>
        <p class="mh-sub">Créer un nouveau compte copropriétaire.</p>
      </div>
      <div class="mh-right">
        <button class="modal-close" onclick="closeModal('modalAjouter')">
          <i class="fa-solid fa-xmark"></i>
        </button>
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
      </div>

      <div class="form-group">
        <label class="form-label">Email <span style="color:var(--red-t)">*</span></label>
        <input type="email" id="addEmail" class="form-control" placeholder="karim@email.ma">
        <div id="errAddEmail" class="form-err"></div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="form-group">
          <label class="form-label">Téléphone</label>
          <input type="text" id="addTel" class="form-control" placeholder="06 00 00 00 00">
        </div>
        <div class="form-group">
          <label class="form-label">CIN</label>
          <input type="text" id="addCin" class="form-control" placeholder="AB123456">
        </div>
      </div>

      <div class="form-section-label" style="margin-top:6px">
        <i class="fa-solid fa-lock" style="color:var(--brand)"></i>
        Accès au compte
      </div>

      <div class="form-group">
        <label class="form-label">Mot de passe <span style="color:var(--red-t)">*</span></label>
        <div style="display:flex;gap:0">
          <input
            type="password"
            id="addPassword"
            class="form-control"
            placeholder="Min. 8 caractères"
            style="border-radius:var(--r-md) 0 0 var(--r-md);border-right:none"
          >
          <button
            type="button"
            onclick="togglePw('addPassword', this)"
            style="padding:0 12px;border:1.5px solid var(--border-2);border-radius:0 var(--r-md) var(--r-md) 0;background:var(--surface-1);color:var(--text-3);cursor:pointer;font-size:14px;transition:all .18s"
          ><i class="fa-solid fa-eye"></i></button>
        </div>
        <div id="errAddPassword" class="form-err"></div>
      </div>

    </div>{{-- /modal-body --}}

    <div class="modal-foot">
      <div class="mf-left"></div>
      <div class="mf-right">
        <button class="btn-outline-erp" onclick="closeModal('modalAjouter')">Annuler</button>
        <button class="btn-primary-erp" id="btnAjouter" onclick="submitAjouter()">
          <i class="fa-solid fa-user-plus"></i> Créer le résident
        </button>
      </div>
    </div>

  </div>
</div>


{{-- ════════════════════════════════════════════════════════════════
     MODAL — MODIFIER RÉSIDENT
════════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalModifier">
  <div class="modal-panel">

    <div class="modal-head">
      <div>
        <h3 class="mh-title">Modifier le résident</h3>
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

      <div class="form-section-label">
        <i class="fa-solid fa-pen-to-square" style="color:var(--brand)"></i>
        Informations personnelles
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
      </div>

      <div class="form-group">
        <label class="form-label">Email <span style="color:var(--red-t)">*</span></label>
        <input type="email" id="modEmail" class="form-control">
        <div id="errModEmail" class="form-err"></div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="form-group">
          <label class="form-label">Téléphone</label>
          <input type="text" id="modTel" class="form-control">
        </div>
        <div class="form-group">
          <label class="form-label">CIN</label>
          <input type="text" id="modCin" class="form-control">
        </div>
      </div>

      <div class="form-section-label" style="margin-top:6px">
        <i class="fa-solid fa-lock" style="color:var(--brand)"></i>
        Modifier le mot de passe
      </div>

      <div class="form-group">
        <label class="form-label">
          Nouveau mot de passe
          <span style="font-size:11.5px;color:var(--text-4)">(laisser vide = inchangé)</span>
        </label>
        <div style="display:flex;gap:0">
          <input
            type="password"
            id="modPassword"
            class="form-control"
            placeholder="Min. 8 caractères"
            style="border-radius:var(--r-md) 0 0 var(--r-md);border-right:none"
          >
          <button
            type="button"
            onclick="togglePw('modPassword', this)"
            style="padding:0 12px;border:1.5px solid var(--border-2);border-radius:0 var(--r-md) var(--r-md) 0;background:var(--surface-1);color:var(--text-3);cursor:pointer;font-size:14px;transition:all .18s"
          ><i class="fa-solid fa-eye"></i></button>
        </div>
        <div id="errModPassword" class="form-err"></div>
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
     MODAL — ASSOCIER APPARTEMENT
════════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalAssocier">
  <div class="modal-panel">

    <div class="modal-head">
      <div>
        <h3 class="mh-title">Associer un appartement</h3>
        <p class="mh-sub" id="assocSubtitle">Résident —</p>
      </div>
      <div class="mh-right">
        <button class="modal-close" onclick="closeModal('modalAssocier')">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>

    <div class="modal-body">
      <input type="hidden" id="assocResId">

      {{-- Alerte cotisations automatiques --}}
      <div style="display:flex;gap:10px;align-items:flex-start;background:var(--blue-bg,#eef2ff);border:1.5px solid rgba(99,102,241,.25);border-radius:var(--r-md);padding:12px 14px;margin-bottom:18px">
        <i class="fa-solid fa-circle-info" style="color:var(--brand);font-size:14px;flex-shrink:0;margin-top:1px"></i>
        <div style="font-size:12.5px;color:var(--text-2);line-height:1.6">
          <strong>Cotisations automatiques :</strong> après l'association, les cotisations seront calculées automatiquement à partir de la date de signature, avec <strong>prorata</strong> pour la première année.
        </div>
      </div>

      <div class="form-section-label">
        <i class="fa-solid fa-building" style="color:var(--brand)"></i>
        Sélectionner l'appartement
      </div>

      <div class="form-group">
        <label class="form-label">Appartement <span style="color:var(--red-t)">*</span></label>
        <select id="assocApt" class="form-control">
          <option value="">— Sélectionner un appartement libre —</option>
          @foreach($appartementsLibres as $apt)
            <option value="{{ $apt->id }}">
              Apt. {{ $apt->numero }}
              @if($apt->residence) – {{ $apt->residence->nom }} @endif
              @if($apt->etage !== null) (Étage {{ $apt->etage }}) @endif
            </option>
          @endforeach
        </select>
        <div id="errAssocApt" class="form-err"></div>
        <div style="font-size:12px;color:var(--text-4);margin-top:5px">
          <i class="fa-solid fa-circle-info" style="margin-right:3px"></i>
          Seuls les appartements libres sont listés. Pour changer l'appartement actuel, dissociez-le d'abord depuis la page Appartements.
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Date de signature du contrat <span style="color:var(--red-t)">*</span></label>
        <input type="date" id="assocDate" class="form-control" max="{{ now()->format('Y-m-d') }}">
        <div id="errAssocDate" class="form-err"></div>
        <div style="font-size:12px;color:var(--text-4);margin-top:5px">
          <i class="fa-solid fa-calendar-check" style="margin-right:3px"></i>
          Cette date détermine le début des cotisations et le prorata de la 1ère année.
        </div>
      </div>

    </div>{{-- /modal-body --}}

    <div class="modal-foot">
      <div class="mf-left"></div>
      <div class="mf-right">
        <button class="btn-outline-erp" onclick="closeModal('modalAssocier')">Annuler</button>
        <button class="btn-primary-erp" id="btnAssocier" onclick="submitAssocier()">
          <i class="fa-solid fa-building-circle-check"></i> Associer et générer les cotisations
        </button>
      </div>
    </div>

  </div>
</div>


{{-- ════════════════════════════════════════════════════════════════
     MODAL — DÉSACTIVER / RÉACTIVER
════════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalToggle">
  <div class="modal-panel" style="max-width:460px">

    <div class="modal-head">
      <div>
        <h3 class="mh-title" id="toggleTitle">Désactiver le résident</h3>
        <p class="mh-sub" id="toggleSubtitle">—</p>
      </div>
      <div class="mh-right">
        <button class="modal-close" onclick="closeModal('modalToggle')">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>

    <div class="modal-body">
      <input type="hidden" id="toggleId">

      <div id="toggleAlertBox" style="background:var(--orange-bg,#fffbeb);border:1px solid rgba(234,179,8,.2);border-radius:var(--r-md);padding:16px">
        <p style="font-size:14px;color:var(--text-1);margin:0 0 6px 0" id="toggleMsg">
          Êtes-vous sûr de vouloir désactiver ce résident ?
        </p>
        <p style="font-size:12.5px;color:var(--text-3);margin:0">
          Un résident désactivé ne peut plus se connecter à son espace.
          Ses données sont conservées et il peut être réactivé à tout moment.
        </p>
      </div>
    </div>{{-- /modal-body --}}

    <div class="modal-foot">
      <div class="mf-left"></div>
      <div class="mf-right">
        <button class="btn-outline-erp" onclick="closeModal('modalToggle')">Annuler</button>
        <button
          id="btnToggle"
          onclick="submitToggle()"
          style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:var(--r-md);background:var(--orange-t,#d97706);color:#fff;font-size:13px;font-weight:700;border:none;cursor:pointer;font-family:var(--font-b);transition:opacity .2s"
          onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'"
        >
          <i class="fa-solid fa-user-minus"></i> Désactiver
        </button>
      </div>
    </div>

  </div>
</div>

{{-- ════════════════════════════════════════════════════════════════
     MODAL — SUPPRIMER RÉSIDENT
════════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalSupprimer">
  <div class="modal-panel" style="max-width:460px">

    <div class="modal-head">
      <div>
        <h3 class="mh-title" style="color:var(--red-t)">
          <i class="fa-solid fa-triangle-exclamation" style="margin-right:6px"></i>Supprimer le résident
        </h3>
        <p class="mh-sub">Cette action est irréversible.</p>
      </div>
      <div class="mh-right">
        <button class="modal-close" onclick="closeModal('modalSupprimer')">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>

    <div class="modal-body">
      <input type="hidden" id="suppId">

      <div style="background:var(--red-bg);border:1px solid rgba(239,68,68,.2);border-radius:var(--r-md);padding:16px">
        <p style="font-size:14px;color:var(--text-1);margin:0 0 6px 0">
          Êtes-vous sûr de vouloir supprimer
          <strong id="suppNom" style="color:var(--red-t)"></strong> ?
        </p>
        <p style="font-size:12.5px;color:var(--text-3);margin:0">
          Le compte sera définitivement supprimé ainsi que toutes ses données associées.
        </p>
      </div>
    </div>

    <div class="modal-foot">
      <div class="mf-left"></div>
      <div class="mf-right">
        <button class="btn-outline-erp" onclick="closeModal('modalSupprimer')">Annuler</button>
        <button
          id="btnSupprimer"
          onclick="submitSupprimer()"
          style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:var(--r-md);background:var(--red-t);color:#fff;font-size:13px;font-weight:700;border:none;cursor:pointer;font-family:var(--font-b);transition:opacity .2s"
          onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'"
        >
          <i class="fa-solid fa-trash"></i> Confirmer la suppression
        </button>
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
const URL_RES = '{{ url("admin/residents") }}';




/* ── Filtre tableau (client-side) ────────────────────────────────── */
function filterTable() {
  const q    = document.getElementById('searchInput').value.toLowerCase();
  const etat = document.getElementById('filterEtat').value;
  const apt  = document.getElementById('filterApt').value;
  let visible = 0;

  document.querySelectorAll('#resTbody tr[data-id]').forEach(tr => {
    const mQ = !q    || tr.textContent.toLowerCase().includes(q);
    const mE = !etat || tr.dataset.etat === etat;
    const mA = !apt  || tr.dataset.apt  === apt;
    const show = mQ && mE && mA;
    tr.style.display = show ? '' : 'none';
    if (show) visible++;
  });

  const counter = document.getElementById('countVisible');
  if (counter) counter.textContent = visible;
}

/* ── Toggle mot de passe ─────────────────────────────────────────── */
function togglePw(inputId, btn) {
  const inp  = document.getElementById(inputId);
  const show = inp.type === 'password';
  inp.type   = show ? 'text' : 'password';
  btn.innerHTML = show
    ? '<i class="fa-solid fa-eye-slash"></i>'
    : '<i class="fa-solid fa-eye"></i>';
}

/* ── Helpers erreurs inline ──────────────────────────────────────── */
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

/* ── Spinner sur les boutons ─────────────────────────────────────── */
function setLoading(btnId, loading, defaultHtml) {
  const btn = document.getElementById(btnId);
  if (!btn) return;
  btn.disabled  = loading;
  btn.innerHTML = loading
    ? '<i class="fa-solid fa-spinner fa-spin" style="margin-right:6px"></i>Chargement…'
    : defaultHtml;
}

/* ── Toast ERP ───────────────────────────────────────────────────── */
function showToast(msg, type = 'success') {
  const map = {
    success: { icon: 'fa-circle-check',        color: 'var(--green-t)',  bg: 'var(--green-bg)',  title: 'Succès'    },
    error:   { icon: 'fa-circle-xmark',         color: 'var(--red-t)',   bg: 'var(--red-bg)',    title: 'Erreur'    },
    warning: { icon: 'fa-triangle-exclamation', color: 'var(--orange-t)',bg: 'var(--orange-bg)', title: 'Attention' },
    info:    { icon: 'fa-circle-info',           color: 'var(--brand)',   bg: '#eef2ff',          title: 'Info'      },
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

/* ── Fetch wrapper JSON ──────────────────────────────────────────── */
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

/* ════════════════════════════════════════════════════════
   SUBMIT — AJOUTER RÉSIDENT
════════════════════════════════════════════════════════ */
async function submitAjouter() {
  clearErrors(['errAddNom','errAddPrenom','errAddEmail','errAddPassword']);

  const nom      = document.getElementById('addNom').value.trim();
  const prenom   = document.getElementById('addPrenom').value.trim();
  const email    = document.getElementById('addEmail').value.trim();
  const tel      = document.getElementById('addTel').value.trim();
  const cin      = document.getElementById('addCin').value.trim();
  const password = document.getElementById('addPassword').value;

  let ok = true;
  if (!nom)                         { showError('errAddNom',      'Le nom est requis.');          ok = false; }
  if (!prenom)                      { showError('errAddPrenom',   'Le prénom est requis.');        ok = false; }
  if (!email)                       { showError('errAddEmail',    'L\'email est requis.');         ok = false; }
  if (email && !email.includes('@')){ showError('errAddEmail',    'Email invalide.');              ok = false; }
  if (!password)                    { showError('errAddPassword', 'Le mot de passe est requis.'); ok = false; }
  if (password && password.length < 8) { showError('errAddPassword','Min. 8 caractères.');        ok = false; }
  if (!ok) return;

  setLoading('btnAjouter', true, '<i class="fa-solid fa-user-plus"></i> Créer le résident');

  try {
    const data = await apiFetch(URL_RES, 'POST', {
      nom, prenom, email, telephone: tel || null, cin: cin || null, password,
    });
    setLoading('btnAjouter', false, '<i class="fa-solid fa-user-plus"></i> Créer le résident');

    if (data.success) {
      closeModal('modalAjouter');
      showToast('Résident ' + prenom + ' ' + nom + ' créé avec succès.');
      ['addNom','addPrenom','addEmail','addTel','addCin','addPassword'].forEach(id => {
        document.getElementById(id).value = '';
      });
      setTimeout(() => location.reload(), 900);
    } else {
      if (data.errors) {
        if (data.errors.email)    showError('errAddEmail',    data.errors.email[0]);
        if (data.errors.password) showError('errAddPassword', data.errors.password[0]);
      }
      showToast(data.message || 'Erreur lors de la création.', 'error');
    }
  } catch {
    setLoading('btnAjouter', false, '<i class="fa-solid fa-user-plus"></i> Créer le résident');
    showToast('Erreur réseau.', 'error');
  }
}

/* ════════════════════════════════════════════════════════
   OUVRIR MODIFIER
════════════════════════════════════════════════════════ */
function openModifier(id, nom, prenom, email, tel, cin) {
  document.getElementById('modId').value       = id;
  document.getElementById('modNom').value      = nom;
  document.getElementById('modPrenom').value   = prenom;
  document.getElementById('modEmail').value    = email;
  document.getElementById('modTel').value      = tel;
  document.getElementById('modCin').value      = cin;
  document.getElementById('modPassword').value = '';
  document.getElementById('modSubtitle').textContent = prenom + ' ' + nom;
  clearErrors(['errModNom','errModPrenom','errModEmail','errModPassword']);
  openModal('modalModifier');
}

/* ════════════════════════════════════════════════════════
   SUBMIT — MODIFIER RÉSIDENT
════════════════════════════════════════════════════════ */
async function submitModifier() {
  clearErrors(['errModNom','errModPrenom','errModEmail','errModPassword']);

  const id       = document.getElementById('modId').value;
  const nom      = document.getElementById('modNom').value.trim();
  const prenom   = document.getElementById('modPrenom').value.trim();
  const email    = document.getElementById('modEmail').value.trim();
  const tel      = document.getElementById('modTel').value.trim();
  const cin      = document.getElementById('modCin').value.trim();
  const password = document.getElementById('modPassword').value;

  let ok = true;
  if (!nom)                          { showError('errModNom',    'Le nom est requis.');    ok = false; }
  if (!prenom)                       { showError('errModPrenom', 'Le prénom est requis.'); ok = false; }
  if (!email)                        { showError('errModEmail',  'L\'email est requis.');  ok = false; }
  if (email && !email.includes('@')) { showError('errModEmail',  'Email invalide.');        ok = false; }
  if (password && password.length < 8) { showError('errModPassword','Min. 8 caractères.'); ok = false; }
  if (!ok) return;

  setLoading('btnModifier', true, '<i class="fa-solid fa-circle-check"></i> Enregistrer');

  try {
    const body = { nom, prenom, email, telephone: tel || null, cin: cin || null };
    if (password) body.password = password;

    const data = await apiFetch(URL_RES + '/' + id, 'PUT', body);
    setLoading('btnModifier', false, '<i class="fa-solid fa-circle-check"></i> Enregistrer');

    if (data.success) {
      closeModal('modalModifier');
      showToast('Résident modifié avec succès.');
      setTimeout(() => location.reload(), 900);
    } else {
      if (data.errors) {
        if (data.errors.email)    showError('errModEmail',    data.errors.email[0]);
        if (data.errors.password) showError('errModPassword', data.errors.password[0]);
      }
      showToast(data.message || 'Erreur.', 'error');
    }
  } catch {
    setLoading('btnModifier', false, '<i class="fa-solid fa-circle-check"></i> Enregistrer');
    showToast('Erreur réseau.', 'error');
  }
}

/* ════════════════════════════════════════════════════════
   OUVRIR ASSOCIER APPARTEMENT
════════════════════════════════════════════════════════ */
function openAssocier(resId, nomComplet) {
  document.getElementById('assocResId').value = resId;
  document.getElementById('assocSubtitle').textContent = nomComplet;
  document.getElementById('assocDate').value = '';
  document.getElementById('assocApt').value = '';
  clearErrors(['errAssocApt','errAssocDate']);
  openModal('modalAssocier');
}

/* ════════════════════════════════════════════════════════
   SUBMIT — ASSOCIER APPARTEMENT
════════════════════════════════════════════════════════ */
async function submitAssocier() {
  clearErrors(['errAssocApt','errAssocDate']);

  const resId = document.getElementById('assocResId').value;
  const aptId = document.getElementById('assocApt').value;
  const date  = document.getElementById('assocDate').value;

  let ok = true;
  if (!aptId) { showError('errAssocApt',  'Veuillez sélectionner un appartement.');        ok = false; }
  if (!date)  { showError('errAssocDate', 'La date de signature est obligatoire.'); ok = false; }
  if (!ok) return;

  setLoading('btnAssocier', true, '<i class="fa-solid fa-building-circle-check"></i> Associer et générer les cotisations');

  try {
    const data = await apiFetch(URL_RES + '/' + resId + '/assign-appartement', 'POST', {
      appartement_id:         aptId,
      date_signature_contrat: date,
    });
    setLoading('btnAssocier', false, '<i class="fa-solid fa-building-circle-check"></i> Associer et générer les cotisations');

    if (data.success) {
      closeModal('modalAssocier');
      showToast(data.message || 'Appartement associé. Cotisations générées automatiquement.');
      setTimeout(() => location.reload(), 900);
    } else {
      showToast(data.message || 'Erreur.', 'error');
    }
  } catch {
    setLoading('btnAssocier', false, '<i class="fa-solid fa-building-circle-check"></i> Associer et générer les cotisations');
    showToast('Erreur réseau.', 'error');
  }
}

/* ════════════════════════════════════════════════════════
   DÉSACTIVER / RÉACTIVER
════════════════════════════════════════════════════════ */
let _toggleAction = '';
 
function toggleEtat(id, action, nomComplet) {
  _toggleAction = action;
  document.getElementById('toggleId').value = id;
  const isDesactiver = action === 'desactiver';
 
  document.getElementById('toggleTitle').textContent    = isDesactiver ? 'Désactiver le résident'  : 'Réactiver le résident';
  document.getElementById('toggleSubtitle').textContent = nomComplet;
  document.getElementById('toggleMsg').textContent      = isDesactiver
    ? 'Êtes-vous sûr de vouloir désactiver ' + nomComplet + ' ?'
    : 'Voulez-vous réactiver ' + nomComplet + ' ?';
 
  const box = document.getElementById('toggleAlertBox');
  const btn = document.getElementById('btnToggle');
 
  if (isDesactiver) {
    box.style.background = 'var(--orange-bg,#fffbeb)';
    box.style.borderColor = 'rgba(234,179,8,.2)';
    btn.style.background  = 'var(--orange-t,#d97706)';
    btn.innerHTML = '<i class="fa-solid fa-user-minus"></i> Désactiver';
  } else {
    box.style.background  = 'var(--green-bg)';
    box.style.borderColor = 'rgba(34,197,94,.2)';
    btn.style.background  = 'var(--green-t)';
    btn.innerHTML = '<i class="fa-solid fa-user-check"></i> Réactiver';
  }
 
  openModal('modalToggle');
}
 
async function submitToggle() {
  const id = document.getElementById('toggleId').value;
  setLoading('btnToggle', true, 'Chargement…');
 
  try {
    const data = await apiFetch(URL_RES + '/' + id + '/toggle-etat', 'PATCH', {});
    setLoading('btnToggle', false, 'Confirmer');
 
    if (data.success) {
      closeModal('modalToggle');
      const msg  = data.etat === 'active' ? 'Résident réactivé.' : 'Résident désactivé.';
      const type = data.etat === 'active' ? 'success' : 'warning';
      showToast(msg, type);
      setTimeout(() => location.reload(), 900);
    } else {
      showToast(data.message || 'Erreur.', 'error');
    }
  } catch {
    setLoading('btnToggle', false, 'Confirmer');
    showToast('Erreur réseau.', 'error');
  }
}
/* ════════════════════════════════════════════════════════
   SUPPRESSION
════════════════════════════════════════════════════════ */

function openSupprimer(id, nom) {
  document.getElementById('suppId').value = id;
  document.getElementById('suppNom').textContent = nom;
  openModal('modalSupprimer');
}

async function submitSupprimer() {
  const id = document.getElementById('suppId').value;
  setLoading('btnSupprimer', true, '<i class="fa-solid fa-trash"></i> Confirmer la suppression');

  try {
    const data = await apiFetch(URL_RES + '/' + id, 'DELETE', {});
    setLoading('btnSupprimer', false, '<i class="fa-solid fa-trash"></i> Confirmer la suppression');

    if (data.success) {
      closeModal('modalSupprimer');
      showToast(data.message || 'Résident supprimé.', 'warning');
      setTimeout(() => location.reload(), 900);
    } else {
      showToast(data.message || 'Erreur.', 'error');
    }
  } catch {
    setLoading('btnSupprimer', false, '<i class="fa-solid fa-trash"></i> Confirmer la suppression');
    showToast('Erreur réseau.', 'error');
  }
}

/* ── Animation keyframe toast + erreurs inline ───────────────────── */
const _s = document.createElement('style');
_s.textContent = `
  @keyframes _toastIn { from { opacity:0;transform:translateX(30px) } to { opacity:1;transform:none } }
  .form-err { display:none;font-size:12px;color:var(--red-t);margin-top:4px }
  .s-badge.inactive { background:var(--surface-2);color:var(--text-3);border-color:var(--border-2) }
`;
document.head.appendChild(_s);

</script>
@endpush