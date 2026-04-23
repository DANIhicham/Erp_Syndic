@extends('layouts.layout')

@section('title', 'Paiements loyers')
@section('page_title', 'Paiements_loyers')

@section('content')

<!-- ══ PAGE CONTENT ══ -->
  <div class="erp-content">

    <!-- Page header -->
    <div class="page-header">
      <div class="ph-left">
        <p>Encaissez les loyers, suivez les retards et gérez les paiements partiels en temps réel.</p>
      </div>
      <br>
      <div class="ph-right">
        <button class="btn-outline-erp"><i class="fa-solid fa-download"></i> Exporter</button>
        <button class="btn-outline-erp"><i class="fa-solid fa-file-invoice"></i> Générer quittances</button>
      </div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-4 mb-4">

      {{-- Card 1: Total Encaissé --}}
          <div class="col-12 col-sm-6 col-xl-3">
              <div class="kpi-card kpi-green">
                  <div class="kpi-body">
                      <div class="kpi-info">
                          <span class="kpi-label">Total Encaissé</span>
                          <h3 class="kpi-value">148 500 <span class="kpi-unit">MAD</span></h3>
                          <div class="kpi-trend up">
                              Ce mois — Avril 2026
                          </div>
                      </div>
                      <div class="kpi-icon-wrap green">
                          <i class="fa-solid fa-circle-check"></i>
                      </div>
                  </div>
              </div>
          </div>


      {{-- Card 2: En Attente --}}
          <div class="col-12 col-sm-6 col-xl-3">
              <div class="kpi-card kpi-orange">
                  <div class="kpi-body">
                      <div class="kpi-info">
                          <span class="kpi-label">En Attente</span>
                          <h3 class="kpi-value">32 400 <span class="kpi-unit">MAD</span></h3>
                          <div class="kpi-trend norange">
                              5 locataires concernés
                          </div>
                      </div>
                      <div class="kpi-icon-wrap orange">
                          <i class="fa-solid fa-hourglass-half"></i>
                      </div>
                  </div>
              </div>
          </div>

      {{-- Card 3: Paiements Partiels --}}
          <div class="col-12 col-sm-6 col-xl-3">
              <div class="kpi-card kpi-orange">
                  <div class="kpi-body">
                      <div class="kpi-info">
                          <span class="kpi-label">Paiements Partiels</span>
                          <h3 class="kpi-value">3 <span class="kpi-unit">dossiers</span></h3>
                          <div class="kpi-trend norange">
                              8 650 MAD de restes à payer
                          </div>
                      </div>
                      <div class="kpi-icon-wrap orange">
                          <i class="fa-solid fa-code-branch"></i>
                      </div>
                  </div>
              </div>
          </div>

      {{-- Card 4: Paiements Partiels --}}
          <div class="col-12 col-sm-6 col-xl-3">
              <div class="kpi-card kpi-red">
                  <div class="kpi-body">
                      <div class="kpi-info">
                          <span class="kpi-label">En Retard</span>
                          <h3 class="kpi-value">3 <span class="kpi-unit">dossiers</span></h3>
                          <div class="kpi-trend down">
                              14 750 MAD impayés
                          </div>
                      </div>
                      <div class="kpi-icon-wrap red">
                          <i class="fa-solid fa-triangle-exclamation"></i>
                      </div>
                  </div>
              </div>
          </div>


    </div><!-- /kpi-grid -->

    {{-- ════════════════════════ FILTER BAR ════════════════════════ --}}
<div class="filter-bar">
  <div class="filter-search">
    <i class="fa-solid fa-magnifying-glass"></i>
    <input type="text" id="searchInput" placeholder="Rechercher par locataire, appartement…" oninput="filterPay()">
  </div>

  <select class="filter-select" id="fMois" onchange="filterPay()">
    <option value="">Tous les mois</option>
    <option value="Janvier">Janvier 2026</option>
    <option value="Février">Février 2026</option>
    <option value="Mars">Mars 2026</option>
    <option value="Avril" selected>Avril 2026</option>
  </select>

  <select class="filter-select" id="fStatut" onchange="filterPay()">
    <option value="">Tous les statuts</option>
    <option value="paid">Payé</option>
    <option value="pending">En attente</option>
    <option value="partial">Partiel</option>
    <option value="late">En retard</option>
  </select>

  <select class="filter-select" id="fResidence" onchange="filterPay()">
    <option value="">Toutes les résidences</option>
    <option value="Atlas">Résidence Atlas</option>
    <option value="Palmeraie">Résidence Palmeraie</option>
    <option value="Majorelle">Résidence Majorelle</option>
  </select>

  <div class="filter-divider d-none d-md-block"></div>
  <div class="filter-count d-none d-md-block"><strong id="rowCount">10</strong> paiements</div>
</div>

