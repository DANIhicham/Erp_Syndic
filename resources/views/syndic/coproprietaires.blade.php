@extends('layouts.layout')

@section('title', 'Copropriétaires')
@section('page_title', 'Copropriétaires')

@section('content')

<!-- ===================== -->
<!-- HEADER -->
<!-- ===================== -->

<div class="page-header">

    <div class="ph-left">
        <h2>Copropriétaires</h2>
        <p>Consulter les informations des résidents et prendre contact avec eux</p>
    </div>


</div>

<!-- ===================== -->
<!-- FILTERS -->
<!-- ===================== -->

<form method="GET">

<div class="filter-bar">

    <!-- SEARCH -->
    <div class="filter-search">

        <i class="fa-solid fa-magnifying-glass"></i>

        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Nom, prénom, appartement..."
        >

    </div>

    <!-- STATUT -->
    <select name="status" class="filter-select" onchange="this.form.submit()">

        <option value="">Tous les statuts</option>

        <option value="payé" {{ request('status')=='payé'?'selected':'' }}>
            Soldé
        </option>

        <option value="partiel" {{ request('status')=='partiel'?'selected':'' }}>
            Partiel
        </option>

        <option value="en_retard" {{ request('status')=='en_retard'?'selected':'' }}>
            Retard
        </option>

    </select>

    <!-- ANNÉE -->
    <select name="annee" class="filter-select" onchange="this.form.submit()">

        @for($i = now()->year; $i >= 2020; $i--)

            <option value="{{ $i }}"
                {{ $annee == $i ? 'selected' : '' }}>

                {{ $i }}

            </option>

        @endfor

    </select>

     <!-- BTN -->
        <button class="btn-primary-erp" type="submit">

            <i class="fa-solid fa-filter"></i>
            Filtrer

        </button>

        <a href="{{ route('coproprietaires.index') }}"
           class="btn-outline-erp">

            Réinitialiser

        </a>

    <!-- COUNT -->
    <div class="filter-count d-none d-md-block">

        <strong>{{ count($appartements) }}</strong>
        copropriétaires

    </div>

</div>

</form>

<!-- ===================== -->
<!-- CARDS -->
<!-- ===================== -->

<div class="row g-3">

@forelse($appartements as $apt)

    <div class="col-12 col-sm-6 col-xl-4">

        <div class="owner-card">

            <!-- TOP -->
            <div class="oc-top">
                @php
                $colors = ['#4f46e5','#0891b2','#059669','#d97706','#7c3aed','#db2777','#0f766e','#dc2626'];
                $id = (int) ($apt['id'] ?? 0);
                $color = $colors[$id % count($colors)];
                @endphp
                <!-- AVATAR -->
                <div class="oc-avatar" style="background: {{ $color }}">
                    {{ $apt['avatar'] }}
                </div>

                <!-- INFO -->
                <div>

                    <div class="oc-name">
                        {{ $apt['nom'] }}
                    </div>

                    <div class="oc-lot">
                        <i class="fa-solid fa-building"></i>
                        Appartement {{ $apt['numero'] }} · Étage {{ $apt['etage'] }}
                    </div>

                </div>

                <!-- STATUS -->
                <span class="s-badge 
                    @if($apt['status'] == 'payé') paid
                    @elseif($apt['status'] == 'partiel') partial
                    @else late
                    @endif" style="margin-left:auto">

                    {{ $apt['status_label'] }}

                </span>

            </div>

            <!-- STATS -->
            <div class="oc-stats">

                <div class="oc-stat">

                    <div class="oc-stat-val" style="color:var(--green-t)">
                        {{ number_format($apt['montant_paye'], 2, ',', ' ') }}
                    </div>

                    <div class="oc-stat-lbl">MAD payé</div>

                </div>

                <div class="oc-stat">

                    <div class="oc-stat-val" style="color:var(--red-t)">
                        {{ number_format($apt['reste'], 2, ',', ' ') }}
                    </div>

                    <div class="oc-stat-lbl">MAD reste</div>

                </div>

            </div>

            <!-- ÉCHÉANCE -->
            <div style="margin-top:8px">

                <small style="color:var(--text-3)">
                    <i class="fa-regular fa-calendar"></i>
                    Échéance :
                    <strong>{{ $apt['date_echeance'] ?? '--' }}</strong>
                </small>

            </div>

            <!-- ACTIONS -->
            <div style="display:flex;gap:6px;margin-top:12px">

                <!-- DETAIL -->
                <button
                    class="btn-outline-erp btn-detail"
                    style="flex: 1; font-size: 12px; padding: 6px; display: flex; align-items: center; justify-content: center; gap: 5px;"
                    onclick="openModal('modalDetail')"

                    data-name="{{ $apt['nom'] }}"
                    data-email="{{ $apt['email'] }}"
                    data-phone="{{ $apt['telephone'] }}"
                    data-lot=" {{ $apt['numero'] }}"
                    data-contrat="{{ $apt['date_signature_contrat'] }}"
                >

                    <i class="fa-solid fa-eye"></i>
                    Détail

                </button>

                <!-- CONTACT -->
                <button
                    class="btn-outline-erp btn-contact"
                    style="flex: 1; font-size: 12px; padding: 6px; display: flex; align-items: center; justify-content: center; gap: 5px;"
                    

                    data-phone="{{ $apt['telephone'] }}"
                >

                    <i class="fa-solid fa-phone"></i>
                    Contact

                </button>

            </div>

        </div>

    </div>

