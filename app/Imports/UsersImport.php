<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
class UsersImport implements ToModel
{
    public function model(array $row)
    {
        if ($row[0] === 'nom') return null; // ignorer header

        $nom = trim($row[0]);
        $prenom = trim($row[1]);

        // Nettoyage
        $nomClean = $this->cleanString($nom);
        $prenomClean = $this->cleanString($prenom);

        // Génération email
        $baseEmail = $prenomClean . '.' . $nomClean . '@bliving.ma';
        $email = $baseEmail;

        $i = 1;
        while (User::where('email', $email)->exists()) {
            $email = $prenomClean . '.' . $nomClean . $i . '@bliving.ma';
            $i++;
        }

        // Mot de passe sécurisé
        $password = Hash::make(ucfirst($prenom) . '@2026!');

        return new User([
            'nom' => ucfirst($nom),
            'prenom' => ucfirst($prenom),
            'email' => $email,
            'telephone' => $row[2],
            'role' => strtolower($row[3]),
            'password' => $password,
            'etat' => 'active',
        ]);
    }

    private function cleanString($string)
    {
        $string = Str::ascii($string);
        $string = str_replace(' ', '.', $string);
        $string = preg_replace('/[^a-zA-Z.]/', '', $string);
        $string = preg_replace('/\.+/', '.', $string);

        return strtolower($string);
    }
}