@extends('layouts.layout')

@section('title', 'Réclamations')
@section('page_title', 'Reclamations')

@section('content')


<div class="page-header">
        <div class="ph-left"><h2>Réclamations Syndic</h2><p>Problèmes signalés dans Bliving Office</p></div>
        <div class="ph-right">
          <button class="btn-primary-erp" onclick="openModal('modalReclamation')"><i class="fa-solid fa-plus"></i> Nouvelle réclamation</button>
        </div>
      </div>

      <div class="kpi-row" style="grid-template-columns:repeat(3,1fr)">
        <div class="kpi-card kpi-red" style="animation-delay:.05s"><div class="kpi-top"><div><div class="kpi-label">Ouvertes</div><div class="kpi-value">3</div><div class="kpi-sub"><span class="kpi-badge-alert"><i class="fa-solid fa-fire"></i> 2 urgentes</span></div></div><div class="kpi-icon red"><i class="fa-solid fa-circle-exclamation"></i></div></div></div>
        <div class="kpi-card kpi-orange" style="animation-delay:.1s"><div class="kpi-top"><div><div class="kpi-label">En cours</div><div class="kpi-value">1</div><div class="kpi-sub">Technicien mandaté</div></div><div class="kpi-icon orange"><i class="fa-solid fa-spinner"></i></div></div></div>
        <div class="kpi-card kpi-green" style="animation-delay:.15s"><div class="kpi-top"><div><div class="kpi-label">Résolues</div><div class="kpi-value">12</div><div class="kpi-sub">Ce mois</div></div><div class="kpi-icon green"><i class="fa-solid fa-check-circle"></i></div></div></div>
      
      </div>

      <div class="filter-bar">
        <div class="filter-search"><i class="fa-solid fa-magnifying-glass"></i><input type="text" placeholder="Titre, appartement, description…"></div>
        <select class="filter-select"><option value="">Tous les statuts</option><option>Ouverte</option><option>En cours</option><option>Résolue</option></select>
        <select class="filter-select"><option value="">Toutes priorités</option><option>Urgente</option><option>Haute</option><option>Moyenne</option><option>Basse</option></select>
        <div class="filter-divider d-none d-md-block"></div>
        <div class="filter-count d-none d-md-block"><strong>5</strong> réclamations</div>
      </div>

      <div class="table-card">
        <div class="table-card-header"><div class="tch-left"><h5>Liste des Réclamations</h5><p>Filtrées par résidence active</p></div></div>
        <div class="table-responsive">
          <table class="erp-table">
            <thead><tr><th><input type="checkbox" class="row-check"></th><th>Titre</th><th>Appartement</th><th>Priorité</th><th>Statut</th><th>Date création</th><th>Actions</th></tr></thead>
            <tbody>
              <tr style="background:rgba(239,68,68,.025)">
                <td><input type="checkbox" class="row-check"></td>
                <td><div><span class="t-name">Panne ascenseur — bloqué au 3ème</span><span class="t-type">Signalé par Rachid Alami</span></div></td>
                <td><span class="ref-code">Apt. 07</span></td>
                <td><span class="prio-badge urgente"><i class="fa-solid fa-fire"></i> Urgente</span></td>
                <td><span class="s-badge open">Ouverte</span></td>
                <td style="font-size:12.5px;color:var(--text-3)">14/04/2026</td>
                <td><div class="row-actions"><button class="ra-btn view"><i class="fa-solid fa-eye"></i></button><button class="ra-btn edit"><i class="fa-solid fa-pen"></i></button></div></td>
              </tr>
              <tr style="background:rgba(239,68,68,.025)">
                <td><input type="checkbox" class="row-check"></td>
                <td><div><span class="t-name">Fuite eau plafond</span><span class="t-type">Signalé par Nadia Berrada</span></div></td>
                <td><span class="ref-code">Apt. 18</span></td>
                <td><span class="prio-badge urgente"><i class="fa-solid fa-fire"></i> Urgente</span></td>
                <td><span class="s-badge in-progress">En cours</span></td>
                <td style="font-size:12.5px;color:var(--text-3)">10/04/2026</td>
                <td><div class="row-actions"><button class="ra-btn view"><i class="fa-solid fa-eye"></i></button><button class="ra-btn edit"><i class="fa-solid fa-pen"></i></button></div></td>
              </tr>
              <tr>
                <td><input type="checkbox" class="row-check"></td>
                <td><div><span class="t-name">Lumière couloir étage 2 défaillante</span><span class="t-type">Signalé par Sara Ouali</span></div></td>
                <td><span class="ref-code">Parties communes</span></td>
                <td><span class="prio-badge haute">Haute</span></td>
                <td><span class="s-badge open">Ouverte</span></td>
                <td style="font-size:12.5px;color:var(--text-3)">16/04/2026</td>
                <td><div class="row-actions"><button class="ra-btn view"><i class="fa-solid fa-eye"></i></button><button class="ra-btn edit"><i class="fa-solid fa-pen"></i></button></div></td>
              </tr>
              <tr>
                <td><input type="checkbox" class="row-check"></td>
                <td><div><span class="t-name">Digicode portail en panne</span><span class="t-type">Signalé par Karim Benali</span></div></td>
                <td><span class="ref-code">Entrée principale</span></td>
                <td><span class="prio-badge moyenne">Moyenne</span></td>
                <td><span class="s-badge open">Ouverte</span></td>
                <td style="font-size:12.5px;color:var(--text-3)">18/04/2026</td>
                <td><div class="row-actions"><button class="ra-btn view"><i class="fa-solid fa-eye"></i></button><button class="ra-btn edit"><i class="fa-solid fa-pen"></i></button></div></td>
              </tr>
              <tr>
                <td><input type="checkbox" class="row-check"></td>
                <td><div><span class="t-name">Nettoyage garage sous-sol insuffisant</span><span class="t-type">Signalé par Youssef Mouhib</span></div></td>
                <td><span class="ref-code">Sous-sol</span></td>
                <td><span class="prio-badge basse">Basse</span></td>
                <td><span class="s-badge resolved">Résolue</span></td>
                <td style="font-size:12.5px;color:var(--text-3)">01/04/2026</td>
                <td><div class="row-actions"><button class="ra-btn view"><i class="fa-solid fa-eye"></i></button></div></td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="table-footer">
          <span class="tf-info">Affichage <strong>1–5</strong> sur <strong>16</strong></span>
          <div class="tf-pagination"><button class="page-btn" disabled><i class="fa-solid fa-chevron-left"></i></button><button class="page-btn active">1</button><button class="page-btn">2</button><button class="page-btn">3</button><button class="page-btn"><i class="fa-solid fa-chevron-right"></i></button></div>
        </div>
      </div>

