@extends('layouts.layout')

@section('title', 'Résidences')
@section('page_title', 'Résidences')

@section('content')

{{-- ── PAGE HEADER ─────────────────────────────────────────────── --}}
<div class="page-header">
  <div class="ph-left">
    <h2>Résidences</h2>
    <p>Gestion des résidences de la copropriété</p>
  </div>
  <div class="ph-right">
    <button class="btn-primary-erp" onclick="openModal('modalAddResidence')">
      <i class="fa-solid fa-plus"></i> Ajouter résidence
    </button>
  </div>
</div>

{{-- ── KPI CARDS ────────────────────────────────────────────────── --}}
<div class="kpi-row" style="grid-template-columns:repeat(3,1fr)">

  <div class="kpi-card kpi-blue" style="animation-delay:.05s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Total résidences</div>
        <div class="kpi-value">{{ $residences->count() }}</div>
        <div class="kpi-sub">résidences enregistrées</div>
      </div>
      <div class="kpi-icon blue"><i class="fa-solid fa-building"></i></div>
    </div>
  </div>

  <div class="kpi-card kpi-green" style="animation-delay:.10s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Avec code postal</div>
        <div class="kpi-value">{{ $residences->whereNotNull('code_postal')->count() }}</div>
        <div class="kpi-sub">résidences géolocalisées</div>
      </div>
      <div class="kpi-icon green"><i class="fa-solid fa-location-dot"></i></div>
    </div>
  </div>

  <div class="kpi-card kpi-orange" style="animation-delay:.15s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Sans code postal</div>
        <div class="kpi-value">{{ $residences->whereNull('code_postal')->count() }}</div>
        <div class="kpi-sub">à compléter</div>
      </div>
      <div class="kpi-icon orange"><i class="fa-solid fa-circle-exclamation"></i></div>
    </div>
  </div>

</div>

{{-- ── FILTER BAR ───────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('admin.residences.index') }}" id="filterForm">
  <div class="filter-bar">

    <div class="filter-search">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input
        type="text"
        name="search"
        value="{{ $search }}"
        placeholder="Nom, adresse, code postal…"
        onchange="document.getElementById('filterForm').submit()"
      >
    </div>

    <div class="filter-divider d-none d-md-block"></div>
    <div class="filter-count d-none d-md-block">
      <strong>{{ $residences->count() }}</strong>
      résidence{{ $residences->count() !== 1 ? 's' : '' }}
    </div>

  </div>
</form>

<!-- {{-- ── SESSION MESSAGES ─────────────────────────────────────────── --}}
@if(session('success'))
  <div class="alert-card alert-success mb-3" style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:var(--r-md);background:var(--green-bg);border:1px solid rgba(34,197,94,.2)">
    <i class="fa-solid fa-circle-check" style="color:var(--green-t)"></i>
    <span style="font-size:13.5px;color:var(--green-t);font-weight:600">{{ session('success') }}</span>
  </div>
@endif

@if(session('error'))
  <div class="alert-card mb-3" style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:var(--r-md);background:var(--red-bg);border:1px solid rgba(239,68,68,.2)">
    <i class="fa-solid fa-triangle-exclamation" style="color:var(--red-t)"></i>
    <span style="font-size:13.5px;color:var(--red-t);font-weight:600">{{ session('error') }}</span>
  </div>
@endif -->

