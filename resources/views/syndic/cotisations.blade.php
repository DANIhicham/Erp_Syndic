@extends('layouts.layout')

@section('title', 'Cotisations')
@section('page_title', 'Cotisations')

@section('content')

{{-- ── PAGE HEADER ─────────────────────────────────────────────── --}}
<div class="page-header">
  <div class="ph-left">
    <h2>Cotisations</h2>
    <p>
      Suivi des paiements des copropriétaires

      {{ $annee }}
    </p>
  </div>
  <div class="ph-right">
    <a href="{{ route('syndic.cotisations.export', ['annee' => $annee]) }}"
       class="btn-outline-erp">
      <i class="fa-solid fa-download"></i> Exporter CSV
    </a>
    <button class="btn-primary-erp" onclick="openModal('modalCotisation')">
      <i class="fa-solid fa-plus"></i> Enregistrer paiement
    </button>
  </div>
</div>

{{-- ── KPI CARDS ────────────────────────────────────────────────── --}}
<div class="kpi-row" style="grid-template-columns:repeat(4,1fr)">


    <div class="kpi-card kpi-green" style="animation-delay:.05s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Appartements payés</div>
        <div class="kpi-value">
          {{ $stats['payes'] }}
          <small style="font-size:13px;font-weight:500;color:var(--text-4)">Appt</small>
        </div>
        <div class="kpi-sub">Le montant annuel :<strong>
          {{ number_format($montantAnnuelFixe, 2, ',', ' ') }} MAD</strong>
        </div>
      </div>
      <div class="kpi-icon green"><i class="fa-solid fa-building-columns"></i></div>
    </div>
  </div>

  <div class="kpi-card kpi-blue" style="animation-delay:.1s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Encaissé</div>
        <div class="kpi-value">
          {{ number_format($stats['encaissé'], 0, ',', ' ') }}
          <small style="font-size:13px;font-weight:500;color:var(--text-4)">MAD</small>
        </div>
        <div class="kpi-sub">{{ $stats['payes'] }} copropriétaires soldés tout</div>
      </div>
      <div class="kpi-icon blue"><i class="fa-solid fa-check-circle"></i></div>
    </div>
  </div>

  <div class="kpi-card kpi-orange" style="animation-delay:.15s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Partiels</div>
        <div class="kpi-value">{{ $stats['partiels'] }}</div>
        <div class="kpi-sub">Paiements incomplets</div>
      </div>
      <div class="kpi-icon orange"><i class="fa-solid fa-code-branch"></i></div>
    </div>
  </div>

  <div class="kpi-card kpi-red" style="animation-delay:.2s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Impayés</div>
        <div class="kpi-value">
          {{ number_format($stats['reste'], 0, ',', ' ') }}
          <small style="font-size:13px;font-weight:500;color:var(--text-4)">MAD</small>
        </div>
        <div class="kpi-sub">
          <span class="kpi-badge-alert">
            <i class="fa-solid fa-clock"></i> {{ $stats['retards'] }} en retard
          </span>
        </div>
      </div>
      <div class="kpi-icon red"><i class="fa-solid fa-triangle-exclamation"></i></div>
    </div>
  </div>

</div>

{{-- ── FILTER BAR ───────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('syndic.cotisations.index') }}" id="filterForm">
  <div class="filter-bar">

    {{-- Recherche texte --}}
    <div class="filter-search">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input
        type="text"
        name="search"
        value="{{ $search }}"
        placeholder="Copropriétaire, numéro appartement…"
        onchange="document.getElementById('filterForm').submit()"
      >
    </div>

    {{-- Filtre année --}}
    <select name="annee" class="filter-select" onchange="this.form.submit()">
      @foreach($annees as $a)
        <option value="{{ $a }}" {{ $annee == $a ? 'selected' : '' }}>{{ $a }}</option>
      @endforeach
    </select>

    {{-- Filtre statut --}}
    <select name="statut" class="filter-select" onchange="this.form.submit()">
      <option value="" {{ $statut === '' ? 'selected' : '' }}>Tous les statuts</option>
      <option value="payé"       {{ $statut === 'payé'       ? 'selected' : '' }}>Payé</option>
      <option value="partiel"    {{ $statut === 'partiel'    ? 'selected' : '' }}>Partiel</option>
      <option value="en_retard"  {{ $statut === 'en_retard'  ? 'selected' : '' }}>En retard</option>
    </select>

    <div class="filter-divider d-none d-md-block"></div>
    <div class="filter-count d-none d-md-block">
      <strong>{{ $appartements->count() }}</strong> copropriétaire{{ $appartements->count() !== 1 ? 's' : '' }}
    </div>

  </div>
