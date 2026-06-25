@extends('layouts.layout')

@section('title', 'Contrats')
@section('page_title', 'Contrats')

@section('content')

  <!-- CONTENT -->
  <div class="erp-content">

    <!-- Page Header -->
    <div class="page-header">
      <div class="ph-left">
        <p>Gérez, suivez et renouvelez tous les baux de vos résidences.</p>
      </div>
      <div class="ph-right">
        <button class="btn-outline-erp" onclick="openModal('exportModal')">
          <i class="fa-solid fa-download"></i> Exporter
        </button>
        <button class="btn-primary-erp" onclick="openModal('newContratModal')">
          <i class="fa-solid fa-plus"></i> Nouveau Contrat
        </button>
      </div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-4 mb-4">
         {{-- Card 1: Contrats Actifs --}}
          <div class="col-12 col-sm-6 col-xl-4">
              <div class="kpi-card kpi-blue">
                  <div class="kpi-body">
                      <div class="kpi-info">
                          <span class="kpi-label">Contrats Actifs</span>
                          <h3 class="kpi-value">18</h3>
                          <div class="kpi">
                              Sur 24 Appts disponibles
                          </div>
                      </div>
                      <div class="kpi-icon-wrap blue">
                          <i class="fa-solid fa-file-contract"></i>
                      </div>
                  </div>
              </div>
          </div>


          {{-- Card 2: Expirant Bientôt --}}
          <div class="col-12 col-sm-6 col-xl-4">
                  <div class="kpi-card kpi-orange">
                      <div class="kpi-body">
                          <div class="kpi-info">
                              <span class="kpi-label">Expirant Bientôt</span>
                              <h3 class="kpi-value">4</h3>
                              <div class="kpi">
                                  Dans les 30 jours
                              </div>
                          </div>
                          <div class="kpi-icon-wrap orange">
                              <i class="fa-solid fa-clock-rotate-left"></i>
                          </div>
                      </div>
                  </div>
          </div>

          

          {{-- Card 3: Nouveaux ce mois --}}
              <div class="col-12 col-sm-6 col-xl-4">
                      <div class="kpi-card kpi-green">
                          <div class="kpi-body">
                              <div class="kpi-info">
                                  <span class="kpi-label">Nouveaux ce mois</span>
                                  <h3 class="kpi-value">3</h3>
                                  <div class="kpi">
                                      Baux signés en avril 2026
                                  </div>
                              </div>
                              <div class="kpi-icon-wrap green">
                                  <i class="fa-solid fa-file-circle-check"></i>
                              </div>
                          </div>
                      </div>
              </div>

      
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
      <div class="filter-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Rechercher par locataire, appartement, référence…" oninput="filterTable()">
      </div>

      <select class="filter-select" id="filterStatut" onchange="filterTable()">
        <option value="">Tous les statuts</option>
        <option value="actif">Actif</option>
        <option value="expiring">Expirant bientôt</option>
        <option value="renouveler">À renouveler</option>
        <option value="termine">Terminé</option>
        <option value="resilie">Résilié</option>
      </select>

      <select class="filter-select" id="filterType" onchange="filterTable()">
        <option value="">Tous les types</option>
        <option value="résidentiel">Bail résidentiel</option>
        <option value="commercial">Bail commercial</option>
      </select>

      <div class="filter-divider d-none d-md-block"></div>
      <div class="filter-count d-none d-md-block"><strong id="rowCount">8</strong> contrats trouvés</div>
    </div>

    <!-- Table Card -->
    <div class="table-card">
      <div class="table-card-header">
        <div class="tch-left">
          <h5>Liste des Contrats</h5>
          <p>Cliquez sur une ligne pour voir les détails complets</p>
        </div>

      </div>

      <div class="table-responsive">
        <table class="table erp-table" id="contratsTable">
          <thead>
            <tr>
              <th><input type="checkbox" class="row-check" id="checkAll"></th>
              <th>Référence <i class="fa-solid fa-sort"></i></th>
              <th>Unité <i class="fa-solid fa-sort"></i></th>
              <th>Locataire <i class="fa-solid fa-sort"></i></th>
              <th>Période <i class="fa-solid fa-sort"></i></th>
              <th>Montant <i class="fa-solid fa-sort"></i></th>
              <th>Type</th>
              <th>Statut <i class="fa-solid fa-sort"></i></th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="tableBody">

            <tr data-statut="actif" data-type="résidentiel" data-residence="atlas" onclick="openContratDetail(0)">
              <td onclick="event.stopPropagation()"><input type="checkbox" class="row-check"></td>
              <td><div class="ref-cell"><span class="ref-code">CTR-2026-001</span></div></td>
              <td><div class="unit-cell"><span class="unit-name">Apt. 05 — Étage 2</span></div></td>
              <td>
                <div class="tenant-cell">
                  <div class="t-avatar" style="background:#4f46e5">KB</div>
                  <div><span class="t-name">Karim Benali</span><span class="t-type">Particulier</span></div>
                </div>
              </td>
              <td>
                <div class="period-cell">
                  <div class="period-dates">01/01/2025 <span>→</span> 31/12/2026</div>
                  <div class="period-bar"><div class="period-fill" style="width:35%"></div></div>
                </div>
              </td>
              <td><span class="amount-main">3 800 MAD</span><span class="amount-caution">Caution: 7 600 MAD</span></td>
              <td><span class="s-badge renouveler">Résidentiel</span></td>
              <td><span class="s-badge paid">Actif</span></td>
              <td onclick="event.stopPropagation()">
                <div class="row-actions">
                  <button class="ra-btn view" title="Voir détail" onclick="openContratDetail(0)"><i class="fa-solid fa-eye"></i></button>
                  <button class="ra-btn download" title="Télécharger PDF"><i class="fa-solid fa-file-pdf"></i></button>
                  <button class="ra-btn delete" title="Résilier"><i class="fa-solid fa-ban"></i></button>
                </div>
              </td>
            </tr>

            <tr data-statut="expiring" data-type="résidentiel" data-residence="atlas" onclick="openContratDetail(1)">
              <td onclick="event.stopPropagation()"><input type="checkbox" class="row-check"></td>
              <td><div class="ref-cell"><span class="ref-code">CTR-2026-002</span></div></td>
              <td><div class="unit-cell"><span class="unit-name">Apt. 12B — Étage 4</span></div></td>
              <td>
                <div class="tenant-cell">
                  <div class="t-avatar" style="background:#0891b2">SO</div>
                  <div><span class="t-name">Sara Ouali</span><span class="t-type">Particulier</span></div>
                </div>
              </td>
              <td>
                <div class="period-cell">
                  <div class="period-dates">01/05/2024 <span>→</span> 30/04/2026</div>
                  <div class="period-bar"><div class="period-fill" style="width:92%"></div></div>
                </div>
              </td>
              <td><span class="amount-main">4 500 MAD</span><span class="amount-caution">Caution: 9 000 MAD</span></td>
              <td><span class="s-badge renouveler">Résidentiel</span></td>
              <td><span class="s-badge expiring"><i class="fa-solid fa-triangle-exclamation" style="font-size:10px"></i> Expirant</span></td>
              <td onclick="event.stopPropagation()">
                <div class="row-actions">
                  <button class="ra-btn view" title="Voir détail" onclick="openContratDetail(1)"><i class="fa-solid fa-eye"></i></button>
                  <button class="ra-btn download" title="Télécharger PDF"><i class="fa-solid fa-file-pdf"></i></button>
                  <button class="ra-btn delete" title="Résilier"><i class="fa-solid fa-ban"></i></button>
                </div>
              </td>
            </tr>

            <tr data-statut="actif" data-type="résidentiel" data-residence="palmeraie" onclick="openContratDetail(2)">
              <td onclick="event.stopPropagation()"><input type="checkbox" class="row-check"></td>
              <td><div class="ref-cell"><span class="ref-code">CTR-2025-089</span></div></td>
              <td><div class="unit-cell"><span class="unit-name">Apt. 03 — RDC</span><span class="unit-res">Résidence Palmeraie</span></div></td>
              <td>
                <div class="tenant-cell">
                  <div class="t-avatar" style="background:#059669">YM</div>
                  <div><span class="t-name">Youssef Mouhib</span><span class="t-type">Particulier</span></div>
                </div>
              </td>
              <td>
                <div class="period-cell">
                  <div class="period-dates">01/06/2024 <span>→</span> 31/05/2027</div>
                  <div class="period-bar"><div class="period-fill" style="width:28%"></div></div>
                </div>
              </td>
              <td><span class="amount-main">2 900 MAD</span><span class="amount-caution">Caution: 5 800 MAD</span></td>
              <td><span class="s-badge renouveler">Résidentiel</span></td>
              <td><span class="s-badge actif">Actif</span></td>
              <td onclick="event.stopPropagation()">
                <div class="row-actions">
                  <button class="ra-btn view" title="Voir détail" onclick="openContratDetail(2)"><i class="fa-solid fa-eye"></i></button>
                  <button class="ra-btn download" title="Télécharger PDF"><i class="fa-solid fa-file-pdf"></i></button>
                  <button class="ra-btn delete" title="Résilier"><i class="fa-solid fa-ban"></i></button>
                </div>
              </td>
            </tr>

            <tr data-statut="expiring" data-type="commercial" data-residence="majorelle" onclick="openContratDetail(3)">
              <td onclick="event.stopPropagation()"><input type="checkbox" class="row-check"></td>
              <td><div class="ref-cell"><span class="ref-code">CTR-2025-044</span></div></td>
              <td><div class="unit-cell"><span class="unit-name">Local C1 — RDC</span><span class="unit-res">Résidence Majorelle</span></div></td>
              <td>
                <div class="tenant-cell">
                  <div class="t-avatar" style="background:#d97706">MB</div>
                  <div><span class="t-name">Medtech Boutique</span><span class="t-type">Société</span></div>
                </div>
              </td>
              <td>
                <div class="period-cell">
                  <div class="period-dates">01/03/2023 <span>→</span> 28/02/2026</div>
                  <div class="period-bar"><div class="period-fill" style="width:97%"></div></div>
                </div>
              </td>
              <td><span class="amount-main">8 200 MAD</span><span class="amount-caution">Caution: 24 600 MAD</span></td>
              <td><span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:11.5px;font-weight:600;background:rgba(245,158,11,.1);color:#92400e;">Commercial</span></td>
              <td><span class="s-badge expiring"><i class="fa-solid fa-triangle-exclamation" style="font-size:10px"></i> Expirant</span></td>
              <td onclick="event.stopPropagation()">
                <div class="row-actions">
                  <button class="ra-btn view" title="Voir détail" onclick="openContratDetail(3)"><i class="fa-solid fa-eye"></i></button>
                  <button class="ra-btn download" title="Télécharger PDF"><i class="fa-solid fa-file-pdf"></i></button>
                  <button class="ra-btn delete" title="Résilier"><i class="fa-solid fa-ban"></i></button>
                </div>
              </td>
            </tr>

            <tr data-statut="actif" data-type="résidentiel" data-residence="atlas" onclick="openContratDetail(4)">
              <td onclick="event.stopPropagation()"><input type="checkbox" class="row-check"></td>
              <td><div class="ref-cell"><span class="ref-code">CTR-2026-007</span></div></td>
              <td><div class="unit-cell"><span class="unit-name">Apt. 07 — Étage 3</span></div></td>
              <td>
                <div class="tenant-cell">
                  <div class="t-avatar" style="background:#7c3aed">RA</div>
                  <div><span class="t-name">Rachid Alami</span><span class="t-type">Particulier</span></div>
                </div>
              </td>
              <td>
                <div class="period-cell">
                  <div class="period-dates">01/02/2026 <span>→</span> 31/01/2028</div>
                  <div class="period-bar"><div class="period-fill" style="width:12%"></div></div>
                </div>
              </td>
              <td><span class="amount-main">3 200 MAD</span><span class="amount-caution">Caution: 6 400 MAD</span></td>
              <td><span class="s-badge renouveler">Résidentiel</span></td>
              <td><span class="s-badge actif">Actif</span></td>
              <td onclick="event.stopPropagation()">
                <div class="row-actions">
                  <button class="ra-btn view" title="Voir détail" onclick="openContratDetail(4)"><i class="fa-solid fa-eye"></i></button>
                  <button class="ra-btn download" title="Télécharger PDF"><i class="fa-solid fa-file-pdf"></i></button>
                  <button class="ra-btn delete" title="Résilier"><i class="fa-solid fa-ban"></i></button>
                </div>
              </td>
            </tr>

            <tr data-statut="resilie" data-type="résidentiel" data-residence="palmeraie" onclick="openContratDetail(5)">
              <td onclick="event.stopPropagation()"><input type="checkbox" class="row-check"></td>
              <td><div class="ref-cell"><span class="ref-code">CTR-2024-031</span></div></td>
              <td><div class="unit-cell"><span class="unit-name">Apt. 22 — Étage 7</span><span class="unit-res">Résidence Palmeraie</span></div></td>
              <td>
                <div class="tenant-cell">
                  <div class="t-avatar" style="background:#db2777">NB</div>
                  <div><span class="t-name">Nadia Berrada</span><span class="t-type">Particulier</span></div>
                </div>
              </td>
              <td>
                <div class="period-cell">
                  <div class="period-dates">01/07/2024 <span>→</span> 31/01/2026</div>
                  <div class="period-bar"><div class="period-fill" style="width:100%;background:#ef4444"></div></div>
                </div>
              </td>
              <td><span class="amount-main">6 100 MAD</span><span class="amount-caution">Caution: 12 200 MAD</span></td>
              <td><span class="s-badge renouveler">Résidentiel</span></td>
              <td><span class="s-badge resilie">Résilié</span></td>
              <td onclick="event.stopPropagation()">
                <div class="row-actions">
                  <button class="ra-btn view" title="Voir détail" onclick="openContratDetail(5)"><i class="fa-solid fa-eye"></i></button>
                  <button class="ra-btn download" title="Télécharger PDF"><i class="fa-solid fa-file-pdf"></i></button>
                </div>
              </td>
            </tr>

            <tr data-statut="actif" data-type="mandat" data-residence="atlas" onclick="openContratDetail(6)">
              <td onclick="event.stopPropagation()"><input type="checkbox" class="row-check"></td>
              <td><div class="ref-cell"><span class="ref-code">MND-2025-003</span></div></td>
              <td><div class="unit-cell"><span class="unit-name">Apt. 09 — Étage 3</span></div></td>
              <td>
                <div class="tenant-cell">
                  <div class="t-avatar" style="background:#0f766e">HZ</div>
                  <div><span class="t-name">Hassan Ziani</span><span class="t-type">Propriétaire</span></div>
                </div>
              </td>
              <td>
                <div class="period-cell">
                  <div class="period-dates">01/01/2025 <span>→</span> 31/12/2027</div>
                  <div class="period-bar"><div class="period-fill" style="width:20%"></div></div>
                </div>
              </td>
              <td><span class="amount-main">5% des loyers</span><span class="amount-caution">Commission syndic</span></td>
              <td><span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:11.5px;font-weight:600;background:rgba(20,184,166,.1);color:#0f766e;">Résidentiel</span></td>
              <td><span class="s-badge actif">Actif</span></td>
              <td onclick="event.stopPropagation()">
                <div class="row-actions">
                  <button class="ra-btn view" title="Voir détail" onclick="openContratDetail(6)"><i class="fa-solid fa-eye"></i></button>
                  <button class="ra-btn download" title="Télécharger PDF"><i class="fa-solid fa-file-pdf"></i></button>
                  <button class="ra-btn delete" title="Résilier"><i class="fa-solid fa-ban"></i></button>
                </div>
              </td>
            </tr>

            <tr data-statut="termine" data-type="résidentiel" data-residence="majorelle" onclick="openContratDetail(7)">
              <td onclick="event.stopPropagation()"><input type="checkbox" class="row-check"></td>
              <td><div class="ref-cell"><span class="ref-code">CTR-2023-017</span></div></td>
              <td><div class="unit-cell"><span class="unit-name">Apt. 18 — Étage 6</span><span class="unit-res">Résidence Majorelle</span></div></td>
              <td>
                <div class="tenant-cell">
                  <div class="t-avatar" style="background:#6d28d9">LK</div>
                  <div><span class="t-name">Leila Khadiri</span><span class="t-type">Particulier</span></div>
                </div>
              </td>
              <td>
                <div class="period-cell">
                  <div class="period-dates">01/01/2023 <span>→</span> 31/12/2024</div>
                  <div class="period-bar"><div class="period-fill" style="width:100%;background:#94a3b8"></div></div>
                </div>
              </td>
              <td><span class="amount-main">5 200 MAD</span><span class="amount-caution">Caution: 10 400 MAD</span></td>
              <td><span class="s-badge renouveler">Résidentiel</span></td>
              <td><span class="s-badge termine">Terminé</span></td>
              <td onclick="event.stopPropagation()">
                <div class="row-actions">
                  <button class="ra-btn view" title="Voir détail" onclick="openContratDetail(7)"><i class="fa-solid fa-eye"></i></button>
                  <button class="ra-btn download" title="Télécharger PDF"><i class="fa-solid fa-file-pdf"></i></button>
                </div>
              </td>
            </tr>

          </tbody>
        </table>
      </div>

      <div class="table-footer">
        <span class="tf-info">Affichage <strong>1–8</strong> sur <strong>18</strong> contrats</span>
        <div class="tf-pagination">
          <button class="page-btn" disabled><i class="fa-solid fa-chevron-left"></i></button>
          <button class="page-btn active">1</button>
          <button class="page-btn">2</button>
          <button class="page-btn">3</button>
          <button class="page-btn"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
      </div>
    </div>

    <!-- Bouton FAB nouveau contrat (bottom) -->
    <div style="display:flex;justify-content:center;margin-top:24px">
      <button class="btn-primary-erp" onclick="openModal('newContratModal')" style="padding:12px 32px;font-size:14px">
        <i class="fa-solid fa-plus"></i> Ajouter un nouveau contrat
      </button>
    </div>

  </div><!-- /erp-content -->



