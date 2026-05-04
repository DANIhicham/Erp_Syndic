@extends('layouts.layout')

@section('title', 'Charges & Dépenses')
@section('page_title', 'Charges & Dépenses')

@section('content')
    <div class="page-header">
      <div class="ph-left">
        <h2>Charges & Dépenses</h2>
        <p>Suivi des échéances — Résidence Atlas · 2026</p>
      </div>
      <div class="ph-right">
        <button class="btn-outline-erp" onclick="exportCSV()"><i class="fa-solid fa-download"></i> Export</button>
        <button class="btn-outline-erp" onclick="openModal('mAddService')"><i class="fa-solid fa-bolt"></i> Service variable</button>
        <button class="btn-outline-erp" onclick="openModal('mAddCharge')"><i class="fa-solid fa-rotate"></i> Charge récurrente</button>
        <button class="btn-primary-erp" onclick="openModal('mAddDepense')"><i class="fa-solid fa-plus"></i> Dépense unique</button>
      </div>
    </div>

    <!-- KPIs -->
    <div class="kpi-row">
      <div class="kpi-card kpi-red" style="animation-delay:.05s">
        <div class="kpi-top">
          <div><div class="kpi-label">En Retard</div><div class="kpi-value" id="kpiLate">0 <small>charges</small></div><div class="kpi-sub">Échéance dépassée</div></div>
          <div class="kpi-icon red"><i class="fa-solid fa-triangle-exclamation"></i></div>
        </div>
      </div>
      <div class="kpi-card kpi-orange" style="animation-delay:.10s">
        <div class="kpi-top">
          <div><div class="kpi-label">À payer bientôt</div><div class="kpi-value" id="kpiSoon">0 <small>charges</small></div><div class="kpi-sub">Échéance dans 15 jours</div></div>
          <div class="kpi-icon orange"><i class="fa-solid fa-hourglass-half"></i></div>
        </div>
      </div>
      <div class="kpi-card kpi-orange" style="animation-delay:.15s">
        <div class="kpi-top">
          <div><div class="kpi-label">Partiels</div><div class="kpi-value" id="kpiPartial">0 <small>charges</small></div><div class="kpi-sub">Paiement incomplet</div></div>
          <div class="kpi-icon orange"><i class="fa-solid fa-code-branch"></i></div>
        </div>
      </div>
      <div class="kpi-card kpi-green" style="animation-delay:.20s">
        <div class="kpi-top">
          <div><div class="kpi-label">Payées ce mois</div><div class="kpi-value" id="kpiPaid">0 <small>MAD</small></div><div class="kpi-sub" id="kpiPaidSub">0 charges soldées</div></div>
          <div class="kpi-icon green"><i class="fa-solid fa-circle-check"></i></div>
        </div>
      </div>
    </div>

    <!-- ALERTES -->
    <div class="alert-strip" id="alertStrip">
      <div class="alert-strip-header" onclick="toggleAlerts()">
        <div class="alert-strip-title">
          <i class="fa-solid fa-bell" style="color:var(--orange)"></i>
          Alertes <span id="alertCountLabel"></span>
        </div>
        <button class="alert-strip-toggle" id="alertBtn"><i class="fa-solid fa-chevron-up"></i></button>
      </div>
      <div id="alertBody" class="alert-body"></div>
    </div>

    <!-- FILTER BAR -->
    <div class="filter-bar">
      <div class="filter-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Charge, fournisseur, catégorie…" oninput="renderTable()">
      </div>
      <select class="filter-select" id="fCat" onchange="renderTable()">
        <option value="">Toutes catégories</option>
        <option value="maintenance">Maintenance</option>
        <option value="securite">Sécurité</option>
        <option value="travaux">Travaux</option>
        <option value="energie">Énergie</option>
        <option value="nettoyage">Nettoyage</option>
        <option value="admin">Administration</option>
      </select>
      <select class="filter-select" id="fStatut" onchange="renderTable()">
        <option value="">Tous statuts</option>
        <option value="paid">Payé</option>
        <option value="partial">Partiel</option>
        <option value="late">En retard</option>
        <option value="pending">À payer</option>
        <option value="upcoming">À venir</option>
      </select>
      <select class="filter-select" id="fType" onchange="renderTable()">
        <option value="">Tous types</option>
        <option value="unique">Dépense unique</option>
        <option value="recurrent">Récurrente (fixe)</option>
        <option value="variable">Service variable</option>
      </select>
      <span style="font-size:12.5px;color:var(--text-3);white-space:nowrap" class="d-none d-md-block"><strong id="rowCount">0</strong> échéances</span>
    </div>

    <!-- TABLEAU -->
    <div class="table-card">
      <div class="tcard-header">
        <button class="btn-outline-erp" style="font-size:12px;padding:7px 12px" onclick="exportCSV()">
          <i class="fa-solid fa-download"></i> CSV
        </button>
      </div>
      <div class="table-responsive">
        <table class="erp-table" id="chargesTable">
          <thead>
            <tr>
              <th><input type="checkbox" id="checkAll" style="accent-color:var(--brand);cursor:pointer"></th>
              <th>Charge / Dépense</th>
              <th>Fournisseur</th>
              <th>Catégorie</th>
              <th>Type</th>
              <th>Échéance</th>
              <th>Montant Dû</th>
              <th>Montant Payé</th>
              <th>Reste</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="tbody"></tbody>
        </table>
      </div>
      <div class="table-footer">
        <span style="font-size:12px;color:var(--text-4)"><strong id="rangeInfo">0</strong> résultats</span>
        <div style="display:flex;gap:4px" id="pagination"></div>
      </div>
    </div>

  </div><!-- /erp-content -->
</div><!-- /erp-main -->
</div><!-- /erp-wrapper -->


<!-- ══════════════════════════════════════════════════════════════
  MODAL 1 — PAYER (dépense unique → montant libre)
  RÈGLE : is_unique = true → l'utilisateur saisit le montant librement