{{-- ════════════════════════ TABLE ════════════════════════════ --}}
<div class="table-card">
  <div class="table-card-header">
    <div class="tch-left">
      <h5>Paiements — Avril 2026</h5>
      <p>Cliquez sur <strong>Encaisser</strong> pour enregistrer un loyer. Lignes rouges = action requise.</p>
    </div>
    <div class="tch-right">
      <button class="btn-outline-erp" style="font-size:12px;padding:7px 12px;">
        <i class="fa-solid fa-paper-plane"></i> Relancer tous
      </button>
    </div>
  </div>

  <div class="table-responsive">
    <table class="erp-table" id="payTable">
      <thead>
        <tr>
          <th><input type="checkbox" class="row-check" id="checkAll"></th>
          <th>Période <i class="fa-solid fa-sort"></i></th>
          <th>Locataire / Unité</th>
          <th>Montant Dû <i class="fa-solid fa-sort"></i></th>
          <th>Montant Payé <i class="fa-solid fa-sort"></i></th>
          <th>Reste à Payer <i class="fa-solid fa-sort"></i></th>
          <th>Statut <i class="fa-solid fa-sort"></i></th>
          <th>Échéance</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="tableBody">

        {{-- ── Ligne 1 : Payé complet ── --}}
        <tr data-statut="paid" data-residence="Atlas" data-mois="Avril">
          <td><input type="checkbox" class="row-check"></td>
          <td>
            {{-- pay-period --}}
            <div class="pay-period">
              <span class="pay-period-month">Avril</span>
              <span class="pay-period-year">2026</span>
            </div>
          </td>
          <td>
            <div class="tenant-cell">
              <div class="t-avatar" style="background:#4f46e5">KB</div>
              <div>
                <span class="t-name">Karim Benali</span>
                <span class="t-type">Apt. 05 · Résidence Atlas</span>
              </div>
            </div>
          </td>
          <td><span class="amount-main">3 800 MAD</span></td>
          <td>
            {{-- amount-paid, pay-mini-bar --}}
            <span class="amount-paid">3 800 MAD</span>
            <div class="pay-mini-bar"><div class="pay-mini-fill full" style="width:100%"></div></div>
          </td>
          <td><span class="amount-rest zero">— MAD</span></td>
          <td><span class="s-badge paid">Payé</span></td>
          <td style="font-size:12.5px;color:var(--text-4)">01/04/2026</td>
          <td>
            <div class="row-actions">
              <button class="ra-btn view"     title="Voir détail"><i class="fa-solid fa-eye"></i></button>
              <button class="ra-btn download" title="Télécharger reçu"><i class="fa-solid fa-file-pdf"></i></button>
            </div>
          </td>
        </tr>

        {{-- ── Ligne 2 : En retard ── --}}
        <tr data-statut="late" data-residence="Atlas" data-mois="Avril" class="tr-late">
          <td><input type="checkbox" class="row-check"></td>
          <td><div class="pay-period"><span class="pay-period-month">Avril</span><span class="pay-period-year">2026</span></div></td>
          <td>
            <div class="tenant-cell">
              <div class="t-avatar" style="background:#0891b2">SO</div>
              <div>
                <span class="t-name">Sara Ouali</span>
                <span class="t-type">Apt. 12B · Résidence Atlas</span>
              </div>
            </div>
          </td>
          <td><span class="amount-main">4 500 MAD</span></td>
          <td>
            <span class="amount-paid" style="color:var(--text-4)">0 MAD</span>
            <div class="pay-mini-bar"><div class="pay-mini-fill none"></div></div>
          </td>
          <td><span class="amount-rest">4 500 MAD</span></td>
          <td><span class="s-badge late">En retard</span></td>
          <td style="font-size:12px;color:var(--red-t);font-weight:600"><i class="fa-solid fa-clock"></i> +15 jours</td>
          <td>
            <div class="row-actions">
              {{-- ra-btn.encaisser --}}
              <button class="ra-btn encaisser" onclick="openEncModal({id:2,name:'Sara Ouali',unit:'Apt. 12B · Résidence Atlas',due:4500,paid:0,color:'#0891b2',init:'SO',month:'Avril 2026'})">
                <i class="fa-solid fa-cash-register"></i> Encaisser
              </button>
              <button class="ra-btn remind" title="Relancer"><i class="fa-solid fa-paper-plane"></i></button>
            </div>
          </td>
        </tr>

        {{-- ── Ligne 3 : Payé ── --}}
        <tr data-statut="paid" data-residence="Palmeraie" data-mois="Avril">
          <td><input type="checkbox" class="row-check"></td>
          <td><div class="pay-period"><span class="pay-period-month">Avril</span><span class="pay-period-year">2026</span></div></td>
          <td>
            <div class="tenant-cell">
              <div class="t-avatar" style="background:#059669">YM</div>
              <div><span class="t-name">Youssef Mouhib</span><span class="t-type">Apt. 03 · Résidence Palmeraie</span></div>
            </div>
          </td>
          <td><span class="amount-main">2 900 MAD</span></td>
          <td>
            <span class="amount-paid">2 900 MAD</span>
            <div class="pay-mini-bar"><div class="pay-mini-fill full" style="width:100%"></div></div>
          </td>
          <td><span class="amount-rest zero">— MAD</span></td>
          <td><span class="s-badge paid">Payé</span></td>
          <td style="font-size:12.5px;color:var(--text-4)">01/04/2026</td>
          <td>
            <div class="row-actions">
              <button class="ra-btn view"     title="Voir"><i class="fa-solid fa-eye"></i></button>
              <button class="ra-btn download" title="Reçu"><i class="fa-solid fa-file-pdf"></i></button>
            </div>
          </td>
        </tr>

        {{-- ── Ligne 4 : Partiel ── --}}
        {{-- tr-partial --}}
        <tr data-statut="partial" data-residence="Majorelle" data-mois="Avril" class="tr-partial">
          <td><input type="checkbox" class="row-check"></td>
          <td><div class="pay-period"><span class="pay-period-month">Avril</span><span class="pay-period-year">2026</span></div></td>
          <td>
            <div class="tenant-cell">
              <div class="t-avatar" style="background:#d97706">LK</div>
              <div><span class="t-name">Leila Khadiri</span><span class="t-type">Apt. 18 · Résidence Majorelle</span></div>
            </div>
          </td>
          <td><span class="amount-main">5 200 MAD</span></td>
          <td>
            <span class="amount-paid">3 000 MAD</span>
            <div class="pay-mini-bar"><div class="pay-mini-fill partial" style="width:58%"></div></div>
          </td>
          <td><span class="amount-rest">2 200 MAD</span></td>
          <td><span class="s-badge partial">Partiel</span></td>
          <td style="font-size:12px;color:var(--amber-t);font-weight:600">05/04/2026</td>
          <td>
            <div class="row-actions">
              <button class="ra-btn encaisser" onclick="openEncModal({id:4,name:'Leila Khadiri',unit:'Apt. 18 · Résidence Majorelle',due:5200,paid:3000,color:'#d97706',init:'LK',month:'Avril 2026'})">
                <i class="fa-solid fa-cash-register"></i> Encaisser
              </button>
              <button class="ra-btn view" title="Voir"><i class="fa-solid fa-eye"></i></button>
            </div>
          </td>
        </tr>

        {{-- ── Ligne 5 : Payé ── --}}
        <tr data-statut="paid" data-residence="Atlas" data-mois="Avril">
          <td><input type="checkbox" class="row-check"></td>
          <td><div class="pay-period"><span class="pay-period-month">Avril</span><span class="pay-period-year">2026</span></div></td>
          <td>
            <div class="tenant-cell">
              <div class="t-avatar" style="background:#7c3aed">RA</div>
              <div><span class="t-name">Rachid Alami</span><span class="t-type">Apt. 07 · Résidence Atlas</span></div>
            </div>
          </td>
          <td><span class="amount-main">3 200 MAD</span></td>
          <td>
            <span class="amount-paid">3 200 MAD</span>
            <div class="pay-mini-bar"><div class="pay-mini-fill full" style="width:100%"></div></div>
          </td>
          <td><span class="amount-rest zero">— MAD</span></td>
          <td><span class="s-badge paid">Payé</span></td>
          <td style="font-size:12.5px;color:var(--text-4)">01/04/2026</td>
          <td>
            <div class="row-actions">
              <button class="ra-btn view"     title="Voir"><i class="fa-solid fa-eye"></i></button>
              <button class="ra-btn download" title="Reçu"><i class="fa-solid fa-file-pdf"></i></button>
            </div>
          </td>
        </tr>

        {{-- ── Ligne 6 : En retard ── --}}
        <tr data-statut="late" data-residence="Palmeraie" data-mois="Avril" class="tr-late">
          <td><input type="checkbox" class="row-check"></td>
          <td><div class="pay-period"><span class="pay-period-month">Avril</span><span class="pay-period-year">2026</span></div></td>
          <td>
            <div class="tenant-cell">
              <div class="t-avatar" style="background:#db2777">NB</div>
              <div><span class="t-name">Nadia Berrada</span><span class="t-type">Apt. 22 · Résidence Palmeraie</span></div>
            </div>
          </td>
          <td><span class="amount-main">6 100 MAD</span></td>
          <td>
            <span class="amount-paid" style="color:var(--text-4)">0 MAD</span>
            <div class="pay-mini-bar"><div class="pay-mini-fill none"></div></div>
          </td>
          <td><span class="amount-rest">6 100 MAD</span></td>
          <td><span class="s-badge late">En retard</span></td>
          <td style="font-size:12px;color:var(--red-t);font-weight:600"><i class="fa-solid fa-clock"></i> +8 jours</td>
          <td>
            <div class="row-actions">
              <button class="ra-btn encaisser" onclick="openEncModal({id:6,name:'Nadia Berrada',unit:'Apt. 22 · Résidence Palmeraie',due:6100,paid:0,color:'#db2777',init:'NB',month:'Avril 2026'})">
                <i class="fa-solid fa-cash-register"></i> Encaisser
              </button>
              <button class="ra-btn remind" title="Relancer"><i class="fa-solid fa-paper-plane"></i></button>
            </div>
          </td>
        </tr>

        {{-- ── Ligne 7 : En attente ── --}}
        <tr data-statut="pending" data-residence="Atlas" data-mois="Avril">
          <td><input type="checkbox" class="row-check"></td>
          <td><div class="pay-period"><span class="pay-period-month">Avril</span><span class="pay-period-year">2026</span></div></td>
          <td>
            <div class="tenant-cell">
              <div class="t-avatar" style="background:#0f766e">HZ</div>
              <div><span class="t-name">Hassan Ziani</span><span class="t-type">Apt. 09 · Résidence Atlas</span></div>
            </div>
          </td>
          <td><span class="amount-main">4 100 MAD</span></td>
          <td>
            <span class="amount-paid" style="color:var(--text-4)">0 MAD</span>
            <div class="pay-mini-bar"><div class="pay-mini-fill none"></div></div>
          </td>
          <td><span class="amount-rest">4 100 MAD</span></td>
          <td><span class="s-badge pending">En attente</span></td>
          <td style="font-size:12.5px;color:var(--text-3)">05/04/2026</td>
          <td>
            <div class="row-actions">
              <button class="ra-btn encaisser" onclick="openEncModal({id:7,name:'Hassan Ziani',unit:'Apt. 09 · Résidence Atlas',due:4100,paid:0,color:'#0f766e',init:'HZ',month:'Avril 2026'})">
                <i class="fa-solid fa-cash-register"></i> Encaisser
              </button>
              <button class="ra-btn remind" title="Relancer"><i class="fa-solid fa-paper-plane"></i></button>
            </div>
          </td>
        </tr>

        {{-- ── Ligne 8 : Partiel ── --}}
        <tr data-statut="partial" data-residence="Majorelle" data-mois="Avril" class="tr-partial">
          <td><input type="checkbox" class="row-check"></td>
          <td><div class="pay-period"><span class="pay-period-month">Avril</span><span class="pay-period-year">2026</span></div></td>
          <td>
            <div class="tenant-cell">
              <div class="t-avatar" style="background:#6d28d9">OM</div>
              <div><span class="t-name">Omar Mansouri</span><span class="t-type">Apt. 11 · Résidence Majorelle</span></div>
            </div>
          </td>
          <td><span class="amount-main">3 600 MAD</span></td>
          <td>
            <span class="amount-paid">1 500 MAD</span>
            <div class="pay-mini-bar"><div class="pay-mini-fill partial" style="width:42%"></div></div>
          </td>
          <td><span class="amount-rest">2 100 MAD</span></td>
          <td><span class="s-badge partial">Partiel</span></td>
          <td style="font-size:12px;color:var(--amber-t);font-weight:600">03/04/2026</td>
          <td>
            <div class="row-actions">
              <button class="ra-btn encaisser" onclick="openEncModal({id:8,name:'Omar Mansouri',unit:'Apt. 11 · Résidence Majorelle',due:3600,paid:1500,color:'#6d28d9',init:'OM',month:'Avril 2026'})">
                <i class="fa-solid fa-cash-register"></i> Encaisser
              </button>
              <button class="ra-btn view" title="Voir"><i class="fa-solid fa-eye"></i></button>
            </div>
          </td>
        </tr>

        {{-- ── Ligne 9 : Payé ── --}}
        <tr data-statut="paid" data-residence="Palmeraie" data-mois="Avril">
          <td><input type="checkbox" class="row-check"></td>
          <td><div class="pay-period"><span class="pay-period-month">Avril</span><span class="pay-period-year">2026</span></div></td>
          <td>
            <div class="tenant-cell">
              <div class="t-avatar" style="background:#b45309">FT</div>
              <div><span class="t-name">Fatima Tahiri</span><span class="t-type">Apt. 14A · Résidence Palmeraie</span></div>
            </div>
          </td>
          <td><span class="amount-main">4 800 MAD</span></td>
          <td>
            <span class="amount-paid">4 800 MAD</span>
            <div class="pay-mini-bar"><div class="pay-mini-fill full" style="width:100%"></div></div>
          </td>
          <td><span class="amount-rest zero">— MAD</span></td>
          <td><span class="s-badge paid">Payé</span></td>
          <td style="font-size:12.5px;color:var(--text-4)">01/04/2026</td>
          <td>
            <div class="row-actions">
              <button class="ra-btn view"     title="Voir"><i class="fa-solid fa-eye"></i></button>
              <button class="ra-btn download" title="Reçu"><i class="fa-solid fa-file-pdf"></i></button>
            </div>
          </td>
        </tr>

        {{-- ── Ligne 10 : En retard ── --}}
        <tr data-statut="late" data-residence="Majorelle" data-mois="Avril" class="tr-late">
          <td><input type="checkbox" class="row-check"></td>
          <td><div class="pay-period"><span class="pay-period-month">Avril</span><span class="pay-period-year">2026</span></div></td>
          <td>
            <div class="tenant-cell">
              <div class="t-avatar" style="background:#0c4a6e">AK</div>
              <div><span class="t-name">Amine Kettani</span><span class="t-type">Local C2 · Résidence Majorelle</span></div>
            </div>
          </td>
          <td><span class="amount-main">4 150 MAD</span></td>
          <td>
            <span class="amount-paid" style="color:var(--text-4)">0 MAD</span>
            <div class="pay-mini-bar"><div class="pay-mini-fill none"></div></div>
          </td>
          <td><span class="amount-rest">4 150 MAD</span></td>
          <td><span class="s-badge late">En retard</span></td>
          <td style="font-size:12px;color:var(--red-t);font-weight:600"><i class="fa-solid fa-clock"></i> +22 jours</td>
          <td>
            <div class="row-actions">
              <button class="ra-btn encaisser" onclick="openEncModal({id:10,name:'Amine Kettani',unit:'Local C2 · Résidence Majorelle',due:4150,paid:0,color:'#0c4a6e',init:'AK',month:'Avril 2026'})">
                <i class="fa-solid fa-cash-register"></i> Encaisser
              </button>
              <button class="ra-btn remind" title="Relancer"><i class="fa-solid fa-paper-plane"></i></button>
            </div>
          </td>
        </tr>

      </tbody>
    </table>
  </div>

  <div class="table-footer">
    <span class="tf-info">Affichage <strong>1–10</strong> sur <strong>18</strong> paiements</span>
    <div class="tf-pagination">
      <button class="page-btn" disabled><i class="fa-solid fa-chevron-left"></i></button>
      <button class="page-btn active">1</button>
      <button class="page-btn">2</button>
      <button class="page-btn"><i class="fa-solid fa-chevron-right"></i></button>
    </div>
  </div>
