@extends('layouts.layout')

@section('title', 'Appartements')
@section('page_title', 'Appartements')

@section('content')

{{-- ════════════════════════════════════════════════════════════════
     PAGE HEADER
════════════════════════════════════════════════════════════════ --}}
<div class="page-header">
  <div class="ph-left">
    <h2>Appartements</h2>
    <p>Gestion des appartements — {{ session('residence_nom', 'Toutes résidences') }}</p>
  </div>
  <div class="ph-right">

    <button class="btn-primary-erp" onclick="openModal('modalAjouter')">
      <i class="fa-solid fa-plus"></i> Ajouter appartement
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
        <div class="kpi-label">Total appartements</div>
        <div class="kpi-value" id="statTotal">{{ $appartements->count() }}</div>
        <div class="kpi-sub">enregistrés</div>
      </div>
      <div class="kpi-icon blue"><i class="fa-solid fa-building"></i></div>
    </div>
  </div>

  <div class="kpi-card kpi-green" style="animation-delay:.10s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Occupés</div>
        <div class="kpi-value" id="statOccupe">{{ $appartements->where('statut_occupation','occupe')->count() }}</div>
        <div class="kpi-sub">appartements occupés</div>
      </div>
      <div class="kpi-icon green"><i class="fa-solid fa-person-shelter"></i></div>
    </div>
  </div>

  <div class="kpi-card kpi-red" style="animation-delay:.15s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Libres</div>
        <div class="kpi-value" id="statLibre">{{ $appartements->where('statut_occupation','libre')->count() }}</div>
        <div class="kpi-sub">appartements vacants</div>
      </div>
      <div class="kpi-icon red"><i class="fa-solid fa-door-open"></i></div>
    </div>
  </div>

  <div class="kpi-card" style="animation-delay:.20s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Sans résident</div>
        <div class="kpi-value" id="statSansResident" style="color:var(--text-3)">
          {{ $appartements->whereNull('proprietaire_id')->count() }}
        </div>
        <div class="kpi-sub">à associer</div>
      </div>
      <div class="kpi-icon" style="background:var(--surface-2)">
        <i class="fa-solid fa-user-slash" style="color:var(--text-3)"></i>
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
      placeholder="Numéro, résidence, résident…"
      oninput="filterTable()"
    >
  </div>

  <select id="filterStatut" class="filter-select" onchange="filterTable()">
    <option value="">Tous les statuts</option>
    <option value="occupe">Occupé</option>
    <option value="libre">Libre</option>
  </select>

  <div class="filter-divider"></div>

  <div class="filter-count">
    <strong id="countVisible">{{ $appartements->count() }}</strong>
    appartement{{ $appartements->count() !== 1 ? 's' : '' }}
  </div>

</div>

