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

    <div class="glow-bg"></div>

    <div class="login-container">
        <div class="brand-header">
            <div class="logo-icon">
                <i class="fa-solid fa-building-columns"></i>
            </div>
            <h1 class="brand-title">BLIVING OFFICE</h1>
            <p class="brand-subtitle">ERP Gestion</p>
        </div>

        <form action="/login" method="POST">
            
            <div class="form-group">
                <label class="form-label" for="email">Adresse email</label>
                <div class="input-icon-wrapper">
                    <i class="fa-regular fa-envelope"></i>
                    <input type="email" id="email" name="email" class="form-input" placeholder="admin@bliving.ma" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Mot de passe</label>
                <div class="input-icon-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
                </div>
            </div>

            <div class="form-options">
                <label class="checkbox-group">
                    <input type="checkbox" name="remember">
                    <span>Se souvenir de moi</span>
                </label>
                <a href="#" class="forgot-link">Mot de passe oublié ?</a>
            </div>

            <button type="submit" class="btn-submit">Se connecter</button>
        </form>
    </div>

</body>
</html>