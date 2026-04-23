<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Exemple Loader Laravel</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Loader plein écran */
        #pageLoader {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(255,255,255,0.9);
            z-index: 9999;
            text-align: center;
        }
        #pageLoader .spinner-border {
            margin-top: 20%;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h2>Exemple de Loader</h2>

    <!-- Formulaire avec bouton -->
    <form method="POST" action="{{ route('test.submit') }}">
        @csrf
        <button id="submitBtn" type="submit" class="btn btn-primary">
            Envoyer
        </button>
    </form>
</div>

<!-- Loader global -->
<div id="pageLoader">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Chargement...</span>
    </div>
    <p>Chargement en cours...</p>
</div>

<!-- Bootstrap JS + Script Loader -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Loader lors du clic sur bouton
    document.getElementById('submitBtn').addEventListener('click', function() {
        document.getElementById('pageLoader').style.display = 'block';
    });

    // Loader lors du changement de page
    window.addEventListener('beforeunload', function () {
        document.getElementById('pageLoader').style.display = 'block';
    });
</script>

</body>
</html>