══════════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="mPay">
  <div class="modal-panel">
    <div class="modal-head">
      <div>
        <h3 class="modal-title" id="mpTitle">Payer la dépense</h3>
        <p class="modal-sub"  id="mpSub">—</p>
      </div>
      <button class="modal-close" onclick="closeModal('mPay')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <!-- Bandeau type -->
      <div class="info-box orange" id="mpInfoBox">
        <i class="fa-solid fa-info-circle"></i>
        <p id="mpInfoText">—</p>
      </div>
      <!-- Recap -->
      <div class="pay-recap">
        <div class="pr-col"><div class="pr-lbl">Montant Dû</div><div class="pr-val due" id="mpDue">—</div></div>
        <div class="pr-col"><div class="pr-lbl">Déjà Payé</div><div class="pr-val paid" id="mpAlready">0 MAD</div></div>
        <div class="pr-col"><div class="pr-lbl">Reste</div><div class="pr-val rest" id="mpRest">—</div></div>
      </div>
      <!-- Formulaire -->
      <div class="form-section-lbl"><i class="fa-solid fa-coins"></i> Paiement</div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-lbl">Montant payé <span class="req">*</span></label>
          <div class="pfx-wrap">
            <span class="pfx">MAD</span>
            <input type="number" class="form-ctrl" id="mpMontant" min="0" step="0.01" placeholder="0.00" oninput="updateLive()">
          </div>
          <div id="mpHint" class="form-hint"></div>
          <!-- Chips -->
          <div id="mpChips" style="display:flex;gap:5px;flex-wrap:wrap;margin-top:6px"></div>
        </div>
        <div class="form-group">
          <label class="form-lbl">Date de paiement <span class="req">*</span></label>
          <input type="date" class="form-ctrl" id="mpDate">
        </div>
      </div>
      <!-- Live -->
      <div class="pay-live" id="payLiveBox">
        <div class="pl-head"><span class="pl-lbl">Progression</span><span class="pl-pct" id="plPct">0%</span></div>
        <div class="pl-bar"><div class="pl-fill" id="plFill" style="width:0%"></div></div>
        <div class="pl-foot"><span class="pl-lbl">Reste après ce paiement</span><span class="pl-rest" id="plRest">—</span></div>
      </div>
      <div class="form-section-lbl" style="margin-top:4px"><i class="fa-solid fa-wallet"></i> Mode & Référence</div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-lbl">Mode</label>
          <select class="form-ctrl" id="mpMode">
            <option value="virement">Virement bancaire</option>
            <option value="cheque">Chèque</option>
            <option value="especes">Espèces</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-lbl">Référence</label>
          <input type="text" class="form-ctrl" id="mpRef" placeholder="Nº chèque / virement">
        </div>
      </div>
      <div class="form-section-lbl"><i class="fa-solid fa-paperclip"></i> Justificatif</div>
      <div class="upload-zone-sm" onclick="document.getElementById('mpFile').click()">
        <input type="file" id="mpFile" accept=".pdf,.jpg,.png">
        <i class="fa-solid fa-cloud-arrow-up"></i><p>Joindre facture ou reçu</p><span>PDF, image — <b>max 10 Mo</b></span>
      </div>
    </div>
    <div class="modal-foot">
      <div class="mf-l"><button class="btn-outline-erp" onclick="closeModal('mPay')">Annuler</button></div>
      <div class="mf-r">
        <button class="btn-primary-erp" id="mpBtn" onclick="submitPay()">
          <i class="fa-solid fa-circle-check"></i> Valider
        </button>
      </div>
    </div>
  </div>
</div>


<!-- ══════════════════════════════════════════════════════════════
  MODAL 2 — MODIFIER CHARGE
  RÈGLE : jamais modifier les échéances payées, recalculer futures
══════════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="mEdit">
  <div class="modal-panel modal-lg">
    <div class="modal-head">
      <div>
        <h3 class="modal-title" id="meTitle">Modifier la charge</h3>
        <p class="modal-sub">Les échéances payées sont protégées — seules les futures seront recalculées</p>
      </div>
      <button class="modal-close" onclick="closeModal('mEdit')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <div class="info-box orange">
        <i class="fa-solid fa-shield-halved"></i>
        <p><strong>Protection des données :</strong> les échéances déjà payées ne seront jamais touchées. Seules les échéances futures non payées seront recalculées avec les nouvelles valeurs.</p>
      </div>
      <div class="form-section-lbl"><i class="fa-solid fa-info-circle"></i> Informations modifiables</div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-lbl">Nom de la charge <span class="req">*</span></label>
          <input type="text" class="form-ctrl" id="meNom">
        </div>
        <div class="form-group">
          <label class="form-lbl">Fournisseur</label>
          <input type="text" class="form-ctrl" id="meFournisseur">
        </div>
        <div class="form-group">
          <label class="form-lbl">Catégorie</label>
          <select class="form-ctrl" id="meCat">
            <option value="maintenance">Maintenance</option>
            <option value="securite">Sécurité</option>
            <option value="travaux">Travaux</option>
            <option value="energie">Énergie</option>
            <option value="nettoyage">Nettoyage</option>
            <option value="admin">Administration</option>
          </select>
        </div>
        <div class="form-group" id="meFreqGroup">
          <label class="form-lbl">Fréquence</label>
          <select class="form-ctrl" id="meFreq">
            <option value="mensuel">Mensuelle</option>
            <option value="trimestriel">Trimestrielle</option>
          </select>
          <div class="form-hint"><i class="fa-solid fa-info-circle"></i> La fréquence ne s'applique pas aux dépenses uniques ni aux services variables</div>
        </div>
      </div>
      <!-- Montant : affiché selon type -->
      <div class="form-section-lbl"><i class="fa-solid fa-coins"></i> Montant</div>
      <div id="meMontantSection">
        <div class="form-grid">
          <div class="form-group">
            <label class="form-lbl" id="meMontantLabel">Nouveau montant (MAD) <span class="req">*</span></label>
            <div class="pfx-wrap">
              <span class="pfx">MAD</span>
              <input type="number" class="form-ctrl" id="meMontant" placeholder="0.00">
            </div>
            <div class="form-hint" id="meMontantHint"></div>
          </div>
          <div class="form-group">
            <label class="form-lbl">Date d'effet <span class="req">*</span></label>
            <input type="date" class="form-ctrl" id="meDateEffet">
          </div>
        </div>
      </div>
      <!-- Impact -->
      <div style="background:#f8fafc;border:1px solid var(--border);border-radius:var(--r-md);padding:11px 13px;margin-top:4px">
        <div style="font-size:11px;font-weight:700;color:var(--text-4);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px"><i class="fa-solid fa-eye" style="color:var(--brand);margin-right:5px"></i>Impact</div>
        <div style="display:flex;flex-direction:column;gap:5px">
          <div style="display:flex;justify-content:space-between;font-size:12.5px">
            <span style="color:var(--text-3)"><i class="fa-solid fa-lock" style="color:var(--green-t);margin-right:5px;font-size:11px"></i>Échéances payées (protégées)</span>
            <strong id="meCountPaid" style="color:var(--green-t)">—</strong>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:12.5px">
            <span style="color:var(--text-3)"><i class="fa-solid fa-sync" style="color:var(--brand);margin-right:5px;font-size:11px"></i>Futures à recalculer</span>
            <strong id="meCountFuture" style="color:var(--brand)">—</strong>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-foot">
      <div class="mf-l"><button class="btn-outline-erp" onclick="closeModal('mEdit')">Annuler</button></div>
      <div class="mf-r">
        <button class="btn-primary-erp" onclick="submitEdit()"><i class="fa-solid fa-floppy-disk"></i> Sauvegarder</button>
      </div>
    </div>
  </div>
</div>


<!-- ══════════════════════════════════════════════════════════════
  MODAL 3 — AJOUTER DÉPENSE UNIQUE
  RÈGLE : montant libre, pas de récurrence