</div>


<div class="toast-stack" id="toastStack"></div>

<div class="modal-overlay" id="encModal" role="dialog" aria-modal="true" aria-labelledby="encModalTitle">

  <div class="modal-panel enc-panel" id="encModalPanel">

    <div class="modal-head">
      <div class="mh-left" style="display:flex;align-items:center;gap:14px;">
        <div class="enc-head-avatar" id="encAvatar">KB</div>
        <div>
          <div style="margin-bottom:6px">
            <span class="mh-ref-code" id="encMonth">Avril 2026</span>
          </div>
          <h3 class="mh-title" id="encModalTitle">Encaisser — Locataire</h3>
          <p class="mh-sub" id="encUnit">Appartement · Résidence</p>
        </div>
      </div>
      <div class="mh-right">
        <button class="modal-close" onclick="closeEncModal()" aria-label="Fermer"><i class="fa-solid fa-xmark"></i></button>
      </div>
    </div>

    {{-- ── Bandeau synthèse ── --}}
    <div class="enc-summary">
      <div class="enc-summary-item">
        <div class="ib-label">Montant Dû</div>
        <div class="ib-value" id="encDue">—</div>
      </div>
      <div class="enc-summary-item">
        <div class="ib-label">Déjà Encaissé</div>
        <div class="ib-value green" id="encAlreadyPaid">0 MAD</div>
      </div>
      <div class="enc-summary-item">
        <div class="ib-label">Reste à Payer</div>
        <div class="ib-value orange" id="encRest">—</div>
      </div>
    </div>

    {{-- ── Corps — réutilise modal-body ── --}}
    <div class="modal-body">

      {{-- ── Section Montant ── --}}
      <div class="form-section-label"><i class="fa-solid fa-coins"></i> Montant du paiement</div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">
            Montant reçu <span style="color:var(--red)">*</span>
          </label>
          {{-- input-prefix-wrap --}}
          <div class="input-prefix-wrap">
            <span class="input-prefix">MAD</span>
            <input type="number" class="form-control-erp" id="encMontantInput"
                   min="0" step="0.01" placeholder="0.00"
                   oninput="onMontantInput()">
          </div>
          {{-- quick-chips, q-chip --}}
          <div class="quick-chips" id="quickChips"></div>
        </div>

        <div class="form-group">
          <label class="form-label">Date de paiement <span style="color:var(--red)">*</span></label>
          <input type="date" class="form-control-erp" id="encDateInput">
        </div>
      </div>

      {{-- pay-preview : aperçu live --}}
      <div class="pay-preview" id="payPreview">
        <div class="pp-header">
          <span class="ib-label" style="margin:0">Progression</span>
          {{-- pp-pct --}}
          <span class="pp-pct" id="ppPct">0%</span>
        </div>
        {{-- pp-bar, pp-fill --}}
        <div class="pp-bar"><div class="pp-fill" id="ppFill" style="width:0%"></div></div>
        {{-- pp-rest-row --}}
        <div class="pp-rest-row" style="margin-top:8px;">
          <span class="ib-label" style="margin:0">Reste après ce paiement</span>
          <span class="ib-value orange" id="ppRest">—</span>
        </div>
      </div>

      {{-- ── Section Mode de paiement ── --}}
      <div class="form-section-label" style="margin-top:20px;"><i class="fa-solid fa-wallet"></i> Mode de paiement</div>

      <div class="mode-selector">
        <label class="mode-card selected" onclick="selectMode(this,'especes')">
          <input type="radio" name="encMode" class="mode-radio" value="especes" checked>
          {{-- clause-icon --}}
          <div class="clause-icon blue"><i class="fa-solid fa-money-bills"></i></div>
          <span class="mode-card-label">Espèces</span>
        </label>
        <label class="mode-card" onclick="selectMode(this,'virement')">
          <input type="radio" name="encMode" class="mode-radio" value="virement">
          <div class="clause-icon blue"><i class="fa-solid fa-building-columns"></i></div>
          <span class="mode-card-label">Virement</span>
        </label>
        <label class="mode-card" onclick="selectMode(this,'cheque')">
          <input type="radio" name="encMode" class="mode-radio" value="cheque">
          <div class="clause-icon blue"><i class="fa-solid fa-money-check"></i></div>
          <span class="mode-card-label">Chèque</span>
        </label>
      </div>

      {{-- ── Référence + Commentaire  ── --}}
      <div class="form-grid" style="margin-top:14px;">
        <div class="form-group">
          <label class="form-label">Référence
            <small style="font-size:11px;color:var(--text-4)">(Nº chèque ou transaction)</small>
          </label>
          <input type="text" class="form-control-erp" id="encRefInput"
                 placeholder="ex: CHQ-00345 ou TRX-ABC">
        </div>
        <div class="form-group">
          <label class="form-label">Commentaire
            <small style="font-size:11px;color:var(--text-4)">(optionnel)</small>
          </label>
          <input type="text" class="form-control-erp" id="encCommentInput"
                 placeholder="ex: Paiement anticipé…">
        </div>
      </div>

      {{-- ── Section Document justificatif ── --}}
      <div class="form-section-label" style="margin-top:20px;"><i class="fa-solid fa-paperclip"></i> Justificatif / Reçu</div>

      {{-- upload-drop --}}
      <div class="upload-drop" id="uploadDrop"
           onclick="document.getElementById('encFileInput').click()"
           ondragover="event.preventDefault();this.classList.add('dragover')"
           ondragleave="this.classList.remove('dragover')"
           ondrop="onFileDrop(event)">
        <i class="fa-solid fa-cloud-arrow-up"></i>
        <p>Glissez votre fichier ici</p>
        <span>ou <b>parcourez</b> — PDF, JPG, PNG (max 10 Mo)</span>
        <input type="file" id="encFileInput" accept=".pdf,.jpg,.jpeg,.png,.webp"
               multiple onchange="onFileChange(event)">
      </div>

      {{-- upload-list --}}
      <div class="upload-list" id="uploadList"></div>

    </div>{{-- /modal-body --}}

    {{-- ── Pied de modal  ── --}}
    <div class="modal-foot">
      <div class="mf-left">
        <button class="btn-outline-erp" onclick="closeEncModal()">Annuler</button>
      </div>
      <div class="mf-right">
        <button class="btn-outline-erp" id="btnDraft">
          <i class="fa-regular fa-floppy-disk"></i> Brouillon
        </button>
        {{-- btn-submit-enc --}}
        <button class="btn-submit-enc" id="btnSubmit" onclick="submitEnc()">
          <span class="btn-text-enc"><i class="fa-solid fa-circle-check"></i> Valider l'encaissement</span>
        </button>
      </div>
    </div>

  </div>{{-- /enc-panel --}}
