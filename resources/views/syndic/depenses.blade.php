@extends('layouts.layout')

@section('title', 'Charges & Dépenses')
@section('page_title', 'Charges & Dépenses')

@section('content')
<div class="page-header">
  <div class="ph-left">
    <h2>Charges & Dépenses</h2>
    <p>Suivi des échéances — {{ $annee }}</p>
  </div>
  <div class="ph-right">
    <a href="{{ route('depenses.export.csv', array_merge(request()->query(), ['residence_id' => $residenceId, 'annee' => $annee])) }}"
       class="btn-outline-erp">
      <i class="fa-solid fa-download"></i> Export CSV
    </a>
    <button class="btn-outline-erp" onclick="openModal('mAddService')">
      <i class="fa-solid fa-bolt"></i> Service variable
    </button>
    <button class="btn-outline-erp" onclick="openModal('mAddCharge')">
      <i class="fa-solid fa-rotate"></i> Charge récurrente
    </button>
    <button class="btn-primary-erp" onclick="openModal('mAddDepense')">
      <i class="fa-solid fa-plus"></i> Dépense unique
    </button>
  </div>
</div>

{{-- ── Toasts Laravel (session flash) ──────────────────────────── --}}
@if(session('success'))
  <div id="flashSuccess" style="display:none">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div id="flashError" style="display:none">{{ session('error') }}</div>
@endif

{{-- ── KPIs ───────────────────────────────────────────────────── --}}
<div class="kpi-row">
  <div class="kpi-card kpi-red" style="animation-delay:.05s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">En Retard</div>
        <div class="kpi-value">{{ $kpis['enRetard'] }} <small>charges</small></div>
        <div class="kpi-sub">Échéance dépassée</div>
      </div>
      <div class="kpi-icon red"><i class="fa-solid fa-triangle-exclamation"></i></div>
    </div>
  </div>

  <div class="kpi-card kpi-orange" style="animation-delay:.10s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">À payer bientôt</div>
        <div class="kpi-value">{{ $kpis['bientot'] }} <small>charges</small></div>
        <div class="kpi-sub">Échéance dans 15 jours</div>
      </div>
      <div class="kpi-icon orange"><i class="fa-solid fa-hourglass-half"></i></div>
    </div>
  </div>

  <div class="kpi-card kpi-orange" style="animation-delay:.15s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Total attendu</div>
        <div class="kpi-value">{{ number_format($kpis['totalAttendu'], 0, ',', ' ') }} <small>MAD</small></div>
        <div class="kpi-sub">{{ $annee }}</div>
      </div>
      <div class="kpi-icon orange"><i class="fa-solid fa-file-invoice-dollar"></i></div>
    </div>
  </div>

  <div class="kpi-card kpi-green" style="animation-delay:.20s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Payées cet année</div>
        <div class="kpi-value">{{ number_format($kpis['payesMoisMontant'], 0, ',', ' ') }} <small>MAD</small></div>
        <div class="kpi-sub">{{ $kpis['payesMoisCount'] }} charges soldées</div>
      </div>
      <div class="kpi-icon green"><i class="fa-solid fa-circle-check"></i></div>
    </div>
  </div>
</div>

{{-- ── Alertes ─────────────────────────────────────────────────── --}}
@if(count($alertes) > 0)
<div class="alert-strip" id="alertStrip">
  <div class="alert-strip-header" onclick="toggleAlerts()">
    <div class="alert-strip-title">
      <i class="fa-solid fa-bell" style="color:var(--orange)"></i>
      Alertes <span id="alertCountLabel">({{ count($alertes) }})</span>
    </div>
    <button class="alert-strip-toggle" id="alertBtn"><i class="fa-solid fa-chevron-up"></i></button>
  </div>

  <div id="alertBody" class="alert-body">
    @foreach($alertes as $alerte)
        @php
            $typeClass = match($alerte['type']) {
                'danger' => 'late',
                'en_attente' => 'soon',
                default => $alerte['type'],
            };
        @endphp
        <div class="alert-row {{ $typeClass }}"><div class="alert-icon-sm"><i class="fa-solid {{ $alerte['icon'] }}"></i></div>
      <div>
        <strong>{{ $alerte['label'] }}</strong> — {{ $alerte['message'] }}
      </div>
      <button class="btn-outline-erp" style="font-size:11px;padding:4px 8px;margin-left:auto"
              onclick="openPayModal(
                  {{ $alerte['id'] }},
                  {{ $alerte['montant'] }},
                  '{{ addslashes($alerte['label']) }}',
                  '{{ $alerte['periode_debut'] }}',
                  '{{ $alerte['periode_fin'] }}',
                  '{{ $alerte['depense_type'] }}'
              )">
        Payer
      </button>
    </div>
    @endforeach
  </div>
</div>
@endif