══════════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="mAddDepense">
  <div class="modal-panel">
    <div class="modal-head">
      <div>
        <h3 class="modal-title">Nouvelle Dépense Unique</h3>
        <p class="modal-sub">Facture ponctuelle — le montant peut être saisi librement au moment du paiement</p>
      </div>
      <button class="modal-close" onclick="closeModal('mAddDepense')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <div class="info-box blue">
        <i class="fa-solid fa-circle-info"></i>
        <p>Une <strong>dépense unique</strong> permet de saisir librement le montant payé (partiel ou complet). Idéal pour les factures imprévues ou les travaux ponctuels.</p>
      </div>
      <div class="form-grid">
        <div class="form-group form-full"><label class="form-lbl">Titre <span class="req">*</span></label><input type="text" class="form-ctrl" id="adNom" placeholder="ex: Réparation toiture"></div>
        <div class="form-group"><label class="form-lbl">Catégorie <span class="req">*</span></label>
          <select class="form-ctrl" id="adCat"><option value="travaux">Travaux</option><option value="maintenance">Maintenance</option><option value="securite">Sécurité</option><option value="energie">Énergie</option><option value="nettoyage">Nettoyage</option><option value="admin">Administration</option></select>
        </div>
        <div class="form-group"><label class="form-lbl">Fournisseur</label><input type="text" class="form-ctrl" id="adFournisseur" placeholder="Nom du prestataire"></div>
        <div class="form-group"><label class="form-lbl">Montant estimé (MAD) <span class="req">*</span></label>
          <div class="pfx-wrap"><span class="pfx">MAD</span><input type="number" class="form-ctrl" id="adMontant" placeholder="0.00"></div>
        </div>
        <div class="form-group"><label class="form-lbl">Date d'échéance <span class="req">*</span></label><input type="date" class="form-ctrl" id="adDate"></div>
        <div class="form-group form-full"><label class="form-lbl">Description</label><textarea class="form-ctrl" rows="2" style="resize:vertical" id="adDesc" placeholder="Détails…"></textarea></div>
      </div>
    </div>
    <div class="modal-foot">
      <div class="mf-l"><button class="btn-outline-erp" onclick="closeModal('mAddDepense')">Annuler</button></div>
      <div class="mf-r">
        <button class="btn-primary-erp" onclick="addDepense()"><i class="fa-solid fa-plus"></i> Créer</button>
      </div>
    </div>
  </div>
</div>


<!-- ══════════════════════════════════════════════════════════════
  MODAL 4 — AJOUTER CHARGE RÉCURRENTE (montant fixe)
  RÈGLE : montant fixe, ne peut pas être modifié au paiement
══════════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="mAddCharge">
  <div class="modal-panel">
    <div class="modal-head">
      <div>
        <h3 class="modal-title">Nouvelle Charge Récurrente</h3>
        <p class="modal-sub">Mensuelle ou trimestrielle — montant fixe, non modifiable au paiement</p>
      </div>
      <button class="modal-close" onclick="closeModal('mAddCharge')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <div class="info-box orange">
        <i class="fa-solid fa-lock"></i>
        <p>Le montant d'une charge récurrente est <strong>fixe</strong>. Il ne peut pas être modifié au moment du paiement (uniquement via "Modifier la charge"). Utilisez <em>Service variable</em> si le montant change chaque mois.</p>
      </div>
      <div class="form-grid">
        <div class="form-group form-full"><label class="form-lbl">Nom <span class="req">*</span></label><input type="text" class="form-ctrl" id="acNom" placeholder="ex: Gardiennage mensuel"></div>
        <div class="form-group"><label class="form-lbl">Catégorie <span class="req">*</span></label>
          <select class="form-ctrl" id="acCat"><option value="securite">Sécurité</option><option value="maintenance">Maintenance</option><option value="nettoyage">Nettoyage</option><option value="admin">Administration</option></select>
        </div>
        <div class="form-group"><label class="form-lbl">Fournisseur</label><input type="text" class="form-ctrl" id="acFournisseur" placeholder="SecurGuard, CleanPro…"></div>
        <div class="form-group"><label class="form-lbl">Fréquence <span class="req">*</span></label>
          <select class="form-ctrl" id="acFreq"><option value="mensuel">Mensuelle</option><option value="trimestriel">Trimestrielle</option></select>
        </div>
        <div class="form-group"><label class="form-lbl">Montant fixe (MAD) <span class="req">*</span></label>
          <div class="pfx-wrap"><span class="pfx">MAD</span><input type="number" class="form-ctrl" id="acMontant" placeholder="0.00"></div>
        </div>
        <div class="form-group"><label class="form-lbl">1ère échéance <span class="req">*</span></label><input type="date" class="form-ctrl" id="acDate"></div>
        <div class="form-group"><label class="form-lbl">Dernière échéance</label><input type="date" class="form-ctrl" id="acDateFin"></div>
      </div>
    </div>
    <div class="modal-foot">
      <div class="mf-l"><button class="btn-outline-erp" onclick="closeModal('mAddCharge')">Annuler</button></div>
      <div class="mf-r">
        <button class="btn-primary-erp" onclick="addCharge()"><i class="fa-solid fa-rotate"></i> Créer & générer les échéances</button>
      </div>
    </div>
  </div>
</div>


<!-- ══════════════════════════════════════════════════════════════
  MODAL 5 — SERVICE VARIABLE (eau, électricité)
  ASTUCE : le montant de chaque échéance est saisi MANUELLEMENT
  chaque mois au moment de relever la facture — pas de montant fixe
══════════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="mAddService">
  <div class="modal-panel">
    <div class="modal-head">
      <div>
        <h3 class="modal-title">Nouveau Service Variable</h3>
        <p class="modal-sub">Eau, électricité — montant réel saisi à chaque facturation</p>
      </div>
      <button class="modal-close" onclick="closeModal('mAddService')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <!-- ASTUCE expliquée à l'utilisateur -->
      <div class="info-box teal">
        <i class="fa-solid fa-lightbulb"></i>
        <p>
          <strong>Astuce pour les services à montant variable (eau, électricité) :</strong><br>
          Ce type de charge est <em>mensuel mais sans montant fixe</em>. Chaque mois, une <strong>échéance sans montant</strong> est créée automatiquement. Lorsque vous recevez la facture, vous saisissez le <strong>montant réel de ce mois</strong> et vous payez directement depuis le tableau.
        </p>
      </div>
      <div class="form-grid">
        <div class="form-group form-full"><label class="form-lbl">Nom du service <span class="req">*</span></label><input type="text" class="form-ctrl" id="asNom" placeholder="ex: Facture SRM eau, Facture ONEE élec."></div>
        <div class="form-group"><label class="form-lbl">Fournisseur</label><input type="text" class="form-ctrl" id="asFournisseur" placeholder="SRM, ONEE, Amendis…"></div>
        <div class="form-group"><label class="form-lbl">Catégorie</label>
          <select class="form-ctrl" id="asCat"><option value="energie">Énergie</option><option value="maintenance">Maintenance</option></select>
        </div>
        <div class="form-group"><label class="form-lbl">Montant estimé / mois (MAD)</label>
          <div class="pfx-wrap"><span class="pfx">MAD</span><input type="number" class="form-ctrl" id="asMontantEstim" placeholder="Estimation (optionnel)"></div>
          <div class="form-hint"><i class="fa-solid fa-info-circle"></i> Utilisé uniquement pour les KPIs — remplacé par le montant réel à chaque facture</div>
        </div>
        <div class="form-group"><label class="form-lbl">Début du service <span class="req">*</span></label><input type="date" class="form-ctrl" id="asDate"></div>
      </div>
    </div>
    <div class="modal-foot">
      <div class="mf-l"><button class="btn-outline-erp" onclick="closeModal('mAddService')">Annuler</button></div>
      <div class="mf-r">
        <button class="btn-primary-erp" onclick="addService()"><i class="fa-solid fa-bolt"></i> Créer le service</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
/* ══════════════════════════════════════════════════════════════════
   DONNÉES STATIQUES — à remplacer par fetch('/api/depenses_residence')
   Structure : table depenses_residence + champ 'type' pour la logique
   ══════════════════════════════════════════════════════════════════
   TYPES :
   - 'unique'    → dépense ponctuelle, montant libre au paiement
   - 'recurrent' → charge fixe mensuelle/trimestrielle, montant verrouillé
   - 'variable'  → service (eau/élec), montant saisi à chaque échéance
*/
const TODAY = new Date('2026-04-09');
let nextId = 100;

