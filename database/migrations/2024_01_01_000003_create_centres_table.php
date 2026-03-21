<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('centres_depot', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('code', 20)->unique();
            $table->foreignId('region_id')->constrained()->onDelete('cascade');
            $table->foreignId('departement_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('ville');
            $table->string('adresse')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('centres_examen', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('code', 20)->unique();
            $table->foreignId('region_id')->constrained()->onDelete('cascade');
            $table->foreignId('departement_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('ville');
            $table->string('adresse')->nullable();
            $table->integer('capacite')->default(0);
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('salles_examen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('centre_examen_id')->constrained('centres_examen')->onDelete('cascade');
            $table->string('nom');
            $table->integer('capacite')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salles_examen');
        Schema::dropIfExists('centres_examen');
        Schema::dropIfExists('centres_depot');
    }
};