</form>

{{-- ── SESSION MESSAGES ─────────────────────────────────────────── --}}
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
@endif

{{-- ── TABLEAU PRINCIPAL ────────────────────────────────────────── --}}
<div class="table-card">
  <div class="table-card-header">
    <div class="tch-left">
      <h5>Cotisations {{ $annee }}</h5>
      <p>
        Montant annuel fixe : <strong>{{ number_format($stats['total_budget'], 0, ',', ' ') }} MAD</strong>
        @if($montantAnnuelFixe > 0)
          ({{ number_format($montantAnnuelFixe/12, 0, ',', ' ') }} MAD/mois)
        @endif
      </p>
    </div>
    <div class="tch-right">
      <button class="btn-outline-erp" style="font-size:12px;padding:7px 12px" onclick="relancerTous()">
        <i class="fa-solid fa-paper-plane"></i> Relancer tous
      </button>
    </div>
  </div>

  <div class="table-responsive">
    <table class="erp-table">
      <thead>
        <tr>
          <th><input type="checkbox" class="row-check" id="checkAll"></th>
          <th>Copropriétaire</th>
          <th>Appartement</th>
          <th>Montant Annuel</th>
          <th>Payé</th>
          <th>Reste</th>
          <th>Date Échéance</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>

        @forelse($appartements as $apt)
          {{-- Couleur fond selon statut --}}
          @php
            $trStyle = match($apt['statut']) {
              'en_retard' => 'background:rgba(239,68,68,.025)',
              'partiel'   => 'background:rgba(245,158,11,.025)',
              default     => '',
            };
            $statutBadge = match($apt['statut']) {
              'payé'      => 'paid',
              'partiel'   => 'partial',
              'en_retard' => 'late',
              default     => 'pending',
            };
            $statutLabel = match($apt['statut']) {
              'payé'      => 'Payé',
              'partiel'   => 'Partiel',
              'en_retard' => 'En retard',
              default     => 'En attente',
            };

            // Couleur avatar déterministe selon l'id
            $colors = ['#4f46e5','#0891b2','#059669','#d97706','#7c3aed','#db2777','#0f766e','#dc2626'];
            $color  = $colors[$apt['id'] % count($colors)];
          @endphp

          <tr
            style="{{ $trStyle }}"
            data-apt-id="{{ $apt['id'] }}"
            data-apt-nom="{{ htmlspecialchars($apt['nom']) }}"
            data-apt-num="{{ $apt['numero'] }}"
            data-annee="{{ $annee }}"
            data-montant-annuel="{{ $apt['montant_annuel'] }}"
            data-montant-paye="{{ $apt['montant_paye'] }}"
            data-reste="{{ $apt['reste'] }}"
            data-statut="{{ $apt['statut'] }}"
          >
            <td><input type="checkbox" class="row-check" value="{{ $apt['id'] }}"></td>

            {{-- Copropriétaire --}}
            <td>
              <div class="tenant-cell">
                <div class="t-avatar" style="background:{{ $color }}">{{ $apt['initiales'] }}</div>
                <div>
                  <span class="t-name">{{ $apt['nom'] }}</span>
                  <span class="t-type">Propriétaire</span>
                  @if($apt['est_ancien_prop'])
                    <span class="s-badge" style="background:#eef2ff;color:var(--brand);font-size:11px;margin-left:5px">
                      <i class="fa-solid fa-right-left" style="margin-right:3px"></i>
                      {{ $apt['label_transfert'] }}
                    </span>
                  @endif
                </div>
              </div>
            </td>

            {{-- Appartement --}}
            <td><span class="ref-code">Apt. {{ $apt['numero'] }}</span></td>

            {{-- Montant annuel --}}
            <td>
              <span class="amount-main">{{ number_format($apt['montant_annuel'], 0, ',', ' ') }} MAD</span>
            </td>

            {{-- Montant payé --}}
            <td>
              @if($apt['montant_paye'] > 0)
                <span style="font-family:var(--font-h);font-weight:700;color:var(--green-t)">
                  {{ number_format($apt['montant_paye'], 0, ',', ' ') }} MAD
                </span>
              @else
                <span style="font-family:var(--font-h);font-weight:700;color:var(--text-4)">0 MAD</span>
              @endif
            </td>

            {{-- Reste --}}
            <td>
              @if($apt['reste'] > 0)
                <span style="font-family:var(--font-h);font-weight:700;color:var(--red-t)">
                  {{ number_format($apt['reste'], 0, ',', ' ') }} MAD
                </span>
              @else
                <span style="color:var(--text-4);font-weight:500">— MAD</span>
              @endif
            </td>

            {{-- Date échéance --}}
            <td>
              <span class="date-text">{{ $apt['date_echeance'] ?? '—' }}</span>
            </td>

            {{-- Statut --}}
            <td>
              <span class="s-badge {{ $statutBadge }}">{{ $statutLabel }}</span>
            </td>

            {{-- Actions --}}
            <td>
              <div class="row-actions">

                {{-- Bouton Encaisser (si non soldé) --}}
                @if($apt['statut'] !== 'payé')
                  <button
                    class="ra-btn encaisser"
                    title="Encaisser"
                    onclick="openEncaisser({{ $apt['id'] }}, '{{ htmlspecialchars($apt['nom']) }}', '{{ $apt['numero'] }}', {{ $apt['montant_annuel'] }}, {{ $apt['montant_paye'] }}, '{{ $apt['statut'] }}', {{ $apt['proprietaire_id'] ?? 'null' }})"
                  >
                    <i class="fa-solid fa-cash-register"></i> Encaisser
                  </button>
                @endif

                {{-- Voir historique --}}
                <button
                  class="ra-btn view"
                  title="Voir historique"
                  onclick="openHistorique({{ $apt['id'] }}, {{ $annee }})"
                >
                  <i class="fa-solid fa-eye"></i>
                </button>

                {{-- Télécharger reçu --}}
                @if($apt['last_transaction_id'])
                    <a
                        href="{{ route('syndic.cotisations.recu', $apt['last_transaction_id']) }}"
                        
                        class="ra-btn download"
                        title="Télécharger reçu"
                    >
                        <i class="fa-solid fa-file-pdf"></i>
                    </a>
                @endif
                {{-- Relancer (si retard) --}}
                @if($apt['statut'] === 'en_retard')
                  <button
                    class="ra-btn"
                    style="color:var(--orange-t)"
                    title="Envoyer relance"
                    onclick="relancerProprietaire({{ $apt['id'] }})"
                  >
                    <i class="fa-solid fa-paper-plane"></i>
                  </button>
                @endif

              </div>
            </td>
          </tr>

        @empty
          <tr>
            <td colspan="9" style="text-align:center;padding:32px;color:var(--text-4);font-size:14px">
              <i class="fa-solid fa-inbox" style="font-size:28px;margin-bottom:8px;display:block"></i>
              Aucun appartement trouvé pour cette résidence / ces filtres.
            </td>
          </tr>
        @endforelse

      </tbody>
    </table>
  </div>

  <div class="table-footer">
    <span class="tf-info">
      <strong>{{ $appartements->count() }}</strong> appartement{{ $appartements->count() !== 1 ? 's' : '' }} affiché{{ $appartements->count() !== 1 ? 's' : '' }}
    </span>
  </div>
