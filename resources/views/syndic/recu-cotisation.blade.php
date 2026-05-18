<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Reçu de paiement</title>

    <style>
        @page {
            size: A4;
            margin: 18mm;
        }

        body {
            font-family: Arial, sans-serif;
            color: #111;
            font-size: 14px;
            line-height: 1.5;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 24px;
            margin: 0;
        }

        .header h2 {
            font-size: 18px;
            margin-top: 10px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        .table td {
            padding: 12px;
            border: 1px solid #222;
        }

        .label {
            width: 45%;
            font-weight: bold;
            background: #f5f5f5;
        }

        .footer {
            margin-top: 80px;
            text-align: right;
            font-weight: bold;
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 18px;
            background: #111;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        @media print {
            .print-btn {
                display: none;
            }
        }
    </style>
</head>

<body>

    

    <div class="header">
        <h1>RESIDENCE {{ strtoupper($residence->nom ?? 'SYNDIC') }}</h1>
        <h2>REÇU DE PAIEMENT DES CHARGES DE COPROPRIÉTÉ</h2>
    </div>

    <table class="table">

        <tr>
            <td class="label">Nom propriétaire</td>
            <td>{{ $proprietaire->prenom }} {{ $proprietaire->nom }}</td>
        </tr>

        <tr>
            <td class="label">Appartement</td>
            <td>{{ $appartement->numero }}</td>
        </tr>

        <tr>
            <td class="label">Montant annuel</td>
            <td>{{ number_format($montantAttendu,0,',',' ') }} MAD</td>
        </tr>

        <tr>
            <td class="label">Montant payé</td>
            <td>{{ number_format($transaction->montant,0,',',' ') }} MAD</td>
        </tr>

        <tr>
            <td class="label">Total payé</td>
            <td>{{ number_format($totalPaye,0,',',' ') }} MAD</td>
        </tr>

        <tr>
            <td class="label">Reste à payer</td>
            <td>{{ number_format($reste,0,',',' ') }} MAD</td>
        </tr>

        <tr>
            <td class="label">Mode règlement</td>
            <td>{{ strtoupper($transaction->mode_paiement) }}</td>
        </tr>


        <tr>
            <td class="label">Période d'effet</td>
            <td>
                {{ $dateDebut->format('d/m/Y') }}
                au
                {{ $dateFin->format('d/m/Y') }}
            </td>
        </tr>

        <tr>
            <td class="label">Date paiement</td>
            <td>
                {{ \Carbon\Carbon::parse($transaction->date_paiement)->format('d/m/Y') }}
            </td>
        </tr>

    </table>

    <div class="footer">
        LE SYNDIC
    </div>

</body>

</html>