{{-- ── TABLEAU PRINCIPAL ────────────────────────────────────────── --}}
<div class="table-card">
  <div class="table-card-header">
    <div class="tch-left">
      <h5>Liste des résidences</h5>
      <p>{{ $residences->count() }} résidence{{ $residences->count() !== 1 ? 's' : '' }} au total</p>
    </div>
  </div>

  <div class="table-responsive">
    <table class="erp-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Résidence</th>
          <th>Adresse</th>
          <th>Code Postal</th>
          <th>Créée le</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>

        @forelse($residences as $residence)
          @php
            $colors = ['#4f46e5','#0891b2','#059669','#d97706','#7c3aed','#db2777','#0f766e','#dc2626'];
            $color  = $colors[$residence->id % count($colors)];
            $initiales = collect(explode(' ', $residence->nom))
                          ->map(fn($w) => strtoupper(substr($w, 0, 1)))
                          ->take(2)
                          ->implode('');
          @endphp

          <tr>
            <td>
              <span class="ref-code">#{{ $residence->id }}</span>
            </td>

            {{-- Résidence --}}
            <td>
              <div class="tenant-cell">
                <div class="t-avatar" style="background:{{ $color }}">{{ $initiales }}</div>
                <div>
                  <span class="t-name">{{ $residence->nom }}</span>
                  <span class="t-type">Résidence</span>
                </div>
              </div>
            </td>

            {{-- Adresse --}}
            <td>
              <span style="font-size:13.5px;color:var(--text-2)">
                <i class="fa-solid fa-map-marker-alt" style="color:var(--text-4);margin-right:5px;font-size:11px"></i>
                {{ $residence->adresse }}
              </span>
            </td>

            {{-- Code Postal --}}
            <td>
              @if($residence->code_postal)
                <span class="s-badge active">{{ $residence->code_postal }}</span>
              @else
                <span style="color:var(--text-4);font-size:13px">—</span>
              @endif
            </td>

            {{-- Date de création --}}
            <td>
              <span class="date-text">
                {{ $residence->created_at ? $residence->created_at->format('d/m/Y') : '—' }}
              </span>
            </td>

            {{-- Actions --}}
            <td>
              <div class="row-actions">


                {{-- Modifier --}}
                <button
                  class="ra-btn edit"
                  title="Modifier"
                  onclick="openEdit({{ $residence->id }}, '{{ addslashes($residence->nom) }}', '{{ addslashes($residence->adresse) }}', '{{ $residence->code_postal ?? '' }}')"
                >
                  <i class="fa-solid fa-pen-to-square"></i>
                </button>

                {{-- Supprimer --}}
                <form
                  method="POST"
                  action="{{ route('admin.residences.destroy', $residence->id) }}"
                  onsubmit="return confirmDelete('{{ addslashes($residence->nom) }}')"
                  style="display:inline"
                >
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="ra-btn delete" title="Supprimer">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </form>

              </div>
            </td>
          </tr>

        @empty
          <tr>
            <td colspan="6" style="text-align:center;padding:32px;color:var(--text-4);font-size:14px">
              <i class="fa-solid fa-building" style="font-size:28px;margin-bottom:8px;display:block"></i>
              Aucune résidence trouvée. Commencez par en ajouter une.
            </td>
          </tr>
        @endforelse

      </tbody>
    </table>
  </div>

  <div class="table-footer">
    <span class="tf-info">
      <strong>{{ $residences->count() }}</strong>
      résidence{{ $residences->count() !== 1 ? 's' : '' }} affichée{{ $residences->count() !== 1 ? 's' : '' }}
    </span>
  </div>
</div>


{{-- ══════════════════════════════════════════════════════════════
     MODAL — AJOUTER RÉSIDENCE
     ══════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalAddResidence">
  <div class="modal-panel">
    <div class="modal-head">
      <div>
        <h3 class="mh-title">Ajouter une résidence</h3>
        <p class="mh-sub">Renseigner les informations de la nouvelle résidence</p>
      </div>
      <div class="mh-right">
        <button class="modal-close" onclick="closeModal('modalAddResidence')">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>

    <form method="POST" action="{{ route('admin.residences.store') }}">
      @csrf
      <div class="modal-body">

        <div class="form-section-label">
          <i class="fa-solid fa-building" style="color:var(--brand)"></i>
          Informations de la résidence
        </div>

        {{-- Nom --}}
        <div class="form-group">
          <label class="form-label">
            Nom de la résidence <span style="color:var(--red-t)">*</span>
          </label>
          <input
            type="text"
            name="nom"
            class="form-control"
            placeholder="Ex : Résidence Les Oliviers"
            required
          >
        </div>

        {{-- Adresse --}}
        <div class="form-group">
          <label class="form-label">
            Adresse <span style="color:var(--red-t)">*</span>
          </label>
          <input
            type="text"
            name="adresse"
            class="form-control"
            placeholder="Ex : 12 Rue Mohammed V, Casablanca"
            required
          >
        </div>

        {{-- Code Postal --}}
        <div class="form-group">
          <label class="form-label">Code Postal</label>
          <input
            type="text"
            name="code_postal"
            class="form-control"
            placeholder="Ex : 20000"
            maxlength="20"
          >
        </div>

      </div>

      <div class="modal-foot">
        <div class="mf-left"></div>
        <div class="mf-right">
          <button type="button" class="btn-outline-erp" onclick="closeModal('modalAddResidence')">
            Annuler
          </button>
          <button type="submit" class="btn-primary-erp">
            <i class="fa-solid fa-circle-check"></i> Enregistrer
          </button>
        </div>
      </div>
    </form>
  </div>
</div>


{{-- ══════════════════════════════════════════════════════════════
     MODAL — MODIFIER RÉSIDENCE
     ══════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalEditResidence">
  <div class="modal-panel">
    <div class="modal-head">
      <div>
        <h3 class="mh-title">Modifier la résidence</h3>
        <p class="mh-sub" id="meSubtitle">Mettre à jour les informations</p>
      </div>
      <div class="mh-right">
        <button class="modal-close" onclick="closeModal('modalEditResidence')">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>

    <form method="POST" id="formEditResidence" action="">
      @csrf
      @method('PUT')
      <div class="modal-body">

        <div class="form-section-label">
          <i class="fa-solid fa-pen-to-square" style="color:var(--brand)"></i>
          Modifier les informations
        </div>

        {{-- Nom --}}
        <div class="form-group">
          <label class="form-label">
            Nom de la résidence <span style="color:var(--red-t)">*</span>
          </label>
          <input
            type="text"
            id="editNom"
            name="nom"
            class="form-control"
            placeholder="Ex : Résidence Les Oliviers"
            required
          >
        </div>

        {{-- Adresse --}}
        <div class="form-group">
          <label class="form-label">
            Adresse <span style="color:var(--red-t)">*</span>
          </label>
          <input
            type="text"
            id="editAdresse"
            name="adresse"
            class="form-control"
            placeholder="Ex : 12 Rue Mohammed V, Casablanca"
            required
          >
        </div>

        {{-- Code Postal --}}
        <div class="form-group">
          <label class="form-label">Code Postal</label>
          <input
            type="text"
            id="editCodePostal"
            name="code_postal"
            class="form-control"
            placeholder="Ex : 20000"
            maxlength="20"
          >
        </div>

      </div>

      <div class="modal-foot">
        <div class="mf-left"></div>
        <div class="mf-right">
          <button type="button" class="btn-outline-erp" onclick="closeModal('modalEditResidence')">
            Annuler
          </button>
          <button type="submit" class="btn-primary-erp">
            <i class="fa-solid fa-circle-check"></i> Mettre à jour
          </button>
        </div>
      </div>
    </form>
  </div>
</div>




{{-- ══════════════════════════════════════════════════════════════
     JAVASCRIPT
     ══════════════════════════════════════════════════════════════ --}}
@push('scripts')
<script>
/* ── Helpers modals ─────────────────────────────────────────────── */
function openModal(id)  { document.getElementById(id).classList.add('open');    document.body.style.overflow = 'hidden'; }
function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow = ''; }

/* Fermer en cliquant sur l'overlay */
document.querySelectorAll('.modal-overlay').forEach(o => {
  o.addEventListener('click', e => { if (e.target === o) closeModal(o.id); });
});

/* Fermer avec Échap */
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.open').forEach(m => closeModal(m.id));
  }
});

