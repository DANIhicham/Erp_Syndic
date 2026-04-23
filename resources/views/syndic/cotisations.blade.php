@extends('layouts.layout')

@section('title', 'Cotisations')
@section('page_title', 'Cotisations')

@section('content')
      <div class="page-header">
        <div class="ph-left">
          <h2>Cotisations</h2>
          <p>Suivi des paiements des copropriétaires — Résidence Atlas · 2026</p>
        </div>
        <div class="ph-right">
          <button class="btn-outline-erp"><i class="fa-solid fa-download"></i> Exporter</button>
          <button class="btn-primary-erp" onclick="openModal('modalCotisation')"><i class="fa-solid fa-plus"></i> Enregistrer paiement</button>
        </div>
      </div>

      <div class="kpi-row" style="grid-template-columns:repeat(4,1fr)">
        <div class="kpi-card kpi-green" style="animation-delay:.05s"><div class="kpi-top"><div><div class="kpi-label">Budget annuel</div><div class="kpi-value">61 440 <small style="font-size:13px;font-weight:500;color:var(--text-4)">MAD</small></div><div class="kpi-sub">7 680 × 8 copropriétaires</div></div><div class="kpi-icon green"><i class="fa-solid fa-building-columns"></i></div></div></div>
        <div class="kpi-card kpi-blue" style="animation-delay:.1s"><div class="kpi-top"><div><div class="kpi-label">Encaissé</div><div class="kpi-value">47 900 <small style="font-size:13px;font-weight:500;color:var(--text-4)">MAD</small></div><div class="kpi-sub">5 copropriétaires soldés</div></div><div class="kpi-icon blue"><i class="fa-solid fa-check-circle"></i></div></div></div>
        <div class="kpi-card kpi-orange" style="animation-delay:.15s"><div class="kpi-top"><div><div class="kpi-label">Partiels</div><div class="kpi-value">3</div><div class="kpi-sub">Paiements incomplets</div></div><div class="kpi-icon orange"><i class="fa-solid fa-code-branch"></i></div></div></div>
        <div class="kpi-card kpi-red" style="animation-delay:.2s"><div class="kpi-top"><div><div class="kpi-label">Impayés</div><div class="kpi-value">13 540 <small style="font-size:13px;font-weight:500;color:var(--text-4)">MAD</small></div><div class="kpi-sub"><span class="kpi-badge-alert"><i class="fa-solid fa-clock"></i> 3 en retard</span></div></div><div class="kpi-icon red"><i class="fa-solid fa-triangle-exclamation"></i></div></div></div>
      </div>

      <div class="filter-bar">
        <div class="filter-search"><i class="fa-solid fa-magnifying-glass"></i><input type="text" placeholder="Rechercher par copropriétaire, lot…"></div>
        <select class="filter-select"><option>2026</option><option>2025</option><option>2024</option></select>
        <select class="filter-select"><option value="">Tous les statuts</option><option>Payé</option><option>Partiel</option><option>En retard</option></select>
        <div class="filter-divider d-none d-md-block"></div>
        <div class="filter-count d-none d-md-block"><strong>8</strong> copropriétaires</div>
      </div>

      <div class="table-card">
        <div class="table-card-header">
          <div class="tch-left"><h5>Cotisations 2026</h5><p>Montant annuel fixe par appartement : 7 680 MAD</p></div>
          <div class="tch-right"><button class="btn-outline-erp" style="font-size:12px;padding:7px 12px"><i class="fa-solid fa-paper-plane"></i> Relancer tous</button></div>
        </div>
        <div class="table-responsive">
          <table class="erp-table">
            <thead>
              <tr><th><input type="checkbox" class="row-check"></th><th>Copropriétaire</th><th>Appartement</th><th>Montant Annuel</th><th>Payé</th><th>Reste</th><th>Date Echeance</th><th>Mode</th><th>Statut</th><th>Actions</th></tr>
            </thead>
            <tbody>
              <tr><td><input type="checkbox" class="row-check"></td><td><div class="tenant-cell"><div class="t-avatar" style="background:#4f46e5">KB</div><div><span class="t-name">Karim Benali</span><span class="t-type">Propriétaire</span></div></div></td><td><span class="ref-code">Apt. 01</span></td><td><span class="amount-main">7 680 MAD</span></td><td><span style="font-family:var(--font-h);font-weight:700;color:var(--green-t)">7 680 MAD</span></td><td><span style="color:var(--text-4);font-weight:500">— MAD</span></td><td>-</td><td><span class="s-badge active">Virement</span></td><td><span class="s-badge paid">Payé</span></td><td><div class="row-actions"><button class="ra-btn view"><i class="fa-solid fa-eye"></i></button><button class="ra-btn download"><i class="fa-solid fa-file-pdf"></i></button></div></td></tr>
              <tr><td><input type="checkbox" class="row-check"></td><td><div class="tenant-cell"><div class="t-avatar" style="background:#0891b2">SO</div><div><span class="t-name">Sara Ouali</span><span class="t-type">Propriétaire</span></div></div></td><td><span class="ref-code">Apt. 02</span></td><td><span class="amount-main">7 680 MAD</span></td><td><span style="font-family:var(--font-h);font-weight:700;color:var(--green-t)">7 680 MAD</span></td><td><span style="color:var(--text-4);font-weight:500">— MAD</span></td><td>-</td><td><span class="s-badge active">Espèces</span></td><td><span class="s-badge paid">Payé</span></td><td><div class="row-actions"><button class="ra-btn view"><i class="fa-solid fa-eye"></i></button><button class="ra-btn download"><i class="fa-solid fa-file-pdf"></i></button></div></td></tr>
              <tr style="background:rgba(245,158,11,.025)"><td><input type="checkbox" class="row-check"></td><td><div class="tenant-cell"><div class="t-avatar" style="background:#d97706">LK</div><div><span class="t-name">Leila Khadiri</span><span class="t-type">Propriétaire</span></div></div></td><td><span class="ref-code">Apt. 03</span></td><td><span class="amount-main">7 680 MAD</span></td><td><span style="font-family:var(--font-h);font-weight:700;color:var(--amber-t)">4 000 MAD</span></td><td><span style="font-family:var(--font-h);font-weight:700;color:var(--orange-t)">3 680 MAD</span></td><td>-</td><td>—</td><td><span class="s-badge partial">Partiel</span></td><td><div class="row-actions"><button class="ra-btn encaisser" onclick="openModal('modalCotisation')"><i class="fa-solid fa-cash-register"></i> Encaisser</button><button class="ra-btn download"><i class="fa-solid fa-file-pdf"></i></button></div></td></tr>
              <tr style="background:rgba(239,68,68,.025)"><td><input type="checkbox" class="row-check"></td><td><div class="tenant-cell"><div class="t-avatar" style="background:#dc2626">RA</div><div><span class="t-name">Rachid Alami</span><span class="t-type">Propriétaire</span></div></div></td><td><span class="ref-code">Apt. 04</span></td><td><span class="amount-main">7 680 MAD</span></td><td><span style="font-family:var(--font-h);font-weight:700;color:var(--text-4)">0 MAD</span></td><td><span style="font-family:var(--font-h);font-weight:700;color:var(--red-t)">7 680 MAD</span></td><td>-</td><td>—</td><td><span class="s-badge late">En retard</span></td><td><div class="row-actions"><button class="ra-btn encaisser" onclick="openModal('modalCotisation')"><i class="fa-solid fa-cash-register"></i> Encaisser</button><button class="ra-btn" style="color:var(--orange-t)" title="Relancer"><i class="fa-solid fa-paper-plane"></i></button></div></td></tr>
              <tr style="background:rgba(239,68,68,.025)"><td><input type="checkbox" class="row-check"></td><td><div class="tenant-cell"><div class="t-avatar" style="background:#7c3aed">NB</div><div><span class="t-name">Nadia Berrada</span><span class="t-type">Propriétaire</span></div></div></td><td><span class="ref-code">Apt. 05</span></td><td><span class="amount-main">7 680 MAD</span></td><td><span style="font-family:var(--font-h);font-weight:700;color:var(--text-4)">0 MAD</span></td><td><span style="font-family:var(--font-h);font-weight:700;color:var(--red-t)">7 680 MAD</span></td><td>-</td><td>—</td><td><span class="s-badge late">En retard</span></td><td><div class="row-actions"><button class="ra-btn encaisser" onclick="openModal('modalCotisation')"><i class="fa-solid fa-cash-register"></i> Encaisser</button><button class="ra-btn" style="color:var(--orange-t)" title="Relancer"><i class="fa-solid fa-paper-plane"></i></button></div></td></tr>
              <tr><td><input type="checkbox" class="row-check"></td><td><div class="tenant-cell"><div class="t-avatar" style="background:#059669">YM</div><div><span class="t-name">Youssef Mouhib</span><span class="t-type">Propriétaire</span></div></div></td><td><span class="ref-code">Apt. 06</span></td><td><span class="amount-main">7 680 MAD</span></td><td><span style="font-family:var(--font-h);font-weight:700;color:var(--green-t)">7 680 MAD</span></td><td><span style="color:var(--text-4);font-weight:500">— MAD</span></td><td>-</td><td><span class="s-badge active">Chèque</span></td><td><span class="s-badge paid">Payé</span></td><td><div class="row-actions"><button class="ra-btn view"><i class="fa-solid fa-eye"></i></button><button class="ra-btn download"><i class="fa-solid fa-file-pdf"></i></button></div></td></tr>
            </tbody>
          </table>
        </div>
        <div class="table-footer">
          <span class="tf-info">Affichage <strong>1–6</strong> sur <strong>8</strong></span>
          <div class="tf-pagination"><button class="page-btn" disabled><i class="fa-solid fa-chevron-left"></i></button><button class="page-btn active">1</button><button class="page-btn">2</button><button class="page-btn"><i class="fa-solid fa-chevron-right"></i></button></div>
        </div>
      </div>

    </div>

    <!-- Modal Encaisser Cotisation -->
