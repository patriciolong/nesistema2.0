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
        if (!Schema::hasTable('chat_conversaciones')) {
            Schema::create('chat_conversaciones', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_cliente')->index();
                $table->text('ultimo_mensaje')->nullable();
                $table->dateTime('ultimo_mensaje_at')->nullable()->index();
                $table->integer('unread_admin')->default(0);
                $table->integer('unread_cliente')->default(0);
                $table->string('estado', 20)->default('activa');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('chat_mensajes')) {
            Schema::create('chat_mensajes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_conversacion')->index();
                $table->enum('remitente_tipo', ['cliente', 'admin'])->default('cliente');
                $table->unsignedBigInteger('id_usuario')->nullable()->index();
                $table->string('nombre_remitente', 150);
                $table->text('mensaje');
                $table->string('adjunto_url', 255)->nullable();
                $table->string('adjunto_nombre', 255)->nullable();
                $table->boolean('leido')->default(false)->index();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_mensajes');
        Schema::dropIfExists('chat_conversaciones');
    }
};
