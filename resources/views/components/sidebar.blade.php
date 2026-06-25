<aside class="erp-sidebar" id="sidebar">
    <!-- Logo -->
    <div class="sidebar-brand">
        <div class="brand-icon">
            <i class="fa-solid fa-building-columns"></i>
        </div>
        @php
            $residenceActive = $residences->firstWhere('id', session('residence_id'));
        @endphp
        <div class="brand-text">
            <span class="brand-name">LIVING FACILITY</span>
            <span class="brand-tag">Systéme Gestion</span>
        </div>
        <button class="sidebar-toggle d-lg-none" id="sidebarClose">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <div class="nav-section">
            @if(in_array(auth()->user()->role, ['admin', 'syndic']))
            <span class="nav-label">Module Syndic</span>
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="{{ url('/dashboard_syndic') }}" class="nav-link {{ Request::is('dashboard_syndic*') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <i class="fa-solid fa-gauge-high"></i>
                        </span>
                        <span class="nav-text">Dashboard Syndic</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('syndic.cotisations.index') }}" class="nav-link {{ Request::is('cotisations*') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <i class="fa-solid fa-coins"></i>
                        </span>
                        <span class="nav-text">Cotisations</span>
                        <!-- <span class="nav-badge danger" id="nb-cot">3</span> -->
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('depenses.index') }}" class="nav-link {{ Request::is('depenses*') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <i class="fa-solid fa-receipt"></i>
                        </span>
                        <span class="nav-text">Charges & Dépenses</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('budget.index') }}" class="nav-link {{ Request::is('budget*') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <i class="fa-solid fa-scale-balanced"></i>
                        </span>
                        <span class="nav-text">Budget Annuel</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('coproprietaires.index') }}" class="nav-link {{ Request::is('coproprietaires*') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <i class="fa-solid fa-users"></i>
                        </span>
                        <span class="nav-text">Copropriétaires</span>
                        <!-- <span class="nav-count">8</span> -->
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('reclamations.index') }}" class="nav-link {{ Request::is('reclamations*') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </span>
                        <span class="nav-text">Réclamations</span>
                        @if($nbReclamationsOuvertes > 0)
                        <span class="nav-badge danger" id="nb-recl">{{ $nbReclamationsOuvertes }}</span>
                         @endif
                    </a>
                </li>
            </ul>
        </div>
        @endif

        @if(in_array(auth()->user()->role, ['admin', 'locateur']))
        <div class="nav-section">
            <span class="nav-label">Module location</span>
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="{{ route('dashboard-location.index') }}" class="nav-link {{ Request::is('dashboard-location*') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <i class="fa-solid fa-gauge"></i>
                        </span>
                        <span class="nav-text">Dashboard Location</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('contrats.index') }}" class="nav-link {{ Request::is('contrats*') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <i class="fa-solid fa-handshake"></i>
                        </span>
                        <span class="nav-text">Contrats</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('locataires.index') }}" class="nav-link {{ Request::is('locataires*') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <i class="fa-solid fa-key"></i>
                        </span>
                        <span class="nav-text">Locataires</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('paiements-loyer.index') }}" class="nav-link {{ Request::is('paiements-loyer*') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <i class="fa-solid fa-money-bill-1-wave"></i>
                        </span>
                        <span class="nav-text">Paiement Loyer</span>
                    </a>
                </li>
            </ul>
        </div>
        @endif

       @if(auth()->user()->role === 'admin') 
        <div class="nav-section">
            <span class="nav-label">Administration Syndic</span>
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="{{ route('admin.residences.index') }}" class="nav-link {{ Request::is('admin/residences*') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <i class="fa-solid fa-house-chimney"></i>
                            <!-- <i class="fa-solid fa-building-user"></i> -->
                        </span>
                        <span class="nav-text">Résidences</span>
                        
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.appartements.index') }}" class="nav-link {{ Request::is('admin/appartements*') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <i class="fa-solid fa-building"></i>
                        </span>
                        <span class="nav-text">Appartements</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.residents.index') }}" class="nav-link {{ Request::is('admin/residents*') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <i class="fa-solid fa-people-group"></i>
                        </span>
                        <span class="nav-text">Résidents</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="nav-section">
            <span class="nav-label">Gestion des Agents</span>
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="{{ route('admin.utilisateurs.index') }}" class="nav-link {{ Request::is('admin/utilisateurs*') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <i class="fa-solid fa-users-gear"></i>
                        </span>
                        <span class="nav-text">Nos Agents</span>
                    </a>
                </li>
                
            </ul>
        </div>
         @endif 

    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="footer-user">
            <div class="user-avatar-sm">
                <span>{{ auth()->user()->initiales }}</span>
            </div>
            <div class="user-info-sm">
                <span class="user-name-sm">{{ auth()->user()->nom }} {{ auth()->user()->prenom }} </span>
                <span class="user-role-sm">{{ auth()->user()->role }}</span>
            </div>
        </div>
    </div>
</aside>

<!-- Sidebar Overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

@push('scripts')
<script>
    // ── Mobile Sidebar Toggle ───────────────────────────────────
        document.getElementById('sidebarOpen')?.addEventListener('click', () => {
            document.getElementById('sidebar').classList.add('open');
            document.getElementById('sidebarOverlay').classList.add('active');
        });
        ['sidebarClose', 'sidebarOverlay'].forEach(id => {
            document.getElementById(id)?.addEventListener('click', () => {
                document.getElementById('sidebar').classList.remove('open');
                document.getElementById('sidebarOverlay').classList.remove('active');
            });
        });
</script>
@endpush