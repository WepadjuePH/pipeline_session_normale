<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Region;
use App\Models\Departement;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            [
                'nom' => 'Adamaoua',
                'code' => 'AD',
                'departements' => [
                    ['nom' => 'Djérem', 'code' => 'DJ'],
                    ['nom' => 'Faro-et-Déo', 'code' => 'FD'],
                    ['nom' => 'Mayo-Banyo', 'code' => 'MB'],
                    ['nom' => 'Mbéré', 'code' => 'MR'],
                    ['nom' => 'Vina', 'code' => 'VI'],
                ],
            ],
            [
                'nom' => 'Centre',
                'code' => 'CE',
                'departements' => [
                    ['nom' => 'Haute-Sanaga', 'code' => 'HS'],
                    ['nom' => 'Lékié', 'code' => 'LK'],
                    ['nom' => 'Mbam-et-Inoubou', 'code' => 'MI'],
                    ['nom' => 'Mbam-et-Kim', 'code' => 'MK'],
                    ['nom' => 'Méfou-et-Afamba', 'code' => 'MA'],
                    ['nom' => 'Méfou-et-Akono', 'code' => 'MO'],
                    ['nom' => 'Mfoundi', 'code' => 'MF'],
                    ['nom' => 'Nyong-et-Kellé', 'code' => 'NK'],
                    ['nom' => 'Nyong-et-Mfoumou', 'code' => 'NM'],
                    ['nom' => 'Nyong-et-So\'o', 'code' => 'NS'],
                ],
            ],
            [
                'nom' => 'Est',
                'code' => 'ES',
                'departements' => [
                    ['nom' => 'Boumba-et-Ngoko', 'code' => 'BN'],
                    ['nom' => 'Haut-Nyong', 'code' => 'HN'],
                    ['nom' => 'Kadey', 'code' => 'KD'],
                    ['nom' => 'Lom-et-Djérem', 'code' => 'LD'],
                ],
            ],
            [
                'nom' => 'Extrême-Nord',
                'code' => 'EN',
                'departements' => [
                    ['nom' => 'Diamaré', 'code' => 'DI'],
                    ['nom' => 'Logone-et-Chari', 'code' => 'LC'],
                    ['nom' => 'Mayo-Danay', 'code' => 'MD'],
                    ['nom' => 'Mayo-Kani', 'code' => 'MKN'],
                    ['nom' => 'Mayo-Sava', 'code' => 'MS'],
                    ['nom' => 'Mayo-Tsanaga', 'code' => 'MT'],
                ],
            ],
            [
                'nom' => 'Littoral',
                'code' => 'LT',
                'departements' => [
                    ['nom' => 'Moungo', 'code' => 'MG'],
                    ['nom' => 'Nkam', 'code' => 'NKM'],
                    ['nom' => 'Sanaga-Maritime', 'code' => 'SM'],
                    ['nom' => 'Wouri', 'code' => 'WR'],
                ],
            ],
            [
                'nom' => 'Nord',
                'code' => 'NO',
                'departements' => [
                    ['nom' => 'Bénoué', 'code' => 'BV'],
                    ['nom' => 'Faro', 'code' => 'FR'],
                    ['nom' => 'Mayo-Louti', 'code' => 'ML'],
                    ['nom' => 'Mayo-Rey', 'code' => 'MRY'],
                ],
            ],
            [
                'nom' => 'Nord-Ouest',
                'code' => 'NW',
                'departements' => [
                    ['nom' => 'Boyo', 'code' => 'BY'],
                    ['nom' => 'Bui', 'code' => 'BU'],
                    ['nom' => 'Donga-Mantung', 'code' => 'DM'],
                    ['nom' => 'Menchum', 'code' => 'ME'],
                    ['nom' => 'Mezam', 'code' => 'MZ'],
                    ['nom' => 'Momo', 'code' => 'MM'],
                    ['nom' => 'Ngo-Ketunjia', 'code' => 'NK2'],
                ],
            ],
            [
                'nom' => 'Ouest',
                'code' => 'OU',
                'departements' => [
                    ['nom' => 'Bamboutos', 'code' => 'BT'],
                    ['nom' => 'Haut-Nkam', 'code' => 'HNK'],
                    ['nom' => 'Hauts-Plateaux', 'code' => 'HP'],
                    ['nom' => 'Koung-Khi', 'code' => 'KK'],
                    ['nom' => 'Menoua', 'code' => 'MN'],
                    ['nom' => 'Mifi', 'code' => 'MFI'],
                    ['nom' => 'Ndé', 'code' => 'ND'],
                    ['nom' => 'Noun', 'code' => 'NN'],
                ],
            ],
            [
                'nom' => 'Sud',
                'code' => 'SU',
                'departements' => [
                    ['nom' => 'Dja-et-Lobo', 'code' => 'DL'],
                    ['nom' => 'Mvila', 'code' => 'MV'],
                    ['nom' => 'Océan', 'code' => 'OC'],
                    ['nom' => 'Vallée-du-Ntem', 'code' => 'VN'],
                ],
            ],
            [
                'nom' => 'Sud-Ouest',
                'code' => 'SW',
                'departements' => [
                    ['nom' => 'Fako', 'code' => 'FK'],
                    ['nom' => 'Koupé-Manengouba', 'code' => 'KM'],
                    ['nom' => 'Lebialem', 'code' => 'LB'],
                    ['nom' => 'Manyu', 'code' => 'MY'],
                    ['nom' => 'Meme', 'code' => 'MME'],
                    ['nom' => 'Ndian', 'code' => 'NDN'],
                ],
            ],
        ];

        foreach ($regions as $regionData) {
            $region = Region::create([
                'nom' => $regionData['nom'],
                'code' => $regionData['code'],
            ]);

            foreach ($regionData['departements'] as $dept) {
                Departement::create([
                    'region_id' => $region->id,
                    'nom' => $dept['nom'],
                    'code' => $dept['code'],
                ]);
            }
        }
    }
}