{{-- ── Barre de filtres ─────────────────────────────────────────── --}}
<form method="GET" action="{{ route('depenses.index') }}" id="filterForm">
  <input type="hidden" name="residence_id" value="{{ $residenceId }}">
  <div class="filter-bar">
    <div class="filter-search">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="text" name="search" id="searchInput"
             placeholder="Charge, fournisseur, catégorie…"
             value="{{ $search }}"
             onchange="document.getElementById('filterForm').submit()">
    </div>

    <select class="filter-select" name="annee" onchange="this.form.submit()">
      @foreach($annees as $a)
        <option value="{{ $a }}" @selected($a == $annee)>{{ $a }}</option>
      @endforeach
      {{-- Ajouter l'année courante si non présente --}}
      @if(!$annees->contains(now()->year))
        <option value="{{ now()->year }}" @selected(now()->year == $annee)>{{ now()->year }}</option>
      @endif
    </select>

    <select class="filter-select" name="mois" onchange="this.form.submit()">
      <option value="">Tous les mois</option>
      @foreach(range(1, 12) as $m)
        <option value="{{ $m }}" @selected($m == $mois)>
          {{ \Carbon\Carbon::create()->month($m)->locale('fr')->monthName }}
        </option>
      @endforeach
    </select>

    <select class="filter-select" name="statut" onchange="this.form.submit()">
      <option value="">Tous statuts</option>
      <option value="paye"       @selected($statut === 'paye')>Payé</option>
      <option value="en_attente" @selected($statut === 'en_attente')>En attente</option>
      <option value="en_retard"  @selected($statut === 'en_retard')>En retard</option>
    </select>

    <select class="filter-select" name="type" onchange="this.form.submit()">
      <option value="">Tous types</option>
      <option value="mensuel"      @selected($type === 'mensuel')>Mensuel</option>
      <option value="trimestriel"  @selected($type === 'trimestriel')>Trimestriel</option>
      <option value="unique"       @selected($type === 'unique')>Dépense unique</option>
      <option value="variable"     @selected($type === 'variable')>Service variable</option>
    </select>

    <span style="font-size:12.5px;color:var(--text-3);white-space:nowrap" class="d-none d-md-block">
      <strong>{{ count($paiements) }}</strong> échéances
    </span>
  </div>
</form>

{{-- ── Tableau ─────────────────────────────────────────────────── --}}
<div class="table-card">
  
  <div class="table-responsive">
    <table class="erp-table" id="chargesTable">
      <thead>
        <tr>
          <th><input type="checkbox" id="checkAll" style="accent-color:var(--brand);cursor:pointer"></th>
          <th>Charge / Dépense</th>
          <th>Fournisseur</th>
          <th>Catégorie</th>
          <th>Type</th>
          <th>Période</th>
          <th>Montant Dû</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($paiements as $p)
        @php
          $d   = $p->depense;
          $cfg = [
            'maintenance' => ['bg' => 'var(--blue-bg)',   'color' => 'var(--blue-t)',   'icon' => 'fa-wrench'],
            'securite'    => ['bg' => 'var(--orange-bg)', 'color' => 'var(--orange-t)', 'icon' => 'fa-shield-halved'],
            'travaux'     => ['bg' => 'var(--red-bg)',    'color' => 'var(--red-t)',    'icon' => 'fa-hard-hat'],
            'energie'     => ['bg' => 'var(--amber-bg)',  'color' => 'var(--amber-t)',  'icon' => 'fa-bolt'],
            'nettoyage'   => ['bg' => 'var(--teal-bg)',   'color' => 'var(--teal-t)',   'icon' => 'fa-broom'],
            'admin'       => ['bg' => 'var(--gray-bg)',   'color' => 'var(--gray-t)',   'icon' => 'fa-file-invoice'],
          ][$d->categorie] ?? ['bg'=>'#eee','color'=>'#666','icon'=>'fa-tag'];

          $statutCfg = [
            'paye'       => ['cls' => 'paid',    'icon' => 'fa-circle-check',        'label' => 'Payé'],
            'en_attente' => ['cls' => 'pending', 'icon' => 'fa-hourglass-half',      'label' => 'En attente'],
            'en_retard'  => ['cls' => 'late',    'icon' => 'fa-triangle-exclamation','label' => 'En retard'],
          ][$p->statut] ?? ['cls' => 'pending', 'icon' => 'fa-circle', 'label' => $p->statut];

          $typeLabel = [
            'mensuel'     => ['icon' => 'fa-rotate',           'label' => 'Mensuel'],
            'trimestriel' => ['icon' => 'fa-calendar-days',    'label' => 'Trimestriel'],
            'unique'      => ['icon' => 'fa-receipt',          'label' => 'Unique'],
            'variable'    => ['icon' => 'fa-bolt',             'label' => 'Variable'],
          ][$d->type] ?? ['icon' => 'fa-tag', 'label' => $d->type];
        @endphp
        <tr data-paiement-id="{{ $p->id }}" data-depense-id="{{ $d->id }}">
          <td><input type="checkbox" class="row-chk" style="accent-color:var(--brand)"></td>

          <td>
            <div style="font-weight:600;font-size:13.5px">{{ $d->titre }}</div>
            @if($d->description)
              <div style="font-size:11.5px;color:var(--text-3);margin-top:2px">{{ Str::limit($d->description, 60) }}</div>
            @endif
            @if($p->documents->count() > 0)
              <span title="Justificatif joint" style="color:var(--teal-t);font-size:11px;margin-top:2px;display:inline-block">
                <i class="fa-solid fa-paperclip"></i> {{ $p->documents->count() }} doc.
              </span>
            @endif
          </td>

          <td style="font-size:12.5px;color:var(--text-3)">{{ $d->fournisseur ?? '—' }}</td>

          <td>
            <span class="cat-badge"
                  style="background:{{ $cfg['bg'] }};color:{{ $cfg['color'] }}">
              <i class="fa-solid {{ $cfg['icon'] }}"></i>
              {{ ucfirst($d->categorie) }}
            </span>
          </td>

          <td>
            <span class="type-badge">
              <i class="fa-solid {{ $typeLabel['icon'] }}"></i>
              {{ $typeLabel['label'] }}
            </span>
          </td>

          <td style="font-size:12px">
            <div>{{ $p->periode_debut->format('d/m/Y') }}</div>
            <div style="color:var(--text-3)">→ {{ $p->periode_fin->format('d/m/Y') }}</div>
          </td>

          <td style="font-weight:700">
            @if($d->type === 'variable' && $p->montant == 0)
              <span style="color:var(--text-4);font-size:12px;font-style:italic">Facture manquante</span>
            @else
              @if($d->type === 'unique')

                  {{ number_format($p->montant_paye,2,',',' ') }}
                  / 
                  {{ number_format($p->montant,2,',',' ') }}
                  <small style="color:var(--text-3)">MAD</small>

                  <div style="font-size:11px;color:var(--orange-t)">
                      Reste :
                      {{ number_format($p->montant - $p->montant_paye,2,',',' ') }}
                      MAD
                  </div>

              @else

                  {{ number_format($p->montant,2,',',' ') }}
                  <small style="color:var(--text-3)">MAD</small>

              @endif
            @endif
          </td>

          <td>
            <span class="s-badge {{ $statutCfg['cls'] }}">
              <i class="fa-solid {{ $statutCfg['icon'] }}"></i>
              {{ $statutCfg['label'] }}
            </span>
            @if($p->date_paiement)
              <div style="font-size:11px;color:var(--text-4);margin-top:3px">
                {{ $p->date_paiement->format('d/m/Y') }}
              </div>
            @endif
          </td>

          <td>
            <div style="display:flex;gap:5px;align-items:center;flex-wrap:nowrap">
              @if($p->statut !== 'paye')
                <button class="btn-primary-erp pay"
                        onclick="openPayModal({{ $p->id }}, {{ $p->montant }}, '{{ addslashes($d->titre) }}', '{{ $p->periode_debut->format('d/m/Y') }}', '{{ $p->periode_fin->format('d/m/Y') }}', '{{ $d->type }}')"
                        title="Payer">
                  <i class="fa-solid fa-circle-check"></i>
                </button>
              @endif
              @if($p->statut !== 'paye')
              <button class="ra-btn edit"
                      onclick="openEditModal({{ $d->id }}, '{{ addslashes($d->titre) }}', '{{ addslashes($d->fournisseur ?? '') }}', '{{ $d->categorie }}', '{{ $d->type }}', {{ $d->montant ?? 'null' }}, '{{ $d->date_debut->format('Y-m-d') }}', '{{ optional($d->date_fin)->format('Y-m-d') }}')"
                      title="Modifier">
                <i class="fa-solid fa-pen"></i>
              </button>
              @endif
              <form method="POST"
                    action="{{ route('depenses.destroy', $d->id) }}"
                    onsubmit="return confirm('Archiver cette dépense ?')"
                    style="margin:0">
                @csrf @method('DELETE')
                <button type="submit" class="ra-btn delete"  style="color:var(--orange-t)"title="Archiver">
                  <i class="fa-solid fa-box-archive"></i>
                </button>
              </form>
              @if($p->statut === 'paye' && $p->documents->count() > 0)
                  <a href="{{ asset('storage/'.$p->documents->first()->chemin_stockage) }}"
                    target="_blank"
                    class="ra-btn"
                    title="Voir justificatif">

                      <i class="fa-solid fa-eye"></i>
                  </a>
              @endif
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="9" style="text-align:center;padding:40px;color:var(--text-4)">
            <i class="fa-solid fa-inbox" style="font-size:28px;margin-bottom:8px;display:block"></i>
            Aucune dépense pour ces filtres.
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="table-footer">
    <span style="font-size:12px;color:var(--text-4)">
      <strong>{{ count($paiements) }}</strong> résultats
    </span>
  </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
  MODAL 1 — PAYER UNE ÉCHÉANCE
══════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="mPay">
  <div class="modal-panel">
    <div class="modal-head">
      <div>
        <h3 class="modal-title" id="mpTitle">Payer la dépense</h3>
        <p class="modal-sub" id="mpSub">—</p>
      </div>
      <button class="modal-close" onclick="closeModal('mPay')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="POST" id="payForm" enctype="multipart/form-data">
      @csrf
      <div class="modal-body">
        <div class="info-box orange" id="mpInfoBox">
          <i class="fa-solid fa-info-circle"></i>
          <p id="mpInfoText">—</p>
        </div>
        <div class="pay-recap">
          <div class="pr-col">
            <div class="pr-lbl">Montant Dû</div>
            <div class="pr-val due" id="mpDue">—</div>
          </div>
          <div class="pr-col">
            <div class="pr-lbl">Période</div>
            <div class="pr-val" id="mpPeriode" style="font-size:12px;color:var(--text-3)">—</div>
          </div>
        </div>

        <div class="form-section-lbl"><i class="fa-solid fa-coins"></i> Paiement</div>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-lbl">Montant payé <span class="req">*</span></label>
            <div class="pfx-wrap">
              <span class="pfx">MAD</span>
              <input type="number" class="form-ctrl" name="montant" id="mpMontant"
                     min="0" step="0.01" placeholder="0.00" required oninput="updatePayLive()">
            </div>
          </div>
          <div class="form-group">
            <label class="form-lbl">Date de paiement <span class="req">*</span></label>
            <input type="date" class="form-ctrl" name="date_paiement" id="mpDate"
                   max="{{ now()->format('Y-m-d') }}" required>
          </div>
        </div>

        {{-- Barre de progression --}}
        <div class="pay-live" id="payLiveBox">
          <div class="pl-head">
            <span class="pl-lbl">Progression</span>
            <span class="pl-pct" id="plPct">0%</span>
          </div>
          <div class="pl-bar"><div class="pl-fill" id="plFill" style="width:0%"></div></div>
        </div>

        <div class="form-section-lbl" style="margin-top:4px">
          <i class="fa-solid fa-wallet"></i> Mode & Référence
        </div>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-lbl">Mode <span class="req">*</span></label>
            <select class="form-ctrl" name="mode_paiement" required>
              <option value="virement">Virement bancaire</option>
              <option value="cheque">Chèque</option>
              <option value="especes">Espèces</option>
              <option value="carte">Carte bancaire</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-lbl">Référence</label>
            <input type="text" class="form-ctrl" name="reference" placeholder="Nº chèque / virement">
          </div>
        </div>

        <div class="form-group">
          <label class="form-lbl">Commentaire</label>
          <textarea class="form-ctrl" name="commentaire" rows="2" placeholder="Notes…"></textarea>
        </div>

        <div class="form-section-lbl"><i class="fa-solid fa-paperclip"></i> Justificatif</div>
        <div class="upload-zone-sm" onclick="document.getElementById('mpFile').click()">
          <input type="file" id="mpFile" name="document" accept=".pdf,.jpg,.jpeg,.png"
                 onchange="showFileName(this)">
          <i class="fa-solid fa-cloud-arrow-up"></i>
          <p id="mpFileName">Joindre facture ou reçu</p>
          <span>PDF, image — <b>max 10 Mo</b></span>
        </div>

        {{-- Champ caché pour le montant variable --}}
        <div class="form-group" id="variableMontantGroup" style="display:none">
          <div class="info-box teal">
            <i class="fa-solid fa-bolt"></i>
            <p><strong>Service variable :</strong> saisissez le montant réel de cette facture.</p>
          </div>
        </div>
      </div>
      <div class="modal-foot">
        <div class="mf-l"><button type="button" class="btn-outline-erp" onclick="closeModal('mPay')">Annuler</button></div>
        <div class="mf-r">
          <button type="submit" class="btn-primary-erp">
            <i class="fa-solid fa-circle-check"></i> Valider le paiement
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
  MODAL 2 — MODIFIER CHARGE
══════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="mEdit">
  <div class="modal-panel modal-lg">
    <div class="modal-head">
      <div>
        <h3 class="modal-title" id="meTitle">Modifier la charge</h3>
        <p class="modal-sub">Les échéances payées sont protégées — seules les futures seront recalculées</p>
      </div>
      <button class="modal-close" onclick="closeModal('mEdit')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="POST" id="editForm">
      @csrf @method('PUT')
      <div class="modal-body">
        <div class="info-box orange">
          <i class="fa-solid fa-shield-halved"></i>
          <p><strong>Protection des données :</strong> les échéances déjà payées ne seront jamais touchées. Seules les échéances futures non payées seront recalculées.</p>
        </div>
        <div class="form-section-lbl"><i class="fa-solid fa-info-circle"></i> Informations modifiables</div>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-lbl">Nom de la charge <span class="req">*</span></label>
            <input type="text" class="form-ctrl" name="titre" id="meNom" required>
          </div>
          <div class="form-group">
            <label class="form-lbl">Fournisseur</label>
            <input type="text" class="form-ctrl" name="fournisseur" id="meFournisseur">
          </div>
          <div class="form-group">
            <label class="form-lbl">Catégorie</label>
            <select class="form-ctrl" name="categorie" id="meCat">
              <option value="maintenance">Maintenance</option>
              <option value="securite">Sécurité</option>
              <option value="travaux">Travaux</option>
              <option value="energie">Énergie</option>
              <option value="nettoyage">Nettoyage</option>
              <option value="admin">Administration</option>
            </select>
          </div>
        </div>

        <div class="form-section-lbl"><i class="fa-solid fa-calendar-alt"></i> Période</div>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-lbl">Date début <span class="req">*</span></label>
            <input type="date" class="form-ctrl" name="date_debut" id="meDateDebut" required>
          </div>
          <div class="form-group">
            <label class="form-lbl">Date fin</label>
            <input type="date" class="form-ctrl" name="date_fin" id="meDateFin">
          </div>
        </div>

        <div class="form-section-lbl"><i class="fa-solid fa-coins"></i> Montant</div>
        <div id="meMontantSection">
          <div class="form-grid">
            <div class="form-group">
              <label class="form-lbl" id="meMontantLabel">Nouveau montant fixe (MAD) <span class="req">*</span></label>
              <div class="pfx-wrap">
                <span class="pfx">MAD</span>
                <input type="number" class="form-ctrl" name="montant" id="meMontant" placeholder="0.00">
              </div>
              <div class="form-hint" id="meMontantHint"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-foot">
        <div class="mf-l"><button type="button" class="btn-outline-erp" onclick="closeModal('mEdit')">Annuler</button></div>
        <div class="mf-r">
          <button type="submit" class="btn-primary-erp">
            <i class="fa-solid fa-floppy-disk"></i> Sauvegarder
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
  MODAL 3 — AJOUTER DÉPENSE UNIQUE
══════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="mAddDepense">
  <div class="modal-panel">
    <div class="modal-head">
      <div>
        <h3 class="modal-title">Nouvelle Dépense Unique</h3>
        <p class="modal-sub">Facture ponctuelle — montant libre</p>
      </div>
      <button class="modal-close" onclick="closeModal('mAddDepense')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="POST" action="{{ route('depenses.store') }}">
      @csrf
      <input type="hidden" name="residence_id" value="{{ $residenceId }}">
      <input type="hidden" name="type" value="unique">
      <div class="modal-body">
        <div class="info-box blue">
          <i class="fa-solid fa-circle-info"></i>
          <p>Une <strong>dépense unique</strong> génère une seule ligne d'échéance. Idéal pour les factures imprévues ou travaux ponctuels.</p>
        </div>
        <div class="form-grid">
          <div class="form-group form-full">
            <label class="form-lbl">Titre <span class="req">*</span></label>
            <input type="text" class="form-ctrl" name="titre" placeholder="ex: Réparation toiture" required>
          </div>
          <div class="form-group">
            <label class="form-lbl">Catégorie <span class="req">*</span></label>
            <select class="form-ctrl" name="categorie" required>
              <option value="travaux">Travaux</option>
              <option value="maintenance">Maintenance</option>
              <option value="securite">Sécurité</option>
              <option value="energie">Énergie</option>
              <option value="nettoyage">Nettoyage</option>
              <option value="admin">Administration</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-lbl">Fournisseur</label>
            <input type="text" class="form-ctrl" name="fournisseur" placeholder="Nom du prestataire">
          </div>
          <div class="form-group">
            <label class="form-lbl">Montant (MAD) <span class="req">*</span></label>
            <div class="pfx-wrap">
              <span class="pfx">MAD</span>
              <input type="number" class="form-ctrl" name="montant" placeholder="0.00" min="0" step="0.01" required>
            </div>
          </div>
          <div class="form-group">
            <label class="form-lbl">Date d'échéance <span class="req">*</span></label>
            <input type="date" class="form-ctrl" name="date_debut" required>
          </div>
          <div class="form-group form-full">
            <label class="form-lbl">Description</label>
            <textarea class="form-ctrl" rows="2" name="description" style="resize:vertical" placeholder="Détails…"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-foot">
        <div class="mf-l"><button type="button" class="btn-outline-erp" onclick="closeModal('mAddDepense')">Annuler</button></div>
        <div class="mf-r">
          <button type="submit" class="btn-primary-erp"><i class="fa-solid fa-plus"></i> Créer</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
  MODAL 4 — AJOUTER CHARGE RÉCURRENTE
══════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="mAddCharge">
  <div class="modal-panel">
    <div class="modal-head">
      <div>
        <h3 class="modal-title">Nouvelle Charge Récurrente</h3>
        <p class="modal-sub">Mensuelle ou trimestrielle — montant fixe, échéances auto-générées</p>
      </div>
      <button class="modal-close" onclick="closeModal('mAddCharge')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="POST" action="{{ route('depenses.store') }}" id="formAddCharge">
      @csrf
      <input type="hidden" name="residence_id" value="{{ $residenceId }}">
      <div class="modal-body">
        <div class="info-box orange">
          <i class="fa-solid fa-lock"></i>
          <p>Le montant est <strong>fixe</strong> et non modifiable au paiement. Utilisez <em>Service variable</em> si le montant varie.</p>
        </div>
        <div class="form-grid">
          <div class="form-group form-full">
            <label class="form-lbl">Nom <span class="req">*</span></label>
            <input type="text" class="form-ctrl" name="titre" placeholder="ex: Gardiennage mensuel" required>
          </div>
          <div class="form-group">
            <label class="form-lbl">Catégorie <span class="req">*</span></label>
            <select class="form-ctrl" name="categorie" required>
              <option value="securite">Sécurité</option>
              <option value="maintenance">Maintenance</option>
              <option value="nettoyage">Nettoyage</option>
              <option value="admin">Administration</option>
              <option value="energie">Énergie</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-lbl">Fournisseur</label>
            <input type="text" class="form-ctrl" name="fournisseur" placeholder="SecurGuard, CleanPro…">
          </div>
          <div class="form-group">
            <label class="form-lbl">Fréquence <span class="req">*</span></label>
            <select class="form-ctrl" name="type" id="acFreq" onchange="toggleChargeFreq(this.value)" required>
              <option value="mensuel">Mensuelle</option>
              <option value="trimestriel">Trimestrielle</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-lbl">Montant fixe (MAD) <span class="req">*</span></label>
            <div class="pfx-wrap">
              <span class="pfx">MAD</span>
              <input type="number" class="form-ctrl" name="montant" placeholder="0.00" min="0" step="0.01" required>
            </div>
          </div>
          <div class="form-group">
            <label class="form-lbl">Date début <span class="req">*</span></label>
            <input type="date" class="form-ctrl" name="date_debut" id="acDateDebut" required onchange="validateChargeDate()">
          </div>
          <div class="form-group">
            <label class="form-lbl">Date fin</label>
            <input type="date" class="form-ctrl" name="date_fin" id="acDateFin" onchange="validateChargeDate()">
            <div class="form-hint" id="acDateHint"></div>
          </div>
          <div class="form-group form-full">
            <label class="form-lbl">Description</label>
            <textarea class="form-ctrl" rows="2" name="description" placeholder="Détails du contrat…"></textarea>
          </div>
        </div>
        {{-- Prévisualisation des périodes --}}
        <div id="acPreview" style="display:none;background:#f8fafc;border:1px solid var(--border);border-radius:var(--r-md);padding:11px 13px;margin-top:8px">
          <div style="font-size:11px;font-weight:700;color:var(--text-4);text-transform:uppercase;margin-bottom:8px">
            <i class="fa-solid fa-calendar-alt" style="color:var(--brand);margin-right:5px"></i>
            Aperçu des échéances générées
          </div>
          <div id="acPreviewList" style="font-size:12px;color:var(--text-3);display:flex;flex-wrap:wrap;gap:6px"></div>
        </div>
      </div>
      <div class="modal-foot">
        <div class="mf-l"><button type="button" class="btn-outline-erp" onclick="closeModal('mAddCharge')">Annuler</button></div>
        <div class="mf-r">
          <button type="submit" class="btn-primary-erp">
            <i class="fa-solid fa-rotate"></i> Créer & générer les échéances
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
  MODAL 5 — SERVICE VARIABLE
══════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="mAddService">
  <div class="modal-panel">
    <div class="modal-head">
      <div>
        <h3 class="modal-title">Nouveau Service Variable</h3>
        <p class="modal-sub">Eau, électricité — montant réel saisi à chaque facturation</p>
      </div>
      <button class="modal-close" onclick="closeModal('mAddService')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="POST" action="{{ route('depenses.store') }}">
      @csrf
      <input type="hidden" name="residence_id" value="{{ $residenceId }}">
      <input type="hidden" name="type" value="variable">
      <div class="modal-body">
        <div class="info-box teal">
          <i class="fa-solid fa-lightbulb"></i>
          <p>
            <strong>Service à montant variable (eau, électricité) :</strong><br>
            Les périodes sont créées automatiquement chaque mois. Lorsque vous recevez la facture, vous saisissez le <strong>montant réel</strong> et payez depuis le tableau.
          </p>
        </div>
        <div class="form-grid">
          <div class="form-group form-full">
            <label class="form-lbl">Nom du service <span class="req">*</span></label>
            <input type="text" class="form-ctrl" name="titre" placeholder="ex: Facture SRM eau" required>
          </div>
          <div class="form-group">
            <label class="form-lbl">Fournisseur</label>
            <input type="text" class="form-ctrl" name="fournisseur" placeholder="SRM, ONEE, Amendis…">
          </div>
          <div class="form-group">
            <label class="form-lbl">Catégorie</label>
            <select class="form-ctrl" name="categorie">
              <option value="energie">Énergie</option>
              <option value="maintenance">Maintenance</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-lbl">Date début <span class="req">*</span></label>
            <input type="date" class="form-ctrl" name="date_debut" required>
          </div>
          <div class="form-group">
            <label class="form-lbl">Date fin</label>
            <input type="date" class="form-ctrl" name="date_fin">
          </div>
          <div class="form-group form-full">
            <label class="form-lbl">Description</label>
            <textarea class="form-ctrl" rows="2" name="description" placeholder="Détails…"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-foot">
        <div class="mf-l"><button type="button" class="btn-outline-erp" onclick="closeModal('mAddService')">Annuler</button></div>
        <div class="mf-r">
          <button type="submit" class="btn-primary-erp">
            <i class="fa-solid fa-bolt"></i> Créer le service
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
/* ══════════════════════════════════════════════════════════════════
   DÉPENSES — JS dynamique (sans données statiques)
   ══════════════════════════════════════════════════════════════════ */

// ── Flash messages Laravel ─────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
  const ok  = document.getElementById('flashSuccess');
  const err = document.getElementById('flashError');
  if (ok)  showToast('s', 'Succès', ok.textContent.trim());
  if (err) showToast('e', 'Erreur', err.textContent.trim());
});

