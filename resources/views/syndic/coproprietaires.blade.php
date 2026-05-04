@extends('layouts.layout')

@section('title', 'Copropriétaires')
@section('page_title', 'Copropriétaires')

@section('content')
<div class="page-header">
        <div class="ph-left"><h2>Copropriétaires</h2><p>Propriétaires de la Bliving Office et suivi de leurs cotisations</p></div>
        <div class="ph-right">
          <button class="btn-outline-erp"><i class="fa-solid fa-download"></i> Export</button>
          <button class="btn-primary-erp" onclick="openModal('modalCoproprietaire')"><i class="fa-solid fa-user-plus"></i> Ajouter</button>
        </div>
      </div>

      <div class="filter-bar">
        <div class="filter-search"><i class="fa-solid fa-magnifying-glass"></i><input type="text" placeholder="Nom, email, CIN, lot…"></div>
        <select class="filter-select"><option value="">Tous les statuts</option><option>Cotisation à jour</option><option>Retard</option></select>
        <div class="filter-divider d-none d-md-block"></div>
        <div class="filter-count d-none d-md-block"><strong>8</strong> copropriétaires</div>
      </div>

      <!-- Grille de cards (🆕 owner-card) -->
      <div class="row g-3">
        <!-- Card 1 -->
        <div class="col-12 col-sm-6 col-xl-4">
          <div class="owner-card">
            <div class="oc-top">
              <div class="oc-avatar" style="background:#4f46e5">KB</div>
              <div><div class="oc-name">Karim Benali</div><div class="oc-lot"><i class="fa-solid fa-building" style="margin-right:4px;font-size:10px"></i>Apt. 01 · Étage 1 · 85m²</div></div>
              <span class="s-badge paid" style="margin-left:auto">Soldé</span>
            </div>
            <div class="oc-stats">
              <div class="oc-stat"><div class="oc-stat-val" style="color:var(--green-t)">7 680</div><div class="oc-stat-lbl">MAD payé</div></div>
              <div class="oc-stat"><div class="oc-stat-val" style="color:var(--text-4)">0</div><div class="oc-stat-lbl">MAD reste</div></div>
            </div>
            <div style="display:flex;gap:6px;margin-top:12px">
              <button class="btn-outline-erp" onclick="openModal('modalDetail')" style="flex:1;justify-content:center;font-size:12px;padding:6px"><i class="fa-solid fa-eye"></i> Détail</button>
              <button class="btn-outline-erp" style="flex:1;justify-content:center;font-size:12px;padding:6px"><i class="fa-solid fa-envelope"></i> Contacter</button>
            </div>
          </div>
        </div>
        <!-- Card 2 -->
        <div class="col-12 col-sm-6 col-xl-4">
          <div class="owner-card">
            <div class="oc-top">
              <div class="oc-avatar" style="background:#0891b2">SO</div>
              <div><div class="oc-name">Sara Ouali</div><div class="oc-lot"><i class="fa-solid fa-building" style="margin-right:4px;font-size:10px"></i>Apt. 02 · Étage 1 · 78m²</div></div>
              <span class="s-badge paid" style="margin-left:auto">Soldé</span>
            </div>
            <div class="oc-stats">
              <div class="oc-stat"><div class="oc-stat-val" style="color:var(--green-t)">7 680</div><div class="oc-stat-lbl">MAD payé</div></div>
              <div class="oc-stat"><div class="oc-stat-val" style="color:var(--text-4)">0</div><div class="oc-stat-lbl">MAD reste</div></div>
            </div>
            <div style="display:flex;gap:6px;margin-top:12px">
              <button class="btn-outline-erp" style="flex:1;justify-content:center;font-size:12px;padding:6px"><i class="fa-solid fa-eye"></i> Détail</button>
              <button class="btn-outline-erp" style="flex:1;justify-content:center;font-size:12px;padding:6px"><i class="fa-solid fa-envelope"></i> Contacter</button>
            </div>
          </div>
        </div>
        <!-- Card 3 — Retard -->
        <div class="col-12 col-sm-6 col-xl-4">
          <div class="owner-card" style="border-color:rgba(239,68,68,.2)">
            <div class="oc-top">
              <div class="oc-avatar" style="background:#dc2626">RA</div>
              <div><div class="oc-name">Rachid Alami</div><div class="oc-lot"><i class="fa-solid fa-building" style="margin-right:4px;font-size:10px"></i>Apt. 04 · Étage 2 · 92m²</div></div>
              <span class="s-badge late" style="margin-left:auto">Retard</span>
            </div>
            <div class="oc-stats">
              <div class="oc-stat"><div class="oc-stat-val" style="color:var(--text-4)">0</div><div class="oc-stat-lbl">MAD payé</div></div>
              <div class="oc-stat"><div class="oc-stat-val" style="color:var(--red-t)">7 680</div><div class="oc-stat-lbl">MAD reste</div></div>
            </div>
            <div style="display:flex;gap:6px;margin-top:12px">
              <button class="btn-outline-erp" style="flex:1;justify-content:center;font-size:12px;padding:6px"><i class="fa-solid fa-eye"></i> Détail</button>
              <button class="btn-outline-erp" style="flex:1;justify-content:center;font-size:12px;padding:6px"><i class="fa-solid fa-envelope"></i> Contacter</button>
            </div>
          </div>
        </div>
        <!-- Card 4 — Partiel -->
        <div class="col-12 col-sm-6 col-xl-4">
          <div class="owner-card" style="border-color:rgba(245,158,11,.25)">
            <div class="oc-top">
              <div class="oc-avatar" style="background:#d97706">LK</div>
              <div><div class="oc-name">Leila Khadiri</div><div class="oc-lot"><i class="fa-solid fa-building" style="margin-right:4px;font-size:10px"></i>Apt. 03 · Étage 2 · 68m²</div></div>
              <span class="s-badge partial" style="margin-left:auto">Partiel</span>
            </div>
            <div class="oc-stats">
              <div class="oc-stat"><div class="oc-stat-val" style="color:var(--amber-t)">4 000</div><div class="oc-stat-lbl">MAD payé</div></div>
              <div class="oc-stat"><div class="oc-stat-val" style="color:var(--orange-t)">3 680</div><div class="oc-stat-lbl">MAD reste</div></div>
            </div>
            <div style="display:flex;gap:6px;margin-top:12px">
              <button class="btn-outline-erp" style="flex:1;justify-content:center;font-size:12px;padding:6px"><i class="fa-solid fa-eye"></i> Détail</button>
              <button class="btn-outline-erp" style="flex:1;justify-content:center;font-size:12px;padding:6px"><i class="fa-solid fa-envelope"></i> Contacter</button>
            </div>
          </div>
        </div>
        <!-- Card 5 -->
        <div class="col-12 col-sm-6 col-xl-4">
          <div class="owner-card">
            <div class="oc-top">
              <div class="oc-avatar" style="background:#059669">YM</div>
              <div><div class="oc-name">Youssef Mouhib</div><div class="oc-lot"><i class="fa-solid fa-building" style="margin-right:4px;font-size:10px"></i>Apt. 06 · Étage 3 · 74m²</div></div>
              <span class="s-badge paid" style="margin-left:auto">Soldé</span>
            </div>
            <div class="oc-stats">
              <div class="oc-stat"><div class="oc-stat-val" style="color:var(--green-t)">7 680</div><div class="oc-stat-lbl">MAD payé</div></div>
              <div class="oc-stat"><div class="oc-stat-val" style="color:var(--text-4)">0</div><div class="oc-stat-lbl">MAD reste</div></div>
            </div>
            <div style="display:flex;gap:6px;margin-top:12px">
              <button class="btn-outline-erp" style="flex:1;justify-content:center;font-size:12px;padding:6px"><i class="fa-solid fa-eye"></i> Détail</button>
              <button class="btn-outline-erp" style="flex:1;justify-content:center;font-size:12px;padding:6px"><i class="fa-solid fa-envelope"></i> Contacter</button>
            </div>
          </div>
        </div>
      </div>