let CHARGES = [
  /* Récurrentes (montant FIXE — non modifiable au paiement) */
  {id:1, nom:'Contrat ascenseur Kone', fournisseur:'Kone Maroc',     cat:'maintenance', type:'recurrent', freq:'mensuel',      echeance:'2026-04-01', montant:3200,  paye:0,    montant_is_zero:false},
  {id:2, nom:'Gardiennage mensuel',    fournisseur:'SecurGuard SA',   cat:'securite',    type:'recurrent', freq:'mensuel',      echeance:'2026-04-14', montant:4500,  paye:0,    montant_is_zero:false},
  {id:3, nom:'Nettoyage parties comm.',fournisseur:'CleanPro',        cat:'nettoyage',   type:'recurrent', freq:'mensuel',      echeance:'2026-04-08', montant:1800,  paye:450,  montant_is_zero:false},
  {id:4, nom:'Entretien pompes eau',   fournisseur:'AquaTech',        cat:'maintenance', type:'recurrent', freq:'trimestriel',  echeance:'2026-04-20', montant:2800,  paye:0,    montant_is_zero:false},
  {id:5, nom:'Frais gestion syndic',   fournisseur:'Cabinet Idrissi', cat:'admin',       type:'recurrent', freq:'mensuel',      echeance:'2026-04-25', montant:1500,  paye:0,    montant_is_zero:false},
  {id:6, nom:'Contrat ascenseur Kone', fournisseur:'Kone Maroc',     cat:'maintenance', type:'recurrent', freq:'mensuel',      echeance:'2026-03-01', montant:3200,  paye:3200, montant_is_zero:false},
  {id:7, nom:'Gardiennage mensuel',    fournisseur:'SecurGuard SA',   cat:'securite',    type:'recurrent', freq:'mensuel',      echeance:'2026-03-14', montant:4500,  paye:4500, montant_is_zero:false},
  /* Services variables (eau/électricité — montant réel saisi à l'échéance) */
  {id:8, nom:'Facture SRM eau Q1',     fournisseur:'SRM Marrakech',   cat:'energie',     type:'variable',  freq:'trimestriel',  echeance:'2026-04-06', montant:2140,  paye:0,    montant_is_zero:false},
  {id:9, nom:'Facture ONEE élec. Avr.',fournisseur:'ONEE Maroc',      cat:'energie',     type:'variable',  freq:'mensuel',      echeance:'2026-04-17', montant:0,     paye:0,    montant_is_zero:true},
  {id:10,nom:'Facture ONEE élec. Mars',fournisseur:'ONEE Maroc',      cat:'energie',     type:'variable',  freq:'mensuel',      echeance:'2026-03-17', montant:1180,  paye:1180, montant_is_zero:false},
  /* Dépenses uniques (montant LIBRE au paiement — partiel possible) */
  {id:11,nom:'Réparation toiture',     fournisseur:'BTP Maroc SARL',  cat:'travaux',     type:'unique',    freq:null,           echeance:'2026-03-15', montant:8500,  paye:8500, montant_is_zero:false},
  {id:12,nom:'Assurance immeuble 2026',fournisseur:'Wafa Assurance',  cat:'admin',       type:'unique',    freq:null,           echeance:'2026-01-02', montant:6200,  paye:6200, montant_is_zero:false},
  {id:13,nom:'Peinture couloirs',      fournisseur:'ColorPro',        cat:'travaux',     type:'unique',    freq:null,           echeance:'2026-03-28', montant:5600,  paye:2800, montant_is_zero:false},
  {id:14,nom:'Remplacement serrures',  fournisseur:'LockTech Maroc',  cat:'securite',    type:'unique',    freq:null,           echeance:'2026-04-22', montant:3400,  paye:0,    montant_is_zero:false},
];

/* ══ LOGIQUE STATUT ══════════════════════════════════════════════ */
function getStatut(c) {
  /* Service sans montant encore saisi → en attente de facture */
  if (c.type === 'variable' && c.montant_is_zero) return 'no_montant';
  const ratio = c.montant > 0 ? c.paye / c.montant : 0;
  const diff  = Math.round((new Date(c.echeance) - TODAY) / 86400000);
  if (ratio >= 1)    return 'paid';
  if (ratio > 0)     return 'partial';
  if (diff < 0)      return 'late';
  if (diff <= 15)    return 'pending';
  return 'upcoming';
}

/* ══ CONFIG STATUT ═══════════════════════════════════════════════ */
const SCFG = {
  paid:       {label:'Payé',         icon:'fa-circle-check',          cls:'paid'},
  partial:    {label:'Partiel',      icon:'fa-code-branch',            cls:'partial'},
  late:       {label:'En retard',    icon:'fa-triangle-exclamation',   cls:'late'},
  pending:    {label:'À payer',      icon:'fa-hourglass-half',         cls:'pending'},
  upcoming:   {label:'À venir',      icon:'fa-calendar-check',         cls:'upcoming'},
  no_montant: {label:'Facture manquante', icon:'fa-file-invoice',      cls:'pending'},
};

/* ══ CONFIG CATÉGORIE ════════════════════════════════════════════ */
const CCFG = {
  maintenance:{bg:'var(--blue-bg)',  color:'var(--blue-t)',  icon:'fa-wrench'},
  securite:   {bg:'var(--orange-bg)',color:'var(--orange-t)',icon:'fa-shield-halved'},
  travaux:    {bg:'var(--red-bg)',   color:'var(--red-t)',   icon:'fa-hard-hat'},
  energie:    {bg:'var(--amber-bg)', color:'var(--amber-t)', icon:'fa-bolt'},
  nettoyage:  {bg:'var(--teal-bg)',  color:'var(--teal-t)',  icon:'fa-broom'},
  admin:      {bg:'var(--gray-bg)',  color:'var(--gray-t)',  icon:'fa-file-invoice'},
};

/* ══ HELPERS ═════════════════════════════════════════════════════ */
function fmt(n)    { return n === 0 ? '0 MAD' : Number(n).toLocaleString('fr-FR') + ' MAD'; }
function fmtDate(d){ return new Date(d).toLocaleDateString('fr-FR',{day:'2-digit',month:'2-digit',year:'numeric'}); }
function daysLate(d){ return Math.round((TODAY - new Date(d)) / 86400000); }
function daysLeft(d){ return Math.round((new Date(d) - TODAY) / 86400000); }

