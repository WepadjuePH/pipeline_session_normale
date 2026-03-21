<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Concours;
use App\Models\User;
use Carbon\Carbon;

class ConcoursSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        $concours = [
            [
                'nom' => 'Concours ENS Yaoundé - Filières Scientifiques',
                'code' => 'ENS-MAT-2026',
                'description' => 'Concours d\'entrée à l\'École Normale Supérieure de Yaoundé pour les filières scientifiques (Mathématiques, Physique, Chimie, Biologie)',
                'type' => 'academique',
                'filiere' => 'Mathématiques',
                'cursus' => 'INGENIEUR',
                'date_ouverture' => Carbon::now()->subDays(10),
                'date_cloture' => Carbon::now()->addDays(20),
                'date_examen' => Carbon::parse('2026-03-03'),
                'heure_examen' => '08:00:00',
                'diplomes_requis' => ['BAC', 'GCE A-Level'],
                'age_minimum' => 17,
                'age_maximum' => 25,
                'frais_inscription' => 15000,
                'monnaie' => 'XAF',
                'nombre_places' => 100,
                'inscription_ouverte' => true,
                'is_active' => true,
                'documents_requis' => [
                    'CNI',
                    'Diplôme',
                    'Acte de naissance',
                    'Reçu de paiement',
                    'Photo 4x4',
                ],
                'created_by' => $admin->id,
            ],
            [
                'nom' => 'Concours ESTLC - Cursus Ingénieur',
                'code' => 'ESTLC-ING-2025',
                'description' => 'Concours d\'entrée à l\'École Supérieure de Transport, de Logistique et de Commerce',
                'type' => 'professionnel',
                'filiere' => 'AMA',
                'cursus' => 'INGENIEUR',
                'date_ouverture' => Carbon::now()->subDays(5),
                'date_cloture' => Carbon::now()->addDays(25),
                'date_examen' => Carbon::parse('2026-03-15'),
                'heure_examen' => '08:00:00',
                'diplomes_requis' => ['DUT/DEUP GMP', 'PASSABLE'],
                'age_minimum' => 18,
                'age_maximum' => 30,
                'frais_inscription' => 20000,
                'monnaie' => 'XAF',
                'nombre_places' => 50,
                'inscription_ouverte' => true,
                'is_active' => true,
                'documents_requis' => [
                    'CNI',
                    'Diplôme',
                    'Acte de naissance',
                    'Reçu de paiement',
                    'Photo 4x4',
                    'Relevé de notes',
                ],
                'created_by' => $admin->id,
            ],
            [
                'nom' => 'Concours ENSP - Génie Informatique',
                'code' => 'ENSP-INFO-2026',
                'description' => 'Concours d\'entrée à l\'École Nationale Supérieure Polytechnique - Département Génie Informatique',
                'type' => 'technique',
                'filiere' => 'Informatique',
                'cursus' => 'INGENIEUR',
                'date_ouverture' => Carbon::now()->addDays(5),
                'date_cloture' => Carbon::now()->addDays(35),
                'date_examen' => Carbon::parse('2026-04-10'),
                'heure_examen' => '08:00:00',
                'diplomes_requis' => ['BAC C/D', 'GCE A-Level'],
                'age_minimum' => 17,
                'age_maximum' => 23,
                'frais_inscription' => 18000,
                'monnaie' => 'XAF',
                'nombre_places' => 80,
                'inscription_ouverte' => true,
                'is_active' => true,
                'documents_requis' => [
                    'CNI',
                    'Diplôme',
                    'Acte de naissance',
                    'Reçu de paiement',
                    'Photo 4x4',
                ],
                'created_by' => $admin->id,
            ],
        ];

        foreach ($concours as $c) {
            Concours::create($c);
        }
    }
}
