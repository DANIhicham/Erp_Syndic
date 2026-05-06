<?php

namespace App\Imports;

use App\Models\Appartement;
use App\Models\Residence;
use App\Models\User;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class AppartementsImport implements ToModel
{
    public function model(array $row)
    {
        // Ignorer header
        if ($row[0] === 'numero') return null;

        // Nettoyage
        $numero = trim($row[0]);
        $etage = $row[1] ?? null;
        $residenceNom = trim($row[2]);
        $email = trim($row[3]);

        // 🔎 Chercher résidence
        $residence = Residence::where('nom', $residenceNom)->first();
        if (!$residence) {
            throw new \Exception("Résidence introuvable: " . $residenceNom);
        }

        // 🔎 Chercher propriétaire
        $proprietaire = User::where('email', $email)->first();
        if (!$proprietaire) {
            throw new \Exception("Propriétaire introuvable: " . $email);
        }

        // 📅 Gestion date signature (multi format)
        $dateSignature = null;

        if (!empty($row[4])) {
            try {
                // Cas Excel (nombre)
                $dateSignature = Date::excelToDateTimeObject($row[4])->format('Y-m-d');
            } catch (\Exception $e) {
                // Cas texte (12/03/2025)
                try {
                    $dateSignature = Carbon::createFromFormat('d/m/Y', $row[4])->format('Y-m-d');
                } catch (\Exception $e) {
                    throw new \Exception("Format date invalide: " . $row[4]);
                }
            }
        }

        // 🔄 UPDATE ou CREATE (anti doublon + ajout date)
        return Appartement::updateOrCreate(
            [
                'numero' => $numero,
                'residence_id' => $residence->id,
            ],
            [
                'etage' => $etage,
                'proprietaire_id' => $proprietaire->id,
                'statut_occupation' => 'occupe',
                'date_signature_contrat' => $dateSignature,
            ]
        );
    }
}