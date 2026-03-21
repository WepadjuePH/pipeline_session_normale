<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Concours;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ConcoursModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_concours_peut_etre_cree()
    {
        $concours = Concours::create([
            'code' => 'ENS-MAT',
            'nom' => 'ENS Mathématiques',
            'description' => 'Test',
            'cursus' => 'Licence',
            'filiere' => 'Mathématiques',
            'date_ouverture' => now(),
            'date_cloture' => now()->addMonths(2),
            'date_examen' => now()->addMonths(3),
            'heure_examen' => '08:00'
        ]);

        $this->assertInstanceOf(Concours::class, $concours);
        $this->assertEquals('ENS-MAT', $concours->code);
    }

    public function test_le_code_doit_etre_unique()
    {
        Concours::create([
            'code' => 'ENS-MAT',
            'nom' => 'ENS Mathématiques',
            'description' => 'Test',
            'cursus' => 'Licence',
            'filiere' => 'Mathématiques',
            'date_ouverture' => now(),
            'date_cloture' => now()->addMonths(2),
            'date_examen' => now()->addMonths(3),
            'heure_examen' => '08:00'
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Concours::create([
            'code' => 'ENS-MAT',
            'nom' => 'ENS Mathématiques 2',
            'description' => 'Test',
            'cursus' => 'Licence',
            'filiere' => 'Mathématiques',
            'date_ouverture' => now(),
            'date_cloture' => now()->addMonths(2),
            'date_examen' => now()->addMonths(3),
            'heure_examen' => '08:00'
        ]);
    }

    public function test_un_concours_peut_avoir_des_candidatures()
    {
        $concours = Concours::create([
            'code' => 'ENS-MAT',
            'nom' => 'ENS Mathématiques',
            'description' => 'Test',
            'cursus' => 'Licence',
            'filiere' => 'Mathématiques',
            'date_ouverture' => now(),
            'date_cloture' => now()->addMonths(2),
            'date_examen' => now()->addMonths(3),
            'heure_examen' => '08:00'
        ]);

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Collection::class,
            $concours->candidatures
        );
    }

    public function test_les_dates_sont_converties_en_carbon()
    {
        $concours = Concours::create([
            'code' => 'ENS-MAT',
            'nom' => 'ENS Mathématiques',
            'description' => 'Test',
            'cursus' => 'Licence',
            'filiere' => 'Mathématiques',
            'date_ouverture' => '2026-01-01',
            'date_cloture' => '2026-03-01',
            'date_examen' => '2026-04-01',
            'heure_examen' => '08:00'
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $concours->date_ouverture);
        $this->assertInstanceOf(\Carbon\Carbon::class, $concours->date_cloture);
        $this->assertInstanceOf(\Carbon\Carbon::class, $concours->date_examen);
    }

    public function test_le_statut_par_defaut_est_ouvert()
    {
        $concours = Concours::create([
            'code' => 'ENS-MAT',
            'nom' => 'ENS Mathématiques',
            'description' => 'Test',
            'cursus' => 'Licence',
            'filiere' => 'Mathématiques',
            'date_ouverture' => now(),
            'date_cloture' => now()->addMonths(2),
            'date_examen' => now()->addMonths(3),
            'heure_examen' => '08:00'
        ]);

        // Refresh to get default values from database
        $concours->refresh();
        
        $this->assertTrue($concours->inscription_ouverte);
        $this->assertTrue($concours->is_active);
    }

    public function test_verification_des_statuts_possibles()
    {
        // Test inscription_ouverte and is_active flags
        $concours1 = Concours::create([
            'code' => 'TEST-1',
            'nom' => 'Test 1',
            'description' => 'Test',
            'cursus' => 'Licence',
            'filiere' => 'Test',
            'date_ouverture' => now(),
            'date_cloture' => now()->addMonths(2),
            'date_examen' => now()->addMonths(3),
            'heure_examen' => '08:00',
            'inscription_ouverte' => true,
            'is_active' => true
        ]);

        $this->assertTrue($concours1->inscription_ouverte);
        $this->assertTrue($concours1->is_active);

        $concours2 = Concours::create([
            'code' => 'TEST-2',
            'nom' => 'Test 2',
            'description' => 'Test',
            'cursus' => 'Licence',
            'filiere' => 'Test',
            'date_ouverture' => now(),
            'date_cloture' => now()->addMonths(2),
            'date_examen' => now()->addMonths(3),
            'heure_examen' => '08:00',
            'inscription_ouverte' => false,
            'is_active' => false
        ]);

        $this->assertFalse($concours2->inscription_ouverte);
        $this->assertFalse($concours2->is_active);
    }
}