<!-- ═══════════════════════════════════════════════════
     MODAL — DETAIL CONTRAT
     ═══════════════════════════════════════════════════ -->
<div class="modal-overlay" id="detailModal">
  <div class="modal-panel" id="detailPanel">

    <div class="modal-head">
      <div class="mh-left">
        <div class="mh-ref">
          <span class="mh-ref-code" id="dRef">CTR-2026-001</span>
          <span class="s-badge actif" id="dBadge">Actif</span>
        </div>
        <h3 class="mh-title" id="dTitle">Contrat — Karim Benali</h3>
        <p class="mh-sub" id="dSub">Apt. 05 — Étage 2 · Bail résidentiel</p>
      </div>
      <div class="mh-right">
        <button class="btn-outline-erp" id="dDownloadBtn"><i class="fa-solid fa-download"></i> PDF</button>
        <button class="modal-close" onclick="closeModal('detailModal')"><i class="fa-solid fa-xmark"></i></button>
      </div>
    </div>

    <div class="modal-body">

      <!-- Info Grid -->
      <div class="info-grid" id="dInfoGrid">
        <div class="info-block">
          <div class="ib-label">Date de début</div>
          <div class="ib-value accent" id="dDateDebut">01/01/2025</div>
        </div>
        <div class="info-block">
          <div class="ib-label">Date de fin</div>
          <div class="ib-value orange" id="dDateFin">31/12/2026</div>
        </div>
        <div class="info-block">
          <div class="ib-label">Loyer mensuel</div>
          <div class="ib-value" id="dLoyer">3 800 MAD</div>
        </div>
        <div class="info-block">
          <div class="ib-label">Caution versée</div>
          <div class="ib-value" id="dCaution">7 600 MAD</div>
        </div>
        <div class="info-block">
          <div class="ib-label">Mode de paiement</div>
          <div class="ib-value" id="dMode">Virement bancaire</div>
        </div>
        <div class="info-block">
          <div class="ib-label">Fréquence</div>
          <div class="ib-value green" id="dFreq">Mensuel</div>
        </div>
      </div>

      <!-- Parties prenantes -->
      <div class="section-title"><i class="fa-solid fa-users"></i> Parties Prenantes</div>
      <div class="parties-grid">
        <div class="party-card">
          <div class="pc-role">Locataire / Partie prenante</div>
          <div class="pc-user">
            <div class="pc-avatar" style="background:#4f46e5" id="dTenantAvatar">KB</div>
            <div>
              <span class="pc-name" id="dTenantName">Karim Benali</span>
              <span class="pc-detail"><i class="fa-solid fa-phone" style="font-size:10px;margin-right:4px"></i>+212 6 61 23 45 67</span>
              <span class="pc-detail"><i class="fa-solid fa-envelope" style="font-size:10px;margin-right:4px"></i>k.benali@email.ma</span>
              <span class="pc-detail"><i class="fa-solid fa-id-card" style="font-size:10px;margin-right:4px"></i>CIN: AB123456</span>
            </div>
          </div>
        </div>
        <div class="party-card">
          <div class="pc-role">Propriétaire</div>
          <div class="pc-user">
            <div class="pc-avatar" style="background:#0f766e">OA</div>
            <div>
              <span class="pc-name">Omar Alaoui</span>
              <span class="pc-detail"><i class="fa-solid fa-phone" style="font-size:10px;margin-right:4px"></i>+212 5 22 34 56 78</span>
              <span class="pc-detail"><i class="fa-solid fa-envelope" style="font-size:10px;margin-right:4px"></i>o.alaoui@syndicpro.ma</span>
              <span class="pc-detail"><i class="fa-solid fa-id-card" style="font-size:10px;margin-right:4px"></i>CIN: CD789012</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Clauses -->


      <!-- Documents -->
      <div class="section-title"><i class="fa-solid fa-paperclip"></i> Documents Annexes</div>
      <div class="docs-list">
        <div class="doc-item">
          <div class="doc-icon pdf"><i class="fa-solid fa-file-pdf"></i></div>
          <span class="doc-name">Contrat de bail signé — CTR-2026-001.pdf</span>
          <span class="doc-size">348 Ko</span>
          <span class="doc-date">Ajouté le 01/01/2025</span>
          <button class="doc-dl"><i class="fa-solid fa-download"></i></button>
        </div>
        <div class="doc-item">
          <div class="doc-icon img"><i class="fa-solid fa-id-card"></i></div>
          <span class="doc-name">CIN Karim Benali (recto-verso)</span>
          <span class="doc-size">1.2 Mo</span>
          <span class="doc-date">Ajouté le 28/12/2024</span>
          <button class="doc-dl"><i class="fa-solid fa-download"></i></button>
        </div>
        <div class="doc-item">
          <div class="doc-icon pdf"><i class="fa-solid fa-file-pdf"></i></div>
          <span class="doc-name">État des lieux d'entrée</span>
          <span class="doc-size">620 Ko</span>
          <span class="doc-date">Ajouté le 01/01/2025</span>
          <button class="doc-dl"><i class="fa-solid fa-download"></i></button>
        </div>
        <div class="doc-item">
          <div class="doc-icon word"><i class="fa-solid fa-shield-halved"></i></div>
          <span class="doc-name">Attestation assurance habitation 2025</span>
          <span class="doc-size">185 Ko</span>
          <span class="doc-date">Ajouté le 03/01/2025</span>
          <button class="doc-dl"><i class="fa-solid fa-download"></i></button>
        </div>
      </div>

      <!-- Historique -->
      <div class="section-title"><i class="fa-solid fa-clock-rotate-left"></i> Historique des Modifications</div>
      <div class="timeline">
        <div class="tl-item">
          <div class="tl-left"><div class="tl-dot blue"></div><div class="tl-line"></div></div>
          <div class="tl-content">
            <div class="tl-action">Contrat créé et signé</div>
            <div class="tl-by">Par Ahmed Mansouri (Administrateur)</div>
            <div class="tl-date"><i class="fa-regular fa-clock"></i> 01 Janvier 2025, 10h23</div>
          </div>
        </div>
        <div class="tl-item">
          <div class="tl-left"><div class="tl-dot green"></div><div class="tl-line"></div></div>
          <div class="tl-content">
            <div class="tl-action">Documents annexes ajoutés (CIN + Assurance)</div>
            <div class="tl-by">Par Ahmed Mansouri (Administrateur)</div>
            <div class="tl-date"><i class="fa-regular fa-clock"></i> 03 Janvier 2025, 14h10</div>
          </div>
        </div>
        <div class="tl-item">
          <div class="tl-left"><div class="tl-dot orange"></div><div class="tl-line"></div></div>
          <div class="tl-content">
            <div class="tl-action">Mise à jour du mode de paiement : Espèces → Virement</div>
            <div class="tl-by">Par Ahmed Mansouri (Administrateur)</div>
            <div class="tl-date"><i class="fa-regular fa-clock"></i> 15 Mars 2025, 09h00</div>
          </div>
        </div>
        <div class="tl-item">
          <div class="tl-left"><div class="tl-dot gray"></div></div>
          <div class="tl-content">
            <div class="tl-action">Révision annuelle du loyer appliquée (+3%)</div>
            <div class="tl-by">Automatique — Système SyndicPro</div>
            <div class="tl-date"><i class="fa-regular fa-clock"></i> 01 Janvier 2026, 00h00</div>
          </div>
        </div>
      </div>

    </div><!-- /modal-body -->

    <div class="modal-foot">
      <div class="mf-left">
        <button class="btn-outline-erp" style="color:var(--red-t);border-color:var(--red-bg)" onclick="closeModal('detailModal')">
          <i class="fa-solid fa-ban"></i> Résilier le contrat
        </button>
      </div>
      <div class="mf-right">
        <button class="btn-outline-erp" onclick="closeModal('detailModal')">Fermer</button>
        <button class="btn-primary-erp"><i class="fa-solid fa-pen"></i> Modifier</button>
      </div>
    </div>

  </div>