<div class="modal-overlay" id="modalCotisation">
  <div class="modal-panel">
    <div class="modal-head">
      <div><h3 class="mh-title">Encaisser Cotisation</h3><p class="mh-sub">Enregistrer un paiement de cotisation syndic</p></div>
      <div class="mh-right"><button class="modal-close" onclick="closeModal('modalCotisation')"><i class="fa-solid fa-xmark"></i></button></div>
    </div>
    <div class="modal-body">
      <div class="form-section-label"><i class="fa-solid fa-user"></i> Copropriétaire & Appartement</div>
      <div class="form-grid">
        <div class="form-group"><label class="form-label">Copropriétaire <span class="req">*</span></label><select class="form-control-erp"><option>— Sélectionner —</option><option>Karim Benali — Apt. 01</option><option>Rachid Alami — Apt. 04</option><option>Leila Khadiri — Apt. 03</option></select></div>
        <div class="form-group"><label class="form-label">Année concernée <span class="req">*</span></label><select class="form-control-erp"><option>2026</option><option>2025</option></select></div>
      </div>
      <div class="form-section-label"><i class="fa-solid fa-coins"></i> Paiement</div>
      <div class="form-grid">
        <div class="form-group"><label class="form-label">Montant payé <span class="req">*</span></label><input type="number" class="form-control-erp" placeholder="ex: 7680"></div>
        <div class="form-group"><label class="form-label">Date paiement <span class="req">*</span></label><input type="date" class="form-control-erp"></div>
        <div class="form-group"><label class="form-label">Mode de paiement</label><select class="form-control-erp"><option>Virement</option><option>Espèces</option><option>Chèque</option></select></div>
        <div class="form-group"><label class="form-label">Référence</label><input type="text" class="form-control-erp" placeholder="Nº virement ou chèque"></div>
      </div>
      <div class="form-group" style="margin-top:4px"><label class="form-label">Commentaire</label><textarea class="form-control-erp" rows="2" placeholder="Notes optionnelles…"></textarea></div>
      <div class="form-section-label"><i class="fa-solid fa-paperclip"></i> Justificatif</div>
      <div class="upload-zone"><i class="fa-solid fa-cloud-arrow-up"></i><p>Joindre reçu ou preuve de paiement</p><span>PDF, image (max 10 Mo)</span></div>
    </div>
    <div class="modal-foot">
      <div class="mf-left"><button class="btn-outline-erp" onclick="closeModal('modalCotisation')">Annuler</button></div>
      <div class="mf-right"><button type="button" class="btn-primary-erp" onclick="submitForm('modalCotisation','Cotisation enregistrée','Paiement de cotisation ajouté avec succès.')"><i class="fa-solid fa-circle-check"></i> Valider</button></div>
    </div>
  </div>
</div>

@endsection
