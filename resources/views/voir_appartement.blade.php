@extends('layouts.layout')

@section('title', 'Appartements')
@section('page_title', 'Appartements')

@section('content')

<div class="detail-container">
    <div class="detail-card">
        <div class="card-header-icon">
            <i class="fa-solid fa-crown" style="color: #f59e0b;"></i>
            <h3>Propriétaire Principal</h3>
        </div>
        <div class="user-profile-info">
            <div class="avatar-large">AM</div>
            <div class="info-content">
                <h4 class="text-dark">Ahmed Mansouri</h4>
                <p class="text-muted"><i class="fa-solid fa-phone"></i> +212 6 00 00 00 00</p>
                <p class="text-muted"><i class="fa-solid fa-envelope"></i> a.mansouri@email.com</p>
            </div>
            <span class="badge badge-info">Possède 3 unités</span>
        </div>
    </div>

    <div class="detail-card">
        <div class="card-header-icon">
            <i class="fa-solid fa-user-tag" style="color: #1d4ed8;"></i>
            <h3>Occupant Actuel</h3>
        </div>
        
        <div class="user-profile-info">
            <div class="avatar-large" style="background-color: #1d4ed8;">KB</div>
            <div class="info-content">
                <h4 class="text-dark">Karim Benali</h4>
                <p class="text-muted"><strong>Bail :</strong> 01/01/2024 au 31/12/2024</p>
                <p class="text-muted"><strong>Loyer :</strong> 3 800 MAD / mois</p>
            </div>
            <div class="status-indicator">
                <span class="badge badge-success">À jour de paiement</span>
            </div>
        </div>

        </div>
</div>
@endsection