</div>




{{-- ══════════════════════════════════════════════════════════════
     MODALS — dans @push('modals') ou directement ici
     (selon votre layout)
     ══════════════════════════════════════════════════════════════ --}}


{{-- ── MODAL 1 — ENCAISSER COTISATION ─────────────────────────── --}}
<div class="modal-overlay" id="modalCotisation">
  <div class="modal-panel">
    <div class="modal-head">
      <div>
        <h3 class="mh-title">Encaisser Cotisation</h3>
        <p class="mh-sub" id="mcSub">Enregistrer un paiement de cotisation syndic</p>
      </div>
      <div class="mh-right">
        <button class="modal-close" onclick="closeModal('modalCotisation')">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>

    <div class="modal-body">

      {{-- Récap montant (affiché dynamiquement) --}}
      <div id="mcRecap" style="display:none;
           background:#f8fafc;border:1px solid var(--border-2);border-radius:var(--r-md);
           padding:12px 16px;margin-bottom:16px;
           display:grid;grid-template-columns:repeat(3,1fr);gap:0">
        <div style="padding:8px;text-align:center;border-right:1px solid var(--border-2)">
          <div style="font-size:10px;font-weight:700;color:var(--text-4);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Montant Annuel</div>
          <div style="font-family:var(--font-h);font-size:15px;font-weight:800;color:var(--text-1)" id="mcDue">—</div>
        </div>
        <div style="padding:8px;text-align:center;border-right:1px solid var(--border-2)">
          <div style="font-size:10px;font-weight:700;color:var(--text-4);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Déjà Payé</div>
          <div style="font-family:var(--font-h);font-size:15px;font-weight:800;color:var(--green-t)" id="mcAlready">—</div>
        </div>
        <div style="padding:8px;text-align:center">
          <div style="font-size:10px;font-weight:700;color:var(--text-4);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Reste</div>
          <div style="font-family:var(--font-h);font-size:15px;font-weight:800;color:var(--red-t)" id="mcRest">—</div>
        </div>
      </div>

      <form id="formCotisation" enctype="multipart/form-data">
        @csrf

        {{-- Champs cachés --}}
        <input type="hidden" name="annee" id="mcAnnee" value="{{ $annee }}">
        <input type="hidden" name="appartement_id" id="mcAppartementId">
        <input type="hidden" name="user_id" id="mcUserId">

        <div class="form-section-label"><i class="fa-solid fa-user"></i> Copropriétaire & Appartement</div>
        <div class="form-grid">

          {{-- Sélecteur copropriétaire (prérempli si ouvert via bouton) --}}
          <div class="form-group">
            <label class="form-label">Copropriétaire <span class="req">*</span></label>
            <select class="form-control-erp" id="mcSelectApt" onchange="onAptChange(this)">
              <option value="">— Sélectionner —</option>
              @foreach($appartements as $apt)
                <option
                  value="{{ $apt['id'] }}"
                  data-nom="{{ htmlspecialchars($apt['nom']) }}"
                  data-num="{{ $apt['numero'] }}"
                  data-annuel="{{ $apt['montant_annuel'] }}"
                  data-paye="{{ $apt['montant_paye'] }}"
                  data-reste="{{ $apt['reste'] }}"
                  data-proprietaire-id="{{ $apt['proprietaire_id'] ?? '' }}"
                >
                  {{ $apt['nom'] }} — Apt. {{ $apt['numero'] }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Année concernée <span class="req">*</span></label>
            <select class="form-control-erp" id="mcSelectAnnee" name="annee">
              @foreach($annees as $a)
                <option value="{{ $a }}" {{ $annee == $a ? 'selected' : '' }}>{{ $a }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="form-section-label"><i class="fa-solid fa-coins"></i> Paiement</div>
        <div class="form-grid">

          <div class="form-group">
            <label class="form-label">Montant payé (MAD) <span class="req">*</span></label>
            <input
              type="number"
              class="form-control-erp"
              name="montant"
              id="mcMontant"
              min="1" step="0.01"
              placeholder="ex: 7 680"
            >
            {{-- Chips raccourcis --}}
            <div id="mcChips" style="display:flex;gap:5px;flex-wrap:wrap;margin-top:6px"></div>
          </div>

          <div class="form-group">
            <label class="form-label">Date paiement <span class="req">*</span></label>
            <input
              type="date"
              class="form-control-erp"
              name="date_paiement"
              id="mcDate"
              value="{{ now()->format('Y-m-d') }}"
            >
          </div>

          <div class="form-group">
            <label class="form-label">Mode de paiement</label>
            <select class="form-control-erp" name="mode_paiement">
              <option value="virement">Virement bancaire</option>
              <option value="especes">Espèces</option>
              <option value="cheque">Chèque</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Référence</label>
            <input
              type="text"
              class="form-control-erp"
              name="reference"
              placeholder="Nº virement ou chèque"
            >
          </div>
        </div>

        <div class="form-group" style="margin-top:4px">
          <label class="form-label">Commentaire</label>
          <textarea
            class="form-control-erp"
            name="commentaire"
            rows="2"
            placeholder="Notes optionnelles…"
          ></textarea>
        </div>

        <div class="form-section-label"><i class="fa-solid fa-paperclip"></i> Justificatif</div>
        <div class="upload-zone" onclick="document.getElementById('mcFile').click()">
          <input type="file" id="mcFile" name="document" accept=".pdf,.jpg,.jpeg,.png" style="display:none" onchange="onFileSelect(this)">
          <i class="fa-solid fa-cloud-arrow-up"></i>
          <p>Joindre reçu ou preuve de paiement</p>
          <span>PDF, image (max 10 Mo)</span>
          <div id="mcFileName" style="margin-top:6px;font-size:12.5px;color:var(--brand);font-weight:600;display:none"></div>
        </div>

      </form>
    </div>

    <div class="modal-foot">
      <div class="mf-left">
        <button class="btn-outline-erp" onclick="closeModal('modalCotisation')">Annuler</button>
      </div>
      <div class="mf-right">
        <button
          type="button"
          class="btn-primary-erp"
          id="mcSubmitBtn"
          onclick="submitCotisation()"
        >
          <i class="fa-solid fa-circle-check"></i> Valider
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ── MODAL 2 — HISTORIQUE DES PAIEMENTS ─────────────────────── --}}
<div class="modal-overlay" id="modalViewHistorique">
  <div class="modal-panel" style="max-width:760px">
    <div class="modal-head">
      <div>
        <h3 class="mh-title">Détails & Historique</h3>
        <p class="mh-sub" id="mhSub">Chargement…</p>
      </div>
      <div class="mh-right">
        <button class="modal-close" onclick="closeModal('modalViewHistorique')">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>

    <div class="modal-body" id="mhBody">
      {{-- Contenu chargé dynamiquement --}}
      <div style="text-align:center;padding:40px;color:var(--text-4)">
        <i class="fa-solid fa-spinner fa-spin" style="font-size:24px;margin-bottom:8px;display:block"></i>
        Chargement de l'historique…
      </div>
    </div>

    <div class="modal-foot">
      <div class="mf-left"></div>
      <div class="mf-right">
        <button class="btn-outline-erp" onclick="closeModal('modalViewHistorique')">Fermer</button>
      </div>
    </div>
  </div>
