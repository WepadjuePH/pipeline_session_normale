<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('candidatures', function (Blueprint $table) {
            // Empêcher deux candidats d'avoir le même numéro de table dans la même salle
            // Cette contrainte garantit qu'il n'y aura JAMAIS de doublon
            $table->unique(['salle_examen_id', 'numero_table'], 'unique_numero_table_par_salle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidatures', function (Blueprint $table) {
            $table->dropUnique('unique_numero_table_par_salle');
        });
    }
};