{{-- ════════════════════════════════════════════════════════════════
     TABLEAU PRINCIPAL
════════════════════════════════════════════════════════════════ --}}
<div class="table-card">

  <div class="table-card-header">
    <div class="tch-left">
      <h5>Liste des appartements</h5>
      <p>{{ $appartements->count() }} appartement{{ $appartements->count() !== 1 ? 's' : '' }} enregistré{{ $appartements->count() !== 1 ? 's' : '' }}</p>
    </div>
  </div>

  <div class="table-responsive">
    <table class="erp-table">
      <thead>
        <tr>
          <th>Numéro</th>
          <th>Résidence</th>
          <th>Étage</th>
          <th>Occupation</th>
          <th>Résident associé</th>
          <th>Date signature</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="aptTbody">

        @forelse($appartements as $apt)
          @php
            $palette = ['#4f46e5','#0891b2','#059669','#d97706','#7c3aed','#db2777','#0f766e','#dc2626'];
            $color   = $palette[$apt->id % count($palette)];
          @endphp

          <tr
            data-id="{{ $apt->id }}"
            data-numero="{{ strtolower($apt->numero) }}"
            data-statut="{{ $apt->statut_occupation }}"
          >

            {{-- Numéro --}}
            <td><span class="ref-code">{{ $apt->numero }}</span></td>

            {{-- Résidence --}}
            <td>
              <span style="font-size:13.5px;color:var(--text-2)">
                <i class="fa-solid fa-location-dot" style="color:var(--text-4);margin-right:5px;font-size:11px"></i>
                {{ $apt->residence->nom ?? '—' }}
              </span>
            </td>

            {{-- Étage --}}
            <td>
              <span style="font-size:13.5px;color:var(--text-3)">
                {{ $apt->etage !== null ? 'Étage '.$apt->etage : '—' }}
              </span>
            </td>

            {{-- Statut occupation --}}
            <td>
              @if($apt->statut_occupation === 'occupe')
                <span class="s-badge active">
                  <i class="fa-solid fa-person-shelter" style="margin-right:4px"></i>Occupé
                </span>
              @else
                <span class="s-badge late">
                  <i class="fa-solid fa-door-open" style="margin-right:4px"></i>Libre
                </span>
              @endif
            </td>

            {{-- Résident associé --}}
            <td>
              @if($apt->proprietaire)
                <div class="tenant-cell">
                  <div class="t-avatar" style="background:{{ $color }}">
                    {{ strtoupper(substr($apt->proprietaire->prenom ?? '', 0, 1)) }}{{ strtoupper(substr($apt->proprietaire->nom ?? '', 0, 1)) }}
                  </div>
                  <div>
                    <span class="t-name">{{ $apt->proprietaire->prenom }} {{ $apt->proprietaire->nom }}</span>
                    <span class="t-type">Propriétaire</span>
                  </div>
                </div>
              @else
                <span style="color:var(--text-4);font-size:13px">
                  <i class="fa-solid fa-circle-minus" style="margin-right:4px"></i>Aucun
                </span>
              @endif
            </td>

            {{-- Date signature --}}
            <td>
              <span class="date-text">
                {{ $apt->date_signature_contrat
                    ? \Carbon\Carbon::parse($apt->date_signature_contrat)->format('d/m/Y')
                    : '—' }}
              </span>
            </td>

            {{-- Actions --}}
            <td>
              <div class="row-actions">

                {{-- Modifier --}}
                <button
                  class="ra-btn edit"
                  title="Modifier"
                  onclick="openModifier({{ $apt->id }},'{{ addslashes($apt->numero) }}',{{ $apt->etage ?? 'null' }},{{ $apt->residence_id }},'{{ $apt->statut_occupation }}')"
                >
                  <i class="fa-solid fa-pen-to-square"></i>
                </button>

                {{-- Associer / Changer résident --}}
                <button
                  class="ra-btn encaisser"
                  title="{{ $apt->proprietaire_id ? 'Changer résident' : 'Associer résident' }}"
                  onclick="openAssocier({{ $apt->id }},'{{ addslashes($apt->numero) }}',{{ $apt->proprietaire_id ?? 'null' }})"
                >
                  <i class="fa-solid fa-user-{{ $apt->proprietaire_id ? 'pen' : 'plus' }}"></i>
                </button>

                {{-- Dissocier --}}
                @if($apt->proprietaire_id)
                  <button
                    class="ra-btn"
                    style="color:var(--orange-t)"
                    title="Dissocier le résident"
                    onclick="dissocierResident({{ $apt->id }},'{{ addslashes($apt->numero) }}')"
                  >
                    <i class="fa-solid fa-user-minus"></i>
                  </button>
                @endif

                {{-- Supprimer --}}
                <button
                  class="ra-btn delete"
                  title="Supprimer"
                  onclick="openSupprimer({{ $apt->id }},'{{ addslashes($apt->numero) }}')"
                >
                  <i class="fa-solid fa-trash"></i>
                </button>

              </div>
            </td>

          </tr>

        @empty
          <tr>
            <td colspan="7" style="text-align:center;padding:40px;color:var(--text-4);font-size:14px">
              <i class="fa-solid fa-building" style="font-size:30px;margin-bottom:10px;display:block"></i>
              Aucun appartement trouvé.
            </td>
          </tr>
        @endforelse

      </tbody>
    </table>
  </div>

  <div class="table-footer">
    <span class="tf-info">
      <strong>{{ $appartements->count() }}</strong>
      appartement{{ $appartements->count() !== 1 ? 's' : '' }} affiché{{ $appartements->count() !== 1 ? 's' : '' }}
    </span>
  </div>

</div>{{-- /table-card --}}