</div>


@endsection

{{-- ══════════════════════════════════════════════════════════════
     JAVASCRIPT
     ══════════════════════════════════════════════════════════════ --}}
@push('scripts')
<script>
/* ── Config CSRF ────────────────────────────────────────────────── */
const CSRF = '{{ csrf_token() }}';
const BASE = '{{ url("/cotisations") }}';

/* ── Helpers ────────────────────────────────────────────────────── */
const fmt = n => Number(n).toLocaleString('fr-FR', {minimumFractionDigits:0}) + ' MAD';

function openModal(id)  { document.getElementById(id).classList.add('open');    document.body.style.overflow='hidden'; }
function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow=''; }

document.querySelectorAll('.modal-overlay').forEach(o => {
  o.addEventListener('click', e => { if (e.target === o) closeModal(o.id); });
});
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.open').forEach(m => closeModal(m.id));
});

/* ── Check all ───────────────────────────────────────────────────── */
document.getElementById('checkAll').addEventListener('change', function() {
  document.querySelectorAll('tbody .row-check').forEach(c => c.checked = this.checked);
});

/* ── Toast ───────────────────────────────────────────────────────── */
function toast(type, title, msg) {
  const icons = { s:'fa-circle-check', w:'fa-triangle-exclamation', e:'fa-circle-xmark' };
  const colors = { s:'var(--green-t)', w:'var(--orange-t)', e:'var(--red-t)' };
  const bgs    = { s:'var(--green-bg)', w:'var(--orange-bg)', e:'var(--red-bg)' };
  const el     = document.createElement('div');
  el.style.cssText = `position:fixed;top:22px;right:22px;z-index:9999;display:flex;align-items:center;gap:11px;padding:12px 16px;min-width:270px;background:#fff;border-radius:12px;box-shadow:0 10px 40px rgba(0,0,0,.12);border:1px solid var(--border-2);animation:toastIn .3s ease;font-family:var(--font-b)`;
  el.innerHTML = `
    <div style="width:32px;height:32px;border-radius:8px;flex-shrink:0;display:flex;align-items:center;justify-content:center;background:${bgs[type]};color:${colors[type]}"><i class="fa-solid ${icons[type]}"></i></div>
    <div><div style="font-family:var(--font-h);font-size:13px;font-weight:700;color:var(--text-1)">${title}</div><div style="font-size:12px;color:var(--text-3)">${msg}</div></div>`;
  document.body.appendChild(el);
  setTimeout(() => { el.style.opacity='0'; el.style.transition='opacity .3s'; setTimeout(()=>el.remove(), 300); }, 4000);
}

