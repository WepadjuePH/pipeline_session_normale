<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CentreDepot;
use App\Models\CentreExamen;
use App\Models\SalleExamen;
use App\Models\Region;
use App\Models\Departement;

class CentreSeeder extends Seeder
{
    public function run(): void
    {
        // Centres de Dépôt
        $centresDepot = [
            [
                'nom' => 'Centre de Dépôt DRES Ouest',
                'code' => 'DRES-OUEST',
                'region' => 'Ouest',
                'departement' => 'Koung-Khi',
                'ville' => 'Bafoussam',
                'adresse' => 'Avenue du Gouverneur',
                'telephone' => '+237 233 44 55 66',
                'email' => 'dres.ouest@sgecn.cm',
            ],
            [
                'nom' => 'Centre de Dépôt DRES Littoral',
                'code' => 'DRES-LITTORAL',
                'region' => 'Littoral',
                'departement' => 'Wouri',
                'ville' => 'Douala',
                'adresse' => 'Boulevard de la Liberté',
                'telephone' => '+237 233 42 00 00',
                'email' => 'dres.littoral@sgecn.cm',
            ],
            [
                'nom' => 'Centre de Dépôt DRES Centre',
                'code' => 'DRES-CENTRE',
                'region' => 'Centre',
                'departement' => 'Mfoundi',
                'ville' => 'Yaoundé',
                'adresse' => 'Quartier Administratif',
                'telephone' => '+237 222 23 45 67',
                'email' => 'dres.centre@sgecn.cm',
            ],
        ];

        foreach ($centresDepot as $centre) {
            $region = Region::where('nom', $centre['region'])->first();
            $departement = Departement::where('nom', $centre['departement'])->first();

            CentreDepot::create([
                'nom' => $centre['nom'],
                'code' => $centre['code'],
                'region_id' => $region->id,
                'departement_id' => $departement->id,
                'ville' => $centre['ville'],
                'adresse' => $centre['adresse'],
                'telephone' => $centre['telephone'],
                'email' => $centre['email'],
                'is_active' => true,
            ]);
        }

        // Centres d'Examen
        $centresExamen = [
            [
                'nom' => 'Lycée Général Leclerc',
                'code' => 'LGL-YDE',
                'region' => 'Centre',
                'departement' => 'Mfoundi',
                'ville' => 'Yaoundé',
                'adresse' => 'Quartier Bastos',
                'capacite' => 500,
                'telephone' => '+237 222 20 11 22',
                'email' => 'lgl@sgecn.cm',
                'salles' => [
                    ['nom' => 'Salle A', 'capacite' => 50],
                    ['nom' => 'Salle B', 'capacite' => 50],
                    ['nom' => 'Salle C', 'capacite' => 50],
                    ['nom' => 'Salle D', 'capacite' => 50],
                    ['nom' => 'Amphithéâtre 1', 'capacite' => 150],
                    ['nom' => 'Amphithéâtre 2', 'capacite' => 150],
                ],
            ],
            [
                'nom' => 'Lycée de Nylon',
                'code' => 'LN-DLA',
                'region' => 'Littoral',
                'departement' => 'Wouri',
                'ville' => 'Douala',
                'adresse' => 'Quartier Nylon',
                'capacite' => 400,
                'telephone' => '+237 233 42 11 22',
                'email' => 'nylon@sgecn.cm',
                'salles' => [
                    ['nom' => 'Salle A', 'capacite' => 40],
                    ['nom' => 'Salle B', 'capacite' => 40],
                    ['nom' => 'Salle C', 'capacite' => 40],
                    ['nom' => 'Salle D', 'capacite' => 40],
                    ['nom' => 'Salle E', 'capacite' => 40],
                    ['nom' => 'Amphithéâtre', 'capacite' => 200],
                ],
            ],
            [
                'nom' => 'Lycée Classique de Bafoussam',
                'code' => 'LCB-BFM',
                'region' => 'Ouest',
                'departement' => 'Koung-Khi',
                'ville' => 'Bafoussam',
                'adresse' => 'Centre-ville',
                'capacite' => 300,
                'telephone' => '+237 233 44 33 22',
                'email' => 'lcb@sgecn.cm',
                'salles' => [
                    ['nom' => 'Salle A', 'capacite' => 50],
                    ['nom' => 'Salle B', 'capacite' => 50],
                    ['nom' => 'Salle C', 'capacite' => 50],
                    ['nom' => 'Salle D', 'capacite' => 50],
                    ['nom' => 'Amphithéâtre', 'capacite' => 100],
                ],
            ],
        ];

        foreach ($centresExamen as $centre) {
            $region = Region::where('nom', $centre['region'])->first();
            $departement = Departement::where('nom', $centre['departement'])->first();

            $centreExamen = CentreExamen::create([
                'nom' => $centre['nom'],
                'code' => $centre['code'],
                'region_id' => $region->id,
                'departement_id' => $departement->id,
                'ville' => $centre['ville'],
                'adresse' => $centre['adresse'],
                'capacite' => $centre['capacite'],
                'telephone' => $centre['telephone'],
                'email' => $centre['email'],
                'is_active' => true,
            ]);

            // Créer les salles pour ce centre
            foreach ($centre['salles'] as $salle) {
                SalleExamen::create([
                    'centre_examen_id' => $centreExamen->id,
                    'nom' => $salle['nom'],
                    'capacite' => $salle['capacite'],
                    'is_active' => true,
                ]);
            }
        }
    }
}
