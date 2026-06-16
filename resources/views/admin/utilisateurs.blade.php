@extends('layouts.layout')

@section('title', 'Utilisateurs')
@section('page_title', 'Utilisateurs')

@section('content')

{{-- ════════════════════════════════════════════════════════════════
     PAGE HEADER
════════════════════════════════════════════════════════════════ --}}
<div class="page-header">
  <div class="ph-left">
    <h2>Utilisateurs</h2>
    <p>Gestion des comptes administrateurs, syndics et locateurs</p>
  </div>
  <div class="ph-right">
    <button class="btn-primary-erp" onclick="openModal('modalAjouter')">
      <i class="fa-solid fa-user-plus"></i> Ajouter utilisateur
    </button>
  </div>
</div>

{{-- ════════════════════════════════════════════════════════════════
     KPI CARDS
════════════════════════════════════════════════════════════════ --}}
<div class="kpi-row" style="grid-template-columns: repeat(4, 1fr)">

  <div class="kpi-card kpi-blue" style="animation-delay:.05s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Total utilisateurs</div>
        <div class="kpi-value">{{ $utilisateurs->count() }}</div>
        <div class="kpi-sub">comptes internes</div>
      </div>
      <div class="kpi-icon blue"><i class="fa-solid fa-users-gear"></i></div>
    </div>
  </div>

  <div class="kpi-card" style="animation-delay:.10s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Admins</div>
        <div class="kpi-value" style="color:#4f46e5">{{ $utilisateurs->where('role','admin')->count() }}</div>
        <div class="kpi-sub">accès total</div>
      </div>
      <div class="kpi-icon" style="background:#eef2ff"><i class="fa-solid fa-user-shield" style="color:#4f46e5"></i></div>
    </div>
  </div>

  <div class="kpi-card kpi-green" style="animation-delay:.15s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Syndics</div>
        <div class="kpi-value">{{ $utilisateurs->where('role','syndic')->count() }}</div>
        <div class="kpi-sub">gestionnaires</div>
      </div>
      <div class="kpi-icon green"><i class="fa-solid fa-briefcase"></i></div>
    </div>
  </div>

  <div class="kpi-card" style="animation-delay:.20s">
    <div class="kpi-top">
      <div>
        <div class="kpi-label">Locateurs</div>
        <div class="kpi-value" style="color:#0891b2">{{ $utilisateurs->where('role','locateur')->count() }}</div>
        <div class="kpi-sub">agents location</div>
      </div>
      <div class="kpi-icon" style="background:#ecfeff"><i class="fa-solid fa-key" style="color:#0891b2"></i></div>
    </div>
  </div>

</div>

{{-- ════════════════════════════════════════════════════════════════
     FILTER BAR
════════════════════════════════════════════════════════════════ --}}
<div class="filter-bar">

  <div class="filter-search">
    <i class="fa-solid fa-magnifying-glass"></i>
    <input
      type="text"
      id="searchInput"
      placeholder="Nom, email, CIN…"
      oninput="filterTable()"
    >
  </div>

  <select id="filterRole" class="filter-select" onchange="filterTable()">
    <option value="">Tous les rôles</option>
    <option value="admin">Admin</option>
    <option value="syndic">Syndic</option>
    <option value="locateur">Locateur</option>
  </select>

  <select id="filterEtat" class="filter-select" onchange="filterTable()">
    <option value="">Tous les états</option>
    <option value="active">Actif</option>
    <option value="desactive">Désactivé</option>
  </select>

  <div class="filter-divider"></div>

  <div class="filter-count">
    <strong id="countVisible">{{ $utilisateurs->count() }}</strong>
    utilisateur{{ $utilisateurs->count() !== 1 ? 's' : '' }}
  </div>

</div>

