<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('concours', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['academique', 'professionnel', 'technique'])->default('academique');
            $table->string('filiere');
            $table->string('cursus')->nullable();
            
            $table->date('date_ouverture');
            $table->date('date_cloture');
            $table->date('date_examen');
            $table->time('heure_examen');
            
            $table->json('diplomes_requis')->nullable();
            $table->integer('age_minimum')->nullable();
            $table->integer('age_maximum')->nullable();
            
            $table->decimal('frais_inscription', 10, 2)->default(0);
            $table->string('monnaie', 3)->default('XAF');
            
            $table->integer('nombre_places')->nullable();
            $table->boolean('inscription_ouverte')->default(true);
            $table->boolean('is_active')->default(true);
            
            $table->json('documents_requis')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('concours');
    }
};
