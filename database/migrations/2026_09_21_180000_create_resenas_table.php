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
        if (!Schema::hasTable('resenas_tramite')) {
            Schema::create('resenas_tramite', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_cliente')->index();
                $table->string('tramite_tipo', 60)->index();
                $table->unsignedBigInteger('tramite_id')->index();
                $table->string('tramite_nombre', 255)->nullable();
                $table->unsignedTinyInteger('calificacion')->default(5); // 1 a 5 estrellas
                $table->unsignedTinyInteger('atencion_personal')->default(5); // 1 a 5 estrellas
                $table->unsignedTinyInteger('rapidez')->default(5); // 1 a 5 estrellas
                $table->text('comentario')->nullable();
                $table->boolean('recomendaria')->default(true);
                $table->dateTime('fecha_resena')->useCurrent();
                $table->timestamps();

                // Índices
                $table->unique(['id_cliente', 'tramite_tipo', 'tramite_id'], 'unique_cliente_tramite_resena');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resenas_tramite');
    }
};