{{-- ════════════════════════════════════════════════════════════════
     TABLEAU
════════════════════════════════════════════════════════════════ --}}
<div class="table-card">

  <div class="table-card-header">
    <div class="tch-left">
      <h5>Liste des utilisateurs</h5>
      <p>{{ $utilisateurs->count() }} utilisateur{{ $utilisateurs->count() !== 1 ? 's' : '' }} enregistré{{ $utilisateurs->count() !== 1 ? 's' : '' }}</p>
    </div>
  </div>

  <div class="table-responsive">
    <table class="erp-table">
      <thead>
        <tr>
          <th>Utilisateur</th>
          <th>Téléphone</th>
          <th>CIN</th>
          <th>Rôle</th>
          <th>État</th>
          <th>Créé le</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="userTbody">

        @forelse($utilisateurs as $user)
          @php
            $roleConfig = [
              'admin'    => ['label' => 'Admin',    'color' => '#4f46e5', 'bg' => '#eef2ff', 'icon' => 'fa-user-shield'],
              'syndic'   => ['label' => 'Syndic',   'color' => '#059669', 'bg' => '#f0fdf4', 'icon' => 'fa-briefcase'],
              'locateur' => ['label' => 'Locateur', 'color' => '#0891b2', 'bg' => '#ecfeff', 'icon' => 'fa-key'],
            ];
            $rc = $roleConfig[$user->role] ?? ['label' => $user->role, 'color' => '#64748b', 'bg' => '#f1f5f9', 'icon' => 'fa-user'];
            $initiales = strtoupper(substr($user->prenom, 0, 1)) . strtoupper(substr($user->nom, 0, 1));
            $isSelf = $user->id === auth()->id();
          @endphp

          <tr
            data-id="{{ $user->id }}"
            data-role="{{ $user->role }}"
            data-etat="{{ $user->etat }}"
          >

            {{-- Utilisateur --}}
            <td>
              <div class="tenant-cell">
                <div class="t-avatar" style="background:{{ $rc['color'] }}">{{ $initiales }}</div>
                <div>
                  <span class="t-name">
                    {{ $user->prenom }} {{ $user->nom }}
                    @if($isSelf)
                      <span style="font-size:10.5px;color:var(--brand);background:#eef2ff;padding:1px 6px;border-radius:20px;margin-left:4px;font-weight:700">Vous</span>
                    @endif
                  </span>
                  <span class="t-type">{{ $user->email }}</span>
                </div>
              </div>
            </td>

            {{-- Téléphone --}}
            <td><span style="font-size:13px;color:var(--text-3)">{{ $user->telephone ?? '—' }}</span></td>

            {{-- CIN --}}
            <td><span class="ref-code" style="font-size:12px">{{ $user->cin ?? '—' }}</span></td>

            {{-- Rôle --}}
            <td>
              <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;background:{{ $rc['bg'] }};color:{{ $rc['color'] }};border:1px solid {{ $rc['color'] }}22">
                <i class="fa-solid {{ $rc['icon'] }}" style="font-size:11px"></i>
                {{ $rc['label'] }}
              </span>
            </td>

            {{-- État --}}
            <td>
              @if($user->etat === 'active')
                <span class="s-badge active">
                  <i class="fa-solid fa-circle" style="font-size:7px;margin-right:4px"></i>Actif
                </span>
              @else
                <span class="s-badge" style="background:var(--surface-2);color:var(--text-3);border-color:var(--border-2)">
                  <i class="fa-regular fa-circle" style="font-size:7px;margin-right:4px"></i>Désactivé
                </span>
              @endif
            </td>

            {{-- Créé le --}}
            <td>
              <span class="date-text">{{ $user->created_at->format('d/m/Y') }}</span>
            </td>

            {{-- Actions --}}
            <td>
              <div class="row-actions">

                {{-- Modifier --}}
                <button
                  class="ra-btn edit"
                  title="Modifier"
                  onclick="openModifier(
                    {{ $user->id }},
                    '{{ addslashes($user->nom) }}',
                    '{{ addslashes($user->prenom) }}',
                    '{{ $user->email }}',
                    '{{ $user->telephone ?? '' }}',
                    '{{ $user->cin ?? '' }}',
                    '{{ $user->role }}'
                  )"
                >
                  <i class="fa-solid fa-pen-to-square"></i>
                </button>

                {{-- Désactiver / Réactiver (désactivé pour soi-même) --}}
                @if(!$isSelf)
                  @if($user->etat === 'active')
                    <button
                      class="ra-btn"
                      style="color:var(--orange-t)"
                      title="Désactiver"
                      onclick="openToggle({{ $user->id }}, 'desactiver', '{{ addslashes($user->prenom) }} {{ addslashes($user->nom) }}')"
                    >
                      <i class="fa-solid fa-user-minus"></i>
                    </button>
                  @else
                    <button
                      class="ra-btn"
                      style="color:var(--green-t)"
                      title="Réactiver"
                      onclick="openToggle({{ $user->id }}, 'reactiver', '{{ addslashes($user->prenom) }} {{ addslashes($user->nom) }}')"
                    >
                      <i class="fa-solid fa-user-check"></i>
                    </button>
                  @endif
                @endif

                {{-- Supprimer (désactivé pour soi-même) --}}
                @if(!$isSelf)
                  <button
                    class="ra-btn delete"
                    title="Supprimer"
                    onclick="openSupprimer({{ $user->id }}, '{{ addslashes($user->prenom) }} {{ addslashes($user->nom) }}')"
                  >
                    <i class="fa-solid fa-trash"></i>
                  </button>
                @endif

              </div>
            </td>

          </tr>

        @empty
          <tr>
            <td colspan="7" style="text-align:center;padding:40px;color:var(--text-4);font-size:14px">
              <i class="fa-solid fa-users-gear" style="font-size:30px;margin-bottom:10px;display:block"></i>
              Aucun utilisateur trouvé.
            </td>
          </tr>
        @endforelse

      </tbody>
    </table>
  </div>

  <div class="table-footer">
    <span class="tf-info">
      <strong>{{ $utilisateurs->count() }}</strong>
      utilisateur{{ $utilisateurs->count() !== 1 ? 's' : '' }} affiché{{ $utilisateurs->count() !== 1 ? 's' : '' }}
    </span>
  </div>