/* ═══════════════════════════════════════════════════════════════════
   MODAL ENCAISSER — Pré-remplissage dynamique
   ═══════════════════════════════════════════════════════════════════ */
function openEncaisser(aptId, nom, num, annuel, paye, statut, proprietaireId) {
  document.getElementById('mcAppartementId').value = aptId;
  // Stocker le user_id du propriétaire de cette cotisation (peut être l'ancien après transfert)
  document.getElementById('mcUserId').value = proprietaireId ?? '';
  document.getElementById('mcSub').textContent = `Enregistrer un paiement — Apt. ${num} · ${nom}`;

  // Sélectionner le bon appartement dans le select
  const sel = document.getElementById('mcSelectApt');
  for (let opt of sel.options) {
    if (opt.value == aptId) { opt.selected = true; break; }
  }

  // Mettre à jour le récap et les chips
  updateRecap(annuel, paye);
  openModal('modalCotisation');
}

/* Quand l'utilisateur change d'appartement dans le select */
function onAptChange(sel) {
  const opt = sel.options[sel.selectedIndex];
  if (!opt.value) return;

  document.getElementById('mcAppartementId').value = opt.value;
  // Mettre à jour user_id selon le propriétaire de cette cotisation
  document.getElementById('mcUserId').value = opt.dataset.proprietaireId ?? '';
  document.getElementById('mcSub').textContent = `Enregistrer un paiement — Apt. ${opt.dataset.num} · ${opt.dataset.nom}`;
  updateRecap(opt.dataset.annuel, opt.dataset.paye);
}