<!-- Modal Réclamation -->
<div class="modal-overlay" id="modalReclamation">
  <div class="modal-panel">
    <div class="modal-head">
      <div><h3 class="mh-title">Nouvelle Réclamation</h3><p class="mh-sub">Signaler un problème dans la résidence</p></div>
      <div class="mh-right"><button class="modal-close" onclick="closeModal('modalReclamation')"><i class="fa-solid fa-xmark"></i></button></div>
    </div>
    <div class="modal-body">
      <div class="form-grid">
        <div class="form-group" style="grid-column:1/-1"><label class="form-label">Titre <span class="req">*</span></label><input type="text" class="form-control-erp" placeholder="ex: Panne ascenseur"></div>
        <div class="form-group"><label class="form-label">Appartement / Lieu</label><select class="form-control-erp"><option>Parties communes</option><option>Apt. 01</option><option>Apt. 07</option><option>Entrée principale</option></select></div>
        <div class="form-group"><label class="form-label">Priorité <span class="req">*</span></label><select class="form-control-erp"><option>Basse</option><option>Moyenne</option><option>Haute</option><option>Urgente</option></select></div>
        <div class="form-group" style="grid-column:1/-1"><label class="form-label">Description <span class="req">*</span></label><textarea class="form-control-erp" rows="3" placeholder="Décrivez le problème en détail…"></textarea></div>
      </div>
    </div>
    <div class="modal-foot">
      <div class="mf-left"><button class="btn-outline-erp" onclick="closeModal('modalReclamation')">Annuler</button></div>
      <div class="mf-right"><button class="btn-primary-erp" onclick="submitForm('modalReclamation','Réclamation créée','Réclamation enregistrée et transmise.')"><i class="fa-solid fa-paper-plane"></i> Soumettre</button></div>
    </div>
  </div>
</div>
@endsection