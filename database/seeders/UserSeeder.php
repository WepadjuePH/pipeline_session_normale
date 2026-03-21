<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Administrateur
        User::create([
            'nom' => 'Admin',
            'prenom' => 'Super',
            'email' => 'admin@sgecn.cm',
            'password' => Hash::make('password'),
            'telephone' => '+237 222 00 00 00',
            'role' => 'admin',
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        // Agent Centre de Dépôt
        User::create([
            'nom' => 'Kouam',
            'prenom' => 'Jean',
            'email' => 'agent.depot@sgecn.cm',
            'password' => Hash::make('password'),
            'telephone' => '+237 233 44 55 66',
            'role' => 'agent_depot',
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        // Agents Centre d'Examen (un par centre)
        User::create([
            'nom' => 'Nkolo',
            'prenom' => 'Marie',
            'email' => 'agent.yaounde@sgecn.cm',
            'password' => Hash::make('password'),
            'telephone' => '+237 222 11 22 33',
            'role' => 'agent_examen',
            'centre_examen_id' => 1, // Centre Yaoundé
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        User::create([
            'nom' => 'Mbarga',
            'prenom' => 'Paul',
            'email' => 'agent.douala@sgecn.cm',
            'password' => Hash::make('password'),
            'telephone' => '+237 233 44 55 77',
            'role' => 'agent_examen',
            'centre_examen_id' => 2, // Centre Douala
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        User::create([
            'nom' => 'Tchoua',
            'prenom' => 'Alice',
            'email' => 'agent.bafoussam@sgecn.cm',
            'password' => Hash::make('password'),
            'telephone' => '+237 244 55 66 88',
            'role' => 'agent_examen',
            'centre_examen_id' => 3, // Centre Bafoussam
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        // Candidats de test
        User::create([
            'nom' => 'Nguema',
            'prenom' => 'Pierre',
            'email' => 'candidat@test.cm',
            'password' => Hash::make('password'),
            'telephone' => '+237 655 44 33 22',
            'role' => 'candidat',
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        User::create([
            'nom' => 'Kamga',
            'prenom' => 'Sophie',
            'email' => 'candidat2@test.cm',
            'password' => Hash::make('password'),
            'telephone' => '+237 677 88 99 00',
            'role' => 'candidat',
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

    }
}
