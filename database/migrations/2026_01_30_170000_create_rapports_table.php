<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rapports', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['depot', 'examen'])->comment('Type de rapport');
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('concours_id')->nullable()->constrained('concours')->onDelete('cascade');
            $table->foreignId('centre_depot_id')->nullable()->constrained('centres_depot')->onDelete('set null');
            $table->foreignId('centre_examen_id')->nullable()->constrained('centres_examen')->onDelete('set null');
            $table->string('titre');
            $table->text('description')->nullable();
            $table->date('periode_debut');
            $table->date('periode_fin');
            $table->json('statistiques')->comment('Statistiques du rapport');
            $table->string('fichier_path')->nullable()->comment('Chemin du fichier CSV/PDF');
            $table->boolean('envoye_admin')->default(false);
            $table->timestamp('envoye_admin_at')->nullable();
            $table->timestamps();
            
            $table->index(['type', 'agent_id']);
            $table->index('envoye_admin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rapports');
    }
};
