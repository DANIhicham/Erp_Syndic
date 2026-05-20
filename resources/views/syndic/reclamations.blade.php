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
        <div class="kpi-card kpi-red" style="animation-delay:.05s"><div class="kpi-top"><div><div class="kpi-label">Ouvertes</div><div class="kpi-value"> {{ $stats['ouvertes'] }}</div><div class="kpi-sub"><span class="kpi-badge-alert"><i class="fa-solid fa-fire"></i> {{ $stats['urgentes'] }}  urgentes</span></div></div><div class="kpi-icon red"><i class="fa-solid fa-circle-exclamation"></i></div></div></div>
        <div class="kpi-card kpi-orange" style="animation-delay:.1s"><div class="kpi-top"><div><div class="kpi-label">En cours</div><div class="kpi-value">{{ $stats['en_cours'] }}</div><div class="kpi-sub">Technicien mandaté</div></div><div class="kpi-icon orange"><i class="fa-solid fa-spinner"></i></div></div></div>
        <div class="kpi-card kpi-green" style="animation-delay:.15s"><div class="kpi-top"><div><div class="kpi-label">Résolues</div><div class="kpi-value">{{ $stats['resolues'] }}</div><div class="kpi-sub">Cet Année</div></div><div class="kpi-icon green"><i class="fa-solid fa-check-circle"></i></div></div></div>

      </div>

<!-- ========================================= -->
<!-- FILTER BAR -->
<!-- ========================================= -->

<form method="GET" action="{{ route('reclamations.index') }}">

    <div class="filter-bar">

        <!-- SEARCH -->
        <div class="filter-search">

            <i class="fa-solid fa-magnifying-glass"></i>

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Titre, appartement, description…"
            >

        </div>

        <!-- STATUS -->
        <select class="filter-select" name="statut">

            <option value="">Tous les statuts</option>

            <option value="ouverte"
                {{ request('statut') == 'ouverte' ? 'selected' : '' }}>
                Ouverte
            </option>

            <option value="en_cours"
                {{ request('statut') == 'en_cours' ? 'selected' : '' }}>
                En cours
            </option>

            <option value="resolue"
                {{ request('statut') == 'resolue' ? 'selected' : '' }}>
                Résolue
            </option>

        </select>

        <!-- PRIORITE -->
        <select class="filter-select" name="priorite">

            <option value="">Toutes priorités</option>

            <option value="urgente"
                {{ request('priorite') == 'urgente' ? 'selected' : '' }}>
                Urgente
            </option>

            <option value="haute"
                {{ request('priorite') == 'haute' ? 'selected' : '' }}>
                Haute
            </option>

            <option value="moyenne"
                {{ request('priorite') == 'moyenne' ? 'selected' : '' }}>
                Moyenne
            </option>

            <option value="basse"
                {{ request('priorite') == 'basse' ? 'selected' : '' }}>
                Basse
            </option>

        </select>

        <!-- BTN -->
        <button class="btn-primary-erp" type="submit">

            <i class="fa-solid fa-filter"></i>
            Filtrer

        </button>

        <a href="{{ route('reclamations.index') }}"
           class="btn-outline-erp">

            Réinitialiser

        </a>

        <div class="filter-divider d-none d-md-block"></div>

        <!-- COUNT -->
        <div class="filter-count d-none d-md-block">

            <strong>{{ $reclamations->count() }}</strong>
            réclamations

        </div>

    </div>

</form>

<!-- ========================================= -->
<!-- TABLE -->
<!-- ========================================= -->