// ── Payer une échéance ─────────────────────────────────────────────
function openPayModal(paiementId, montant, titre, periodeDebut, periodeFin, type) {
    const form = document.getElementById('payForm');
    form.action = `/depenses/paiements/${paiementId}/payer`;

    document.getElementById('mpTitle').textContent =
        `Payer — ${titre}`;

    document.getElementById('mpSub').textContent =
        `Période : ${periodeDebut} → ${periodeFin}`;

    document.getElementById('mpDue').textContent =
        montant > 0
        ? `${parseFloat(montant).toLocaleString('fr-FR',{minimumFractionDigits:2})} MAD`
        : '—';
    document.getElementById('mpDue').dataset.amount = montant || 0;

    document.getElementById('mpPeriode').textContent =
        `${periodeDebut} → ${periodeFin}`;

    const variableGroup = document.getElementById('variableMontantGroup');
    const montantInput = document.getElementById('mpMontant');

    // Charges récurrentes fixes
    if (type === 'mensuel' || type === 'trimestriel') {

        variableGroup.style.display = 'none';

        montantInput.value = montant || '';
        montantInput.readOnly = true;

        montantInput.style.background = '#f3f4f6';
        montantInput.style.cursor = 'not-allowed';

    // Service variable
    } else if (type === 'variable' && (!montant || montant == 0)) {

        variableGroup.style.display = '';

        montantInput.value = '';
        montantInput.readOnly = false;

        montantInput.style.background = '';
        montantInput.style.cursor = '';

    // Dépense unique → libre (paiement partiel)
    } else {

        variableGroup.style.display = 'none';

        montantInput.value = montant || '';
        montantInput.readOnly = false;

        montantInput.style.background = '';
        montantInput.style.cursor = '';
    }

    if(type === 'unique'){

    const reste = montant - montantPaye;

    const input = document.getElementById('mpMontant');

    input.max = reste;
    }
    
    document.getElementById('mpDate').value =
        new Date().toISOString().slice(0,10);

    updatePayLive();

    openModal('mPay');
}