<!-- Modal Copropriétaire -->
<div class="modal-overlay" id="modalCoproprietaire">
  <div class="modal-panel">
    <div class="modal-head">
      <div><h3 class="mh-title">Ajouter Copropriétaire</h3><p class="mh-sub">Lier un utilisateur à un appartement de la résidence</p></div>
      <div class="mh-right"><button class="modal-close" onclick="closeModal('modalCoproprietaire')"><i class="fa-solid fa-xmark"></i></button></div>
    </div>
    <div class="modal-body">
      <div class="form-section-label"><i class="fa-solid fa-user"></i> Identité</div>
      <div class="form-grid">
        <div class="form-group"><label class="form-label">Nom <span class="req">*</span></label><input type="text" class="form-control-erp" placeholder="Nom de famille"></div>
        <div class="form-group"><label class="form-label">Prénom <span class="req">*</span></label><input type="text" class="form-control-erp" placeholder="Prénom"></div>
        <div class="form-group"><label class="form-label">Email <span class="req">*</span></label><input type="email" class="form-control-erp" placeholder="email@exemple.ma"></div>
        <div class="form-group"><label class="form-label">Téléphone</label><input type="tel" class="form-control-erp" placeholder="+212 6XX XX XX XX"></div>
        <div class="form-group"><label class="form-label">CIN</label><input type="text" class="form-control-erp" placeholder="AB123456"></div>
      </div>
      <div class="form-section-label"><i class="fa-solid fa-building"></i> Appartement</div>
      <div class="form-grid">
        <div class="form-group"><label class="form-label">Appartement <span class="req">*</span></label><select class="form-control-erp"><option>— Sélectionner —</option><option>Apt. 07 — Résidence Atlas</option><option>Apt. 08 — Résidence Atlas</option></select></div>
        <div class="form-group"><label class="form-label">Date de signature contrat</label><input type="date" class="form-control-erp"></div>
      </div>
    </div>
    <div class="modal-foot">
      <div class="mf-left"><button class="btn-outline-erp" onclick="closeModal('modalCoproprietaire')">Annuler</button></div>
      <div class="mf-right"><button class="btn-primary-erp" onclick="submitForm('modalCoproprietaire','Copropriétaire ajouté','Profil créé et associé à la résidence.')"><i class="fa-solid fa-user-plus"></i> Créer</button></div>
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
      <div class="mf-right"><button class="btn-primary-erp" onclick="submitForm('modalCotisation','Cotisation enregistrée','Paiement de cotisation ajouté avec succès.')"><i class="fa-solid fa-circle-check"></i> Valider</button></div>
    </div>
  </div>
</div>

<!-- modal -->
 <div class="modal-overlay" id="modalDetail">
  <div class="modal-panel">
    
    <div class="modal-head">
      <div>
        <h3 class="mh-title" id="detailName">Nom</h3>
        <p class="mh-sub" id="detailLot">Lot</p>
      </div>
      <button class="modal-close" onclick="closeModal('modalDetail')">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div class="modal-body">
      <p><strong>Email :</strong> <span id="detailEmail"></span></p>
      <p><strong>Téléphone :</strong> <span id="detailPhone"></span></p>
      <p><strong>Date signature de contrat :</strong> <span id="detailContrat"></span></p>

    </div>

    <div class="modal-foot">
      <button class="btn-outline-erp" onclick="closeModal('modalDetail')">Fermer</button>
    </div>

  </div>
</div>
@endsection