@empty

    <div style="text-align:center;padding:40px;color:var(--text-3)">
        Aucun copropriétaire trouvé
    </div>

@endforelse

</div>

<!-- ===================== -->
<!-- MODAL DETAIL -->
<!-- ===================== -->

<div class="modal-overlay" id="modalDetail">

    <div class="modal-panel">

        <!-- HEADER -->
        <div class="modal-head">

            <div>
                <h3 class="mh-title" id="md_name">Nom</h3>
                <p class="mh-sub" id="md_lot">Appartement</p>
            </div>

            <button class="modal-close" onclick="closeModal('modalDetail')">
                <i class="fa-solid fa-xmark"></i>
            </button>

        </div>

        <!-- BODY -->
        <div class="modal-body">

            <div style="display:grid;gap:10px">

                <div>
                    <strong>Email :</strong>
                    <div id="md_email" style="color:var(--text-3)"></div>
                </div>

                <div>
                    <strong>Téléphone :</strong>
                    <div id="md_phone" style="color:var(--text-3)"></div>
                </div>

                <div>
                    <strong>Appartement :</strong>
                    <div id="md_lot2" style="color:var(--text-3)"></div>
                </div>
                <div>
                    <strong>Date signature contrat :</strong>
                    <div id="md_contrat" style="color:var(--text-3)"></div>
                </div>

            </div>

        </div>

        <!-- FOOT -->
        <div class="modal-foot">

            <button class="btn-outline-erp" onclick="closeModal('modalDetail')">
                Fermer
            </button>

        </div>

    </div>

</div>


<!-- ===================== -->
<!-- SCRIPT SIMPLE -->
<!-- ===================== -->

<script>

// CONTACT WhatsApp
document.querySelectorAll('.btn-contact').forEach(btn => {
    btn.addEventListener('click', function () {

        let phone = this.dataset.phone;

        if (phone) {
            window.open('https://wa.me/' + phone, '_blank');
        }

    });
});

document.querySelectorAll('.btn-detail').forEach(btn => {

    btn.addEventListener('click', function () {

        document.getElementById('md_name').innerText  = this.dataset.name;
        document.getElementById('md_email').innerText = this.dataset.email || '-';
        document.getElementById('md_phone').innerText = this.dataset.phone || '-';
        document.getElementById('md_lot').innerText   = this.dataset.lot;
        document.getElementById('md_lot2').innerText   = this.dataset.lot;
        document.getElementById('md_contrat').innerText = this.dataset.contrat || '-';
        openModal('modalDetail');

    });

});

</script>

@endsection