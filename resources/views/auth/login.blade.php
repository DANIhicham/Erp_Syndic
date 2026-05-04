<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - BLIVING OFFICE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
  <!-- Login CSS -->
  <link href="{{ asset('css/login.css') }}" rel="stylesheet">
</head>

<body>
        <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
    <div class="glow-bg"></div>

        <div class="login-container">

        <!-- STATUS SESSION -->
        <x-auth-session-status class="mb-3" :status="session('status')" />

        <div class="brand-header">
            <div class="logo-icon">
                <i class="fa-solid fa-building-columns"></i>
            </div>
            <h1 class="brand-title">BLIVING</h1>
            <p class="brand-subtitle">ERP Gestion</p>
        </div>

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <!-- EMAIL -->
            <div class="form-group">
                <label class="form-label" for="email">Adresse email</label>
                <div class="input-icon-wrapper">
                    <i class="fa-regular fa-envelope"></i>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="admin@bliving.ma"
                        value="{{ old('email') }}"
                        required 
                        autofocus
                    >
                </div>

                <!-- ERREUR EMAIL -->
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>

            <!-- PASSWORD -->
            <div class="form-group">
                <label class="form-label" for="password">Mot de passe</label>
                <div class="input-icon-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        placeholder="••••••••"
                        required
                    >
                </div>

                <!-- ERREUR PASSWORD -->
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <!-- OPTIONS -->
            <div class="form-options">
                <label class="checkbox-group">
                    <input type="checkbox" name="remember">
                    <span>Se souvenir de moi</span>
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="forgot-link">
                        Mot de passe oublié ?
                    </a>
                @endif
            </div>

            <!-- SUBMIT -->
            <button type="submit" class="btn-submit">
                Se connecter
            </button>

        </form>
    </div>

</body>
</html>