function updateRecap(annuel, paye) {
  const a = parseFloat(annuel) || 0;
  const p = parseFloat(paye)   || 0;
  const r = Math.max(0, a - p);

  document.getElementById('mcDue').textContent    = fmt(a);
  document.getElementById('mcAlready').textContent = fmt(p);
  document.getElementById('mcRest').textContent   = fmt(r);
  document.getElementById('mcRecap').style.display = 'grid';

  // Montant par défaut = reste
  const montantInput = document.getElementById('mcMontant');
  if (r > 0) montantInput.value = r;

  // Chips raccourcis
  buildChips(a, r);
}

function buildChips(annuel, reste) {
  const wrap = document.getElementById('mcChips');
  wrap.innerHTML = '';
  const list = [
    { l: `Solde complet (${fmt(reste)})`, v: reste },
    { l: `50% (${fmt(Math.round(reste/2))})`, v: Math.round(reste/2) },
    { l: fmt(annuel / 12) + '/mois', v: Math.round(annuel / 12) },
  ].filter(c => c.v > 0);

  list.forEach(c => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.style.cssText = 'padding:3px 9px;border-radius:20px;font-size:11px;font-weight:600;border:1.5px solid var(--border-2);background:#f8fafc;color:var(--text-2);cursor:pointer;font-family:var(--font-b);transition:var(--tr)';
    btn.textContent = c.l;
    btn.addEventListener('click', () => {
      document.getElementById('mcMontant').value = c.v;
      wrap.querySelectorAll('button').forEach(b => { b.style.background='#f8fafc'; b.style.color='var(--text-2)'; });
      btn.style.background = 'var(--brand)';
      btn.style.color = '#fff';
      btn.style.borderColor = 'var(--brand)';
    });
    wrap.appendChild(btn);
  });
}

/* Upload fichier */
function onFileSelect(input) {
  const el = document.getElementById('mcFileName');
  if (input.files.length > 0) {
    el.style.display = 'block';
    el.textContent = '📎 ' + input.files[0].name;
  } else {
    el.style.display = 'none';
  }
}

/* ═══════════════════════════════════════════════════════════════════
   SUBMIT PAIEMENT
   ═══════════════════════════════════════════════════════════════════ */
