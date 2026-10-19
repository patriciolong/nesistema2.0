<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $table = 'leads';

    protected $fillable = [
        'nombre',
        'apellidos',
        'pais',
        'estado',
        'ciudad',
        'telefono',
        'email',
        'servicio',
        'mensaje',
        'acepta_terminos',
        'origen',
        'ip_address',
        'synced_to_nesistema',
    ];

    protected $casts = [
        'acepta_terminos' => 'boolean',
        'synced_to_nesistema' => 'boolean',
    ];

    /**
     * Nombre completo del interesado.
     */
    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombre} {$this->apellidos}");
    }

    /**
     * Indica si la solicitud corresponde a una consulta de abogado.
     */
    public function getEsConsultaAbogadoAttribute(): bool
    {
        return str_contains(mb_strtolower($this->servicio ?? ''), 'abogado') || 
               $this->origen === 'directorio_abogados';
    }

    /**
     * Extrae el nombre del abogado si está en el servicio.
     */
    public function getAbogadoSolicitadoAttribute(): ?string
    {
        if (preg_match('/abogado:\s*(.+)$/i', $this->servicio ?? '', $matches)) {
            return trim($matches[1]);
        }
        return null;
    }
}