</div>


<!-- ═══════════════════════════════════════════════════
     MODAL — NOUVEAU CONTRAT
     ═══════════════════════════════════════════════════ -->
<div class="modal-overlay form-modal" id="newContratModal">
  <div class="modal-panel">

    <div class="modal-head">
      <div class="mh-left">
        <h3 class="mh-title">Nouveau Contrat</h3>
        <p class="mh-sub">Remplissez les informations du bail à créer</p>
      </div>
      <div class="mh-right">
        <button class="modal-close" onclick="closeModal('newContratModal')"><i class="fa-solid fa-xmark"></i></button>
      </div>
    </div>

    <div class="modal-body">
      <form id="newContratForm">

        <!-- Appartement & locataire -->
        <div class="form-section-label"><i class="fa-solid fa-building" style="color:var(--brand)"></i> Unité & Parties</div>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Appartement *</label>
            <select class="form-control-erp">
              <option value="">— Sélectionner —</option>
              <option>Apt. 05</option>
              <option>Apt. 08</option>
              <option>Apt. 14</option>
              <option>Apt. 11</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Type de contrat *</label>
            <select class="form-control-erp">
              <option value="">— Sélectionner —</option>
              <option>Bail résidentiel</option>
              <option>Bail commercial</option>
              
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Locataire / Partie prenante *</label>
            <select class="form-control-erp">
              <option value="">— Sélectionner ou créer —</option>
              <option>Karim Benali</option>
              <option>Sara Ouali</option>
              <option>Youssef Mouhib</option>
              <option>+ Nouveau locataire</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Propriétaire</label>
              <option class="form-control-erp">Omar Alaoui</option>
          </div>
        </div>

        <!-- Période & financier -->
        <div class="form-section-label"><i class="fa-solid fa-calendar-days" style="color:var(--brand)"></i> Période & Financier</div>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Date de début *</label>
            <input type="date" class="form-control-erp">
          </div>
          <div class="form-group">
            <label class="form-label">Date de fin *</label>
            <input type="date" class="form-control-erp">
          </div>
          <div class="form-group">
            <label class="form-label">Loyer mensuel (MAD) *</label>
            <input type="number" class="form-control-erp" placeholder="ex: 3800">
          </div>
          <div class="form-group">
            <label class="form-label">Caution (MAD)</label>
            <input type="number" class="form-control-erp" placeholder="ex: 7600">
          </div>
          <div class="form-group">
            <label class="form-label">Mode de paiement</label>
            <select class="form-control-erp">
              <option>Virement bancaire</option>
              <option>Chèque</option>
              <option>Espèces</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Fréquence de paiement</label>
            <select class="form-control-erp">
              <option>Journalier</option>
              <option>Mensuel</option>
              <option>Trimestriel</option>
              <option>Annuel</option>
              <option>total</option>
            </select>
          </div>
        </div>

        <!-- Clauses -->


        <!-- Documents -->
        <div class="form-section-label"><i class="fa-solid fa-paperclip" style="color:var(--brand)"></i> Documents à joindre</div>
        <div style="display:flex;flex-direction:column;gap:10px;">
          <div style="border:2px dashed var(--border-2);border-radius:var(--r-md);padding:24px;text-align:center;cursor:pointer;transition:var(--tr);" onmouseenter="this.style.borderColor='var(--brand)'" onmouseleave="this.style.borderColor='var(--border-2)'">
            <i class="fa-solid fa-cloud-arrow-up" style="font-size:28px;color:var(--text-4);margin-bottom:8px;display:block"></i>
            <div style="font-size:13.5px;font-weight:600;color:var(--text-2)">Glissez vos fichiers ici</div>
            <div style="font-size:12px;color:var(--text-4);margin-top:4px">ou <span style="color:var(--brand);font-weight:600;cursor:pointer">parcourez</span> — PDF, images, Word (max 10 Mo)</div>
            <input type="file" multiple style="display:none">
          </div>
        </div>

      </form>
    </div>

    <div class="modal-foot">
      <div class="mf-left">
        <button class="btn-outline-erp" onclick="closeModal('newContratModal')">Annuler</button>
      </div>
      <div class="mf-right">
        <button class="btn-primary-erp" onclick="submitNewContrat()">
          <i class="fa-solid fa-check"></i> Créer le contrat
        </button>
      </div>
    </div>

  </div>
