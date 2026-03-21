<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('concours_id')->constrained()->onDelete('cascade');
            
            $table->string('code_candidat', 50)->unique();
            
            $table->foreignId('centre_depot_id')->constrained('centres_depot');
            $table->date('date_naissance');
            $table->string('lieu_naissance');
            $table->enum('sexe', ['masculin', 'feminin']);
            $table->string('nationalite')->default('Camerounaise');
            $table->foreignId('region_origine_id')->constrained('regions');
            $table->foreignId('departement_origine_id')->constrained('departements');
            $table->string('telephone');
            $table->text('adresse');
            $table->string('premiere_langue');
            $table->string('cni', 50);
            
            $table->string('filiere');
            $table->string('diplome_admission');
            $table->enum('mention_diplome', ['passable', 'assez_bien', 'bien', 'tres_bien', 'non_applicable'])->nullable();
            $table->year('annee_diplome');
            $table->foreignId('centre_examen_id')->nullable()->constrained('centres_examen');
            
            $table->string('nom_pere')->nullable();
            $table->string('telephone_pere')->nullable();
            $table->string('nom_mere')->nullable();
            $table->string('telephone_mere')->nullable();
            
            $table->string('document_cni')->nullable();
            $table->string('document_diplome')->nullable();
            $table->string('document_acte_naissance')->nullable();
            $table->string('document_recu_paiement')->nullable();
            $table->string('photo_candidat')->nullable();
            
            $table->enum('statut', [
                'en_attente',
                'documents_a_corriger',
                'valide_depot',
                'convoque',
                'present',
                'absent',
                'fraude',
                'rejete'
            ])->default('en_attente');
            
            $table->foreignId('salle_examen_id')->nullable()->constrained('salles_examen');
            $table->string('numero_table', 10)->nullable();
            
            $table->text('qr_code_data')->nullable();
            
            $table->foreignId('valide_par_depot')->nullable()->constrained('users');
            $table->timestamp('valide_depot_at')->nullable();
            $table->foreignId('valide_par_examen')->nullable()->constrained('users');
            $table->timestamp('valide_examen_at')->nullable();
            
            $table->text('motif_rejet')->nullable();
            
            $table->boolean('locked')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_id', 'concours_id']);
            $table->index('statut');
            $table->index('code_candidat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidatures');
    }
};
