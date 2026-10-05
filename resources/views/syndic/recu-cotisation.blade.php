<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: A4;
            margin: 25mm;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 8pt;
            line-height: 1.5;
            color: #000;
        }
        .container {
            width: 100%;
        }
        .header {
            text-align: center;
            margin-bottom: 50px;
            font-family: 'Times New Roman', Times, serif;
        }
        .header h1 {
            font-size: 16pt;
            text-decoration: underline;
            margin: 5px 0;
            text-transform: uppercase;
        }
        .title {
            text-align: center;
            margin-bottom: 50px;
        }
        .title h2 {
            font-size: 9pt;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .info-row {
            margin-bottom: 40px;
        }
        .label {
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .signature {
            margin-top: 100px;
            text-align: right;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>RÉSIDENCE {{ strtoupper($residence->nom ?? 'SYNDIC') }}</h1>
            <h1>CONSEIL SYNDICAL</h1>
        </div>

        <div class="title">
            <h2>REÇU DE PAIEMENT DES CHARGES DE COPROPRIÉTÉ</h2>
        </div>

        <div class="info-row">
            <span class="label">NOM PROPRIÉTAIRE :</span> {{ $proprietaire->prenom }} {{ $proprietaire->nom }} 
        </div>
        <div class="info-row">
            <span class="label">MONTANT CHARGES ANNUELLES PAR APPARTEMENT :</span> {{ number_format($montantAttendu,0,',',' ') }} MAD
        </div>
        <div class="info-row">
            <span class="label">NUMÉRO APPARTEMENT À PAYER :</span> {{ $appartement->numero }}
        </div>
        <div class="info-row">
            <span class="label">MONTANT À PAYER :</span> {{ number_format($totalPaye,0,',',' ') }} MAD
        </div>
        <div class="info-row">
            <span class="label">MONTANT PAYÉ :</span> {{ number_format($transaction->montant,0,',',' ') }} MAD
        </div>
        <div class="info-row">
            <span class="label">RESTE À PAYER :</span> {{ number_format($reste,0,',',' ') }} MAD
        </div>
        <div class="info-row">
            <span class="label">PÉRIODE D'EFFET :</span> {{ $dateDebut->format('d/m/Y') }} au {{ $dateFin->format('d/m/Y') }}
        </div>
        <div class="info-row">
            <span class="label">MODE DE RÈGLEMENT :</span> {{ strtoupper($transaction->mode_paiement) }}
        </div>

        <div class="signature">
            LE SYNDIC
        </div>
    </div>
</body>
</html>