/* ── Ouvrir le modal Modifier ────────────────────────────────────── */
function openEdit(id, nom, adresse, codePostal) {
  document.getElementById('editNom').value        = nom;
  document.getElementById('editAdresse').value    = adresse;
  document.getElementById('editCodePostal').value = codePostal;
  document.getElementById('meSubtitle').textContent = 'Modifier · ' + nom;

  // Mettre à jour l'action du formulaire dynamiquement
  const baseUrl = '{{ url("admin/residences") }}';
  document.getElementById('formEditResidence').action = baseUrl + '/' + id;

  openModal('modalEditResidence');
}

/* ── Confirmer suppression ───────────────────────────────────────── */
function confirmDelete(nom) {
  return confirm('Supprimer la résidence « ' + nom + ' » ?\nCette action est irréversible.');
}

/* ── Toast notifications ─────────────────────────────────────────── */
function toast(type, title, msg) {
  const icons  = { s: 'fa-circle-check', w: 'fa-triangle-exclamation', e: 'fa-circle-xmark' };
  const colors = { s: 'var(--green-t)',  w: 'var(--orange-t)',          e: 'var(--red-t)'   };
  const bgs    = { s: 'var(--green-bg)', w: 'var(--orange-bg)',         e: 'var(--red-bg)'  };

  const el = document.createElement('div');
  el.style.cssText = `
    position:fixed;top:22px;right:22px;z-index:9999;
    display:flex;align-items:center;gap:11px;
    padding:12px 16px;min-width:270px;
    background:#fff;border-radius:12px;
    box-shadow:0 10px 40px rgba(0,0,0,.12);
    border:1px solid var(--border-2);
    animation:toastIn .3s ease;
    font-family:var(--font-b)
  `;
  el.innerHTML = `
    <div style="width:32px;height:32px;border-radius:8px;flex-shrink:0;display:flex;align-items:center;justify-content:center;background:${bgs[type]};color:${colors[type]}">
      <i class="fa-solid ${icons[type]}"></i>
    </div>
    <div>
      <div style="font-family:var(--font-h);font-size:13px;font-weight:700;color:var(--text-1)">${title}</div>
      <div style="font-size:12px;color:var(--text-3)">${msg}</div>
    </div>`;
  document.body.appendChild(el);
  setTimeout(() => {
    el.style.opacity    = '0';
    el.style.transition = 'opacity .3s';
    setTimeout(() => el.remove(), 300);
  }, 4000);
}

/* ── Afficher un toast si session success/error ──────────────────── */
@if(session('success'))
  window.addEventListener('DOMContentLoaded', () => {
    toast('s', 'Succès', '{{ addslashes(session("success")) }}');
  });
@endif
@if(session('error'))
  window.addEventListener('DOMContentLoaded', () => {
    toast('e', 'Erreur', '{{ addslashes(session("error")) }}');
  });
@endif

/* ── Animation CSS ───────────────────────────────────────────────── */
const style = document.createElement('style');
style.textContent = `
  @keyframes toastIn { from { opacity:0; transform:translateX(30px) } to { opacity:1; transform:none } }
`;
document.head.appendChild(style);
</script>
@endpush
@endsection