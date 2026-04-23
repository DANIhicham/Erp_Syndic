@extends('layouts.layout')

@section('title', 'Appartements')
@section('page_title', 'Appartements')

@section('content')

    <div class="appartements-module">

    <div class="page-header">
        <div class="header-titles">
            <p class="page-subtitle">Gestion du parc immobilier et suivi des occupations</p>
        </div>
        <button class="btn-primary">
            <i class="fa-solid fa-plus"></i> Ajouter un appartement
        </button>
    </div>

    <div class="row g-4 mb-4">
        {{-- Card 1: Total Appartements --}}
          <div class="col-12 col-sm-6 col-xl-4">
              <div class="kpi-card kpi-blue">
                  <div class="kpi-body">
                      <div class="kpi-info">
                          <span class="kpi-label">Total Appartements</span>
                          <h3 class="kpi-value">24</h3>  
                      </div>
                      <div class="kpi-icon-wrap blue">
                          <i class="fa-solid fa-building"></i>
                      </div>
                  </div>
              </div>
          </div>

        {{-- Card 2: Appartements Loués --}}
          <div class="col-12 col-sm-6 col-xl-4">
              <div class="kpi-card kpi-green">
                  <div class="kpi-body">
                      <div class="kpi-info">
                          <span class="kpi-label">Appartements Loués</span>
                          <h3 class="kpi-value">18</h3>
            
                      </div>
                      <div class="kpi-icon-wrap green">
                          <i class="fa-solid fa-key"></i>
                      </div>
                  </div>
              </div>
          </div>

        {{-- Card 3: Appartements libres--}}
          <div class="col-12 col-sm-6 col-xl-4">
              <div class="kpi-card kpi-gray">
                  <div class="kpi-body">
                      <div class="kpi-info">
                          <span class="kpi-label">Appartements libres</span>
                          <h3 class="kpi-value">4</h3>
            
                      </div>
                      <div class="kpi-icon-wrap gray">
                          <i class="fa-solid fa-door-open"></i>
                      </div>
                  </div>
              </div>
          </div>

        {{-- Card 4: En travaux--}}
          <div class="col-12 col-sm-6 col-xl-4">
              <div class="kpi-card kpi-orange">
                  <div class="kpi-body">
                      <div class="kpi-info">
                          <span class="kpi-label">En travaux</span>
                          <h3 class="kpi-value">2</h3>
            
                      </div>
                      <div class="kpi-icon-wrap orange">
                          <i class="fa-solid fa-hammer"></i>
                      </div>
                  </div>
              </div>
          </div>

    </div>

<div class="toolbar">
    <div class="search-wrapper">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" class="search-input" placeholder="Rechercher un appartement, propriétaire...">
    </div>
    
    <div class="filters-group">
        <select class="filter-select">
            <option>Tous les types</option>
            <option>En Priorité</option>
            <option>En Location</option>
        </select>        
        <select class="filter-select">
            <option>Tous les statuts</option>
            <option>Loué</option>
            <option>Libre</option>
        </select>
    </div>
</div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Unité</th>
                    <th>Résidence</th>
                    <th>Propriétaire Principal</th>
                    <th>Locataire Actuel</th>
                    <th>Loyer</th>
                    <th>Type</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong class="text-dark">Apt. 05</strong>
                        <span class="text-small block">Étage 2 • 85m²</span>
                    </td>
                    <td>Résidence Atlas</td>
                    <td>
                        <div class="user-cell">
                            <div class="tenant-avatar" style="background-color:red;">HK</div>
                            <span class="text-dark">Halim Khabir</span>
                        </div>
                    </td>
                    <td>
                        <div class="user-cell">
                            <div class="tenant-avatar" style="background-color:blue;">KB</div>
                            <span class="text-dark">Karim Benali</span>
                        </div>
                    </td>
                    <td><strong class="text-dark">3 800 MAD</strong></td>
                    <td><span class="badge badge-success">location</span></td>
                    <td><span class="badge badge-success">Loué</span></td>

                    <td>
                        <button class="action-btn"><i class="fa-solid fa-eye"></i></button>
                        <button class="action-btn"><i class="fa-solid fa-pen"></i></button>
                    </td>
                </tr>
                </tbody>
        </table>
    </div>
</div>

@endsection