/* ══ RENDU D'UNE LIGNE ══════════════════════════════════════════ */
function renderRow(c) {
  const s    = getStatut(c);
  const rest = Math.max(0, c.montant - c.paye);
  const pct  = c.montant > 0 ? Math.min(100, Math.round(c.paye / c.montant * 100)) : 0;
  const cc   = CCFG[c.cat] || CCFG.admin;
  const sc   = SCFG[s];
  const fill = pct >= 100 ? 'full' : pct > 0 ? 'partial' : 'none';
  const trCls= s==='late' ? 'tr-late' : s==='partial' ? 'tr-partial' : (s==='pending'||s==='no_montant') ? 'tr-soon' : '';

  /* Badge type */
  const typeLabel  = c.type==='unique' ? 'Dépense unique' : c.type==='variable' ? 'Service variable' : `Récurrente (${c.freq})`;
  const typeCls    = c.type==='variable' ? 'type-badge variable' : 'type-badge';
  const typeIcon   = c.type==='unique' ? 'fa-receipt' : c.type==='variable' ? 'fa-bolt' : 'fa-rotate';

  /* Date avec info contextuelle */
  let dateHtml = `<span class="date-text">${fmtDate(c.echeance)}</span>`;
  if (s==='late')       dateHtml += `<br><small style="color:var(--red-t);font-weight:600;font-size:10.5px"><i class="fa-solid fa-clock"></i> +${daysLate(c.echeance)} j</small>`;
  if (s==='pending')    dateHtml += `<br><small style="color:var(--orange-t);font-weight:600;font-size:10.5px"><i class="fa-solid fa-bell"></i> dans ${daysLeft(c.echeance)} j</small>`;
  if (s==='no_montant') dateHtml += `<br><small style="color:var(--teal-t);font-weight:600;font-size:10.5px"><i class="fa-solid fa-file-invoice"></i> Saisir facture</small>`;

  /* Montant dû */
  const montantHtml = c.montant_is_zero
    ? `<span class="amt-due" style="color:var(--teal-t)">— (à saisir)</span>`
    : `<span class="amt-due">${fmt(c.montant)}</span>`;

  /* ═══ LOGIQUE ACTIONS CONTEXTUELLES ═══════════════════════════
     RÈGLE 1 : dépense unique (type='unique')
       → Payer/Compléter avec montant libre (partiel ok)
     RÈGLE 2 : charge récurrente (type='recurrent')
       → Payer UNIQUEMENT le montant fixe exact (pas de partiel)
       → Bouton désactivé si déjà payé
     RÈGLE 3 : service variable (type='variable')
       → Si pas de montant (no_montant) → bouton "Saisir facture" qui ouvre
         le modal avec champ montant activé
       → Si montant saisi → comportement normal
  */
  let actHtml = '';

  if (s === 'paid') {
    // Charge soldée → juste voir PDF 
    actHtml = `
      <button class="ra-btn ra-pdf" title="Voir facture" onclick="viewPDF(${c.id})"><i class="fa-solid fa-file-pdf"></i></button>`;

  } else if (c.type === 'unique') {
    // Dépense unique → montant LIBRE, partiel autorisé
    if (s === 'partial') {
      actHtml = `
        <button class="btn-pay-pill pill-orange" onclick="openPayModal(${c.id})">
          <i class="fa-solid fa-plus"></i> Compléter
        </button>
        <button class="ra-btn ra-pdf"  title="Voir facture" onclick="viewPDF(${c.id})"><i class="fa-solid fa-file-pdf"></i></button>
        <button class="ra-btn ra-edit" title="Modifier" onclick="openEditModal(${c.id})"><i class="fa-solid fa-pen"></i></button>
        <button class="ra-btn ra-del"  title="Supprimer" onclick="doDelete(${c.id})"><i class="fa-solid fa-trash"></i></button>`;
    } else {
      actHtml = `
        <button class="btn-pay-pill" onclick="openPayModal(${c.id})">
          <i class="fa-solid fa-cash-register"></i> Payer
        </button>
        <button class="ra-btn ra-edit" title="Modifier" onclick="openEditModal(${c.id})"><i class="fa-solid fa-pen"></i></button>
        <button class="ra-btn ra-del"  title="Supprimer" onclick="doDelete(${c.id})"><i class="fa-solid fa-trash"></i></button>`;
    }

  } else if (c.type === 'recurrent') {
    // Charge récurrente → montant FIXE, pas de partiel
    actHtml = `
      <button class="btn-pay-pill" onclick="openPayModal(${c.id})">
        <i class="fa-solid fa-cash-register"></i> Payer
      </button>
      <button class="ra-btn ra-edit" title="Modifier" onclick="openEditModal(${c.id})"><i class="fa-solid fa-pen"></i></button>
      <button class="ra-btn ra-del"  title="Supprimer" onclick="doDelete(${c.id})"><i class="fa-solid fa-trash"></i></button>`;

  } else if (c.type === 'variable') {
    // Service variable
    if (s === 'no_montant') {
      // Pas encore de montant → bouton "Saisir facture"
      actHtml = `
        <button class="btn-pay-pill" style="background:linear-gradient(135deg,var(--teal-t),var(--teal))" onclick="openPayModal(${c.id})">
          <i class="fa-solid fa-file-invoice"></i> Saisir facture
        </button>
        <button class="ra-btn ra-del" title="Supprimer" onclick="doDelete(${c.id})"><i class="fa-solid fa-trash"></i></button>`;
    } else if (s === 'partial') {
      actHtml = `
        <button class="btn-pay-pill pill-orange" onclick="openPayModal(${c.id})">
          <i class="fa-solid fa-plus"></i> Compléter
        </button>
        <button class="ra-btn ra-pdf"  title="Facture" onclick="viewPDF(${c.id})"><i class="fa-solid fa-file-pdf"></i></button>
        <button class="ra-btn ra-edit" title="Modifier" onclick="openEditModal(${c.id})"><i class="fa-solid fa-pen"></i></button>`;
    } else {
      actHtml = `
        <button class="btn-pay-pill" onclick="openPayModal(${c.id})">
          <i class="fa-solid fa-cash-register"></i> Payer
        </button>
        <button class="ra-btn ra-edit" title="Modifier" onclick="openEditModal(${c.id})"><i class="fa-solid fa-pen"></i></button>
        <button class="ra-btn ra-del"  title="Supprimer" onclick="doDelete(${c.id})"><i class="fa-solid fa-trash"></i></button>`;
    }
  }

  return `
    <tr class="${trCls}" data-statut="${s}" data-cat="${c.cat}" data-type="${c.type}" data-id="${c.id}">
      <td><input type="checkbox" class="row-chk" style="accent-color:var(--brand);cursor:pointer"></td>
      <td>
        <div class="charge-cell">
          <div class="charge-icon-wrap" style="background:${cc.bg};color:${cc.color}">
            <i class="fa-solid ${cc.icon}"></i>
          </div>
          <div>
            <span class="charge-name">${c.nom}</span>
            <span class="charge-fq"><span class="${typeCls}"><i class="fa-solid ${typeIcon}"></i> ${typeLabel}</span></span>
          </div>
        </div>
      </td>
      <td style="font-size:13px;color:var(--text-2);font-weight:500">${c.fournisseur || '—'}</td>
      <td><span class="cat-badge ${c.cat}">${c.cat.charAt(0).toUpperCase()+c.cat.slice(1)}</span></td>
      <td>
        ${c.type==='unique'
          ? '<span class="type-badge" style="background:rgba(239,68,68,.07);color:var(--red-t);border-color:rgba(239,68,68,.2)"><i class="fa-solid fa-receipt"></i> Unique</span>'
          : c.type==='variable'
          ? '<span class="type-badge variable"><i class="fa-solid fa-bolt"></i> Variable</span>'
          : `<span class="type-badge"><i class="fa-solid fa-rotate"></i> ${c.freq||''}</span>`}
      </td>
      <td>${dateHtml}</td>
      <td>${montantHtml}</td>
      <td>
        ${c.montant_is_zero
          ? `<span class="amt-paid" style="color:var(--text-4);font-family:var(--font-b);font-weight:400">—</span>`
          : `<span class="amt-paid" style="${c.paye===0?'color:var(--text-4);font-family:var(--font-b);font-weight:400':''}">${fmt(c.paye)}</span>
             <div class="pay-bar"><div class="pay-bar-fill ${fill}" style="width:${pct}%"></div></div>`}
      </td>
      <td>${c.montant_is_zero ? '<span class="amt-rest zero">—</span>' : rest>0 ? `<span class="amt-rest">${fmt(rest)}</span>` : `<span class="amt-rest zero">— MAD</span>`}</td>
      <td><span class="pay-badge ${sc.cls}"><i class="fa-solid ${sc.icon}" style="font-size:10px"></i>${sc.label}</span></td>
      <td><div style="display:flex;align-items:center;gap:3px">${actHtml}</div></td>
    </tr>`;
}