</div>


{{-- ════════════════════════════════════════════════════════════════
     MODAL — AJOUTER UTILISATEUR
════════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalAjouter">
  <div class="modal-panel">

    <div class="modal-head">
      <div>
        <h3 class="mh-title">Ajouter un utilisateur</h3>
        <p class="mh-sub">Créer un compte Admin, Syndic ou Locateur.</p>
      </div>
      <div class="mh-right">
        <button class="modal-close" onclick="closeModal('modalAjouter')">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>

    <div class="modal-body">

      <div class="form-section-label">
        <i class="fa-solid fa-user" style="color:var(--brand)"></i>
        Informations personnelles
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="form-group">
          <label class="form-label">Nom <span style="color:var(--red-t)">*</span></label>
          <input type="text" id="addNom" class="form-control" placeholder="Ex : Benali">
          <div id="errAddNom" class="form-err"></div>
        </div>
        <div class="form-group">
          <label class="form-label">Prénom <span style="color:var(--red-t)">*</span></label>
          <input type="text" id="addPrenom" class="form-control" placeholder="Ex : Karim">
          <div id="errAddPrenom" class="form-err"></div>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Email <span style="color:var(--red-t)">*</span></label>
        <input type="email" id="addEmail" class="form-control" placeholder="karim@facility.ma">
        <div id="errAddEmail" class="form-err"></div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="form-group">
          <label class="form-label">Téléphone</label>
          <input type="text" id="addTel" class="form-control" placeholder="06 00 00 00 00">
        </div>
        <div class="form-group">
          <label class="form-label">CIN</label>
          <input type="text" id="addCin" class="form-control" placeholder="AB123456">
        </div>
      </div>

      <div class="form-section-label" style="margin-top:6px">
        <i class="fa-solid fa-shield-halved" style="color:var(--brand)"></i>
        Rôle &amp; Accès
      </div>

      {{-- Rôle avec sélecteur visuel --}}
      <div class="form-group">
        <label class="form-label">Rôle <span style="color:var(--red-t)">*</span></label>
        <div id="addRoleCards" style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px">

          <label class="role-card" data-role="admin" onclick="selectRole('addRoleCards','admin')">
            <input type="radio" name="addRole" value="admin" style="display:none">
            <div class="rc-icon" style="background:#eef2ff;color:#4f46e5"><i class="fa-solid fa-user-shield"></i></div>
            <div class="rc-label">Admin</div>
            <div class="rc-sub">Accès total</div>
          </label>

          <label class="role-card" data-role="syndic" onclick="selectRole('addRoleCards','syndic')">
            <input type="radio" name="addRole" value="syndic" style="display:none">
            <div class="rc-icon" style="background:#f0fdf4;color:#059669"><i class="fa-solid fa-briefcase"></i></div>
            <div class="rc-label">Syndic</div>
            <div class="rc-sub">Gestionnaire</div>
          </label>

          <label class="role-card" data-role="locateur" onclick="selectRole('addRoleCards','locateur')">
            <input type="radio" name="addRole" value="locateur" style="display:none">
            <div class="rc-icon" style="background:#ecfeff;color:#0891b2"><i class="fa-solid fa-key"></i></div>
            <div class="rc-label">Locateur</div>
            <div class="rc-sub">Agent location</div>
          </label>

        </div>
        <div id="errAddRole" class="form-err"></div>
      </div>

      <div class="form-section-label" style="margin-top:6px">
        <i class="fa-solid fa-lock" style="color:var(--brand)"></i>
        Mot de passe
      </div>

      <div class="form-group">
        <label class="form-label">Mot de passe <span style="color:var(--red-t)">*</span></label>
        <div style="display:flex;gap:0">
          <input
            type="password"
            id="addPassword"
            class="form-control"
            placeholder="Min. 8 caractères"
            style="border-radius:var(--r-md) 0 0 var(--r-md);border-right:none"
          >
          <button
            type="button"
            onclick="togglePw('addPassword', this)"
            style="padding:0 12px;border:1.5px solid var(--border-2);border-radius:0 var(--r-md) var(--r-md) 0;background:var(--surface-1);color:var(--text-3);cursor:pointer;font-size:14px;transition:all .18s"
          ><i class="fa-solid fa-eye"></i></button>
        </div>
        <div id="errAddPassword" class="form-err"></div>
      </div>

    </div>{{-- /modal-body --}}

    <div class="modal-foot">
      <div class="mf-left"></div>
      <div class="mf-right">
        <button class="btn-outline-erp" onclick="closeModal('modalAjouter')">Annuler</button>
        <button class="btn-primary-erp" id="btnAjouter" onclick="submitAjouter()">
          <i class="fa-solid fa-user-plus"></i> Créer l'utilisateur
        </button>
      </div>
    </div>

  </div>
</div>


{{-- ════════════════════════════════════════════════════════════════
     MODAL — MODIFIER UTILISATEUR
════════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalModifier">
  <div class="modal-panel">

    <div class="modal-head">
      <div>
        <h3 class="mh-title">Modifier l'utilisateur</h3>
        <p class="mh-sub" id="modSubtitle">—</p>
      </div>
      <div class="mh-right">
        <button class="modal-close" onclick="closeModal('modalModifier')">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>

    <div class="modal-body">
      <input type="hidden" id="modId">

      <div class="form-section-label">
        <i class="fa-solid fa-pen-to-square" style="color:var(--brand)"></i>
        Informations personnelles
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="form-group">
          <label class="form-label">Nom <span style="color:var(--red-t)">*</span></label>
          <input type="text" id="modNom" class="form-control">
          <div id="errModNom" class="form-err"></div>
        </div>
        <div class="form-group">
          <label class="form-label">Prénom <span style="color:var(--red-t)">*</span></label>
          <input type="text" id="modPrenom" class="form-control">
          <div id="errModPrenom" class="form-err"></div>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Email <span style="color:var(--red-t)">*</span></label>
        <input type="email" id="modEmail" class="form-control">
        <div id="errModEmail" class="form-err"></div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="form-group">
          <label class="form-label">Téléphone</label>
          <input type="text" id="modTel" class="form-control">
        </div>
        <div class="form-group">
          <label class="form-label">CIN</label>
          <input type="text" id="modCin" class="form-control">
        </div>
      </div>

      <div class="form-section-label" style="margin-top:6px">
        <i class="fa-solid fa-shield-halved" style="color:var(--brand)"></i>
        Rôle
      </div>

      <div class="form-group">
        <label class="form-label">Rôle <span style="color:var(--red-t)">*</span></label>
        <div id="modRoleCards" style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px">

          <label class="role-card" data-role="admin" onclick="selectRole('modRoleCards','admin')">
            <input type="radio" name="modRole" value="admin" style="display:none">
            <div class="rc-icon" style="background:#eef2ff;color:#4f46e5"><i class="fa-solid fa-user-shield"></i></div>
            <div class="rc-label">Admin</div>
            <div class="rc-sub">Accès total</div>
          </label>

          <label class="role-card" data-role="syndic" onclick="selectRole('modRoleCards','syndic')">
            <input type="radio" name="modRole" value="syndic" style="display:none">
            <div class="rc-icon" style="background:#f0fdf4;color:#059669"><i class="fa-solid fa-briefcase"></i></div>
            <div class="rc-label">Syndic</div>
            <div class="rc-sub">Gestionnaire</div>
          </label>

          <label class="role-card" data-role="locateur" onclick="selectRole('modRoleCards','locateur')">
            <input type="radio" name="modRole" value="locateur" style="display:none">
            <div class="rc-icon" style="background:#ecfeff;color:#0891b2"><i class="fa-solid fa-key"></i></div>
            <div class="rc-label">Locateur</div>
            <div class="rc-sub">Agent location</div>
          </label>

        </div>
        <div id="errModRole" class="form-err"></div>
      </div>

      <div class="form-section-label" style="margin-top:6px">
        <i class="fa-solid fa-lock" style="color:var(--brand)"></i>
        Modifier le mot de passe
      </div>

      <div class="form-group">
        <label class="form-label">
          Nouveau mot de passe
          <span style="font-size:11.5px;color:var(--text-4)">(laisser vide = inchangé)</span>
        </label>
        <div style="display:flex;gap:0">
          <input
            type="password"
            id="modPassword"
            class="form-control"
            placeholder="Min. 8 caractères"
            style="border-radius:var(--r-md) 0 0 var(--r-md);border-right:none"
          >
          <button
            type="button"
            onclick="togglePw('modPassword', this)"
            style="padding:0 12px;border:1.5px solid var(--border-2);border-radius:0 var(--r-md) var(--r-md) 0;background:var(--surface-1);color:var(--text-3);cursor:pointer;font-size:14px;transition:all .18s"
          ><i class="fa-solid fa-eye"></i></button>
        </div>
        <div id="errModPassword" class="form-err"></div>
      </div>

    </div>{{-- /modal-body --}}

    <div class="modal-foot">
      <div class="mf-left"></div>
      <div class="mf-right">
        <button class="btn-outline-erp" onclick="closeModal('modalModifier')">Annuler</button>
        <button class="btn-primary-erp" id="btnModifier" onclick="submitModifier()">
          <i class="fa-solid fa-circle-check"></i> Enregistrer
        </button>
      </div>
    </div>

  </div>
</div>


{{-- ════════════════════════════════════════════════════════════════
     MODAL — DÉSACTIVER / RÉACTIVER
════════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalToggle">
  <div class="modal-panel" style="max-width:460px">

    <div class="modal-head">
      <div>
        <h3 class="mh-title" id="toggleTitle">Désactiver l'utilisateur</h3>
        <p class="mh-sub" id="toggleSubtitle">—</p>
      </div>
      <div class="mh-right">
        <button class="modal-close" onclick="closeModal('modalToggle')">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>

    <div class="modal-body">
      <input type="hidden" id="toggleId">

      <div id="toggleAlertBox" style="border-radius:var(--r-md);padding:16px">
        <p style="font-size:14px;color:var(--text-1);margin:0 0 6px 0" id="toggleMsg"></p>
        <p style="font-size:12.5px;color:var(--text-3);margin:0">
          Un utilisateur désactivé ne peut plus se connecter.
          Ses données sont conservées et il peut être réactivé à tout moment.
        </p>
      </div>
    </div>

    <div class="modal-foot">
      <div class="mf-left"></div>
      <div class="mf-right">
        <button class="btn-outline-erp" onclick="closeModal('modalToggle')">Annuler</button>
        <button
          id="btnToggle"
          onclick="submitToggle()"
          style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:var(--r-md);color:#fff;font-size:13px;font-weight:700;border:none;cursor:pointer;font-family:var(--font-b);transition:opacity .2s"
          onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'"
        >Confirmer</button>
      </div>
    </div>

  </div>
</div>


{{-- ════════════════════════════════════════════════════════════════
     MODAL — SUPPRIMER
════════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalSupprimer">
  <div class="modal-panel" style="max-width:460px">

    <div class="modal-head">
      <div>
        <h3 class="mh-title" style="color:var(--red-t)">
          <i class="fa-solid fa-triangle-exclamation" style="margin-right:6px"></i>Supprimer l'utilisateur
        </h3>
        <p class="mh-sub">Cette action est irréversible.</p>
      </div>
      <div class="mh-right">
        <button class="modal-close" onclick="closeModal('modalSupprimer')">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>

    <div class="modal-body">
      <input type="hidden" id="suppId">

      <div style="background:var(--red-bg);border:1px solid rgba(239,68,68,.2);border-radius:var(--r-md);padding:16px">
        <p style="font-size:14px;color:var(--text-1);margin:0 0 6px 0">
          Êtes-vous sûr de vouloir supprimer
          <strong id="suppNom" style="color:var(--red-t)"></strong> ?
        </p>
        <p style="font-size:12.5px;color:var(--text-3);margin:0">
          Le compte sera définitivement supprimé et irrécupérable.
        </p>
      </div>
    </div>

    <div class="modal-foot">
      <div class="mf-left"></div>
      <div class="mf-right">
        <button class="btn-outline-erp" onclick="closeModal('modalSupprimer')">Annuler</button>
        <button
          id="btnSupprimer"
          onclick="submitSupprimer()"
          style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:var(--r-md);background:var(--red-t);color:#fff;font-size:13px;font-weight:700;border:none;cursor:pointer;font-family:var(--font-b);transition:opacity .2s"
          onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'"
        >
          <i class="fa-solid fa-trash"></i> Confirmer la suppression
        </button>
      </div>
    </div>

  </div>
</div>

@endsection


{{-- ════════════════════════════════════════════════════════════════
     JAVASCRIPT
════════════════════════════════════════════════════════════════ --}}
@push('scripts')
<script>

/* ── Config ──────────────────────────────────────────────────────── */
const CSRF    = '{{ csrf_token() }}';
const URL_USR = '{{ url("admin/utilisateurs") }}';

/* ── Modals ──────────────────────────────────────────────────────── */
function openModal(id) {
  document.getElementById(id).classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeModal(id) {
  document.getElementById(id).classList.remove('open');
  document.body.style.overflow = '';
}
document.querySelectorAll('.modal-overlay').forEach(o => {
  o.addEventListener('click', e => { if (e.target === o) closeModal(o.id); });
});
document.addEventListener('keydown', e => {
  if (e.key === 'Escape')
    document.querySelectorAll('.modal-overlay.open').forEach(m => closeModal(m.id));
});

/* ── Filtre tableau ──────────────────────────────────────────────── */
function filterTable() {
  const q    = document.getElementById('searchInput').value.toLowerCase();
  const role = document.getElementById('filterRole').value;
  const etat = document.getElementById('filterEtat').value;
  let visible = 0;

  document.querySelectorAll('#userTbody tr[data-id]').forEach(tr => {
    const mQ = !q    || tr.textContent.toLowerCase().includes(q);
    const mR = !role || tr.dataset.role === role;
    const mE = !etat || tr.dataset.etat === etat;
    const show = mQ && mR && mE;
    tr.style.display = show ? '' : 'none';
    if (show) visible++;
  });

  const c = document.getElementById('countVisible');
  if (c) c.textContent = visible;
}

/* ── Sélecteur de rôle (cartes visuelles) ────────────────────────── */
function selectRole(containerId, role) {
  document.querySelectorAll('#' + containerId + ' .role-card').forEach(card => {
    const isSelected = card.dataset.role === role;
    card.style.borderColor  = isSelected ? 'var(--brand)'   : 'var(--border-2)';
    card.style.background   = isSelected ? '#eef2ff'        : 'var(--surface-1)';
    card.style.boxShadow    = isSelected ? '0 0 0 3px rgba(99,102,241,.15)' : 'none';
    const radio = card.querySelector('input[type="radio"]');
    if (radio) radio.checked = isSelected;
  });
}

function getSelectedRole(containerId) {
  const checked = document.querySelector('#' + containerId + ' input[type="radio"]:checked');
  return checked ? checked.value : '';
}

/* ── Toggle mot de passe ─────────────────────────────────────────── */
function togglePw(inputId, btn) {
  const inp  = document.getElementById(inputId);
  const show = inp.type === 'password';
  inp.type   = show ? 'text' : 'password';
  btn.innerHTML = show
    ? '<i class="fa-solid fa-eye-slash"></i>'
    : '<i class="fa-solid fa-eye"></i>';
}

/* ── Helpers validation ──────────────────────────────────────────── */
function clearErrors(ids) {
  ids.forEach(id => {
    const el = document.getElementById(id);
    if (el) { el.textContent = ''; el.style.display = 'none'; }
  });
}
function showError(id, msg) {
  const el = document.getElementById(id);
  if (el) { el.textContent = msg; el.style.display = 'block'; }
}
function setLoading(btnId, loading, defaultHtml) {
  const btn = document.getElementById(btnId);
  if (!btn) return;
  btn.disabled  = loading;
  btn.innerHTML = loading
    ? '<i class="fa-solid fa-spinner fa-spin" style="margin-right:6px"></i>Chargement…'
    : defaultHtml;
}

/* ── Toast ERP ───────────────────────────────────────────────────── */
function showToast(msg, type = 'success') {
  const map = {
    success: { icon: 'fa-circle-check',        color: 'var(--green-t)',  bg: 'var(--green-bg)',  title: 'Succès'    },
    error:   { icon: 'fa-circle-xmark',         color: 'var(--red-t)',   bg: 'var(--red-bg)',    title: 'Erreur'    },
    warning: { icon: 'fa-triangle-exclamation', color: 'var(--orange-t)',bg: 'var(--orange-bg)', title: 'Attention' },
  };
  const { icon, color, bg, title } = map[type] ?? map.success;
  const el = document.createElement('div');
  el.style.cssText = `
    position:fixed;top:22px;right:22px;z-index:9999;
    display:flex;align-items:center;gap:11px;
    padding:12px 16px;min-width:280px;max-width:360px;
    background:#fff;border-radius:12px;
    box-shadow:0 10px 40px rgba(0,0,0,.13);
    border:1px solid var(--border-2);
    animation:_toastIn .3s ease;font-family:var(--font-b)`;
  el.innerHTML = `
    <div style="width:34px;height:34px;border-radius:8px;flex-shrink:0;display:flex;align-items:center;justify-content:center;background:${bg};color:${color};font-size:15px">
      <i class="fa-solid ${icon}"></i></div>
    <div style="flex:1;min-width:0">
      <div style="font-family:var(--font-h);font-size:13px;font-weight:700;color:var(--text-1)">${title}</div>
      <div style="font-size:12px;color:var(--text-3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${msg}</div>
    </div>`;
  document.body.appendChild(el);
  setTimeout(() => {
    el.style.transition = 'opacity .3s,transform .3s';
    el.style.opacity    = '0';
    el.style.transform  = 'translateX(20px)';
    setTimeout(() => el.remove(), 300);
  }, 4000);
}

/* ── Fetch wrapper ───────────────────────────────────────────────── */
async function apiFetch(url, method, body) {
  const res = await fetch(url, {
    method,
    headers: {
      'Content-Type': 'application/json',
      'Accept':       'application/json',
      'X-CSRF-TOKEN': CSRF,
    },
    body: body ? JSON.stringify(body) : undefined,
  });
  return res.json();
}

/* ════════════════════════════════════════════════════════
   SUBMIT — AJOUTER
════════════════════════════════════════════════════════ */
async function submitAjouter() {
  clearErrors(['errAddNom','errAddPrenom','errAddEmail','errAddRole','errAddPassword']);

  const nom      = document.getElementById('addNom').value.trim();
  const prenom   = document.getElementById('addPrenom').value.trim();
  const email    = document.getElementById('addEmail').value.trim();
  const tel      = document.getElementById('addTel').value.trim();
  const cin      = document.getElementById('addCin').value.trim();
  const role     = getSelectedRole('addRoleCards');
  const password = document.getElementById('addPassword').value;

  let ok = true;
  if (!nom)                          { showError('errAddNom',      'Le nom est requis.');          ok = false; }
  if (!prenom)                       { showError('errAddPrenom',   'Le prénom est requis.');        ok = false; }
  if (!email)                        { showError('errAddEmail',    'L\'email est requis.');         ok = false; }
  if (email && !email.includes('@')) { showError('errAddEmail',    'Email invalide.');              ok = false; }
  if (!role)                         { showError('errAddRole',     'Veuillez choisir un rôle.');   ok = false; }
  if (!password)                     { showError('errAddPassword', 'Le mot de passe est requis.'); ok = false; }
  if (password && password.length < 8) { showError('errAddPassword','Min. 8 caractères.');         ok = false; }
  if (!ok) return;

  setLoading('btnAjouter', true, '<i class="fa-solid fa-user-plus"></i> Créer l\'utilisateur');

  try {
    const data = await apiFetch(URL_USR, 'POST', {
      nom, prenom, email, telephone: tel || null, cin: cin || null, role, password,
    });
    setLoading('btnAjouter', false, '<i class="fa-solid fa-user-plus"></i> Créer l\'utilisateur');

    if (data.success) {
      closeModal('modalAjouter');
      showToast(prenom + ' ' + nom + ' créé avec succès.');
      ['addNom','addPrenom','addEmail','addTel','addCin','addPassword'].forEach(id => {
        document.getElementById(id).value = '';
      });
      selectRole('addRoleCards', ''); // reset cartes
      setTimeout(() => location.reload(), 900);
    } else {
      if (data.errors) {
        if (data.errors.email)    showError('errAddEmail',    data.errors.email[0]);
        if (data.errors.password) showError('errAddPassword', data.errors.password[0]);
      }
      showToast(data.message || 'Erreur lors de la création.', 'error');
    }
  } catch {
    setLoading('btnAjouter', false, '<i class="fa-solid fa-user-plus"></i> Créer l\'utilisateur');
    showToast('Erreur réseau.', 'error');
  }
}

/* ════════════════════════════════════════════════════════
   OUVRIR MODIFIER
════════════════════════════════════════════════════════ */
function openModifier(id, nom, prenom, email, tel, cin, role) {
  document.getElementById('modId').value       = id;
  document.getElementById('modNom').value      = nom;
  document.getElementById('modPrenom').value   = prenom;
  document.getElementById('modEmail').value    = email;
  document.getElementById('modTel').value      = tel;
  document.getElementById('modCin').value      = cin;
  document.getElementById('modPassword').value = '';
  document.getElementById('modSubtitle').textContent = prenom + ' ' + nom;
  selectRole('modRoleCards', role);
  clearErrors(['errModNom','errModPrenom','errModEmail','errModRole','errModPassword']);
  openModal('modalModifier');
}

/* ════════════════════════════════════════════════════════
   SUBMIT — MODIFIER
════════════════════════════════════════════════════════ */
async function submitModifier() {
  clearErrors(['errModNom','errModPrenom','errModEmail','errModRole','errModPassword']);

  const id       = document.getElementById('modId').value;
  const nom      = document.getElementById('modNom').value.trim();
  const prenom   = document.getElementById('modPrenom').value.trim();
  const email    = document.getElementById('modEmail').value.trim();
  const tel      = document.getElementById('modTel').value.trim();
  const cin      = document.getElementById('modCin').value.trim();
  const role     = getSelectedRole('modRoleCards');
  const password = document.getElementById('modPassword').value;

  let ok = true;
  if (!nom)                          { showError('errModNom',    'Le nom est requis.');    ok = false; }
  if (!prenom)                       { showError('errModPrenom', 'Le prénom est requis.'); ok = false; }
  if (!email)                        { showError('errModEmail',  'L\'email est requis.');  ok = false; }
  if (email && !email.includes('@')) { showError('errModEmail',  'Email invalide.');        ok = false; }
  if (!role)                         { showError('errModRole',   'Veuillez choisir un rôle.'); ok = false; }
  if (password && password.length < 8) { showError('errModPassword','Min. 8 caractères.'); ok = false; }
  if (!ok) return;

  setLoading('btnModifier', true, '<i class="fa-solid fa-circle-check"></i> Enregistrer');

  try {
    const body = { nom, prenom, email, telephone: tel || null, cin: cin || null, role };
    if (password) body.password = password;

    const data = await apiFetch(URL_USR + '/' + id, 'PUT', body);
    setLoading('btnModifier', false, '<i class="fa-solid fa-circle-check"></i> Enregistrer');

    if (data.success) {
      closeModal('modalModifier');
      showToast('Utilisateur modifié avec succès.');
      setTimeout(() => location.reload(), 900);
    } else {
      if (data.errors) {
        if (data.errors.email)    showError('errModEmail',    data.errors.email[0]);
        if (data.errors.password) showError('errModPassword', data.errors.password[0]);
      }
      showToast(data.message || 'Erreur.', 'error');
    }
  } catch {
    setLoading('btnModifier', false, '<i class="fa-solid fa-circle-check"></i> Enregistrer');
    showToast('Erreur réseau.', 'error');
  }
}

/* ════════════════════════════════════════════════════════
   OUVRIR TOGGLE (désactiver / réactiver)
════════════════════════════════════════════════════════ */
function openToggle(id, action, nomComplet) {
  document.getElementById('toggleId').value = id;
  const isDesactiver = action === 'desactiver';
 
  document.getElementById('toggleTitle').textContent    = isDesactiver ? 'Désactiver l\'utilisateur' : 'Réactiver l\'utilisateur';
  document.getElementById('toggleSubtitle').textContent = nomComplet;
  document.getElementById('toggleMsg').textContent      = isDesactiver
    ? 'Êtes-vous sûr de vouloir désactiver ' + nomComplet + ' ?'
    : 'Voulez-vous réactiver ' + nomComplet + ' ?';
 
  const box = document.getElementById('toggleAlertBox');
  const btn = document.getElementById('btnToggle');
 
  if (isDesactiver) {
    box.style.cssText    = 'border-radius:var(--r-md);padding:16px;background:var(--orange-bg,#fffbeb);border:1px solid rgba(234,179,8,.2)';
    btn.style.background = 'var(--orange-t,#d97706)';
    btn.innerHTML        = '<i class="fa-solid fa-user-minus"></i> Désactiver';
  } else {
    box.style.cssText    = 'border-radius:var(--r-md);padding:16px;background:var(--green-bg);border:1px solid rgba(34,197,94,.2)';
    btn.style.background = 'var(--green-t)';
    btn.innerHTML        = '<i class="fa-solid fa-user-check"></i> Réactiver';
  }
 
  openModal('modalToggle');
}
 
/* ════════════════════════════════════════════════════════
   SUBMIT — TOGGLE
════════════════════════════════════════════════════════ */
async function submitToggle() {
  const id = document.getElementById('toggleId').value;
  setLoading('btnToggle', true, 'Chargement…');
 
  try {
    const data = await apiFetch(URL_USR + '/' + id + '/toggle-etat', 'PATCH', {});
    setLoading('btnToggle', false, 'Confirmer');
 
    if (data.success) {
      closeModal('modalToggle');
      const type = data.etat === 'active' ? 'success' : 'warning';
      showToast(data.message, type);
      setTimeout(() => location.reload(), 900);
    } else {
      showToast(data.message || 'Erreur.', 'error');
    }
  } catch {
    setLoading('btnToggle', false, 'Confirmer');
    showToast('Erreur réseau.', 'error');
  }
}

/* ════════════════════════════════════════════════════════
   OUVRIR / SUBMIT — SUPPRIMER
════════════════════════════════════════════════════════ */
function openSupprimer(id, nom) {
  document.getElementById('suppId').value = id;
  document.getElementById('suppNom').textContent = nom;
  openModal('modalSupprimer');
}

async function submitSupprimer() {
  const id = document.getElementById('suppId').value;
  setLoading('btnSupprimer', true, '<i class="fa-solid fa-trash"></i> Confirmer la suppression');

  try {
    const data = await apiFetch(URL_USR + '/' + id, 'DELETE', {});
    setLoading('btnSupprimer', false, '<i class="fa-solid fa-trash"></i> Confirmer la suppression');

    if (data.success) {
      closeModal('modalSupprimer');
      showToast(data.message, 'warning');
      setTimeout(() => location.reload(), 900);
    } else {
      showToast(data.message || 'Erreur.', 'error');
    }
  } catch {
    setLoading('btnSupprimer', false, '<i class="fa-solid fa-trash"></i> Confirmer la suppression');
    showToast('Erreur réseau.', 'error');
  }
}

/* ── Styles injectés ─────────────────────────────────────────────── */
const _s = document.createElement('style');
_s.textContent = `
  @keyframes _toastIn { from { opacity:0;transform:translateX(30px) } to { opacity:1;transform:none } }

  .form-err {
    display: none;
    font-size: 12px;
    color: var(--red-t);
    margin-top: 4px;
  }

  /* Cartes de rôle */
  .role-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 14px 10px;
    border: 1.5px solid var(--border-2);
    border-radius: var(--r-md);
    cursor: pointer;
    background: var(--surface-1);
    transition: all .18s;
    text-align: center;
    user-select: none;
  }
  .role-card:hover {
    border-color: var(--brand);
    background: #eef2ff;
  }
  .rc-icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
  }
  .rc-label {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-1);
    font-family: var(--font-h);
  }
  .rc-sub {
    font-size: 11px;
    color: var(--text-4);
  }
`;
document.head.appendChild(_s);

</script>
@endpush
