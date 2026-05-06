  <header class="erp-navbar">
    <div class="navbar-left">
      <button class="navbar-toggler-custom d-lg-none" id="sidebarOpen"><i class="fa-solid fa-bars"></i></button>
      <div>
        <h1 class="page-title" id="navTitle">@yield('page_title', 'Dashboard')</h1>
        <nav class="d-none d-md-block">
          <ol class="breadcrumb-custom">
            <li><a href="#">Accueil</a></li>
            <li class="sep"><i class="fa-solid fa-chevron-right"></i></li>
            <li class="active" id="navCrumb">@yield('page_title', 'Dashboard')</li>
          </ol>
        </nav>
      </div>
    </div>
    <div class="navbar-right">
      <!--  Sélecteur résidence active -->
      <div class="residence-selector d-none d-md-flex">
        <span class="rs-dot"></span>
        <form method="POST" action="{{ route('set.residence') }}">
            @csrf
            <select name="residence_id" onchange="this.form.submit()" class="form-control-erp">
                @foreach($residences as $residence)
                    <option value="{{ $residence->id }}"
                        {{ session('residence_id') == $residence->id ? 'selected' : '' }}>
                        {{ $residence->nom }}
                    </option>
                @endforeach
            </select>
        </form>
        <i class="fa-solid fa-chevron-down rs-arrow"></i>
      </div>
      <button class="action-btn"><i class="fa-solid fa-bell"></i><span class="notif-dot"></span></button>
      <!-- <button class="user-toggle">
        <div class="user-avatar">AM</div>
        <div class="d-none d-md-block"><span class="u-name">Ahmed M.</span><span class="u-role">Administrateur</span></div>
      </button> -->
        <div class="navbar-user dropdown">
            <button class="user-toggle" data-bs-toggle="dropdown">
                <div class="user-avatar">
                    <span>{{ auth()->user()->initiales }}</span>
                </div>
                <div class="user-details d-none d-md-block">
                    <span class="user-name">{{ auth()->user()->nom }} {{ auth()->user()->prenom }} </span>
                    <span class="user-role">{{ auth()->user()->role }} </span>
                </div>
                <i class="fa-solid fa-chevron-down chevron-icon d-none d-md-block"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end user-dropdown">
                <li class="dropdown-user-header">
                    <div class="duh-avatar">{{ auth()->user()->initiales }}</div>
                    <div>
                        <strong>{{ auth()->user()->nom }} {{ auth()->user()->prenom }}</strong>
                        <small>{{ auth()->user()->email }}</small>
                    </div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#"><i class="fa-solid fa-user me-2"></i>Mon profil</a></li>
                <li><a class="dropdown-item" href="#"><i class="fa-solid fa-gear me-2"></i>Paramètres</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" id="logout-form">
                            @csrf
                            <button type="submit" class="dropdown-item logout-item" style="border:none;background:none;width:100%;text-align:left;">
                                <i class="fa-solid fa-right-from-bracket me-2"></i>Déconnexion
                            </button>
                        </form>
                    </li>
                </li>
            </ul>
        </div>      
    </div>
  </header>