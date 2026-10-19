<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResenaTramite extends Model
{
    use HasFactory;

    protected $table = 'resenas_tramite';

    protected $fillable = [
        'id_cliente',
        'tramite_tipo',
        'tramite_id',
        'tramite_nombre',
        'calificacion',
        'atencion_personal',
        'rapidez',
        'comentario',
        'recomendaria',
        'fecha_resena',
    ];

    protected $casts = [
        'calificacion' => 'integer',
        'atencion_personal' => 'integer',
        'rapidez' => 'integer',
        'recomendaria' => 'boolean',
        'fecha_resena' => 'datetime',
    ];

    /**
     * Relación con el Cliente que realizó la reseña.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    /**
     * Retorna estrellas en formato visual.
     */
    public function getEstrellasTextoAttribute(): string
    {
        return str_repeat('⭐', max(1, min(5, (int) $this->calificacion)));
    }

    /**
     * Obtiene los datos detallados del trámite asociado (url, asesor que atendió, oficina, etc.).
     */
    public function getTramiteDetalleAttribute(): array
    {
        $tipo = $this->tramite_tipo;
        $id = $this->tramite_id;
        $url = route('clientes.tramites', $this->id_cliente);
        $asesor = 'No especificado';
        $oficina = 'Sede Principal';
        $estado = 'Listo / Entregado';
        $fecha = null;
        $costo = null;
        $detallesExtra = [];

        try {
            switch ($tipo) {
                case 'poderes':
                    $tramite = \Illuminate\Support\Facades\DB::table('tramite_poderes')->where('id_tram_poderes', $id)->first();
                    if ($tramite) {
                        $url = route('poderes.show', $id);
                        if (!empty($tramite->id_usuario)) {
                            $u = User::find($tramite->id_usuario);
                            $asesor = $u ? $u->name : ('Usuario #' . $tramite->id_usuario);
                        } else {
                            $asesor = 'Asesor Notarial';
                        }
                        $oficina = $tramite->tp_oficina ?: $oficina;
                        $estado = $tramite->estado ?: $estado;
                        $fecha = $tramite->tp_fecha;
                        $costo = $tramite->tp_costo_tramite;
                        $detallesExtra['Apoderado'] = $tramite->tp_nombres_otorga_poder;
                    }
                    break;

                case 'divorcios':
                    $tramite = \Illuminate\Support\Facades\DB::table('tramite_divorcio')->where('id_tram_div', $id)->first();
                    if ($tramite) {
                        $url = route('divorcios.show', $id);
                        if (!empty($tramite->id_usuario)) {
                            $u = User::find($tramite->id_usuario);
                            $asesor = $u ? $u->name : ('Usuario #' . $tramite->id_usuario);
                        } else {
                            $asesor = 'Asesor Legal';
                        }
                        $oficina = $tramite->td_oficina ?: $oficina;
                        $estado = $tramite->estado ?: $estado;
                        $fecha = $tramite->td_fecha;
                        $costo = $tramite->td_valor;
                        $detallesExtra['Cónyuge'] = $tramite->td_nombre_c;
                    }
                    break;

                case 'impuestos':
                    $tramite = \Illuminate\Support\Facades\DB::table('tramite_impuestos')->where('id_tram_impuestos', $id)->first();
                    if ($tramite) {
                        $url = route('impuestos.show', $id);
                        if (!empty($tramite->id_usuario)) {
                            $u = User::find($tramite->id_usuario);
                            $asesor = $u ? $u->name : ('Usuario #' . $tramite->id_usuario);
                        } else {
                            $asesor = 'Asesor Contable';
                        }
                        $oficina = $tramite->ti_oficina ?: $oficina;
                        $estado = $tramite->estado ?: $estado;
                        $fecha = $tramite->ti_fecha;
                        $costo = $tramite->ti_costo;
                    }
                    break;

                case 'varios':
                    $tramite = \Illuminate\Support\Facades\DB::table('tramites_varios')->where('id_tramite_varios', $id)->first();
                    if ($tramite) {
                        $url = route('tramites-varios.show', $id);
                        if (!empty($tramite->id_usuario)) {
                            $u = User::find($tramite->id_usuario);
                            $asesor = $u ? $u->name : ('Usuario #' . $tramite->id_usuario);
                        } else {
                            $asesor = 'Asesor Notarial';
                        }
                        $oficina = $tramite->tv_oficina ?: $oficina;
                        $estado = $tramite->estado ?: $estado;
                        $fecha = $tramite->tv_fecha;
                        $costo = $tramite->tv_valor_tramite;
                        $detallesExtra['Motivo'] = $tramite->tv_motivo;
                    }
                    break;

                case 'formularios':
                    $tramite = TramitePersonalizado::with('tipoTramite')->find($id);
                    if ($tramite) {
                        $url = route('tramites-personalizados.show', $id);
                        $asesor = $tramite->usuario ?: 'Asesor Notarial';
                        $oficina = $tramite->oficina ?: $oficina;
                        $estado = $tramite->estado ?: $estado;
                        $fecha = $tramite->fecha;
                        $costo = $tramite->valor_tramite;
                        if ($tramite->tipoTramite) {
                            $detallesExtra['Tipo'] = $tramite->tipoTramite->nombre;
                        }
                    }
                    break;

                case 'documentos':
                    $tramite = DocumentoGenerado::with('plantilla')->find($id);
                    if ($tramite) {
                        $url = route('plantillas.index');
                        $asesor = $tramite->usuario_creador ?: 'Notaría';
                        $estado = $tramite->estado ?: $estado;
                        $fecha = $tramite->created_at ? $tramite->created_at->format('Y-m-d') : null;
                        if ($tramite->plantilla) {
                            $detallesExtra['Plantilla'] = $tramite->plantilla->nombre_plantilla;
                        }
                    }
                    break;
            }
        } catch (\Throwable $e) {
            // Safe fallback
        }

        return [
            'url' => $url,
            'asesor' => $asesor,
            'oficina' => $oficina,
            'estado' => $estado,
            'fecha' => $fecha,
            'costo' => $costo,
            'detalles_extra' => $detallesExtra,
        ];
    }
}