function submitCotisation() {
  const aptId   = document.getElementById('mcAppartementId').value;
  const montant = document.getElementById('mcMontant').value;
  const date    = document.getElementById('mcDate').value;

  if (!aptId)   { toast('e', 'Copropriétaire requis', 'Sélectionnez un appartement.'); return; }
  if (!montant) { toast('e', 'Montant requis', 'Saisissez un montant.'); return; }
  if (!date)    { toast('e', 'Date requise', 'Sélectionnez une date de paiement.'); return; }

  const btn = document.getElementById('mcSubmitBtn');
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Enregistrement…';
  btn.disabled  = true;

  const userId = document.getElementById('mcUserId').value;
  if (!userId) { toast('e', 'Propriétaire introuvable', 'Impossible d\'identifier le propriétaire de cette cotisation.'); return; }

  const formData = new FormData(document.getElementById('formCotisation'));
  formData.set('appartement_id', aptId);
  formData.set('user_id', userId); // propriétaire de la cotisation (ancien ou nouveau)

  fetch(`${BASE}/payer`, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    body: formData,
  })
  .then(res => res.json())
  .then(data => {
    btn.innerHTML = '<i class="fa-solid fa-circle-check"></i> Valider';
    btn.disabled  = false;

    if (data.success) {
      closeModal('modalCotisation');
      toast('s', 'Paiement enregistré ✓', data.message);
      // Recharger la page pour afficher les données mises à jour
      setTimeout(() => window.location.reload(), 1200);
    } else {
      toast('e', 'Erreur', data.message || 'Une erreur est survenue.');
    }
  })
  .catch(() => {
    btn.innerHTML = '<i class="fa-solid fa-circle-check"></i> Valider';
    btn.disabled  = false;
    toast('e', 'Erreur réseau', 'Impossible de contacter le serveur.');
  });
}

/* ═══════════════════════════════════════════════════════════════════
   HISTORIQUE — Chargement AJAX
   ═══════════════════════════════════════════════════════════════════ */
function openHistorique(aptId, annee) {
  document.getElementById('mhSub').textContent = 'Chargement…';
  document.getElementById('mhBody').innerHTML  = `
    <div style="text-align:center;padding:40px;color:var(--text-4)">
      <i class="fa-solid fa-spinner fa-spin" style="font-size:24px;margin-bottom:8px;display:block"></i>
      Chargement de l'historique…
    </div>`;
  openModal('modalViewHistorique');

  fetch(`${BASE}/historique/${aptId}?annee=${annee}`, {
    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
  })
  .then(r => r.json())
  .then(data => {
    if (!data.success) { toast('e', 'Erreur', data.message); return; }
    renderHistorique(data);
  })
  .catch(() => toast('e', 'Erreur réseau', 'Impossible de charger l\'historique.'));
}

function renderHistorique(data) {
  document.getElementById('mhSub').textContent = `Appartement ${data.numero_apt} · ${data.proprietaire}`;

  const pct   = data.montant_annuel > 0
    ? Math.min(100, Math.round(data.total_paye / data.montant_annuel * 100))
    : 0;
  const badgeCls = data.statut === 'payé' ? 'paid' : data.statut === 'partiel' ? 'partial' : 'late';

  let rows = '';
  if (data.transactions.length === 0) {
    rows = `<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-4)">Aucun paiement enregistré pour ${data.proprietaire} en ${document.querySelector('select[name=annee]')?.value || ''}.</td></tr>`;
  } else {
    data.transactions.forEach(t => {
      rows += `
        <tr>
          <td>${t.date_paiement}</td>
          <td><span style="font-family:var(--font-h);font-weight:700;color:var(--green-t)">${fmt(t.montant)}</span></td>
          <td><span class="s-badge active">${t.mode_paiement}</span></td>
          <td><span style="color:var(--text-4);font-size:13px">${t.reference || '—'}</span></td>
          <td style="font-size:12.5px;color:var(--text-3);max-width:140px;overflow:hidden;text-overflow:ellipsis">${t.commentaire || '—'}</td>
          <td>
            ${t.document_url
              ? `<a href="${t.document_url}" target="_blank" class="ra-btn download"><i class="fa-solid fa-file-pdf"></i></a>`
              : '<span style="color:var(--text-4);font-size:12px">—</span>'
            }
            <button class="ra-btn delete" onclick="supprimerTransaction(${t.id})" title="Supprimer">
              <i class="fa-solid fa-trash"></i>
            </button>
          </td>
        </tr>`;
    });
  }

  document.getElementById('mhBody').innerHTML = `
    {{-- Infos copropriétaire --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:20px">
      <div class="kpi-card" style="padding:14px;border:1px solid var(--border-2)">
        <div style="font-size:12px;color:var(--text-3);margin-bottom:4px">Copropriétaire</div>
        <div style="font-weight:700;font-size:15px;color:var(--text-1)">${data.proprietaire}</div>
        <div style="font-size:12px;color:var(--text-4);margin-top:3px">
          ${data.email ? '<i class="fa-solid fa-envelope" style="margin-right:4px"></i>' + data.email : ''}
          ${data.telephone ? ' · <i class="fa-solid fa-phone" style="margin-left:4px;margin-right:4px"></i>' + data.telephone : ''}
        </div>
      </div>
      <div class="kpi-card" style="padding:14px;border:1px solid var(--border-2)">
        <div style="font-size:12px;color:var(--text-3);margin-bottom:6px">
          Bilan — <strong>${data.montant_annuel > 0 ? fmt(data.montant_annuel) : '—'}</strong> annuel
        </div>
        <div style="display:flex;justify-content:space-between;align-items:flex-end">
          <div>
            <div style="font-size:11px;color:var(--text-4)">Payé</div>
            <div style="font-weight:700;color:var(--green-t)">${fmt(data.total_paye)}</div>
          </div>
          <div style="text-align:right">
            <div style="font-size:11px;color:var(--text-4)">Reste</div>
            <div style="font-weight:700;color:${data.reste > 0 ? 'var(--red-t)' : 'var(--text-4)'}">${fmt(data.reste)}</div>
          </div>
          
        </div>
        <div style="height:4px;background:#f1f5f9;border-radius:2px;margin-top:10px;overflow:hidden">
          <div style="height:100%;border-radius:2px;width:${pct}%;background:linear-gradient(90deg,var(--green),#4ade80);transition:width .8s ease"></div>
        </div>
      </div>
    </div>

    <div class="form-section-label" style="padding-top:0;border-top:none;margin-bottom:12px">
      <i class="fa-solid fa-clock-rotate-left" style="color:var(--brand)"></i>
      Historique détaillé des paiements (${data.transactions.length} transaction${data.transactions.length !== 1 ? 's' : ''})
    </div>

    <div class="table-responsive" style="border:1px solid var(--border-2);border-radius:8px">
      <table class="erp-table" style="margin:0;box-shadow:none">
        <thead>
          <tr>
            <th>Date</th>
            <th>Montant</th>
            <th>Mode</th>
            <th>Référence</th>
            <th>Commentaire</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>
    </div>`;
}

