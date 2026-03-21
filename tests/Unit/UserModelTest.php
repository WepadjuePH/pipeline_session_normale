<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_utilisateur_peut_etre_cree()
    {
        $user = User::factory()->create([
            'nom' => 'Test',
            'prenom' => 'User',
            'email' => 'test@example.com'
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test', $user->nom);
        $this->assertEquals('User', $user->prenom);
        $this->assertEquals('test@example.com', $user->email);
    }

    public function test_le_mot_de_passe_est_hash()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123')
        ]);

        $this->assertNotEquals('password123', $user->password);
        $this->assertTrue(password_verify('password123', $user->password));
    }

    public function test_le_role_par_defaut_est_candidat()
    {
        $user = User::factory()->create();

        $this->assertEquals('candidat', $user->role);
    }

    public function test_un_utilisateur_peut_avoir_des_candidatures()
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Collection::class,
            $user->candidatures
        );
    }

    public function test_verification_du_role_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $candidat = User::factory()->create(['role' => 'candidat']);

        $this->assertEquals('admin', $admin->role);
        $this->assertEquals('candidat', $candidat->role);
    }

    public function test_verification_du_role_agent_depot()
    {
        $agent = User::factory()->create(['role' => 'agent_depot']);

        $this->assertEquals('agent_depot', $agent->role);
    }

    public function test_verification_du_role_agent_examen()
    {
        $agent = User::factory()->create(['role' => 'agent_examen']);

        $this->assertEquals('agent_examen', $agent->role);
    }

    public function test_le_telephone_est_requis()
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->telephone);
        $this->assertStringStartsWith('237', $user->telephone);
    }

    public function test_l_email_doit_etre_unique()
    {
        User::factory()->create(['email' => 'unique@test.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        User::factory()->create(['email' => 'unique@test.com']);
    }
}
