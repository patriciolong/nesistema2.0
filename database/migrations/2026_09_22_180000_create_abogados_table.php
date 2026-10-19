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
        Schema::create('abogados', function (Blueprint $table) {
            $table->id();
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('especialidad'); // e.g. Derecho Civil y Notarial, Derecho Migratorio, Divorcios
            $table->string('colegio_abogados')->nullable(); // e.g. Colegio de Abogados del Azuay / Pichincha / NY State Bar
            $table->string('matricula_profesional')->nullable(); // e.g. Matrícula N° 01-2018-452
            $table->string('ciudad'); // e.g. New York, Cuenca, Quito, Guayaquil, Newark
            $table->string('pais')->default('Ecuador / USA');
            $table->integer('experiencia_anios')->default(5);
            $table->string('idiomas')->default('Español, Inglés');
            $table->text('biografia_corta')->nullable();
            $table->string('foto')->nullable(); // URL / Storage path
            $table->string('telefono_privado')->nullable(); // Privado para administradores
            $table->string('email_privado')->nullable(); // Privado para administradores
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->boolean('destacado')->default(false);
            $table->timestamps();
            
            $table->index(['estado', 'ciudad']);
            $table->index(['estado', 'especialidad']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abogados');
    }
};