</div>{{-- /modal-overlay --}}



@push('scripts')
<script>
/* ══════════════════════════════════════════════════════════════
   PAIEMENTS LOYER — Script JS
   ══════════════════════════════════════════════════════════════ */

/* ── Sidebar mobile (identique contrats.js) ─────────────────── */
document.getElementById('sidebarOpen')?.addEventListener('click', () => {
  document.getElementById('sidebar').classList.add('open');
  document.getElementById('sidebarOverlay').classList.add('active');
});
['sidebarClose', 'sidebarOverlay'].forEach(id => {
  document.getElementById(id)?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('active');
  });
});

/* ── Checkbox tout sélectionner (identique contrats.js) ─────── */
document.getElementById('checkAll')?.addEventListener('change', function () {
  document.querySelectorAll('#tableBody .row-check').forEach(c => c.checked = this.checked);
});

/* ── Filtres tableau (identique contrats.js) ────────────────── */
function filterPay() {
  const q   = document.getElementById('searchInput').value.trim().toLowerCase();
  const st  = document.getElementById('fStatut').value;
  const mo  = document.getElementById('fMois').value;
  const re  = document.getElementById('fResidence').value;
  let n = 0;

  document.querySelectorAll('#tableBody tr').forEach(tr => {
    const ok = (!q  || tr.textContent.toLowerCase().includes(q))
            && (!st || tr.dataset.statut    === st)
            && (!mo || tr.dataset.mois      === mo)
            && (!re || tr.dataset.residence === re);
    tr.style.display = ok ? '' : 'none';
    if (ok) n++;
  });

  document.getElementById('rowCount').textContent = n;
}

