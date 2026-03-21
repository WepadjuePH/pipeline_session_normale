<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AgentExamenSeeder extends Seeder
{
    public function run(): void
    {
        // Supprimer les anciens agents examen
        User::where('role', 'agent_examen')->delete();

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
    }
}