/* ═══════════════════════════════════════════════════════════════════
   SUPPRIMER TRANSACTION
   ═══════════════════════════════════════════════════════════════════ */
function supprimerTransaction(id) {
  if (!confirm('Supprimer ce paiement ? Cette action est irréversible.')) return;

  fetch(`${BASE}/transaction/${id}`, {
    method: 'DELETE',
    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      toast('s', 'Transaction supprimée', data.message);
      closeModal('modalViewHistorique');
      setTimeout(() => window.location.reload(), 1200);
    } else {
      toast('e', 'Erreur', data.message);
    }
  })
  .catch(() => toast('e', 'Erreur réseau', 'Impossible de supprimer.'));
}

/* ═══════════════════════════════════════════════════════════════════
   RELANCER
   ═══════════════════════════════════════════════════════════════════ */
function relancerProprietaire(aptId) {
  fetch(`${BASE}/relancer/${aptId}`, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) toast('s', 'Relance envoyée', data.message);
    else              toast('e', 'Erreur', data.message);
  })
  .catch(() => toast('e', 'Erreur réseau', 'Impossible d\'envoyer la relance.'));
}

function relancerTous() {
  const checked = [...document.querySelectorAll('tbody .row-check:checked')];
  const ids = checked.map(c => c.value).filter(Boolean);

  if (ids.length === 0) {
    toast('w', 'Aucune sélection', 'Cochez au moins un copropriétaire.');
    return;
  }

  if (!confirm(`Envoyer une relance à ${ids.length} copropriétaire(s) ?`)) return;

  Promise.all(ids.map(id =>
    fetch(`${BASE}/relancer/${id}`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    }).then(r => r.json())
  ))
  .then(results => {
    const ok = results.filter(r => r.success).length;
    toast('s', `${ok} relance(s) envoyée(s)`, `Sur ${ids.length} sélectionné(s).`);
  })
  .catch(() => toast('e', 'Erreur', 'Certaines relances ont échoué.'));
}

/* ── Animation CSS (inline) ─────────────────────────────────────── */
const style = document.createElement('style');
style.textContent = '@keyframes toastIn { from{opacity:0;transform:translateX(30px)}to{opacity:1;transform:none} }';
document.head.appendChild(style);
</script>
@endpush