/* ════════════════════════════════════════════════════════════
   MODAL ENCAISSEMENT
   ════════════════════════════════════════════════════════════ */

/* État courant */
let _enc = { due: 0, paid: 0 };

/* ── Ouvrir (réutilise openModal / closeModal de contrats.js) ── */
function openEncModal(data) {
  if (!data) data = { id: null, name: '—', unit: '—', due: 0, paid: 0,
                      color: '#6366f1', init: '?', month: 'Avril 2026' };

  _enc = { due: data.due, paid: data.paid };
  const rest = data.due - data.paid;

  /* Header */
  const av = document.getElementById('encAvatar');
  av.textContent = data.init;
  av.style.background = data.color;
  document.getElementById('encModalTitle').textContent = 'Encaisser — ' + data.name;
  document.getElementById('encUnit').textContent  = data.unit;
  document.getElementById('encMonth').textContent = data.month;

  /* Bandeau synthèse */
  document.getElementById('encDue').textContent         = fmt(data.due);
  document.getElementById('encAlreadyPaid').textContent = fmt(data.paid);
  document.getElementById('encRest').textContent        = rest > 0 ? fmt(rest) : '— (soldé)';

  /* Date = aujourd'hui */
  document.getElementById('encDateInput').value = new Date().toISOString().slice(0, 10);

  /* Pré-rempli avec le reste */
  const inp = document.getElementById('encMontantInput');
  inp.value = rest > 0 ? rest : '';
  inp.max   = rest;

  /* Chips */
  buildChips(rest);
  onMontantInput();

  /* Reset mode */
  document.querySelectorAll('.mode-card').forEach(c => c.classList.remove('selected'));
  document.querySelector('.mode-card').classList.add('selected');
  resetModeRef();

  /* Reset upload */
  document.getElementById('uploadList').innerHTML = '';
  document.getElementById('encFileInput').value  = '';

  /* Reset champs */
  document.getElementById('encRefInput').value     = '';
  document.getElementById('encCommentInput').value = '';

  /* Ouvre (même pattern que contrats.js) */
  document.getElementById('encModal').classList.add('open');
  document.body.style.overflow = 'hidden';
  document.getElementById('encModalPanel').scrollTop = 0;
}