</div>

@push('scripts')

<script>

// ── Modal helpers ──────────────────────────────────────────
function openModal(id) {
  document.getElementById(id).classList.add('open');
  document.body.style.overflow='hidden';
}
function closeModal(id) {
  document.getElementById(id).classList.remove('open');
  document.body.style.overflow='';
}
// Close on overlay click
document.querySelectorAll('.modal-overlay').forEach(overlay=>{
  overlay.addEventListener('click',function(e){
    if(e.target===this) closeModal(this.id);
  });
});
// ESC key
document.addEventListener('keydown',e=>{
  if(e.key==='Escape') document.querySelectorAll('.modal-overlay.open').forEach(m=>closeModal(m.id));
});

// ── Contrat Data (static demo) ─────────────────────────────
const contrats = [
  { ref:'CTR-2026-001', title:'Contrat — Karim Benali',    sub:'Apt. 05 — Étage 2 · Résidence Atlas · Bail résidentiel',    badge:'actif',    badgeLabel:'Actif',             debut:'01/01/2025', fin:'31/12/2026', loyer:'3 800 MAD', caution:'7 600 MAD', mode:'Virement bancaire', freq:'Mensuel', tenantInit:'KB', tenantColor:'#4f46e5', tenantName:'Karim Benali' },
  { ref:'CTR-2026-002', title:'Contrat — Sara Ouali',      sub:'Apt. 12B — Étage 4 · Résidence Atlas · Bail résidentiel',  badge:'expiring', badgeLabel:'Expirant bientôt',  debut:'01/05/2024', fin:'30/04/2026', loyer:'4 500 MAD', caution:'9 000 MAD', mode:'Chèque',           freq:'Mensuel', tenantInit:'SO', tenantColor:'#0891b2', tenantName:'Sara Ouali' },
  { ref:'CTR-2025-089', title:'Contrat — Youssef Mouhib',  sub:'Apt. 03 — RDC · Résidence Palmeraie · Bail résidentiel',   badge:'actif',    badgeLabel:'Actif',             debut:'01/06/2024', fin:'31/05/2027', loyer:'2 900 MAD', caution:'5 800 MAD', mode:'Virement bancaire', freq:'Mensuel', tenantInit:'YM', tenantColor:'#059669', tenantName:'Youssef Mouhib' },
  { ref:'CTR-2025-044', title:'Contrat — Medtech Boutique',sub:'Local C1 — RDC · Résidence Majorelle · Bail commercial',   badge:'expiring', badgeLabel:'Expirant bientôt',  debut:'01/03/2023', fin:'28/02/2026', loyer:'8 200 MAD', caution:'24 600 MAD',mode:'Virement bancaire', freq:'Mensuel', tenantInit:'MB', tenantColor:'#d97706', tenantName:'Medtech Boutique' },
  { ref:'CTR-2026-007', title:'Contrat — Rachid Alami',    sub:'Apt. 07 — Étage 3 · Résidence Atlas · Bail résidentiel',   badge:'actif',    badgeLabel:'Actif',             debut:'01/02/2026', fin:'31/01/2028', loyer:'3 200 MAD', caution:'6 400 MAD', mode:'Chèque',           freq:'Mensuel', tenantInit:'RA', tenantColor:'#7c3aed', tenantName:'Rachid Alami' },
  { ref:'CTR-2024-031', title:'Contrat — Nadia Berrada',   sub:'Apt. 22 — Étage 7 · Résidence Palmeraie · Bail résidentiel',badge:'resilie', badgeLabel:'Résilié',           debut:'01/07/2024', fin:'31/01/2026', loyer:'6 100 MAD', caution:'12 200 MAD',mode:'Espèces',           freq:'Mensuel', tenantInit:'NB', tenantColor:'#db2777', tenantName:'Nadia Berrada' },
  { ref:'MND-2025-003', title:'Mandat — Hassan Ziani',     sub:'Apt. 09 — Étage 3 · Résidence Atlas · Mandat de gestion',  badge:'actif',    badgeLabel:'Actif',             debut:'01/01/2025', fin:'31/12/2027', loyer:'5% loyers', caution:'N/A',         mode:'Virement bancaire', freq:'Mensuel', tenantInit:'HZ', tenantColor:'#0f766e', tenantName:'Hassan Ziani' },
  { ref:'CTR-2023-017', title:'Contrat — Leila Khadiri',   sub:'Apt. 18 — Étage 6 · Résidence Majorelle · Bail résidentiel',badge:'termine', badgeLabel:'Terminé',          debut:'01/01/2023', fin:'31/12/2024', loyer:'5 200 MAD', caution:'10 400 MAD',mode:'Chèque',           freq:'Mensuel', tenantInit:'LK', tenantColor:'#6d28d9', tenantName:'Leila Khadiri' },
];