function updatePayLive() {

    const montantDu = parseFloat(
        document.getElementById('mpDue').dataset.amount
    ) || 0;

    const montantPay = parseFloat(
        document.getElementById('mpMontant').value
    ) || 0;

    const pct = montantDu > 0
        ? Math.min(
            100,
            Math.round((montantPay / montantDu) * 100)
          )
        : 0;

    document.getElementById('plPct').textContent = `${pct}%`;
    document.getElementById('plFill').style.width = `${pct}%`;
}

// ── Modifier charge ────────────────────────────────────────────────
function openEditModal(id, titre, fournisseur, categorie, type, montant, dateDebut, dateFin) {
  const form     = document.getElementById('editForm');
  form.action    = `/depenses/${id}`;

  document.getElementById('meTitle').textContent        = `Modifier — ${titre}`;
  document.getElementById('meNom').value                = titre;
  document.getElementById('meFournisseur').value        = fournisseur;
  document.getElementById('meCat').value                = categorie;
  document.getElementById('meDateDebut').value          = dateDebut;
  document.getElementById('meDateFin').value            = dateFin || '';

  const montantInp  = document.getElementById('meMontant');
  const montantHint = document.getElementById('meMontantHint');
  const montantLbl  = document.getElementById('meMontantLabel');

  if (type === 'variable') {
    montantInp.disabled     = true;
    montantInp.style.opacity = '.5';
    montantInp.value        = '';
    montantLbl.textContent  = 'Montant (non applicable)';
    montantHint.innerHTML   = '<i class="fa-solid fa-bolt" style="color:var(--teal-t)"></i> Service variable : le montant est saisi à chaque facturation.';
  } else {
    montantInp.disabled      = false;
    montantInp.style.opacity = '1';
    montantInp.value         = montant ?? '';
    montantLbl.innerHTML     = `Nouveau montant fixe (MAD) <span class="req">*</span>`;
    montantHint.innerHTML    = '';
  }

  openModal('mEdit');
}

