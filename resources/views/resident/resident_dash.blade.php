<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Espace Résident – Syndic Bliving</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link
    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap"
    rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
    <link href="{{ asset('css/resident.css') }}" rel="stylesheet">
</head>

<body>

  <!-- SIDEBAR OVERLAY -->
  <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <div class="logo-text"> Syndic ERP</div>
      <div class="logo-sub">Espace résident</div>
    </div>
    <ul class="sidebar-nav">
      <li><a href="#" class="active"><i class="bi bi-grid-1x2-fill"></i> Tableau de bord</a></li>
    </ul>
    <div class="sidebar-footer">
      <div class="avatar-pill">
        <div class="avatar-circle">KA</div>
        <div class="avatar-info">
          <div class="av-name">Karim Alaoui</div>
          <div class="av-apt">Apt. B-203</div>
        </div>

      </div> <a href="#" style="margin-top:4px"><i class="bi bi-box-arrow-left"></i> Déconnexion</a>
    </div>
  </aside>

  <!-- MAIN -->
  <div class="main-wrapper">

          <button class="btn-hamburger" onclick="openSidebar()"><i class="bi bi-list"></i></button>

    <!-- PAGE CONTENT -->
    <main class="page-content">

      <!-- HERO HEADER -->
      <div class="resident-hero">
        <div class="hero-left">
          <div class="greeting">Bienvenue dans votre espace</div>
          <div class="resident-name">Karim Alaoui</div>
          <div class="apt-badge">
            <i class="bi bi-house-door-fill" style="font-size:.75rem"></i>
            Appartement B-203 · Résidence Al Firdaous · Étage 2
          </div>
        </div>
        <div class="hero-right">
          <div class="hero-stat">
            <div class="hs-val">1 200</div>
            <div class="hs-lbl">Budget annuel (MAD)</div>
          </div>
          <div class="hero-stat">
            <div class="hs-val">850</div>
            <div class="hs-lbl">Payé cette année</div>
          </div>
          <div class="hero-stat">
            <div class="hs-val">3</div>
            <div class="hs-lbl">Réclamations</div>
          </div>
        </div>
      </div>

      <!-- ROW: Reste à payer + Paiements -->
      <div class="row g-4 mb-4">

        <!-- RESTE À PAYER CARD -->
        <div class="col-lg-4">
          <div class="section-header">
            <div class="section-title"><i class="bi bi-wallet2"></i> Solde de l'année</div>
          </div>
          <div class="reste-card danger-card mb-3">
            <div class="reste-left">
              <div class="reste-label">Reste à payer – 2026</div>
              <div class="reste-amount">350,00 <small style="font-size:1rem">MAD</small></div>
              <div class="reste-year">Sur un total de 1 200,00 MAD</div>
              <div class="paiement-progress mt-2" style="width:180px">
                <div class="paiement-progress-fill" style="width:71%;background:var(--danger)"></div>
              </div>
              <div style="font-size:.72rem;color:var(--text-mid);margin-top:4px">71% payé · Échéance : 30/06/2026</div>
            </div>
            <div class="reste-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
          </div>

          <!-- Résumé des statuts -->
          <div class="card-clean">
            <div class="card-body-p">
              <div
                style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-mid);margin-bottom:14px">
                Historique annuel</div>
              <div style="display:flex;flex-direction:column;gap:10px">
                <div style="display:flex;justify-content:space-between;align-items:center">
                  <span style="font-size:.83rem;color:var(--text-dark)">2024</span>
                  <div style="display:flex;align-items:center;gap:8px">
                    <span class="mono" style="font-size:.8rem">1 200 MAD</span>
                    <span class="badge-status badge-paye">Payé</span>
                  </div>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center">
                  <span style="font-size:.83rem;color:var(--text-dark)">2026</span>
                  <div style="display:flex;align-items:center;gap:8px">
                    <span class="mono" style="font-size:.8rem">850 / 1 200</span>
                    <span class="badge-status badge-partiel">Partiel</span>
                  </div>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center">
                  <span style="font-size:.83rem;color:var(--text-dark)">2023</span>
                  <div style="display:flex;align-items:center;gap:8px">
                    <span class="mono" style="font-size:.8rem">1 100 MAD</span>
                    <span class="badge-status badge-paye">Payé</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TABLEAU PAIEMENTS -->
        <div class="col-lg-8">
          <div class="section-header">
            <div class="section-title"><i class="bi bi-receipt"></i> Historique des paiements</div>
            
          </div>
          <div class="card-clean">
            <div class="table-wrap">
              <table class="table-clean">
                <thead>
                  <tr>
                    <th>Année</th>
                    <th>Montant payé</th>
                    <th>Reste</th>
                    <th>Date paiement</th>
                    <th>Échéance</th>
                    <th>Mode</th>
                    <th>Statut</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td class="mono">2026</td>
                    <td class="mono">850,00 MAD</td>
                    <td class="mono" style="color:var(--danger);font-weight:600">350,00 MAD</td>
                    <td>15/03/2026</td>
                    <td style="color:var(--danger)">30/06/2026</td>
                    <td><span style="font-size:.8rem">💳 Virement</span></td>
                    <td><span class="badge-status badge-partiel">Partiel</span></td>
                  </tr>
                  <tr>
                    <td class="mono">2024</td>
                    <td class="mono">1 200,00 MAD</td>
                    <td class="mono" style="color:var(--success);font-weight:600">0,00 MAD</td>
                    <td>10/01/2024</td>
                    <td>31/12/2024</td>
                    <td><span style="font-size:.8rem">🏦 Chèque</span></td>
                    <td><span class="badge-status badge-paye">Payé</span></td>
                  </tr>
                  <tr>
                    <td class="mono">2023</td>
                    <td class="mono">1 100,00 MAD</td>
                    <td class="mono" style="color:var(--warning);font-weight:600">100,00 MAD</td>
                    <td>20/06/2023</td>
                    <td style="color:var(--text-mid)">31/12/2023</td>
                    <td><span style="font-size:.8rem">💵 Espèces</span></td>
                    <td><span class="badge-status badge-retard">En retard</span></td>
                  </tr>
                  <tr>
                    <td class="mono">2022</td>
                    <td class="mono">1 200,00 MAD</td>
                    <td class="mono" style="color:var(--success);font-weight:600">0,00 MAD</td>
                    <td>05/01/2022</td>
                    <td>31/12/2022</td>
                    <td><span style="font-size:.8rem">💳 Virement</span></td>
                    <td><span class="badge-status badge-paye">Payé</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- SECTION RÉCLAMATIONS -->
      <div class="section-header mt-2">
        <div class="section-title"><i class="bi bi-chat-left-dots"></i> Mes réclamations</div>
        <button class="btn-primary-custom" onclick="openModal('reclModal')">
          <i class="bi bi-plus-lg"></i> Nouvelle réclamation
        </button>
      </div>
      <div class="card-clean">
        <div class="table-wrap">
          <table class="table-clean" id="reclTable">
            <thead>
              <tr>
                <th>#</th>
                <th>Titre</th>
                <th>Priorité</th>
                <th>Description</th>
                <th>Statut</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody id="reclBody">
              <tr>
                <td class="mono" style="color:var(--text-light)">#001</td>
                <td style="font-weight:600">Fuite d'eau au couloir</td>
                <td><span class="badge-prio prio-haute">Haute</span></td>
                <td style="color:var(--text-mid);max-width:260px;font-size:.82rem">Fuite visible depuis le 10 mars,
                  humidité sur le plafond du couloir.</td>
                <td><span class="badge-status badge-en-cours">En cours</span></td>
                <td style="color:var(--text-mid);font-size:.82rem">12/03/2026</td>
              </tr>
              <tr>
                <td class="mono" style="color:var(--text-light)">#002</td>
                <td style="font-weight:600">Ascenseur en panne</td>
                <td><span class="badge-prio prio-urgente">Urgente</span></td>
                <td style="color:var(--text-mid);max-width:260px;font-size:.82rem">L'ascenseur est bloqué depuis 2
                  jours, problème signalé à la société de maintenance.</td>
                <td><span class="badge-status badge-ouverte">Ouverte</span></td>
                <td style="color:var(--text-mid);font-size:.82rem">18/03/2026</td>
              </tr>
              <tr>
                <td class="mono" style="color:var(--text-light)">#003</td>
                <td style="font-weight:600">Éclairage parking</td>
                <td><span class="badge-prio prio-basse">Basse</span></td>
                <td style="color:var(--text-mid);max-width:260px;font-size:.82rem">Deux ampoules grillées dans le
                  parking souterrain, zone B.</td>
                <td><span class="badge-status badge-resolue">Résolue</span></td>
                <td style="color:var(--text-mid);font-size:.82rem">02/02/2026</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </main>
  </div>

  <!-- ═══════════════════════ MODALS ═══════════════════════ -->

  <!-- MODAL: Nouvelle Réclamation -->
  <div class="modal-backdrop-custom" id="reclModal">
    <div class="modal-box">
      <div class="modal-header-custom">
        <div>
          <div class="modal-title-text">Nouvelle réclamation</div>
          <div class="modal-subtitle">Décrivez votre problème, nous vous répondrons rapidement.</div>
        </div>
        <button class="btn-close-custom" onclick="closeModal('reclModal')"><i class="bi bi-x"></i></button>
      </div>
      <div class="modal-body-custom">
        <!-- Titre -->
        <div class="mb-3">
          <label class="form-label-custom">Titre</label>
          <input type="text" id="reclTitre" class="form-control-custom"
            placeholder="Ex: Fuite d'eau dans les communs…" />
        </div>
        <!-- Priorité -->
        <div class="mb-3">
          <label class="form-label-custom">Priorité</label>
          <div class="prio-selector">
            <button class="prio-btn" data-prio="Basse" onclick="selectPrio(this)">🟢 Basse</button>
            <button class="prio-btn" data-prio="Moyenne" onclick="selectPrio(this)">🟡 Moyenne</button>
            <button class="prio-btn" data-prio="Haute" onclick="selectPrio(this)">🟠 Haute</button>
            <button class="prio-btn" data-prio="Urgente" onclick="selectPrio(this)">🔴 Urgente</button>
          </div>
          <input type="hidden" id="reclPrio" value="" />
        </div>
        <!-- Description -->
        <div class="mb-1">
          <label class="form-label-custom">Description</label>
          <textarea id="reclDesc" class="form-control-custom"
            placeholder="Décrivez le problème en détail : emplacement, date d'apparition, impact…"></textarea>
        </div>
      </div>
      <div class="modal-footer-custom">
        <button class="btn-cancel" onclick="closeModal('reclModal')">Annuler</button>
        <button class="btn-primary-custom" onclick="submitReclamation()"><i class="bi bi-send"></i> Soumettre</button>
      </div>
    </div>
  </div>

  <!-- MODAL: Nouveau Paiement (simple) -->
  <div class="modal-backdrop-custom" id="payModal">
    <div class="modal-box">
      <div class="modal-header-custom">
        <div>
          <div class="modal-title-text">Enregistrer un paiement</div>
          <div class="modal-subtitle">Renseignez les informations de votre paiement de cotisation.</div>
        </div>
        <button class="btn-close-custom" onclick="closeModal('payModal')"><i class="bi bi-x"></i></button>
      </div>
      <div class="modal-body-custom">
        <div class="row g-3">
          <div class="col-6">
            <label class="form-label-custom">Année concernée</label>
            <select class="form-select-custom">
              <option>2026</option>
              <option>2024</option>
              <option>2023</option>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label-custom">Montant payé (MAD)</label>
            <input type="number" class="form-control-custom" placeholder="350,00" />
          </div>
          <div class="col-6">
            <label class="form-label-custom">Mode de paiement</label>
            <select class="form-select-custom">
              <option>Virement</option>
              <option>Chèque</option>
              <option>Espèces</option>
              <option>Carte bancaire</option>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label-custom">Référence</label>
            <input type="text" class="form-control-custom" placeholder="Ex: VIR-20260401" />
          </div>
          <div class="col-12">
            <label class="form-label-custom">Commentaire (optionnel)</label>
            <textarea class="form-control-custom" placeholder="Informations complémentaires…"
              style="min-height:64px"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer-custom">
        <button class="btn-cancel" onclick="closeModal('payModal')">Annuler</button>
        <button class="btn-primary-custom"
          onclick="closeModal('payModal');showToast('✅ Paiement enregistré avec succès')"><i
            class="bi bi-check2-circle"></i> Enregistrer</button>
      </div>
    </div>
  </div>

  <!-- TOAST -->
  <div class="toast-container-custom" id="toastContainer"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    /* ── SIDEBAR ── */
    function openSidebar() {
      document.getElementById('sidebar').classList.add('open');
      document.getElementById('sidebarOverlay').classList.add('open');
    }
    function closeSidebar() {
      document.getElementById('sidebar').classList.remove('open');
      document.getElementById('sidebarOverlay').classList.remove('open');
    }

    /* ── MODAL ── */
    function openModal(id) {
      document.getElementById(id).classList.add('open');
      document.body.style.overflow = 'hidden';
    }
    function closeModal(id) {
      document.getElementById(id).classList.remove('open');
      document.body.style.overflow = '';
    }
    document.querySelectorAll('.modal-backdrop-custom').forEach(m => {
      m.addEventListener('click', e => { if (e.target === m) closeModal(m.id); });
    });

    /* ── PRIORITÉ ── */
    let selectedPrio = '';
    function selectPrio(btn) {
      document.querySelectorAll('.prio-btn').forEach(b => {
        b.className = 'prio-btn';
      });
      const p = btn.dataset.prio.toLowerCase();
      btn.classList.add('selected-' + p);
      selectedPrio = btn.dataset.prio;
      document.getElementById('reclPrio').value = selectedPrio;
    }

    /* ── SOUMETTRE RÉCLAMATION ── */
    let reclCount = 3;
    function submitReclamation() {
      const titre = document.getElementById('reclTitre').value.trim();
      const desc = document.getElementById('reclDesc').value.trim();
      const prio = selectedPrio;

      if (!titre) { alert('Veuillez saisir un titre.'); return; }
      if (!prio) { alert('Veuillez choisir une priorité.'); return; }
      if (!desc) { alert('Veuillez ajouter une description.'); return; }

      reclCount++;
      const id = String(reclCount).padStart(3, '0');
      const today = new Date().toLocaleDateString('fr-FR');
      const prioKey = prio.toLowerCase();

      const prioClass = { basse: 'prio-basse', moyenne: 'prio-moyenne', haute: 'prio-haute', urgente: 'prio-urgente' }[prioKey];

      const tr = document.createElement('tr');
      tr.innerHTML = `
      <td class="mono" style="color:var(--text-light)">#${id}</td>
      <td style="font-weight:600">${titre}</td>
      <td><span class="badge-prio ${prioClass}">${prio}</span></td>
      <td style="color:var(--text-mid);max-width:260px;font-size:.82rem">${desc.substring(0, 100)}${desc.length > 100 ? '…' : ''}</td>
      <td><span class="badge-status badge-ouverte">Ouverte</span></td>
      <td style="color:var(--text-mid);font-size:.82rem">${today}</td>
    `;
      document.getElementById('reclBody').prepend(tr);

      // Reset
      document.getElementById('reclTitre').value = '';
      document.getElementById('reclDesc').value = '';
      document.querySelectorAll('.prio-btn').forEach(b => b.className = 'prio-btn');
      selectedPrio = '';
      closeModal('reclModal');
      showToast('✅ Réclamation soumise avec succès !');
    }

    /* ── TOAST ── */
    function showToast(msg) {
      const c = document.getElementById('toastContainer');
      const t = document.createElement('div');
      t.className = 'toast-item';
      t.innerHTML = msg;
      c.appendChild(t);
      setTimeout(() => {
        t.style.opacity = '0';
        t.style.transition = 'opacity .4s';
        setTimeout(() => t.remove(), 400);
      }, 3000);
    }
  </script>
</body>

</html>