{{-- ════════════════════════════════════════════════════════════════
     MODAL — AJOUTER APPARTEMENT
════════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalAjouter">
  <div class="modal-panel">

    <div class="modal-head">
      <div>
        <h3 class="mh-title">Ajouter un appartement</h3>
        <p class="mh-sub">La date de signature sera définie lors de l'association d'un résident.</p>
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
        Informations de l'appartement
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="form-group">
          <label class="form-label">Numéro <span style="color:var(--red-t)">*</span></label>
          <input type="text" id="addNumero" class="form-control" placeholder="Ex : B-203">
          <div id="errAddNumero" class="form-err"></div>
        </div>
        <div class="form-group">
          <label class="form-label">Étage</label>
          <input type="number" id="addEtage" class="form-control" placeholder="Ex : 2" min="0">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Résidence <span style="color:var(--red-t)">*</span></label>
        <select id="addResidence" class="form-control">
          <option value="">— Sélectionner une résidence —</option>
          @foreach($residences as $r)
            <option value="{{ $r->id }}" {{ session('residence_id') == $r->id ? 'selected' : '' }}>
              {{ $r->nom }}
            </option>
          @endforeach
        </select>
        <div id="errAddResidence" class="form-err"></div>
      </div>

      <div class="form-group">
        <label class="form-label">Statut d'occupation <span style="color:var(--red-t)">*</span></label>
        <select id="addStatut" class="form-control">
          <option value="libre">Libre</option>
        </select>
      </div>

    </div>{{-- /modal-body --}}

    <div class="modal-foot">
      <div class="mf-left"></div>
      <div class="mf-right">
        <button class="btn-outline-erp" onclick="closeModal('modalAjouter')">Annuler</button>
        <button class="btn-primary-erp" id="btnAjouter" onclick="submitAjouter()">
          <i class="fa-solid fa-plus"></i> Créer l'appartement
        </button>
      </div>
    </div>

  </div>
</div>


{{-- ════════════════════════════════════════════════════════════════
     MODAL — MODIFIER APPARTEMENT
════════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalModifier">
  <div class="modal-panel">

    <div class="modal-head">
      <div>
        <h3 class="mh-title">Modifier l'appartement</h3>
        <p class="mh-sub" id="modSubtitle">Appartement —</p>
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
        Modifier les informations
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="form-group">
          <label class="form-label">Numéro <span style="color:var(--red-t)">*</span></label>
          <input type="text" id="modNumero" class="form-control">
          <div id="errModNumero" class="form-err"></div>
        </div>
        <div class="form-group">
          <label class="form-label">Étage</label>
          <input type="number" id="modEtage" class="form-control" min="0">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Résidence <span style="color:var(--red-t)">*</span></label>
        <select id="modResidence" class="form-control">
          @foreach($residences as $r)
            <option value="{{ $r->id }}">{{ $r->nom }}</option>
          @endforeach
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Statut d'occupation</label>
        <select id="modStatut" class="form-control">
          <option value="libre">Libre</option>
          <option value="occupe">Occupé</option>
        </select>
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
     MODAL — ASSOCIER RÉSIDENT
════════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalAssocier">
  <div class="modal-panel">

    <div class="modal-head">
      <div>
        <h3 class="mh-title">Associer un résident</h3>
        <p class="mh-sub" id="assocSubtitle">Appartement —</p>
      </div>
      <div class="mh-right">
        <button class="modal-close" onclick="closeModal('modalAssocier')">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>

    <div class="modal-body">
      <input type="hidden" id="assocAptId">

      {{-- Alerte cotisations automatiques --}}
      <div style="display:flex;gap:10px;align-items:flex-start;background:var(--blue-bg,#eef2ff);border:1.5px solid rgba(99,102,241,.25);border-radius:var(--r-md);padding:12px 14px;margin-bottom:18px">
        <i class="fa-solid fa-circle-info" style="color:var(--brand);font-size:14px;flex-shrink:0;margin-top:1px"></i>
        <div style="font-size:12.5px;color:var(--text-2);line-height:1.6">
          <strong>Cotisations automatiques :</strong> après l'association, les cotisations seront calculées automatiquement à partir de la date de signature, avec prorata pour la première année.
        </div>
      </div>

      <div class="form-section-label">
        <i class="fa-solid fa-user-plus" style="color:var(--brand)"></i>
        Sélectionner le résident
      </div>

      <div class="form-group">
        <label class="form-label">Résident / Propriétaire <span style="color:var(--red-t)">*</span></label>
        <select id="assocResident" class="form-control">
          <option value="">— Sélectionner un résident —</option>
          @foreach($tousResidents as $r)
            <option value="{{ $r->id }}">
              {{ $r->nom }} {{ $r->prenom }} 
              @if($r->appartement) (Apt. {{ $r->appartement->numero }}) @endif
            </option>
          @endforeach
        </select>
        <div id="errAssocResident" class="form-err"></div>
      </div>

      <div class="form-group">
        <label class="form-label">Date de signature du contrat <span style="color:var(--red-t)">*</span></label>
        <input type="date" id="assocDateSignature" class="form-control" max="{{ now()->format('Y-m-d') }}">
        <div id="errAssocDate" class="form-err"></div>
        <div style="font-size:12px;color:var(--text-4);margin-top:5px">
          <i class="fa-solid fa-calendar-check" style="margin-right:4px"></i>
          Cette date détermine l'année de début et le prorata des cotisations.
        </div>
      </div>

    </div>{{-- /modal-body --}}

    <div class="modal-foot">
      <div class="mf-left"></div>
      <div class="mf-right">
        <button class="btn-outline-erp" onclick="closeModal('modalAssocier')">Annuler</button>
        <button class="btn-primary-erp" id="btnAssocier" onclick="submitAssocier()">
          <i class="fa-solid fa-user-check"></i> Associer et générer les cotisations
        </button>
      </div>
    </div>

  </div>
</div>


{{-- ════════════════════════════════════════════════════════════════
     MODAL — SUPPRIMER
════════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalSupprimer">
  <div class="modal-panel" style="max-width:460px">

    <div class="modal-head">
      <div>
        <h3 class="mh-title" style="color:var(--red-t)">
          <i class="fa-solid fa-triangle-exclamation" style="margin-right:6px"></i>Supprimer l'appartement
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
          Êtes-vous sûr de vouloir supprimer l'appartement
          <strong id="suppNumero" style="color:var(--red-t)"></strong> ?
        </p>
        <p style="font-size:12.5px;color:var(--text-3);margin:0">
          Toutes les données associées (cotisations, transactions) peuvent être affectées.
        </p>
      </div>
    </div>{{-- /modal-body --}}

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
const URL_APT = '{{ url("admin/appartements") }}';

/* ── Helpers : ouvrir / fermer modals ────────────────────────────── 
function openModal(id) {
  document.getElementById(id).classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeModal(id) {
  document.getElementById(id).classList.remove('open');
  document.body.style.overflow = '';
}*/

/* Clic sur l'overlay → fermer 
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', e => {
    if (e.target === overlay) closeModal(overlay.id);
  });
});*/

/* Touche Échap → fermer 
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.open').forEach(m => closeModal(m.id));
  }
});*/

/* ── Filtre tableau (client-side) ────────────────────────────────── */
function filterTable() {
  const q      = document.getElementById('searchInput').value.toLowerCase();
  const statut = document.getElementById('filterStatut').value;
  let visible  = 0;

  document.querySelectorAll('#aptTbody tr[data-id]').forEach(tr => {
    const matchQ = !q || tr.textContent.toLowerCase().includes(q);
    const matchS = !statut || tr.dataset.statut === statut;
    const show   = matchQ && matchS;
    tr.style.display = show ? '' : 'none';
    if (show) visible++;
  });

  const counter = document.getElementById('countVisible');
  if (counter) counter.textContent = visible;
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

/* ── Toast ERP (même style que cotisations) ──────────────────────── */
function showToast(msg, type = 'success') {
  const map = {
    success: { icon: 'fa-circle-check',       color: 'var(--green-t)',  bg: 'var(--green-bg)',  title: 'Succès'     },
    error:   { icon: 'fa-circle-xmark',        color: 'var(--red-t)',   bg: 'var(--red-bg)',    title: 'Erreur'     },
    warning: { icon: 'fa-triangle-exclamation',color: 'var(--orange-t)',bg: 'var(--orange-bg)', title: 'Attention'  },
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
    font-family:var(--font-b)
  `;
  el.innerHTML = `
    <div style="width:34px;height:34px;border-radius:8px;flex-shrink:0;
                display:flex;align-items:center;justify-content:center;
                background:${bg};color:${color};font-size:15px">
      <i class="fa-solid ${icon}"></i>
    </div>
    <div style="flex:1;min-width:0">
      <div style="font-family:var(--font-h);font-size:13px;font-weight:700;color:var(--text-1)">${title}</div>
      <div style="font-size:12px;color:var(--text-3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${msg}</div>
    </div>
  `;
  document.body.appendChild(el);
  setTimeout(() => {
    el.style.transition = 'opacity .3s, transform .3s';
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
   OUVRIR MODIFIER
════════════════════════════════════════════════════════ */
function openModifier(id, numero, etage, residenceId, statut) {
  document.getElementById('modId').value     = id;
  document.getElementById('modNumero').value = numero;
  document.getElementById('modEtage').value  = etage ?? '';
  document.getElementById('modStatut').value = statut;
  document.getElementById('modSubtitle').textContent = 'Appartement ' + numero;

  const sel = document.getElementById('modResidence');
  for (let o of sel.options) {
    if (o.value == residenceId) { o.selected = true; break; }
  }
  clearErrors(['errModNumero']);
  openModal('modalModifier');
}

/* ════════════════════════════════════════════════════════
   OUVRIR ASSOCIER
════════════════════════════════════════════════════════ */
function openAssocier(id, numero, currentResidentId) {
  document.getElementById('assocAptId').value = id;
  document.getElementById('assocSubtitle').textContent = 'Appartement ' + numero;
  document.getElementById('assocDateSignature').value  = '';

  const sel = document.getElementById('assocResident');
  for (let o of sel.options) {
    o.selected = (currentResidentId && o.value == currentResidentId);
  }
  clearErrors(['errAssocResident', 'errAssocDate']);
  openModal('modalAssocier');
}

/* ════════════════════════════════════════════════════════
   OUVRIR SUPPRIMER
════════════════════════════════════════════════════════ */
function openSupprimer(id, numero) {
  document.getElementById('suppId').value = id;
  document.getElementById('suppNumero').textContent = numero;
  openModal('modalSupprimer');
}

/* ════════════════════════════════════════════════════════
   SUBMIT — AJOUTER
════════════════════════════════════════════════════════ */
async function submitAjouter() {
  clearErrors(['errAddNumero', 'errAddResidence']);

  const numero      = document.getElementById('addNumero').value.trim();
  const etage       = document.getElementById('addEtage').value;
  const residenceId = document.getElementById('addResidence').value;
  const statut      = document.getElementById('addStatut').value;

  let ok = true;
  if (!numero)      { showError('errAddNumero',    'Le numéro est requis.');      ok = false; }
  if (!residenceId) { showError('errAddResidence', 'La résidence est requise.'); ok = false; }
  if (!ok) return;

  setLoading('btnAjouter', true, '<i class="fa-solid fa-plus"></i> Créer l\'appartement');

  try {
    const data = await apiFetch(URL_APT, 'POST', {
      numero,
      etage:             etage || null,
      residence_id:      residenceId,
      statut_occupation: statut,
    });
    setLoading('btnAjouter', false, '<i class="fa-solid fa-plus"></i> Créer l\'appartement');

    if (data.success) {
      closeModal('modalAjouter');
      showToast('Appartement ' + data.appartement.numero + ' créé avec succès.');
      setTimeout(() => location.reload(), 900);
    } else {
      showToast(data.message || 'Erreur lors de la création.', 'error');
    }
  } catch {
    setLoading('btnAjouter', false, '<i class="fa-solid fa-plus"></i> Créer l\'appartement');
    showToast('Erreur réseau.', 'error');
  }
}

/* ════════════════════════════════════════════════════════
   SUBMIT — MODIFIER
════════════════════════════════════════════════════════ */
async function submitModifier() {
  clearErrors(['errModNumero']);

  const id          = document.getElementById('modId').value;
  const numero      = document.getElementById('modNumero').value.trim();
  const etage       = document.getElementById('modEtage').value;
  const residenceId = document.getElementById('modResidence').value;
  const statut      = document.getElementById('modStatut').value;

  if (!numero) { showError('errModNumero', 'Le numéro est requis.'); return; }

  setLoading('btnModifier', true, '<i class="fa-solid fa-circle-check"></i> Enregistrer');

  try {
    const data = await apiFetch(URL_APT + '/' + id, 'PUT', {
      numero,
      etage:             etage || null,
      residence_id:      residenceId,
      statut_occupation: statut,
    });
    setLoading('btnModifier', false, '<i class="fa-solid fa-circle-check"></i> Enregistrer');

    if (data.success) {
      closeModal('modalModifier');
      showToast('Appartement modifié avec succès.');
      setTimeout(() => location.reload(), 900);
    } else {
      showToast(data.message || 'Erreur.', 'error');
    }
  } catch {
    setLoading('btnModifier', false, '<i class="fa-solid fa-circle-check"></i> Enregistrer');
    showToast('Erreur réseau.', 'error');
  }
}

/* ════════════════════════════════════════════════════════
   SUBMIT — ASSOCIER RÉSIDENT
════════════════════════════════════════════════════════ */
async function submitAssocier() {
  clearErrors(['errAssocResident', 'errAssocDate']);

  const aptId    = document.getElementById('assocAptId').value;
  const resident = document.getElementById('assocResident').value;
  const date     = document.getElementById('assocDateSignature').value;

  let ok = true;
  if (!resident) { showError('errAssocResident', 'Veuillez sélectionner un résident.');          ok = false; }
  if (!date)     { showError('errAssocDate',     'La date de signature est obligatoire.'); ok = false; }
  if (!ok) return;

  setLoading('btnAssocier', true, '<i class="fa-solid fa-user-check"></i> Associer et générer les cotisations');

  try {
    const data = await apiFetch(URL_APT + '/' + aptId + '/associer', 'POST', {
      proprietaire_id:        resident,
      date_signature_contrat: date,
    });
    setLoading('btnAssocier', false, '<i class="fa-solid fa-user-check"></i> Associer et générer les cotisations');

    if (data.success) {
      closeModal('modalAssocier');
      showToast(data.message || 'Résident associé. Cotisations générées.');
      setTimeout(() => location.reload(), 900);
    } else {
      showToast(data.message || 'Erreur.', 'error');
    }
  } catch {
    setLoading('btnAssocier', false, '<i class="fa-solid fa-user-check"></i> Associer et générer les cotisations');
    showToast('Erreur réseau.', 'error');
  }
}

/* ════════════════════════════════════════════════════════
   DISSOCIER RÉSIDENT
════════════════════════════════════════════════════════ */
async function dissocierResident(aptId, numero) {
  if (!confirm('Dissocier le résident de l\'appartement ' + numero + ' ?')) return;

  try {
    const data = await apiFetch(URL_APT + '/' + aptId + '/dissocier', 'POST', {});
    if (data.success) {
      showToast('Résident dissocié. Appartement libéré.', 'warning');
      setTimeout(() => location.reload(), 900);
    } else {
      showToast(data.message || 'Erreur.', 'error');
    }
  } catch {
    showToast('Erreur réseau.', 'error');
  }
}

/* ════════════════════════════════════════════════════════
   SUBMIT — SUPPRIMER
════════════════════════════════════════════════════════ */
async function submitSupprimer() {
  const id = document.getElementById('suppId').value;
  setLoading('btnSupprimer', true, '<i class="fa-solid fa-trash"></i> Confirmer la suppression');

  try {
    const data = await apiFetch(URL_APT + '/' + id, 'DELETE', {});
    setLoading('btnSupprimer', false, '<i class="fa-solid fa-trash"></i> Confirmer la suppression');

    if (data.success) {
      closeModal('modalSupprimer');
      showToast('Appartement supprimé.', 'warning');
      setTimeout(() => location.reload(), 900);
    } else {
      showToast(data.message || 'Erreur.', 'error');
    }
  } catch {
    setLoading('btnSupprimer', false, '<i class="fa-solid fa-trash"></i> Confirmer la suppression');
    showToast('Erreur réseau.', 'error');
  }
}

/* ── Animation keyframe toast ────────────────────────────────────── */
const _s = document.createElement('style');
_s.textContent = `@keyframes _toastIn { from { opacity:0;transform:translateX(30px) } to { opacity:1;transform:none } }
.form-err { display:none;font-size:12px;color:var(--red-t);margin-top:4px }`;
document.head.appendChild(_s);

</script>
@endpush