// ── Charge récurrente — validation dates ───────────────────────────
function validateChargeDate() {
  const debut  = document.getElementById('acDateDebut').value;
  const fin    = document.getElementById('acDateFin').value;
  const freq   = document.getElementById('acFreq').value;
  const hint   = document.getElementById('acDateHint');

  if (!debut) return;

  const dDebut = new Date(debut);

  if (fin) {
    const dFin = new Date(fin);
    if (dFin < dDebut) {
      hint.innerHTML = '<span style="color:var(--red-t)"><i class="fa-solid fa-triangle-exclamation"></i> La date de fin ne peut pas être avant la date de début.</span>';
      document.getElementById('acDateFin').value = '';
      return;
    }
    // Vérification 12 mois pour mensuel
    if (freq === 'mensuel') {
      const diffMois = (dFin.getFullYear() - dDebut.getFullYear()) * 12 + (dFin.getMonth() - dDebut.getMonth());
      if (diffMois > 12) {
        hint.innerHTML = '<span style="color:var(--orange-t)"><i class="fa-solid fa-info-circle"></i> Période limitée à 12 mois pour les charges mensuelles.</span>';
        // Calcul automatique de la date max
        const maxFin = new Date(dDebut);
        maxFin.setMonth(maxFin.getMonth() + 12);
        maxFin.setDate(0); // Fin du mois -1 = fin du 12ème mois
        document.getElementById('acDateFin').max = maxFin.toISOString().slice(0,10);
      } else {
        hint.innerHTML = '';
        document.getElementById('acDateFin').max = '';
      }
    }
    // Prévisualisation des périodes
    genererPreview(dDebut, dFin, freq);
  } else {
    document.getElementById('acPreview').style.display = 'none';
  }
}