function openContratDetail(index) {
  const c = contrats[index];
  document.getElementById('dRef').textContent        = c.ref;
  document.getElementById('dTitle').textContent      = c.title;
  document.getElementById('dSub').textContent        = c.sub;
  document.getElementById('dDateDebut').textContent  = c.debut;
  document.getElementById('dDateFin').textContent    = c.fin;
  document.getElementById('dLoyer').textContent      = c.loyer;
  document.getElementById('dCaution').textContent    = c.caution;
  document.getElementById('dMode').textContent       = c.mode;
  document.getElementById('dFreq').textContent       = c.freq;
  document.getElementById('dTenantAvatar').textContent = c.tenantInit;
  document.getElementById('dTenantAvatar').style.background = c.tenantColor;
  document.getElementById('dTenantName').textContent = c.tenantName;

  const badge = document.getElementById('dBadge');
  badge.className = `s-badge ${c.badge}`;
  badge.textContent = c.badgeLabel;

  // Hide renew button for resilie/termine
  const renewBtn = document.getElementById('dRenewBtn');
  renewBtn.style.display = (c.badge === 'resilie') ? 'none' : '';

  openModal('detailModal');
}

// ── Table search & filter ──────────────────────────────────
function filterTable() {
  const search    = document.getElementById('searchInput').value.toLowerCase();
  const statut    = document.getElementById('filterStatut').value;
  const type      = document.getElementById('filterType').value;
  const residence = document.getElementById('filterResidence').value;
  const rows      = document.querySelectorAll('#tableBody tr');
  let visible = 0;

  rows.forEach(row => {
    const text      = row.textContent.toLowerCase();
    const rowStatut = row.dataset.statut || '';
    const rowType   = row.dataset.type   || '';
    const rowRes    = row.dataset.residence || '';

    const matchSearch    = !search    || text.includes(search);
    const matchStatut    = !statut    || rowStatut === statut;
    const matchType      = !type      || rowType.includes(type);
    const matchResidence = !residence || rowRes === residence;

    const show = matchSearch && matchStatut && matchType && matchResidence;
    row.style.display = show ? '' : 'none';
    if(show) visible++;
  });

  document.getElementById('rowCount').textContent = visible;
}