<div class="table-card">

    <div class="table-card-header">

        <div class="tch-left">

            <h5>Liste des Réclamations</h5>

            <p>Filtrées par résidence active</p>

        </div>

    </div>

    <div class="table-responsive">

        <table class="erp-table">

            <thead>

                <tr>

                    <th>
                        <input type="checkbox" class="row-check">
                    </th>

                    <th>Titre</th>

                    <th>Appartement</th>

                    <th>Priorité</th>

                    <th>Statut</th>

                    <th>Date création</th>

                    <th>Actions</th>

                </tr>

            </thead>

            <tbody>

                @forelse($reclamations as $rec)

                    <tr @if($rec->priorite == 'urgente')
                        style="background:rgba(239,68,68,.025)"
                    @endif>

                        <!-- CHECK -->
                        <td>
                            <input type="checkbox" class="row-check">
                        </td>

                        <!-- TITRE -->
                        <td>

                            <div>

                                <span class="t-name">
                                    {{ $rec->titre }}
                                </span>

                                <span class="t-type">

                                    Signalé par
                                    {{ $rec->user->prenom ?? '' }}
                                    {{ $rec->user->nom ?? '' }}

                                </span>

                            </div>

                        </td>

                        <!-- APPARTEMENT -->
                        <td>

                            <span class="ref-code">

                                Apt.
                                {{ $rec->appartement->numero ?? '-' }}

                            </span>

                        </td>

                        <!-- PRIORITE -->
                        <td>

                            <span class="prio-badge {{ $rec->priorite }}">

                                @if($rec->priorite == 'urgente')
                                    <i class="fa-solid fa-fire"></i>
                                @endif

                                {{ ucfirst($rec->priorite) }}

                            </span>

                        </td>

                        <!-- STATUS -->
                        <td>

                            <span class="s-badge

                                @if($rec->statut == 'ouverte')
                                    open
                                @elseif($rec->statut == 'en_cours')
                                    in-progress
                                @else
                                    resolved
                                @endif
                            ">

                                @if($rec->statut == 'ouverte')
                                    Ouverte
                                @elseif($rec->statut == 'en_cours')
                                    En cours
                                @else
                                    Résolue
                                @endif

                            </span>

                        </td>

                        <!-- DATE -->
                        <td style="font-size:12.5px;color:var(--text-3)">

                            {{ \Carbon\Carbon::parse($rec->date_creation)->format('d/m/Y') }}

                        </td>

                        <!-- ACTIONS -->
                        <td>

                            <div class="row-actions">

                                <button
                                    class="ra-btn view btn-view-reclamation"

                                    data-titre="{{ $rec->titre }}"
                                    data-user="{{ $rec->user->prenom }} {{ $rec->user->nom }}"
                                    data-appartement="Apt. {{ $rec->appartement->numero ?? '-' }}"
                                    data-priorite="{{ ucfirst($rec->priorite) }}"
                                    data-statut="{{ $rec->statut }}"
                                    data-description="{{ $rec->description }}"
                                    data-date="{{ \Carbon\Carbon::parse($rec->date_creation)->format('d/m/Y') }}"
                                >

                                    <i class="fa-solid fa-eye"></i>

                                </button>

                                <button
                                    class="ra-btn edit btn-fix-reclamation"
                                    data-id="{{ $rec->id }}"
                                >

                                    <i class="fa-solid fa-wrench"></i>

                                </button>
                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="7"
                            style="text-align:center;padding:40px;color:var(--text-3)">

                            Aucune réclamation trouvée

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    <!-- FOOTER -->
    <div class="table-footer">

        <span class="tf-info">

            Total :
            <strong>{{ $reclamations->count() }}</strong>

        </span>

    </div>

</div>