function toggleChargeFreq(freq) {
  validateChargeDate();
}

function genererPreview(debut, fin, freq) {
  const preview = document.getElementById('acPreview');
  const list    = document.getElementById('acPreviewList');
  list.innerHTML = '';

  const periodes = [];
  const months   = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];

  if (freq === 'mensuel') {
    let cur = new Date(debut.getFullYear(), debut.getMonth(), 1);
    const finMonth = new Date(fin.getFullYear(), fin.getMonth(), 1);
    while (cur <= finMonth && periodes.length < 24) {
      periodes.push(`${months[cur.getMonth()]} ${cur.getFullYear()}`);
      cur.setMonth(cur.getMonth() + 1);
    }
  } else if (freq === 'trimestriel') {
    const trimLabels = ['T1','T2','T3','T4'];
    const trimMois   = [[0,2],[3,5],[6,8],[9,11]];
    for (let y = debut.getFullYear(); y <= fin.getFullYear(); y++) {
      trimMois.forEach(([m1, m2], i) => {
        const deb = new Date(y, m1, 1);
        const f   = new Date(y, m2+1, 0);
        if (f >= debut && deb <= fin) {
          periodes.push(`${trimLabels[i]} ${y}`);
        }
      });
    }
  }

  periodes.forEach(p => {
    const span = document.createElement('span');
    span.style.cssText = 'background:var(--brand-bg,#eff6ff);color:var(--brand);padding:3px 8px;border-radius:4px;font-size:11.5px;font-weight:600';
    span.textContent = p;
    list.appendChild(span);
  });

  preview.style.display = periodes.length > 0 ? '' : 'none';
}

