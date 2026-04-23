@extends('layouts.layout')

@section('title', 'Charges & Dépenses')
@section('page_title', 'Charges & Dépenses')

@section('content')
    <div class="page-header">
        <div class="ph-left">
          <h2>Charges & Dépenses</h2>
          <p>Dépenses réelles de la Résidence Atlas — 2026</p>
        </div>
        <div class="ph-right">
          <button class="btn-outline-erp"><i class="fa-solid fa-download"></i> Exporter</button>
          <button class="btn-primary-erp" onclick="openModal('modalDepense')"><i class="fa-solid fa-plus"></i> Ajouter dépense</button>
        </div>
      </div>

      <div class="kpi-row" style="grid-template-columns:repeat(4,1fr)">
        <div class="kpi-card kpi-orange" style="animation-delay:.05s"><div class="kpi-top"><div><div class="kpi-label">Total Dépenses</div><div class="kpi-value">39 200 <small style="font-size:13px;font-weight:500;color:var(--text-4)">MAD</small></div><div class="kpi-sub">Exercice 2026</div></div><div class="kpi-icon orange"><i class="fa-solid fa-receipt"></i></div></div></div>
        <div class="kpi-card kpi-teal" style="animation-delay:.2s"><div class="kpi-top"><div><div class="kpi-label">SRM</div><div class="kpi-value">12 000 <small style="font-size:13px;font-weight:500;color:var(--text-4)">MAD</small></div><div class="kpi-sub">Gardiennage, Nettoyage, eau, électricité </div></div><div class="kpi-icon teal"><i class="fa-solid fa-shield-halved"></i></div></div></div>
        <div class="kpi-card kpi-blue" style="animation-delay:.15s"><div class="kpi-top"><div><div class="kpi-label">Maintenance</div><div class="kpi-value">12 400 <small style="font-size:13px;font-weight:500;color:var(--text-4)">MAD</small></div><div class="kpi-sub">Entretien Ascenseur</div></div><div class="kpi-icon blue"><i class="fa-solid fa-wrench"></i></div></div></div>
        <div class="kpi-card kpi-red" style="animation-delay:.1s"><div class="kpi-top"><div><div class="kpi-label">Travaux / Autres</div><div class="kpi-value">14 800 <small style="font-size:13px;font-weight:500;color:var(--text-4)">MAD</small></div><div class="kpi-sub">4 interventions</div></div><div class="kpi-icon red"><i class="fa-solid fa-hard-hat"></i></div></div></div>

      </div>

      <div class="filter-bar">
        <div class="filter-search"><i class="fa-solid fa-magnifying-glass"></i><input type="text" placeholder="Titre, fournisseur, description…"></div>
        <select class="filter-select"><option value="">Toutes catégories</option><option>Maintenance</option><option>Sécurité</option><option>Travaux</option><option>Admin</option></select>
        <select class="filter-select"><option>2026</option><option>2025</option></select>
        <div class="filter-divider d-none d-md-block"></div>
        <div class="filter-count d-none d-md-block"><strong>12</strong> dépenses</div>
      </div>

      <div class="table-card">
        <div class="table-card-header">
          <div class="tch-left"><h5>Registre des Dépenses</h5><p>Dépenses réelles avec justificatifs</p></div>
        </div>
        <div class="table-responsive">
          <table class="erp-table">
            <thead><tr><th><input type="checkbox" class="row-check"></th><th>Titre</th><th>Catégorie</th><th>Fournisseur</th><th>Montant</th><th>Date facture</th><th>Justificatif</th><th>Actions</th></tr></thead>
            <tbody>
              <tr><td><input type="checkbox" class="row-check"></td><td><div><span class="t-name">Contrat ascenseur Kone</span><span class="t-type">Maintenance mensuelle</span></div></td><td><span class="cat-badge maintenance">Maintenance</span></td><td>Kone Maroc</td><td><span class="amount-main">3 200 MAD</span><span class="amount-sub">/ mois</span></td><td style="font-size:12.5px;color:var(--text-3)">05/04/2026</td><td><span class="s-badge active"><i class="fa-solid fa-check"></i> Facture jointe</span></td><td><div class="row-actions"><button class="ra-btn view"><i class="fa-solid fa-eye"></i></button><button class="ra-btn download"><i class="fa-solid fa-file-pdf"></i></button><button class="ra-btn delete"><i class="fa-solid fa-trash"></i></button></div></td></tr>
              <tr><td><input type="checkbox" class="row-check"></td><td><div><span class="t-name">Réparation toiture Apt. 18</span><span class="t-type">Suite réclamation CR-003</span></div></td><td><span class="cat-badge travaux">Travaux</span></td><td>BTP Maroc SARL</td><td><span class="amount-main">8 500 MAD</span></td><td style="font-size:12.5px;color:var(--text-3)">12/03/2026</td><td><span class="s-badge active"><i class="fa-solid fa-check"></i> Facture jointe</span></td><td><div class="row-actions"><button class="ra-btn view"><i class="fa-solid fa-eye"></i></button><button class="ra-btn download"><i class="fa-solid fa-file-pdf"></i></button><button class="ra-btn delete"><i class="fa-solid fa-trash"></i></button></div></td></tr>
              <tr><td><input type="checkbox" class="row-check"></td><td><div><span class="t-name">Gardiennage mensuel</span><span class="t-type">Mars 2026</span></div></td><td><span class="cat-badge securite">Sécurité</span></td><td>SecurGuard SA</td><td><span class="amount-main">4 500 MAD</span><span class="amount-sub">/ mois</span></td><td style="font-size:12.5px;color:var(--text-3)">31/03/2026</td><td><span class="s-badge active"><i class="fa-solid fa-check"></i> Facture jointe</span></td><td><div class="row-actions"><button class="ra-btn view"><i class="fa-solid fa-eye"></i></button><button class="ra-btn download"><i class="fa-solid fa-file-pdf"></i></button><button class="ra-btn delete"><i class="fa-solid fa-trash"></i></button></div></td></tr>
              <tr><td><input type="checkbox" class="row-check"></td><td><div><span class="t-name">Nettoyage parties communes</span><span class="t-type">Avril 2026</span></div></td><td><span class="cat-badge nettoyage">Nettoyage</span></td><td>CleanPro</td><td><span class="amount-main">1 800 MAD</span></td><td style="font-size:12.5px;color:var(--text-3)">01/04/2026</td><td><span class="s-badge pending">En attente</span></td><td><div class="row-actions"><button class="ra-btn edit"><i class="fa-solid fa-pen"></i></button><button class="ra-btn delete"><i class="fa-solid fa-trash"></i></button></div></td></tr>
              <tr><td><input type="checkbox" class="row-check"></td><td><div><span class="t-name">Facture SRM eau commune</span><span class="t-type">Q1 2026</span></div></td><td><span class="cat-badge energie">Énergie</span></td><td>SRM Marrakech</td><td><span class="amount-main">2 140 MAD</span></td><td style="font-size:12.5px;color:var(--text-3)">20/03/2026</td><td><span class="s-badge active"><i class="fa-solid fa-check"></i> Facture jointe</span></td><td><div class="row-actions"><button class="ra-btn view"><i class="fa-solid fa-eye"></i></button><button class="ra-btn download"><i class="fa-solid fa-file-pdf"></i></button><button class="ra-btn delete"><i class="fa-solid fa-trash"></i></button></div></td></tr>
            </tbody>
          </table>
        </div>
        <div class="table-footer">
          <span class="tf-info">Affichage <strong>1–5</strong> sur <strong>12</strong></span>
          <div class="tf-pagination"><button class="page-btn" disabled><i class="fa-solid fa-chevron-left"></i></button><button class="page-btn active">1</button><button class="page-btn">2</button><button class="page-btn">3</button><button class="page-btn"><i class="fa-solid fa-chevron-right"></i></button></div>
        </div>
      </div>

