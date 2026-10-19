<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatConversacion extends Model
{
    use HasFactory;

    protected $table = 'chat_conversaciones';

    protected $fillable = [
        'id_cliente',
        'ultimo_mensaje',
        'ultimo_mensaje_at',
        'unread_admin',
        'unread_cliente',
        'estado',
    ];

    protected $casts = [
        'ultimo_mensaje_at' => 'datetime',
        'unread_admin' => 'integer',
        'unread_cliente' => 'integer',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(ChatMensaje::class, 'id_conversacion', 'id');
    }

    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }
}