// ── Check all ─────────────────────────────────────────────
document.getElementById('checkAll').addEventListener('change',function(){
  document.querySelectorAll('.row-check:not(#checkAll)').forEach(c=>c.checked=this.checked);
});

// ── Submit new contrat (demo) ──────────────────────────────
function submitNewContrat() {
  const btn = event.target;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Création...';
  btn.disabled = true;
  setTimeout(()=>{
    closeModal('newContratModal');
    btn.innerHTML = '<i class="fa-solid fa-check"></i> Créer le contrat';
    btn.disabled = false;
    // Show a subtle success indicator
    const bar = document.createElement('div');
    bar.style.cssText='position:fixed;top:20px;right:20px;z-index:9999;background:#22c55e;color:#fff;padding:12px 20px;border-radius:12px;font-family:var(--font-h);font-weight:700;font-size:13.5px;box-shadow:0 8px 24px rgba(34,197,94,.4);display:flex;align-items:center;gap:8px;animation:slideDown .3s ease';
    bar.innerHTML='<i class="fa-solid fa-circle-check"></i> Contrat créé avec succès !';
    document.body.appendChild(bar);
    setTimeout(()=>bar.remove(),3500);
  },1200);
}

// ── Sort cols (visual only) ────────────────────────────────
document.querySelectorAll('.erp-table thead th').forEach(th=>{
  th.addEventListener('click',function(){
    document.querySelectorAll('.erp-table thead th').forEach(t=>t.classList.remove('sorted'));
    this.classList.add('sorted');
  });
});
</script>
@endpush

@endsection