// ── Alertes toggle ────────────────────────────────────────────────
let alertsOpen = true;
function toggleAlerts() {
  alertsOpen = !alertsOpen;
  const body = document.getElementById('alertBody');
  const btn  = document.getElementById('alertBtn');
  body.style.display = alertsOpen ? '' : 'none';
  btn.innerHTML = alertsOpen ? '<i class="fa-solid fa-chevron-up"></i>' : '<i class="fa-solid fa-chevron-down"></i>';
}

// ── Helpers modal ─────────────────────────────────────────────────
function openModal(id)  { document.getElementById(id).classList.add('open');    document.body.style.overflow = 'hidden'; }
function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow = ''; }

document.querySelectorAll('.modal-overlay').forEach(o =>
  o.addEventListener('click', e => { if (e.target === o) closeModal(o.id); })
);
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.open').forEach(m => closeModal(m.id));
});

// ── Checkbox sélection totale ─────────────────────────────────────
document.getElementById('checkAll')?.addEventListener('change', function () {
  document.querySelectorAll('#chargesTable .row-chk').forEach(c => c.checked = this.checked);
});

// ── Sidebar ───────────────────────────────────────────────────────
document.getElementById('sidebarOpen')?.addEventListener('click', () => {
  document.getElementById('sidebar').classList.add('open');
  document.getElementById('sidebarOverlay').classList.add('active');
});
['sidebarClose', 'sidebarOverlay'].forEach(id =>
  document.getElementById(id)?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('active');
  })
);

