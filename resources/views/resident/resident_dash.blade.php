<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Espace Résident – Syndic Bliving</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <link href="{{ asset('css/resident.css') }}" rel="stylesheet">
</head>

<body>

  {{-- ═══════════════════════════════════════════════════════
       SIDEBAR OVERLAY
  ═══════════════════════════════════════════════════════ --}}
  <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

  {{-- ═══════════════════════════════════════════════════════
       SIDEBAR — données dynamiques
  ═══════════════════════════════════════════════════════ --}}
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <div class="logo-text">Syndic ERP</div>
      <div class="logo-sub">Espace résident</div>
    </div>
    <ul class="sidebar-nav">
      <li>
        <a href="{{ route('resident.dashboard') }}" class="active">
          <i class="bi bi-grid-1x2-fill"></i> Tableau de bord
        </a>
      </li>
    </ul>
    <div class="sidebar-footer">
      <div class="avatar-pill">
        {{-- Initiales dynamiques --}}
        <div class="avatar-circle">
          {{ strtoupper(substr($user->prenom, 0, 1)) }}{{ strtoupper(substr($user->nom, 0, 1)) }}
        </div>
        <div class="avatar-info">
          <div class="av-name">{{ $user->prenom }} {{ $user->nom }}</div>
          <div class="av-apt">Apt. {{ $appartement->numero }}</div>
        </div>
      </div>
      <a href="{{ route('logout') }}"
         onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
         style="margin-top:4px">
        <i class="bi bi-box-arrow-left"></i> Déconnexion
      </a>
      <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none">
        @csrf
      </form>
    </div>
  </aside>

  {{-- ═══════════════════════════════════════════════════════
       MAIN WRAPPER
  ═══════════════════════════════════════════════════════ --}}
  <div class="main-wrapper">

    <button class="btn-hamburger" onclick="openSidebar()"><i class="bi bi-list"></i></button>

    <main class="page-content">

      {{-- ═══════════════════════════════════════════════════
           HERO HEADER — 100% dynamique
      ═══════════════════════════════════════════════════ --}}
      <div class="resident-hero">
        <div class="hero-left">
          <div class="greeting">Bienvenue dans votre espace</div>
          <div class="resident-name">{{ $user->prenom }} {{ $user->nom }}</div>
          <div class="apt-badge">
            <i class="bi bi-house-door-fill" style="font-size:.75rem"></i>
            Appartement {{ $appartement->numero }}
            · {{ $residence->nom }}
            · Étage {{ $appartement->etage }}
          </div>
        </div>
        <div class="hero-right">
          <div class="hero-stat">
            <div class="hs-val">{{ number_format($budgetAnnuel, 0, ',', ' ') }}</div>
            <div class="hs-lbl">Budget annuel (MAD)</div>
          </div>
          <div class="hero-stat">
            <div class="hs-val">{{ number_format($montantPaye, 0, ',', ' ') }}</div>
            <div class="hs-lbl">Payé cette année</div>
          </div>
          <div class="hero-stat">
            <div class="hs-val">{{ $nbReclamations }}</div>
            <div class="hs-lbl">Réclamations</div>
          </div>
        </div>
      </div>

      {{-- ═══════════════════════════════════════════════════
           ROW : Solde + Tableau paiements
      ═══════════════════════════════════════════════════ --}}
      <div class="row g-4 mb-4">

        {{-- ── CARTE SOLDE DE L'ANNÉE ──────────────────────── --}}
        <div class="col-lg-4">
          <div class="section-header">
            <div class="section-title"><i class="bi bi-wallet2"></i> Solde de l'année</div>
          </div>

          {{-- Couleur de la carte selon statut --}}
          @php
            $cardClass = match($statutAnnee) {
              'payé'      => 'success-card',
              'partiel'   => 'warning-card',
              'en_retard' => 'danger-card',
              default     => 'warning-card',
            };
            $iconStatut = match($statutAnnee) {
              'payé'      => 'bi-check-circle-fill',
              'partiel'   => 'bi-clock-history',
              'en_retard' => 'bi-exclamation-triangle-fill',
              default     => 'bi-hourglass-split',
            };
            $barColor = match($statutAnnee) {
              'payé'      => 'var(--success)',
              'partiel'   => 'var(--warning)',
              'en_retard' => 'var(--danger)',
              default     => 'var(--warning)',
            };
            $badgeStatut = match($statutAnnee) {
              'payé'      => ['class' => 'badge-paye',   'label' => 'Payé'],
              'partiel'   => ['class' => 'badge-partiel','label' => 'Partiel'],
              'en_retard' => ['class' => 'badge-retard', 'label' => 'En retard'],
              default     => ['class' => 'badge-partiel','label' => 'En attente'],
            };
          @endphp

          <div class="reste-card {{ $cardClass }} mb-3" id="resteCard">
            <div class="reste-left">
              <div class="reste-label">
                Reste à payer – {{ $annee }}
                <span class="badge-status {{ $badgeStatut['class'] }} ms-2" id="badgeStatut">
                  {{ $badgeStatut['label'] }}
                </span>
              </div>
              <div class="reste-amount" id="resteAmount">
                {{ number_format($resteAPayer, 2, ',', ' ') }}
                <small style="font-size:1rem">MAD</small>
              </div>
              <div class="reste-year">
                Sur un total de {{ number_format($montantAttendu, 2, ',', ' ') }} MAD
              </div>
              <div class="paiement-progress mt-2" style="width:180px">
                <div class="paiement-progress-fill"
                     id="progressBar"
                     style="width:{{ $pourcentage }}%; background:{{ $barColor }}">
                </div>
              </div>
              <div style="font-size:.72rem;color:var(--text-mid);margin-top:4px" id="progressLabel">
                <span id="pourcentageLabel">{{ $pourcentage }}</span>% payé
                · Échéance : {{ $dateEcheance->format('d/m/Y') }}
              </div>
            </div>
            <div class="reste-icon">
              <i class="bi {{ $iconStatut }}"></i>
            </div>
          </div>

          {{-- Historique annuel --}}
          <div class="card-clean">
            <div class="card-body-p">
              <div style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-mid);margin-bottom:14px">
                Historique annuel
              </div>
              <div style="display:flex;flex-direction:column;gap:10px">

                @forelse($historiques as $hist)
                  @php
                    $histBadge = match($hist['statut']) {
                      'payé'      => 'badge-paye',
                      'partiel'   => 'badge-partiel',
                      'en_retard' => 'badge-retard',
                      default     => 'badge-partiel',
                    };
                    $histLabel = match($hist['statut']) {
                      'payé'      => 'Payé',
                      'partiel'   => 'Partiel',
                      'en_retard' => 'En retard',
                      default     => 'En attente',
                    };
                    $histMontantLabel = $hist['statut'] === 'payé'
                      ? number_format($hist['paye'], 0, ',', ' ') . ' MAD'
                      : number_format($hist['paye'], 0, ',', ' ') . ' / ' . number_format($hist['attendu'], 0, ',', ' ');
                  @endphp
                  <div style="display:flex;justify-content:space-between;align-items:center">
                    <span style="font-size:.83rem;color:var(--text-dark)">{{ $hist['annee'] }}</span>
                    <div style="display:flex;align-items:center;gap:8px">
                      <span class="mono" style="font-size:.8rem">{{ $histMontantLabel }}</span>
                      <span class="badge-status {{ $histBadge }}">{{ $histLabel }}</span>
                    </div>
                  </div>
                @empty
                  <div style="font-size:.82rem;color:var(--text-mid);text-align:center;padding:12px 0">
                    Aucun historique disponible.
                  </div>
                @endforelse

              </div>
            </div>
          </div>
        </div>

        {{-- ── TABLEAU HISTORIQUE DES PAIEMENTS ───────────── --}}
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
                <tbody id="paiementsBody">

                  @forelse($transactions as $t)
                    @php
                      $tBadge = match($t['statut']) {
                        'payé'      => 'badge-paye',
                        'partiel'   => 'badge-partiel',
                        'en_retard' => 'badge-retard',
                        default     => 'badge-partiel',
                      };
                      $tLabel = match($t['statut']) {
                        'payé'      => 'Payé',
                        'partiel'   => 'Partiel',
                        'en_retard' => 'En retard',
                        default     => 'En attente',
                      };
                      $resteColor = match($t['statut']) {
                        'payé'      => 'var(--success)',
                        'partiel'   => 'var(--warning)',
                        'en_retard' => 'var(--danger)',
                        default     => 'var(--warning)',
                      };
                      $modeIcon = match($t['mode'] ?? '') {
                        'Virement'       => '💳',
                        'Chèque'         => '🏦',
                        'Espèces'        => '💵',
                        'Carte bancaire' => '💳',
                        default          => '💰',
                      };
                      $echeanceColor = $t['statut'] === 'en_retard'
                        ? 'color:var(--danger)'
                        : 'color:var(--text-mid)';
                    @endphp
                    <tr>
                      <td class="mono">{{ $t['annee'] }}</td>
                      <td class="mono">{{ number_format($t['montant'], 2, ',', ' ') }} MAD</td>
                      <td class="mono" style="color:{{ $resteColor }};font-weight:600">
                        {{ number_format($t['reste'], 2, ',', ' ') }} MAD
                      </td>
                      <td>{{ $t['date_paiement'] ?? '—' }}</td>
                      <td style="{{ $echeanceColor }}">{{ $t['echeance'] }}</td>
                      <td>
                        <span style="font-size:.8rem">
                          {{ $modeIcon }} {{ $t['mode'] ?? '—' }}
                        </span>
                      </td>
                      <td><span class="badge-status {{ $tBadge }}">{{ $tLabel }}</span></td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="7" style="text-align:center;color:var(--text-mid);padding:24px;font-size:.85rem">
                        <i class="bi bi-inbox" style="font-size:1.5rem;display:block;margin-bottom:6px"></i>
                        Aucun paiement enregistré.
                      </td>
                    </tr>
                  @endforelse

                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      {{-- ═══════════════════════════════════════════════════
           SECTION RÉCLAMATIONS
      ═══════════════════════════════════════════════════ --}}
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

              @forelse($reclamations as $recl)
                @php
                  $prioCls = match(strtolower($recl->priorite)) {
                    'haute'   => 'prio-haute',
                    'urgente' => 'prio-urgente',
                    'moyenne' => 'prio-moyenne',
                    default   => 'prio-basse',
                  };
                  $statutCls = match(strtolower($recl->statut)) {
                    'en cours'   => 'badge-en-cours',
                    'résolue', 'resolue' => 'badge-resolue',
                    default      => 'badge-ouverte',
                  };
                  $statutLabel = match(strtolower($recl->statut)) {
                    'en_cours', 'en cours' => 'En cours',
                    'résolue', 'resolue'   => 'Résolue',
                    default                => 'Ouverte',
                  };
                @endphp
                <tr>
                  <td class="mono" style="color:var(--text-light)">
                    #{{ str_pad($recl->id, 3, '0', STR_PAD_LEFT) }}
                  </td>
                  <td style="font-weight:600">{{ $recl->titre }}</td>
                  <td>
                    <span class="badge-prio {{ $prioCls }}">{{ $recl->priorite }}</span>
                  </td>
                  <td style="color:var(--text-mid);max-width:260px;font-size:.82rem">
                    {{ Str::limit($recl->description, 10) }}
                  </td>
                  <td>
                    <span class="badge-status {{ $statutCls }}">{{ $statutLabel }}</span>
                  </td>
                  <td style="color:var(--text-mid);font-size:.82rem">
                    {{ \Carbon\Carbon::parse($recl->date_creation)->format('d/m/Y') }}
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" style="text-align:center;color:var(--text-mid);padding:24px;font-size:.85rem">
                    <i class="bi bi-chat-dots" style="font-size:1.5rem;display:block;margin-bottom:6px"></i>
                    Aucune réclamation soumise.
                  </td>
                </tr>
              @endforelse

            </tbody>
          </table>
        </div>
      </div>

    </main>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       MODALS
  ═══════════════════════════════════════════════════════ --}}

  {{-- ── MODAL : NOUVELLE RÉCLAMATION ──────────────────────── --}}
  <div class="modal-backdrop-custom" id="reclModal">
    <div class="modal-box">
      <div class="modal-header-custom">
        <div>
          <div class="modal-title-text">Nouvelle réclamation</div>
          <div class="modal-subtitle">Décrivez votre problème, nous vous répondrons rapidement.</div>
        </div>
        <button class="btn-close-custom" onclick="closeModal('reclModal')">
          <i class="bi bi-x"></i>
        </button>
      </div>
      <div class="modal-body-custom">

        {{-- Titre --}}
        <div class="mb-3">
          <label class="form-label-custom">Titre</label>
          <input type="text" id="reclTitre" class="form-control-custom"
            placeholder="Ex: Fuite d'eau dans les communs…" />
          <div id="errTitre" style="font-size:.78rem;color:var(--danger);margin-top:4px;display:none"></div>
        </div>

        {{-- Priorité --}}
        <div class="mb-3">
          <label class="form-label-custom">Priorité</label>
          <div class="prio-selector">
            <button class="prio-btn" data-prio="basse"   onclick="selectPrio(this)">🟢 Basse</button>
            <button class="prio-btn" data-prio="moyenne" onclick="selectPrio(this)">🟡 Moyenne</button>
            <button class="prio-btn" data-prio="haute"   onclick="selectPrio(this)">🟠 Haute</button>
            <button class="prio-btn" data-prio="urgente" onclick="selectPrio(this)">🔴 Urgente</button>
          </div>
          <input type="hidden" id="reclPrio" value="" />
          <div id="errPrio" style="font-size:.78rem;color:var(--danger);margin-top:4px;display:none"></div>
        </div>

        {{-- Description --}}
        <div class="mb-1">
          <label class="form-label-custom">Description</label>
          <textarea id="reclDesc" class="form-control-custom"
            placeholder="Décrivez le problème en détail : emplacement, date d'apparition, impact…"></textarea>
          <div id="errDesc" style="font-size:.78rem;color:var(--danger);margin-top:4px;display:none"></div>
        </div>

      </div>
      <div class="modal-footer-custom">
        <button class="btn-cancel" onclick="closeModal('reclModal')">Annuler</button>
        <button class="btn-primary-custom" id="btnReclSubmit" onclick="submitReclamation()">
          <i class="bi bi-send"></i> Soumettre
        </button>
      </div>
    </div>
  </div>


  {{-- TOAST --}}
  <div class="toast-container-custom" id="toastContainer"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  /* ════════════════════════════════════════════════════════
     CONFIG — URLs + CSRF passés depuis PHP
  ════════════════════════════════════════════════════════ */
  const CSRF        = '{{ csrf_token() }}';
  const URL_RECL    = '{{ route("resident.reclamations.store") }}';
  const ANNEE_COURANTE = {{ $annee }};

  /* ════════════════════════════════════════════════════════
     SIDEBAR
  ════════════════════════════════════════════════════════ */
  function openSidebar() {
    document.getElementById('sidebar').classList.add('open');
    document.getElementById('sidebarOverlay').classList.add('open');
  }
  function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('open');
  }

  /* ════════════════════════════════════════════════════════
     MODALS
  ════════════════════════════════════════════════════════ */
  function openModal(id) {
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
  }
  // Fermer en cliquant sur l'overlay
  document.querySelectorAll('.modal-backdrop-custom').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) closeModal(m.id); });
  });
  // Fermer avec Echap
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      document.querySelectorAll('.modal-backdrop-custom.open').forEach(m => closeModal(m.id));
    }
  });

  /* ════════════════════════════════════════════════════════
     PRIORITÉ
  ════════════════════════════════════════════════════════ */
  let selectedPrio = '';
  function selectPrio(btn) {
    document.querySelectorAll('.prio-btn').forEach(b => b.className = 'prio-btn');
    const p = btn.dataset.prio.toLowerCase();
    btn.classList.add('selected-' + p);
    selectedPrio = btn.dataset.prio;
    document.getElementById('reclPrio').value = selectedPrio;
    document.getElementById('errPrio').style.display = 'none';
  }

  /* ════════════════════════════════════════════════════════
     SOUMETTRE RÉCLAMATION — AJAX
  ════════════════════════════════════════════════════════ */
  function submitReclamation() {
    const titre = document.getElementById('reclTitre').value.trim();
    const desc  = document.getElementById('reclDesc').value.trim();
    const prio  = selectedPrio;

    // Validation côté client
    let ok = true;
    if (!titre) {
      document.getElementById('errTitre').textContent = 'Le titre est obligatoire.';
      document.getElementById('errTitre').style.display = 'block';
      ok = false;
    } else { document.getElementById('errTitre').style.display = 'none'; }

    if (!prio) {
      document.getElementById('errPrio').textContent = 'Veuillez choisir une priorité.';
      document.getElementById('errPrio').style.display = 'block';
      ok = false;
    } else { document.getElementById('errPrio').style.display = 'none'; }

    if (desc.length < 10) {
      document.getElementById('errDesc').textContent = 'La description doit faire au moins 10 caractères.';
      document.getElementById('errDesc').style.display = 'block';
      ok = false;
    } else { document.getElementById('errDesc').style.display = 'none'; }

    if (!ok) return;

    // État loading
    const btn = document.getElementById('btnReclSubmit');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Envoi…';

    fetch(URL_RECL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept':       'application/json',
        'X-CSRF-TOKEN': CSRF,
      },
      body: JSON.stringify({ titre, description: desc, priorite: prio }),
    })
    .then(r => r.json())
    .then(data => {
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-send"></i> Soumettre';

      if (!data.success) {
        showToast('❌ ' + (data.message || 'Une erreur est survenue.'));
        return;
      }

      // Ajouter la ligne dans le tableau sans rechargement
      const r = data.reclamation;
      const prioCls = {
        basse: 'prio-basse', moyenne: 'prio-moyenne',
        haute: 'prio-haute', urgente: 'prio-urgente',
      }[r.priorite.toLowerCase()] || 'prio-basse';

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="mono" style="color:var(--text-light)">${r.ref}</td>
        <td style="font-weight:600">${escHtml(r.titre)}</td>
        <td><span class="badge-prio ${prioCls}">${r.priorite}</span></td>
        <td style="color:var(--text-mid);max-width:260px;font-size:.82rem">
          ${escHtml(r.description.substring(0, 100))}${r.description.length > 100 ? '…' : ''}
        </td>
        <td><span class="badge-status badge-ouverte">Ouverte</span></td>
        <td style="color:var(--text-mid);font-size:.82rem">${r.date}</td>`;
      document.getElementById('reclBody').prepend(tr);

      // Reset formulaire
      document.getElementById('reclTitre').value = '';
      document.getElementById('reclDesc').value  = '';
      document.querySelectorAll('.prio-btn').forEach(b => b.className = 'prio-btn');
      selectedPrio = '';
      document.getElementById('reclPrio').value = '';

      // Mise à jour compteur hero
      const hsStat = document.querySelectorAll('.hero-stat .hs-val');
      if (hsStat[2]) hsStat[2].textContent = parseInt(hsStat[2].textContent || 0) + 1;

      closeModal('reclModal');
      showToast('✅ Réclamation soumise avec succès !');
    })
    .catch(() => {
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-send"></i> Soumettre';
      showToast('❌ Erreur réseau. Veuillez réessayer.');
    });
  }


  /* ── Mise à jour UI solde en temps réel ─────────────────── */
  function updateSoldeUI(data) {
    const pct    = data.pourcentage;
    const reste  = data.reste;
    const statut = data.statut;

    // Pourcentage
    document.getElementById('progressBar').style.width = pct + '%';
    document.getElementById('pourcentageLabel').textContent = pct;

    // Montant reste
    document.getElementById('resteAmount').innerHTML =
      formatMAD(reste) + ' <small style="font-size:1rem">MAD</small>';

    // Couleur barre + carte
    const card = document.getElementById('resteCard');
    const barColors = { paye: 'var(--success)', partiel: 'var(--warning)', en_retard: 'var(--danger)' };
    const cardClasses = ['success-card', 'warning-card', 'danger-card'];
    cardClasses.forEach(c => card.classList.remove(c));

    if (statut === 'payé') {
      card.classList.add('success-card');
      document.getElementById('progressBar').style.background = 'var(--success)';
    } else if (statut === 'partiel') {
      card.classList.add('warning-card');
      document.getElementById('progressBar').style.background = 'var(--warning)';
    } else {
      card.classList.add('danger-card');
      document.getElementById('progressBar').style.background = 'var(--danger)';
    }

    // Badge statut
    const badgeEl = document.getElementById('badgeStatut');
    const labels  = { paye: 'Payé', partiel: 'Partiel', en_retard: 'En retard', en_attente: 'En attente' };
    const badgeCls= { paye: 'badge-paye', partiel: 'badge-partiel', en_retard: 'badge-retard', en_attente: 'badge-partiel' };
    badgeEl.className = 'badge-status ' + (badgeCls[statut] || 'badge-partiel') + ' ms-2';
    badgeEl.textContent = labels[statut] || 'En attente';

    // Mise à jour hero "Payé cette année"
    const hsStat = document.querySelectorAll('.hero-stat .hs-val');
    if (hsStat[1]) {
      const paye = data.montant_paye;
      hsStat[1].textContent = formatNumber(paye);
    }
  }



  /* ════════════════════════════════════════════════════════
     TOAST
  ════════════════════════════════════════════════════════ */
  function showToast(msg) {
    const c = document.getElementById('toastContainer');
    const t = document.createElement('div');
    t.className = 'toast-item';
    t.innerHTML = msg;
    c.appendChild(t);
    setTimeout(() => {
      t.style.opacity    = '0';
      t.style.transition = 'opacity .4s';
      setTimeout(() => t.remove(), 400);
    }, 3000);
  }

  /* ════════════════════════════════════════════════════════
     HELPERS
  ════════════════════════════════════════════════════════ */
  function formatMAD(n) {
    return Number(n).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }
  function formatNumber(n) {
    return Number(n).toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
  }
  function escHtml(s) {
    return String(s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }
  </script>

</body>
</html>