<!-- Modal Ajouter Dépense -->
<div class="modal-overlay" id="modalDepense">
  <div class="modal-panel">
    <div class="modal-head">
      <div><h3 class="mh-title">Ajouter une Dépense</h3><p class="mh-sub">Enregistrer une dépense réelle de la résidence</p></div>
      <div class="mh-right"><button class="modal-close" onclick="closeModal('modalDepense')"><i class="fa-solid fa-xmark"></i></button></div>
    </div>
    <div class="modal-body">
      <div class="form-section-label"><i class="fa-solid fa-info-circle"></i> Informations</div>
      <div class="form-grid">
        <div class="form-group" style="grid-column:1/-1"><label class="form-label">Titre <span class="req">*</span></label><input type="text" class="form-control-erp" placeholder="ex: Contrat ascenseur Kone"></div>
        <div class="form-group"><label class="form-label">Catégorie <span class="req">*</span></label><select class="form-control-erp"><option>Maintenance</option><option>Travaux</option><option>Administration</option><option>Service</option><option>Autre</option></select></div>
        <div class="form-group"><label class="form-label">Fournisseur</label><input type="text" class="form-control-erp" placeholder="Nom du fournisseur"></div>
        <div class="form-group"><label class="form-label">Montant réel <span class="req">*</span></label><input type="number" class="form-control-erp" placeholder="0.00"></div>
        <div class="form-group"><label class="form-label">Date facture <span class="req">*</span></label><input type="date" class="form-control-erp"></div>
        <div class="form-group"><label class="form-label">Date fin</label><input type="date" class="form-control-erp"></div>
        <div class="form-group"><label class="form-label">Année <span class="req">*</span></label><select class="form-control-erp"><option>2026</option><option>2025</option></select></div>
        <div class="form-group"><label class="form-label">Frequence <span class="req">*</span></label><select class="form-control-erp"><option>Mensuel</option><option>Trimensuel</option><option>Annuel</option><option>Unique</option></select></div>

      </div>
      <div class="form-group" style="margin-top:4px"><label class="form-label">Description</label><textarea class="form-control-erp" rows="2" placeholder="Détails optionnels…"></textarea></div>
      <div class="form-section-label"><i class="fa-solid fa-paperclip"></i> Justificatif</div>
      <div class="upload-zone"><i class="fa-solid fa-cloud-arrow-up"></i><p>Joindre facture ou devis</p><span>PDF, image (max 20 Mo)</span></div>
    </div>
    <div class="modal-foot">
      <div class="mf-left"><button class="btn-outline-erp" onclick="closeModal('modalDepense')">Annuler</button></div>
      <div class="mf-right"><button class="btn-primary-erp" onclick="submitForm('modalDepense','Dépense ajoutée','Dépense enregistrée avec succès.')"><i class="fa-solid fa-circle-check"></i> Enregistrer</button></div>
    </div>
  </div>
</div>
@endsection