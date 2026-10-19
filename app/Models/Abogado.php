<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Abogado extends Model
{
    use HasFactory;

    protected $table = 'abogados';

    protected $fillable = [
        'nombres',
        'apellidos',
        'especialidad',
        'colegio_abogados',
        'matricula_profesional',
        'ciudad',
        'pais',
        'experiencia_anios',
        'idiomas',
        'biografia_corta',
        'foto',
        'telefono_privado',
        'email_privado',
        'estado',
        'destacado',
    ];

    protected $casts = [
        'destacado' => 'boolean',
        'experiencia_anios' => 'integer',
    ];

    /**
     * Scope para abogados activos.
     */
    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }

    /**
     * Scope para abogados destacados.
     */
    public function scopeDestacado($query)
    {
        return $query->where('destacado', true);
    }

    /**
     * Obtener el nombre completo del abogado.
     */
    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->apellidos}");
    }

    /**
     * Obtener la URL de la foto o una imagen por defecto.
     */
    public function getFotoUrlAttribute(): string
    {
        if (!empty($this->foto)) {
            if (str_starts_with($this->foto, 'http://') || str_starts_with($this->foto, 'https://')) {
                return $this->foto;
            }
            if (str_starts_with($this->foto, 'assets/')) {
                return asset($this->foto);
            }
            return asset('storage/' . $this->foto);
        }

        return asset('assets/images/team/placeholder-avatar.png');
    }

    /**
     * Retorna los datos públicos formateados para el directorio web (sin exponer teléfono o email privado).
     */
    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'nombre_completo' => $this->nombre_completo,
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'especialidad' => $this->especialidad,
            'colegio_abogados' => $this->colegio_abogados,
            'matricula_profesional' => $this->matricula_profesional,
            'ciudad' => $this->ciudad,
            'pais' => $this->pais,
            'experiencia_anios' => $this->experiencia_anios,
            'idiomas' => $this->idiomas,
            'biografia_corta' => $this->biografia_corta,
            'foto_url' => $this->foto_url,
            'destacado' => (bool)$this->destacado,
        ];
    }
}