/* ══ RENDU TABLE ════════════════════════════════════════════════ */
function renderTable() {
  const q  = document.getElementById('searchInput').value.trim().toLowerCase();
  const ct = document.getElementById('fCat').value;
  const st = document.getElementById('fStatut').value;
  const tp = document.getElementById('fType').value;

  const data = CHARGES.filter(c => {
    const s = getStatut(c);
    return (!q  || [c.nom,c.fournisseur,c.cat,c.type].join(' ').toLowerCase().includes(q))
        && (!ct || c.cat  === ct)
        && (!st || s      === st)
        && (!tp || c.type === tp);
  });

  document.getElementById('tbody').innerHTML     = data.map(renderRow).join('');
  document.getElementById('rowCount').textContent = data.length;
  document.getElementById('rangeInfo').textContent = `${data.length} résultat${data.length!==1?'s':''}`;
  refreshKPIs();
  renderAlerts();
}

/* ══ KPIs ═══════════════════════════════════════════════════════ */
function refreshKPIs() {
  const late    = CHARGES.filter(c => getStatut(c)==='late').length;
  const soon    = CHARGES.filter(c => getStatut(c)==='pending'||getStatut(c)==='no_montant').length;
  const partial = CHARGES.filter(c => getStatut(c)==='partial').length;
  const paid    = CHARGES.filter(c => getStatut(c)==='paid');
  const paidAmt = paid.reduce((s,c)=>s+c.paye,0);
  document.getElementById('kpiLate').innerHTML    = `${late} <small>charges</small>`;
  document.getElementById('kpiSoon').innerHTML    = `${soon} <small>charges</small>`;
  document.getElementById('kpiPartial').innerHTML = `${partial} <small>charges</small>`;
  document.getElementById('kpiPaid').innerHTML    = `${paidAmt.toLocaleString('fr-FR')} <small>MAD</small>`;
  document.getElementById('kpiPaidSub').textContent = `${paid.length} charges soldées`;
}

/* ══ ALERTES ════════════════════════════════════════════════════ */
function renderAlerts() {
  const late    = CHARGES.filter(c => getStatut(c)==='late');
  const nm      = CHARGES.filter(c => getStatut(c)==='no_montant');
  const soon    = CHARGES.filter(c => getStatut(c)==='pending');
  const partial = CHARGES.filter(c => getStatut(c)==='partial');
  const paid    = CHARGES.filter(c => getStatut(c)==='paid').slice(0,2);
  const total   = late.length + nm.length + soon.length + partial.length + paid.length;

  document.getElementById('alertCountLabel').textContent = `— ${total} notification${total!==1?'s':''}`;

  let html = '';
  late.forEach(c => { html += `<div class="alert-row late"><div class="alert-icon-sm"><i class="fa-solid fa-fire"></i></div><div class="alert-row-text"><strong>${c.nom}</strong> — En retard de ${daysLate(c.echeance)} jours</div><span class="alert-row-amount">${fmt(c.montant)}</span><button class="btn-pay-pill" style="margin-left:8px;height:26px;font-size:11px" onclick="openPayModal(${c.id})"><i class="fa-solid fa-cash-register"></i></button></div>`; });
  nm.forEach(c => { html += `<div class="alert-row info"><div class="alert-icon-sm"><i class="fa-solid fa-file-invoice"></i></div><div class="alert-row-text"><strong>${c.nom}</strong> — Facture à saisir (${fmtDate(c.echeance)})</div><span class="alert-row-amount" style="color:var(--teal-t)">Montant inconnu</span><button class="btn-pay-pill" style="background:linear-gradient(135deg,var(--teal-t),var(--teal));margin-left:8px;height:26px;font-size:11px" onclick="openPayModal(${c.id})"><i class="fa-solid fa-file-invoice"></i></button></div>`; });
  partial.forEach(c => { html += `<div class="alert-row soon"><div class="alert-icon-sm"><i class="fa-solid fa-code-branch"></i></div><div class="alert-row-text"><strong>${c.nom}</strong> — Partiel, reste ${fmt(c.montant-c.paye)}</div><span class="alert-row-amount">${fmt(c.montant-c.paye)}</span><button class="btn-pay-pill pill-orange" style="background:linear-gradient(135deg,var(--teal-t),var(--teal));margin-left:8px;height:26px;font-size:11px" onclick="openPayModal(${c.id})"><i class="fa-solid fa-plus"></i></button></div>`; });
  soon.forEach(c => { html += `<div class="alert-row soon"><div class="alert-icon-sm"><i class="fa-solid fa-clock"></i></div><div class="alert-row-text"><strong>${c.nom}</strong> — Échéance dans ${daysLeft(c.echeance)} jours</div><span class="alert-row-amount">${fmt(c.montant)}</span></div>`; });
  paid.forEach(c => { html += `<div class="alert-row ok"><div class="alert-icon-sm"><i class="fa-solid fa-circle-check"></i></div><div class="alert-row-text"><strong>${c.nom}</strong> — Payé le ${fmtDate(c.echeance)}</div><span class="alert-row-amount">${fmt(c.paye)}</span></div>`; });

  document.getElementById('alertBody').innerHTML = html || '<div style="font-size:13px;color:var(--text-4);padding:8px 0">Aucune alerte pour le moment.</div>';
}

let alertOpen = true;
function toggleAlerts() {
  alertOpen = !alertOpen;
  document.getElementById('alertBody').style.display = alertOpen ? '' : 'none';
  document.getElementById('alertBtn').innerHTML = alertOpen ? '<i class="fa-solid fa-chevron-up"></i>' : '<i class="fa-solid fa-chevron-down"></i>';
}