function closeEncModal() {
  document.getElementById('encModal').classList.remove('open');
  document.body.style.overflow = '';
}

/* Fermer sur click overlay (identique contrats.js) */
document.getElementById('encModal').addEventListener('click', function (e) {
  if (e.target === this) closeEncModal();
});
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeEncModal();
});

/* ── Chips de raccourcis ─────────────────────────────────────── */
function buildChips(rest) {
  const wrap = document.getElementById('quickChips');
  wrap.innerHTML = '';
  const list = [];
  if (rest > 0) list.push({ l: `Montant exact (${fmt(rest)})`, v: rest });
  if (rest > 0) list.push({ l: `50% (${fmt(Math.round(rest/2))})`, v: Math.round(rest/2) });
  [1000, 2000, 5000].forEach(v => list.push({ l: fmt(v), v }));

  list.filter(c => c.v > 0).forEach(c => {
    const btn = document.createElement('button');
    btn.type      = 'button';
    btn.className = 'q-chip';
    btn.textContent = c.l;
    btn.addEventListener('click', () => {
      document.getElementById('encMontantInput').value = c.v;
      wrap.querySelectorAll('.q-chip').forEach(x => x.classList.remove('active'));
      btn.classList.add('active');
      onMontantInput();
    });
    wrap.appendChild(btn);
  });
}