<!-- MODAL RECLAMATION -->
<div class="modal-overlay" id="modalReclamation">

    <div class="modal-panel">

        <!-- HEADER -->
        <div class="modal-head">

            <div>

                <h3 class="mh-title">
                    Nouvelle Réclamation
                </h3>

                <p class="mh-sub">
                    Signaler un problème dans la résidence
                </p>

            </div>

            <div class="mh-right">

                <button class="modal-close"
                        onclick="closeModal('modalReclamation')">

                    <i class="fa-solid fa-xmark"></i>

                </button>

            </div>

        </div>

        <!-- FORM -->
        <form method="POST"
              action="{{ route('reclamations.store') }}">

            @csrf

            <div class="modal-body">

                <div class="form-grid">

                    <!-- TITRE -->
                    <div class="form-group" style="grid-column:1/-1">

                        <label class="form-label">

                            Titre
                            <span class="req">*</span>

                        </label>

                        <input
                            type="text"
                            name="titre"
                            class="form-control-erp"
                            placeholder="ex: Panne ascenseur"
                            required
                        >

                    </div>

                    <!-- APPARTEMENT -->
                    <div class="form-group">

                        <label class="form-label">

                            Appartement / Lieu

                        </label>

                        <select
                            name="appartement_id"
                            class="form-control-erp"
                        >
                            
                            @foreach($appartements as $apt)
                                <option value="{{ $apt->id }}">
                                    Apt. {{ $apt->numero }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- PRIORITE -->
                    <div class="form-group">
                        <label class="form-label">
                            Priorité
                            <span class="req">*</span>
                        </label>

                        <select
                            name="priorite"
                            class="form-control-erp"
                            required
                        >
                            <option value="basse">
                                Basse
                            </option>
                            <option value="moyenne">
                                Moyenne
                            </option>
                            <option value="haute">
                                Haute
                            </option>
                            <option value="urgente">
                                Urgente
                            </option>
                        </select>
                    </div>

                    <!-- DESCRIPTION -->
                    <div class="form-group" style="grid-column:1/-1">
                        <label class="form-label">
                            Description
                            <span class="req">*</span>
                        </label>
                        <textarea
                            name="description"
                            class="form-control-erp"
                            rows="4"
                            placeholder="Décrivez le problème en détail…"
                            required
                        ></textarea>
                    </div>
                </div>
            </div>
            <!-- FOOTER -->
            <div class="modal-foot">
                <div class="mf-left">
                    <button
                        type="button"
                        class="btn-outline-erp"
                        onclick="closeModal('modalReclamation')"
                    >
                        Annuler
                    </button>
                </div>
                <div class="mf-right">
                    <button
                        type="submit"
                        class="btn-primary-erp"
                    >
                        <i class="fa-solid fa-paper-plane"></i>
                        Soumettre
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- ========================================= -->
<!-- MODAL DETAIL RECLAMATION -->
<!-- ========================================= -->

<div class="modal-overlay" id="modalDetailReclamation">

    <div class="modal-panel">

        <!-- HEADER -->
        <div class="modal-head">

            <div>

                <h3 class="mh-title" id="mdr_titre">
                    Réclamation
                </h3>

                <p class="mh-sub" id="mdr_user">
                    Signalé par
                </p>

            </div>

            <button class="modal-close"
                    onclick="closeModal('modalDetailReclamation')">

                <i class="fa-solid fa-xmark"></i>

            </button>

        </div>

        <!-- BODY -->
        <div class="modal-body">

            <div style="display:grid;gap:14px">

                <div>

                    <strong>Appartement :</strong>

                    <div id="mdr_appartement"
                         style="color:var(--text-3)">
                    </div>

                </div>

                <div>

                    <strong>Priorité :</strong>

                    <div id="mdr_priorite"
                         style="color:var(--text-3)">
                    </div>

                </div>

                <div>

                    <strong>Statut :</strong>

                    <div id="mdr_statut"
                         style="color:var(--text-3)">
                    </div>

                </div>

                <div>

                    <strong>Description :</strong>

                    <div id="mdr_description"
                         style="color:var(--text-3);white-space:pre-line">
                    </div>

                </div>

                <div>

                    <strong>Date création :</strong>

                    <div id="mdr_date"
                         style="color:var(--text-3)">
                    </div>

                </div>

            </div>

        </div>

        <!-- FOOT -->
        <div class="modal-foot">

            <button class="btn-outline-erp"
                    onclick="closeModal('modalDetailReclamation')">

                Fermer

            </button>

        </div>

    </div>

</div>

<!-- ========================================= -->
<!-- MODAL FIXER PROBLEME -->
<!-- ========================================= -->

<div class="modal-overlay" id="modalFixerProbleme">

    <div class="modal-panel">

        <!-- HEADER -->
        <div class="modal-head">

            <div>

                <h3 class="mh-title">
                    Résoudre Réclamation
                </h3>

                <p class="mh-sub">
                    Mettre à jour le statut du problème
                </p>

            </div>

            <button class="modal-close"
                    onclick="closeModal('modalFixerProbleme')">

                <i class="fa-solid fa-xmark"></i>

            </button>

        </div>

        <!-- FORM -->
        <form method="POST"
              id="formFixReclamation">

            @csrf

            @method('PUT')

            <div class="modal-body">

                <div class="form-grid">

                    <!-- STATUT -->
                    <div class="form-group">

                        <label class="form-label">

                            Nouveau statut

                        </label>

                        <select name="statut"
                                class="form-control-erp"
                                required>

                            <option value="ouverte">
                                Ouverte
                            </option>

                            <option value="en_cours">
                                En cours
                            </option>

                            <option value="resolue">
                                Résolue
                            </option>

                        </select>

                    </div>

                </div>

            </div>

            <!-- FOOT -->
            <div class="modal-foot">

                <div class="mf-left">

                    <button type="button"
                            class="btn-outline-erp"
                            onclick="closeModal('modalFixerProbleme')">

                        Annuler

                    </button>

                </div>

                <div class="mf-right">

                    <button type="submit"
                            class="btn-primary-erp">

                        <i class="fa-solid fa-check"></i>

                        Enregistrer

                    </button>

                </div>

            </div>

        </form>

    </div>

</div>

<!-- ========================================= -->
<!-- SCRIPT -->
<!-- ========================================= -->

<script>

// DETAIL
document.querySelectorAll('.btn-view-reclamation').forEach(btn => {

    btn.addEventListener('click', function () {

        document.getElementById('mdr_titre').innerText =
            this.dataset.titre;

        document.getElementById('mdr_user').innerText =
            'Signalé par ' + this.dataset.user;

        document.getElementById('mdr_appartement').innerText =
            this.dataset.appartement;

        document.getElementById('mdr_priorite').innerText =
            this.dataset.priorite;

        document.getElementById('mdr_statut').innerText =
            this.dataset.statut;

        document.getElementById('mdr_description').innerText =
            this.dataset.description;

        document.getElementById('mdr_date').innerText =
            this.dataset.date;

        openModal('modalDetailReclamation');

    });

});

// FIXER
document.querySelectorAll('.btn-fix-reclamation').forEach(btn => {

    btn.addEventListener('click', function () {

        let reclamationId = this.dataset.id;

        document.getElementById('formFixReclamation').action =
            '/reclamations/' + reclamationId + '/status';

        openModal('modalFixerProbleme');

    });

});

</script>

@endsection