/* ══ MODAL PAIEMENT ══════════════════════════════════════════════
   Adapte le comportement selon le TYPE de charge
*/
let _payCtx = {};
function openPayModal(id) {
  const c = CHARGES.find(x => x.id === id);
  if (!c) return;
  const rest = c.montant - c.paye;
  _payCtx = { ...c };

  const isVariable   = c.type === 'variable';
  const isUnique     = c.type === 'unique';
  const isRecurrent  = c.type === 'recurrent';
  const noMontant    = c.montant_is_zero;

  /* Titre */
  let title = 'Payer la dépense';
  if (c.paye > 0 && c.paye < c.montant) title = 'Compléter le paiement';
  if (noMontant) title = 'Saisir facture & Payer';
  document.getElementById('mpTitle').textContent = title;
  document.getElementById('mpSub').textContent   = `${c.nom} · ${c.fournisseur}`;

  /* Info box selon type */
  const infoBox  = document.getElementById('mpInfoBox');
  const infoText = document.getElementById('mpInfoText');
  const mpInput  = document.getElementById('mpMontant');

  if (isRecurrent) {
    infoBox.className  = 'info-box orange';
    infoText.innerHTML = `<strong>Charge récurrente — montant fixe :</strong> Le montant est verrouillé à <strong>${fmt(c.montant)}</strong>. Le paiement partiel n'est pas autorisé pour les charges à montant fixe.`;
    mpInput.value   = c.montant;
    mpInput.disabled = true;
    mpInput.style.opacity = '.6';
  } else if (isVariable && noMontant) {
    infoBox.className  = 'info-box teal';
    infoText.innerHTML = `<strong>Service variable :</strong> Saisissez le <strong>montant réel de cette facture</strong>, puis procédez au paiement. Ce montant sera enregistré pour cette échéance.`;
    mpInput.value    = '';
    mpInput.disabled = false;
    mpInput.style.opacity = '1';
  } else if (isUnique) {
    infoBox.className  = 'info-box blue';
    infoText.innerHTML = `<strong>Dépense unique :</strong> Vous pouvez payer un montant partiel. Le reste restera en attente jusqu'au solde complet.`;
    mpInput.value    = rest > 0 ? rest : '';
    mpInput.disabled = false;
    mpInput.style.opacity = '1';
  } else {
    infoBox.className  = 'info-box blue';
    infoText.innerHTML = `Paiement pour <strong>${c.nom}</strong>.`;
    mpInput.value    = rest > 0 ? rest : '';
    mpInput.disabled = false;
    mpInput.style.opacity = '1';
  }

  /* Recap */
  document.getElementById('mpDue').textContent    = noMontant ? '—' : fmt(c.montant);
  document.getElementById('mpAlready').textContent = fmt(c.paye);
  document.getElementById('mpRest').textContent   = noMontant ? '—' : fmt(rest);

  /* Hint sous le champ montant */
  const hint = document.getElementById('mpHint');
  if (isRecurrent) {
    hint.innerHTML = `<i class="fa-solid fa-lock"></i> Montant fixe verrouillé — ${fmt(c.montant)}`;
  } else if (isUnique) {
    hint.innerHTML = `<i class="fa-solid fa-info-circle"></i> Paiement partiel autorisé — reste : ${noMontant ? '—' : fmt(rest)}`;
  } else if (isVariable && noMontant) {
    hint.innerHTML = `<i class="fa-solid fa-bolt"></i> Saisissez le montant réel de la facture`;
  } else {
    hint.innerHTML = '';
  }

  /* Chips (seulement si montant modifiable) */
  const wrap = document.getElementById('mpChips');
  wrap.innerHTML = '';
  if (!isRecurrent && rest > 0 && !noMontant) {
    [{l:`Exact (${fmt(rest)})`,v:rest},{l:`50% (${fmt(Math.round(rest/2))})`,v:Math.round(rest/2)}].forEach(ch => {
      const btn = document.createElement('button');
      btn.style.cssText='padding:3px 9px;border-radius:20px;font-size:11px;font-weight:600;border:1.5px solid var(--border-2);background:#f8fafc;color:var(--text-2);cursor:pointer;font-family:var(--font-b)';
      btn.textContent = ch.l;
      btn.addEventListener('click',()=>{ mpInput.value=ch.v; wrap.querySelectorAll('button').forEach(b=>{b.style.background='#f8fafc';b.style.color='var(--text-2)';b.style.borderColor='var(--border-2)'}); btn.style.background='var(--brand)';btn.style.color='#fff';btn.style.borderColor='var(--brand)'; updateLive(); });
      wrap.appendChild(btn);
    });
  }

  document.getElementById('mpDate').value = TODAY.toISOString().slice(0,10);
  updateLive();
  openModal('mPay');
}

function updateLive() {
  const c  = _payCtx;
  let m    = parseFloat(document.getElementById('mpMontant').value) || 0;
  /* Pour les services sans montant, le montant saisi devient le montant_total */
  const baseDue = c.montant_is_zero ? m : c.montant;
  const total   = c.paye + m;
  const rest    = Math.max(0, baseDue - total);
  const pct     = baseDue > 0 ? Math.min(100, Math.round(total / baseDue * 100)) : 0;
  const fill    = document.getElementById('plFill');
  document.getElementById('plPct').textContent = pct + '%';
  fill.style.width = pct + '%';
  fill.style.background = pct>=100 ? 'linear-gradient(90deg,var(--green),#4ade80)' : pct>=50 ? 'linear-gradient(90deg,var(--amber),#fbbf24)' : 'linear-gradient(90deg,var(--red),#f87171)';
  const re = document.getElementById('plRest');
  re.textContent = rest > 0 ? fmt(rest) : '✓ Soldé';
  re.style.color = rest > 0 ? 'var(--red-t)' : 'var(--green-t)';
}

function submitPay() {
  const m    = parseFloat(document.getElementById('mpMontant').value) || 0;
  const date = document.getElementById('mpDate').value;
  const c    = CHARGES.find(x => x.id === _payCtx.id);

  if (!m || m <= 0) { showToast('e','Montant requis','Saisissez un montant valide.'); return; }
  if (!date)        { showToast('e','Date requise','Sélectionnez une date.'); return; }

  const btn = document.getElementById('mpBtn');
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Enregistrement…';
  btn.disabled  = true;

  setTimeout(() => {
    if (c) {
      /* Pour service variable sans montant → on fixe le montant d'abord */
      if (c.montant_is_zero) {
        c.montant = m;
        c.montant_is_zero = false;
      }
      /* Pour récurrente → forcer le montant exact */
      const toAdd = c.type === 'recurrent' ? c.montant : m;
      c.paye = Math.min(c.montant, c.paye + toAdd);
    }
    btn.innerHTML = '<i class="fa-solid fa-circle-check"></i> Valider';
    btn.disabled  = false;
    closeModal('mPay');
    const rest = c ? Math.max(0, c.montant - c.paye) : 0;
    if (rest === 0) showToast('s','Paiement validé ✓',`${fmt(m)} enregistré — charge soldée.`);
    else            showToast('w','Paiement partiel',`Reste à payer : ${fmt(rest)}`);
    renderTable();
  }, 1100);
}

/* ══ MODAL MODIFIER ══════════════════════════════════════════════
   Adapte le formulaire selon le type
*/
function openEditModal(id) {
  const c = CHARGES.find(x => x.id === id);
  if (!c) return;
  document.getElementById('meTitle').textContent = `Modifier — ${c.nom}`;
  document.getElementById('meNom').value          = c.nom;
  document.getElementById('meFournisseur').value  = c.fournisseur || '';
  document.getElementById('meCat').value          = c.cat;
  document.getElementById('meDateEffet').value    = TODAY.toISOString().slice(0,10);

  const freqGroup    = document.getElementById('meFreqGroup');
  const montantInp   = document.getElementById('meMontant');
  const montantHint  = document.getElementById('meMontantHint');
  const montantLbl   = document.getElementById('meMontantLabel');

  if (c.type === 'recurrent') {
    freqGroup.style.display = '';
    document.getElementById('meFreq').value = c.freq;
    montantInp.value    = c.montant;
    montantInp.disabled = false;
    montantInp.style.opacity = '1';
    montantLbl.innerHTML = 'Nouveau montant fixe (MAD) <span class="req">*</span>';
    montantHint.innerHTML = '';
  } else if (c.type === 'variable') {
    freqGroup.style.display = 'none';
    montantInp.value    = '';
    montantInp.disabled = true;
    montantInp.style.opacity = '.5';
    montantLbl.innerHTML = 'Montant (non applicable)';
    montantHint.innerHTML = '<i class="fa-solid fa-bolt" style="color:var(--teal-t)"></i> Service variable : le montant est saisi à chaque facturation, pas ici.';
  } else {
    /* unique */
    freqGroup.style.display = 'none';
    montantInp.value    = c.montant;
    montantInp.disabled = false;
    montantInp.style.opacity = '1';
    montantLbl.innerHTML = 'Montant estimé (MAD) <span class="req">*</span>';
    montantHint.innerHTML = '<i class="fa-solid fa-info-circle"></i> Montant indicatif — le montant réel est saisi au paiement.';
  }

  /* Impact */
  const paidEch   = c.paye >= c.montant ? '1 échéance' : '0 échéance';
  const futureEch = c.type==='unique' ? '1 dépense unique' : c.type==='variable' ? 'Prochaines factures' : c.freq==='mensuel' ? '8 échéances' : '3 échéances';
  document.getElementById('meCountPaid').textContent   = paidEch;
  document.getElementById('meCountFuture').textContent = futureEch;

  openModal('mEdit');
}