/* ── Aperçu live ─────────────────────────────────────────────── */
function onMontantInput() {
  const m       = parseFloat(document.getElementById('encMontantInput').value) || 0;
  const total   = _enc.paid + m;
  const rest    = Math.max(0, _enc.due - total);
  const pct     = _enc.due > 0 ? Math.min(100, Math.round(total / _enc.due * 100)) : 0;
  const fill    = document.getElementById('ppFill');

  document.getElementById('ppPct').textContent  = pct + '%';
  fill.style.width = pct + '%';

  /* Couleur dynamique */
  if (pct >= 100) fill.style.background = 'linear-gradient(90deg,var(--green),#4ade80)';
  else if (pct >= 50) fill.style.background = 'linear-gradient(90deg,var(--amber),#fbbf24)';
  else fill.style.background = 'linear-gradient(90deg,var(--red),#f87171)';

  const restEl = document.getElementById('ppRest');
  restEl.textContent = rest > 0 ? fmt(rest) : '✓ Soldé';
  restEl.className   = rest > 0 ? 'ib-value orange' : 'ib-value green';
}

/* ── Sélecteur mode paiement ─────────────────────────────────── */
function selectMode(el, val) {
  document.querySelectorAll('.mode-card').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  el.querySelector('.mode-radio').checked = true;

  const ref = document.getElementById('encRefInput');
  if (val === 'especes') {
    ref.disabled     = true;
    ref.style.opacity = '.45';
    ref.placeholder  = 'Non applicable';
  } else {
    ref.disabled     = false;
    ref.style.opacity = '1';
    ref.placeholder  = val === 'virement' ? 'ex: TRX-202604-ABCD' : 'ex: CHQ-00345';
  }
}

