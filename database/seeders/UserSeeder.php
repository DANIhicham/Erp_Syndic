<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Création d'un Administrateur
        // User::create([
        //     'nom' => 'Admin',
        //     'prenom' => 'Bliving',
        //     'email' => 'admin@bliving.com',
        //     'telephone' => '0600000000',
        //     'role' => 'admin',
        //     'password' => Hash::make('password123'), // Hachage Bcrypt
        //     'etat' => 'active',
        // ]);

        // Création d'un Propriétaire de test
        // User::create([
        //     'nom' => 'Alami',
        //     'prenom' => 'Ahmed',
        //     'email' => 'alami@example.com',
        //     'telephone' => '0611223344',
        //     'role' => 'Proprietaire',
        //     'password' => Hash::make('propre123'),
        //     'etat' => 'active',
        // ]);

        // Création d'un Agent syndic de test
        User::create([
            'nom' => 'Salma',
            'prenom' => 'Bliving',
            'email' => 'salma@bliving.com',
            'telephone' => '0611223344',
            'role' => 'syndic',
            'password' => Hash::make('password123'),
            'etat' => 'active',
        ]);
    }
}
