<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidatures', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['region_origine_id']);
            $table->dropForeign(['departement_origine_id']);
            
            // Drop the old columns
            $table->dropColumn(['region_origine_id', 'departement_origine_id']);
        });
        
        Schema::table('candidatures', function (Blueprint $table) {
            // Add new text columns
            $table->string('region_origine')->after('nationalite');
            $table->string('departement_origine')->after('region_origine');
        });
    }

    public function down(): void
    {
        Schema::table('candidatures', function (Blueprint $table) {
            // Drop text columns
            $table->dropColumn(['region_origine', 'departement_origine']);
        });
        
        Schema::table('candidatures', function (Blueprint $table) {
            // Restore foreign key columns
            $table->foreignId('region_origine_id')->after('nationalite')->constrained('regions');
            $table->foreignId('departement_origine_id')->after('region_origine_id')->constrained('departements');
        });
    }
};