function resetModeRef() {
  const ref = document.getElementById('encRefInput');
  ref.disabled     = true;   /* Espèces sélectionné par défaut */
  ref.style.opacity = '.45';
  ref.placeholder  = 'Non applicable';
}

/* ── Upload fichiers ─────────────────────────────────────────── */
function onFileChange(e) { addFiles(e.target.files); }
function onFileDrop(e) {
  e.preventDefault();
  document.getElementById('uploadDrop').classList.remove('dragover');
  addFiles(e.dataTransfer.files);
}

function addFiles(files) {
  const list = document.getElementById('uploadList');
  Array.from(files).forEach(f => {
    const isPdf = f.type === 'application/pdf';
    const el    = document.createElement('div');
    el.className = 'upload-item';
    el.innerHTML = `
      <div class="doc-icon ${isPdf ? 'pdf' : 'img'}">
        <i class="fa-solid ${isPdf ? 'fa-file-pdf' : 'fa-image'}"></i>
      </div>
      <span class="doc-name">${f.name}</span>
      <span class="doc-size">${fmtSize(f.size)}</span>
      <button type="button" class="upload-remove" onclick="this.closest('.upload-item').remove()" title="Supprimer">
        <i class="fa-solid fa-xmark"></i>
      </button>`;
    list.appendChild(el);
  });
}

function fmtSize(b) {
  if (b < 1024)       return b + ' o';
  if (b < 1048576)    return (b/1024).toFixed(0) + ' Ko';
  return (b/1048576).toFixed(1) + ' Mo';
}

/* ── Soumission ──────────────────────────────────────────────── */
function submitEnc() {
  const m    = parseFloat(document.getElementById('encMontantInput').value) || 0;
  const date = document.getElementById('encDateInput').value;

  if (!m || m <= 0) {
    showToast('warning', 'Montant requis', 'Veuillez saisir un montant valide.');
    document.getElementById('encMontantInput').focus();
    return;
  }
  if (!date) {
    showToast('warning', 'Date requise', 'Veuillez sélectionner une date de paiement.');
    return;
  }

  const btn = document.getElementById('btnSubmit');
  btn.classList.add('is-loading');
  btn.disabled = true;

  /* Simulation appel API (remplacer par fetch('/api/paiements') en prod) */
  setTimeout(() => {
    btn.classList.remove('is-loading');
    btn.disabled = false;
    closeEncModal();

    const name = document.getElementById('encModalTitle').textContent.replace('Encaisser — ', '');
    const rest = Math.max(0, _enc.due - _enc.paid - m);

    if (rest === 0) {
      showToast('success', 'Paiement enregistré ✓', `${fmt(m)} encaissé pour ${name}.`);
    } else {
      showToast('warning', 'Paiement partiel enregistré', `Reste à percevoir : ${fmt(rest)}`);
    }
  }, 1300);
}

/* ── Toast ───────────────────────────────────────────────────── */
const TOAST_ICONS = {
  success: { cls:'green',  icon:'fa-circle-check' },
  warning: { cls:'orange', icon:'fa-triangle-exclamation' },
  error:   { cls:'red',    icon:'fa-circle-xmark' }
};

function showToast(type, title, msg) {
  const stack = document.getElementById('toastStack');
  const cfg   = TOAST_ICONS[type] || TOAST_ICONS.success;
  const el    = document.createElement('div');
  el.className = 'toast-item';
  el.innerHTML = `
    <div class="clause-icon ${cfg.cls}"><i class="fa-solid ${cfg.icon}"></i></div>
    <div class="toast-body">
      <div class="toast-title">${title}</div>
      <div class="toast-msg">${msg}</div>
    </div>
    <button class="toast-close" onclick="removeToast(this.closest('.toast-item'))">
      <i class="fa-solid fa-xmark"></i>
    </button>`;
  stack.appendChild(el);
  setTimeout(() => removeToast(el), 4500);
}

function removeToast(el) {
  if (!el || !el.parentNode) return;
  el.classList.add('is-out');
  setTimeout(() => el.remove(), 280);
}

/* ── Helpers ─────────────────────────────────────────────────── */
function fmt(n) { return Number(n).toLocaleString('fr-FR') + ' MAD'; }
</script>
@endpush

@endsection