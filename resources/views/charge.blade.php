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

      <div class="kpi-row" style="grid-template-columns:repeat(3,1fr)">
        <div class="kpi-card kpi-orange" style="animation-delay:.05s"><div class="kpi-top"><div><div class="kpi-label">Total Dépenses</div><div class="kpi-value">39 200 <small style="font-size:13px;font-weight:500;color:var(--text-4)">MAD</small></div><div class="kpi-sub">Exercice 2026</div></div><div class="kpi-icon orange"><i class="fa-solid fa-receipt"></i></div></div></div>
        <div class="kpi-card kpi-teal" style="animation-delay:.2s"><div class="kpi-top"><div><div class="kpi-label">SRM</div><div class="kpi-value">12 000 <small style="font-size:13px;font-weight:500;color:var(--text-4)">MAD</small></div><div class="kpi-sub">Eau, électricité </div></div><div class="kpi-icon teal"><i class="fa-solid fa-bolt"></i></div></div></div>
        <div class="kpi-card kpi-blue" style="animation-delay:.15s"><div class="kpi-top"><div><div class="kpi-label">Maintenance</div><div class="kpi-value">12 400 <small style="font-size:13px;font-weight:500;color:var(--text-4)">MAD</small></div><div class="kpi-sub">Entretien Ascenseur</div></div><div class="kpi-icon blue"><i class="fa-solid fa-wrench"></i></div></div></div>
        <div class="kpi-card kpi-orange" style="animation-delay:.15s"><div class="kpi-top"><div><div class="kpi-label">Services</div><div class="kpi-value">12 400 <small style="font-size:13px;font-weight:500;color:var(--text-4)">MAD</small></div><div class="kpi-sub">Gardiennage, Nettoyage</div></div><div class="kpi-icon orange"><i class="fa-solid fa-shield-halved"></i></div></div></div>
        <div class="kpi-card kpi-red" style="animation-delay:.1s"><div class="kpi-top"><div><div class="kpi-label">Travaux / Autres</div><div class="kpi-value">14 800 <small style="font-size:13px;font-weight:500;color:var(--text-4)">MAD</small></div><div class="kpi-sub">4 interventions</div></div><div class="kpi-icon red"><i class="fa-solid fa-hard-hat"></i></div></div></div>

      </div>

      <div class="filter-bar">
        <div class="filter-search"><i class="fa-solid fa-magnifying-glass"></i><input type="text" placeholder="Rechercher une charge, un fournisseur..."></div>
        <select class="filter-select"><option value="">Toutes les catégories</option><option>Eau & Électricité</option><option>Maintenance Ascenseur</option><option>Nettoyage</option><option>Assurance</option></select>
        <select class="filter-select"><option value="">Tous les statuts</option><option>Payé</option><option>Partiel</option><option>En retard</option><option>À payer</option></select>
        <select class="filter-select"><option>Avril 2026</option><option>Mars 2026</option><option>Année 2026</option></select>
      </div>

      <div class="table-card">
        <div class="table-card-header">
          <div class="tch-left"><h5>Liste des Échéances</h5><p>Suivi détaillé des paiements fournisseurs et charges</p></div>
        </div>
        <div class="table-responsive">
          <table class="erp-table">
            <thead>
              <tr>
                <th><input type="checkbox" class="row-check"></th>
                <th>Nom de la Charge</th>
                <th>Catégorie</th>
                <th>Échéance</th>
                <th>Total</th>
                <th>Payé</th>
                <th>Reste</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              
              <tr>
                <td><input type="checkbox" class="row-check"></td>
                <td><div class="tenant-cell"><div><span class="t-name">Maintenance Ascenseur</span><span class="t-type">Schindler Maroc</span></div></div></td>
                <td><span class="ref-code">Maintenance</span></td>
                <td><span style="color:var(--text-4); font-size:13px;">10/04/2026</span></td>
                <td><span class="amount-main">2 500 MAD</span></td>
                <td><span style="font-family:var(--font-h);font-weight:700;color:var(--green-t)">2 500 MAD</span></td>
                <td><span style="color:var(--text-4);font-weight:500">— MAD</span></td>
                <td><span class="s-badge paid">Payé</span></td>
                <td>
                  <div class="row-actions">
                    <button class="ra-btn view" title="Voir détails"><i class="fa-solid fa-eye"></i></button>
                    <button class="ra-btn download" title="Télécharger facture"><i class="fa-solid fa-file-invoice"></i></button>
                          <button class="ra-btn" onclick="openModal('modalEditCharge')" title="Modifier">
                              <i class="fa-solid fa-pen-to-square" style="color: var(--blue-t)"></i>
                          </button>
                  </div>
                </td>
              </tr>

              <tr style="background:rgba(239,68,68,.035); border-left: 3px solid var(--red-t);">
                <td><input type="checkbox" class="row-check"></td>
                <td><div class="tenant-cell"><div><span class="t-name" style="color:var(--red-t);">Facture Électricité Commune</span><span class="t-type">Lydec</span></div></div></td>
                <td><span class="ref-code">Eau & Élec</span></td>
                <td><span style="color:var(--red-t); font-size:13px; font-weight:600;"><i class="fa-solid fa-circle-exclamation"></i> 15/04/2026</span></td>
                <td><span class="amount-main">1 800 MAD</span></td>
                <td><span style="font-family:var(--font-h);font-weight:700;color:var(--text-4)">0 MAD</span></td>
                <td><span style="font-family:var(--font-h);font-weight:700;color:var(--red-t)">1 800 MAD</span></td>
                <td><span class="s-badge late">En retard</span></td>
                <td>
                  <div class="row-actions">
                    <button class="ra-btn encaisser" style="background:var(--red-t); color:white; border:none;" onclick="openModal('modalPayer')" title="Payer maintenant"><i class="fa-solid fa-credit-card"></i> Payer</button>
                  </div>
                </td>
              </tr>

              <tr style="background:rgba(245,158,11,.025)">
                <td><input type="checkbox" class="row-check"></td>
                <td><div class="tenant-cell"><div><span class="t-name">Nettoyage Mensuel</span><span class="t-type">Clean Pro Sarl</span></div></div></td>
                <td><span class="ref-code">Nettoyage</span></td>
                <td><span style="color:var(--text-4); font-size:13px;">25/04/2026</span></td>
                <td><span class="amount-main">3 000 MAD</span></td>
                <td><span style="font-family:var(--font-h);font-weight:700;color:var(--amber-t)">1 500 MAD</span></td>
                <td><span style="font-family:var(--font-h);font-weight:700;color:var(--orange-t)">1 500 MAD</span></td>
                <td><span class="s-badge partial">Partiel</span></td>
                <td>
                  <div class="row-actions">
                    <button class="ra-btn encaisser" onclick="openModal('modalPayer')" title="Compléter le paiement"><i class="fa-solid fa-coins"></i> Compléter</button>
                    <button class="ra-btn view"><i class="fa-solid fa-eye"></i></button>
                  </div>
                </td>
              </tr>

              <tr>
                <td><input type="checkbox" class="row-check"></td>
                <td><div class="tenant-cell"><div><span class="t-name">Prime d'Assurance</span><span class="t-type">Wafa Assurance</span></div></div></td>
                <td><span class="ref-code">Assurance</span></td>
                <td><span style="color:var(--orange-t); font-size:13px; font-weight:500;"><i class="fa-regular fa-clock"></i> 02/05/2026</span></td>
                <td><span class="amount-main">4 500 MAD</span></td>
                <td><span style="font-family:var(--font-h);font-weight:700;color:var(--text-4)">0 MAD</span></td>
                <td><span style="font-family:var(--font-h);font-weight:700;color:var(--text-2)">4 500 MAD</span></td>
                <td><span class="s-badge active" style="background:#f3f4f6; color:#4b5563;">À Payer</span></td>
                <td>
                  <div class="row-actions">
                    <button class="ra-btn encaisser" onclick="openModal('modalPayer')"><i class="fa-solid fa-credit-card"></i> Payer</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
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
<!-- modal edit-->
 <div class="modal-overlay" id="modalEditCharge" >
  <div class="modal-panel enc-panel">
    
    <div class="modal-head">
      <div class="mh-left" style="display:flex;align-items:center;gap:14px;">
        <div class="enc-head-avatar" style="background:#1d4ed8;"><i class="fa-solid fa-pen-to-square"></i></div>
        <div>
          <div style="margin-bottom:6px">
            <span class="mh-ref-code" style="background:rgba(29,78,216,.1); color:#1d4ed8;">Modification</span>
          </div>
          <h3 class="mh-title">Modifier la charge</h3>
          <p class="mh-sub" id="editChargeTitle">Édition des détails de la dépense</p>
        </div>
      </div>
      <div class="mh-right">
        <button class="modal-close" onclick="closeModal('modalEditCharge')"><i class="fa-solid fa-xmark"></i></button>
      </div>
    </div>

    <div class="modal-body">
      {{-- Section Identité de la charge --}}
      <div class="form-section-label"><i class="fa-solid fa-tag"></i> Identification</div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Nom de la charge <span style="color:var(--red)">*</span></label>
          <input type="text" class="form-control-erp" id="editNomCharge" value="Maintenance Ascenseur">
        </div>
        <div class="form-group">
          <label class="form-label">Catégorie <span style="color:var(--red)">*</span></label>
          <select class="form-control-erp" id="editCategorieCharge">
            <option>Eau & Électricité</option>
            <option selected>Maintenance</option>
            <option>Nettoyage</option>
            <option>Assurance</option>
            <option>Autre</option>
          </select>
        </div>
      </div>

      <div class="form-group" style="margin-top:14px">
        <label class="form-label">Fournisseur / Prestataire</label>
        <input type="text" class="form-control-erp" id="editFournisseur" value="Schindler Maroc">
      </div>

      {{-- Section Financière --}}
      <div class="form-section-label" style="margin-top:20px;"><i class="fa-solid fa-money-bill-wave"></i> Montant & Échéance</div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Montant Total (MAD) <span style="color:var(--red)">*</span></label>
          <div class="input-prefix-wrap">
            <span class="input-prefix">MAD</span>
            <input type="number" class="form-control-erp" id="editMontantTotal" value="2500.00">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Date d'échéance <span style="color:var(--red)">*</span></label>
          <input type="date" class="form-control-erp" id="editDateEcheance" value="2026-04-10">
        </div>
      </div>

      <div class="form-group" style="margin-top:14px">
        <label class="form-label">Note interne / Commentaire</label>
        <textarea class="form-control-erp" rows="2" placeholder="Précisez la nature de la modification si nécessaire..."></textarea>
      </div>
    </div>

    <div class="modal-foot">
      <div class="mf-left">
        <button class="btn-outline-erp" onclick="closeModal('modalEditCharge')">Annuler</button>
      </div>
      <div class="mf-right">
        <button type="submit" class="btn-primary-erp" style="background:#1d4ed8">
          <i class="fa-solid fa-save"></i> Mettre à jour la charge
        </button>
      </div>
    </div>
  </div>
</div>
@endsection