function submitEdit() {
  const nom = document.getElementById('meNom').value.trim();
  if (!nom) { showToast('e','Nom requis','Saisissez le nom de la charge.'); return; }
  const btn = document.querySelector('#mEdit .btn-primary-erp');
  const orig = btn.innerHTML;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>…';
  btn.disabled  = true;
  setTimeout(() => {
    /* ⚠ Règle : on ne modifie QUE les champs de la charge mère, pas les échéances payées */
    CHARGES.forEach(c => {
      if (c.id === +document.getElementById('meTitle').textContent.split('—')[0].replace('Modifier ','').trim() || true) {
        // Pour démo on prend le dernier id ouvert
      }
    });
    btn.innerHTML = orig; btn.disabled = false;
    closeModal('mEdit');
    showToast('s','Charge modifiée','Echéances futures recalculées. Historique protégé.');
    renderTable();
  }, 1000);
}

/* ══ AJOUTER DÉPENSE / CHARGE / SERVICE ══════════════════════════ */
function addDepense() {
  const nom     = document.getElementById('adNom').value.trim();
  const montant = parseFloat(document.getElementById('adMontant').value)||0;
  const date    = document.getElementById('adDate').value;
  if (!nom||!montant||!date){ showToast('e','Champs requis','Remplissez tous les champs obligatoires.'); return; }
  CHARGES.unshift({ id:nextId++, nom, fournisseur:document.getElementById('adFournisseur').value||'—', cat:document.getElementById('adCat').value, type:'unique', freq:null, echeance:date, montant, paye:0, montant_is_zero:false });
  closeModal('mAddDepense');
  document.getElementById('adNom').value=''; document.getElementById('adMontant').value=''; document.getElementById('adDate').value='';
  showToast('s','Dépense créée',`"${nom}" ajoutée au registre.`);
  renderTable();
}

function addCharge() {
  const nom     = document.getElementById('acNom').value.trim();
  const montant = parseFloat(document.getElementById('acMontant').value)||0;
  const date    = document.getElementById('acDate').value;
  const freq    = document.getElementById('acFreq').value;
  if (!nom||!montant||!date){ showToast('e','Champs requis','Remplissez tous les champs obligatoires.'); return; }
  CHARGES.unshift({ id:nextId++, nom, fournisseur:document.getElementById('acFournisseur').value||'—', cat:document.getElementById('acCat').value, type:'recurrent', freq, echeance:date, montant, paye:0, montant_is_zero:false });
  closeModal('mAddCharge');
  showToast('s','Charge créée',`"${nom}" ajoutée — échéances générées.`);
  renderTable();
}

function addService() {
  const nom  = document.getElementById('asNom').value.trim();
  const date = document.getElementById('asDate').value;
  if (!nom||!date){ showToast('e','Champs requis','Remplissez tous les champs.'); return; }
  const estim = parseFloat(document.getElementById('asMontantEstim').value)||0;
  CHARGES.unshift({ id:nextId++, nom, fournisseur:document.getElementById('asFournisseur').value||'—', cat:document.getElementById('asCat').value, type:'variable', freq:'mensuel', echeance:date, montant:estim, paye:0, montant_is_zero:true });
  closeModal('mAddService');
  showToast('s','Service créé',`"${nom}" — saisissez le montant réel à chaque facturation.`);
  renderTable();
}

/* ══ DELETE ══════════════════════════════════════════════════════ */
function doDelete(id) {
  const c = CHARGES.find(x=>x.id===id);
  if (!c||!confirm(`Supprimer "${c.nom}" ?`)) return;
  CHARGES.splice(CHARGES.indexOf(c),1);
  showToast('w','Supprimé',`"${c.nom}" retiré du registre.`);
  renderTable();
}

function viewPDF(id) {
  const c = CHARGES.find(x=>x.id===id);
  showToast('s','Facture','Téléchargement de la facture en cours.');
}

/* ══ EXPORT CSV ══════════════════════════════════════════════════ */
function exportCSV() {
  const rows = CHARGES.map(c => {
    const s = getStatut(c);
    const r = Math.max(0,c.montant-c.paye);
    return `"${c.nom}","${c.fournisseur}","${c.cat}","${c.type}","${c.freq||''}","${c.echeance}",${c.montant},${c.paye},${r},"${SCFG[s]?.label||s}"`;
  });
  const csv  = ['Nom,Fournisseur,Catégorie,Type,Fréquence,Échéance,Montant Dû,Payé,Reste,Statut',...rows].join('\n');
  const link = document.createElement('a');
  link.href  = 'data:text/csv;charset=utf-8,'+encodeURIComponent(csv);
  link.download = 'charges_depenses.csv';
  link.click();
  showToast('s','Export CSV','Fichier téléchargé.');
}

/* ══ MODAL HELPERS ═══════════════════════════════════════════════ */
function openModal(id) { document.getElementById(id).classList.add('open'); document.body.style.overflow='hidden'; }
function closeModal(id){ document.getElementById(id).classList.remove('open'); document.body.style.overflow=''; }
document.querySelectorAll('.modal-overlay').forEach(o => o.addEventListener('click', e => { if(e.target===o) closeModal(o.id); }));
document.addEventListener('keydown', e => { if(e.key==='Escape') document.querySelectorAll('.modal-overlay.open').forEach(m=>closeModal(m.id)); });

/* ══ SIDEBAR ════════════════════════════════════════════════════ */
document.getElementById('sidebarOpen')?.addEventListener('click',()=>{document.getElementById('sidebar').classList.add('open');document.getElementById('sidebarOverlay').classList.add('active')});
['sidebarClose','sidebarOverlay'].forEach(id=>document.getElementById(id)?.addEventListener('click',()=>{document.getElementById('sidebar').classList.remove('open');document.getElementById('sidebarOverlay').classList.remove('active')}));
document.getElementById('checkAll').addEventListener('change',function(){document.querySelectorAll('#tbody .row-chk').forEach(c=>c.checked=this.checked)});

/* ══ TOAST ══════════════════════════════════════════════════════ */
function showToast(t,title,msg){
  const cfg={s:{cls:'s',i:'fa-circle-check'},w:{cls:'w',i:'fa-triangle-exclamation'},e:{cls:'e',i:'fa-circle-xmark'}};
  const c=cfg[t]||cfg.s;
  const el=document.createElement('div'); el.className='toast-item';
  el.innerHTML=`<div class="t-ico ${c.cls}"><i class="fa-solid ${c.i}"></i></div><div class="t-body"><div class="t-title">${title}</div><div class="t-msg">${msg}</div></div><button class="t-x" onclick="rmToast(this.closest('.toast-item'))"><i class="fa-solid fa-xmark"></i></button>`;
  document.getElementById('toastStack').appendChild(el);
  setTimeout(()=>rmToast(el),4500);
}
function rmToast(el){if(!el||!el.parentNode)return;el.classList.add('out');setTimeout(()=>el.remove(),280)}

/* ══ INIT ════════════════════════════════════════════════════════ */
renderTable();

</script>
@endpush
@endsection