// ── Toast ─────────────────────────────────────────────────────────
function showToast(t, title, msg) {
  const cfg = { s: { cls: 's', i: 'fa-circle-check' }, w: { cls: 'w', i: 'fa-triangle-exclamation' }, e: { cls: 'e', i: 'fa-circle-xmark' } };
  const c   = cfg[t] || cfg.s;
  const el  = document.createElement('div');
  el.className = 'toast-item';
  el.innerHTML = `<div class="toast-icon-wrap ${c.cls}"><i class="fa-solid ${c.i}"></i></div><div class="toast-body"><div class="toast-title">${title}</div><div class="toast-msg">${msg}</div></div><button class="toast-x" onclick="rmToast(this.closest('.toast-item'))"><i class="fa-solid fa-xmark"></i></button>`;
  document.getElementById('toastStack').appendChild(el);
  setTimeout(() => rmToast(el), 4500);
}
function rmToast(el) { if (!el || !el.parentNode) return; el.classList.add('out'); setTimeout(() => el.remove(), 280); }

// ── Erreurs de validation Laravel ─────────────────────────────────
@if($errors->any())
document.addEventListener('DOMContentLoaded', function () {
  @foreach($errors->all() as $error)
    showToast('e', 'Validation', '{{ addslashes($error) }}');
  @endforeach
});
@endif
</script>
@endpush
@endsection