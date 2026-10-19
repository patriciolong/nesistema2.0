<?php

namespace App\Imports;

use App\Models\Cliente;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class ClientesImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    protected string $duplicados;
    protected string $oficinaDefault;
    protected string $userName;

    public int $totalRows = 0;
    public int $createdCount = 0;
    public int $updatedCount = 0;
    public int $skippedCount = 0;
    public array $errors = [];

    public function __construct(string $duplicados = 'actualizar', string $oficinaDefault = 'General', string $userName = 'Sistema')
    {
        $this->duplicados = $duplicados;
        $this->oficinaDefault = $oficinaDefault;
        $this->userName = $userName;
    }

    /**
     * Extraer valor de una fila buscando múltiples alias posibles de encabezado.
     */
    protected function getField(Collection|array $row, array $aliases, $default = null)
    {
        foreach ($aliases as $alias) {
            $slugAlias = strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace([' ', '-', '.'], '_', $alias))));
            
            foreach ($row as $key => $value) {
                $cleanKey = strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace([' ', '-', '.'], '_', (string) $key))));
                if ($cleanKey === $slugAlias && $value !== null && trim((string) $value) !== '') {
                    return trim((string) $value);
                }
            }
        }
        return $default;
    }

    /**
     * Procesar la colección completa de filas.
     */
    public function collection(Collection $rows)
    {
        $rowNumber = 1; // Fila 1 es el encabezado en WithHeadingRow

        foreach ($rows as $row) {
            $rowNumber++;
            $this->totalRows++;

            // 1. Obtener Identificación / Cédula obligatoria
            $identificacion = $this->getField($row, [
                'identificacion', 'cedula', 'dni', 'c_identificacion', 'ci', 'identidad', 
                'numero_identificacion', 'documento', 'id_cliente_doc', 'cedula_identidad', 'ruc', 'pasaporte'
            ]);

            // 2. Nombres y Apellidos
            $nombre = $this->getField($row, ['nombres', 'nombre', 'c_nombre', 'primer_nombre', 'first_name']);
            $apellido = $this->getField($row, ['apellidos', 'apellido', 'c_apellido', 'primer_apellido', 'last_name']);

            // Si viene una columna 'nombre_completo' o 'cliente' y no hay nombre/apellido por separado
            if (empty($nombre) && empty($apellido)) {
                $nombreCompleto = $this->getField($row, ['nombre_completo', 'cliente', 'nombres_y_apellidos', 'razon_social']);
                if ($nombreCompleto) {
                    $partes = explode(' ', trim($nombreCompleto), 2);
                    $nombre = $partes[0] ?? 'Cliente';
                    $apellido = $partes[1] ?? '.';
                }
            }

            if (empty($identificacion)) {
                $this->errors[] = "Fila #{$rowNumber}: Omitida por no contar con Cédula / Identificación.";
                $this->skippedCount++;
                continue;
            }

            if (empty($nombre)) {
                $nombre = 'Cliente';
            }
            if (empty($apellido)) {
                $apellido = '.';
            }

            // 3. Demás campos opcionales con valores por defecto seguros para evitar errores de BD
            $telefono = $this->getField($row, ['telefono', 'celular', 'c_telefono', 'movil', 'tel', 'phone', 'telefonos'], 'S/N');
            $email = $this->getField($row, ['email', 'correo', 'correo_electronico', 'c_email', 'mail'], 'sin_email@ejemplo.com');
            $direccion = $this->getField($row, ['direccion', 'domicilio', 'c_direccion', 'address', 'residencia'], 'S/D');
            $edadRaw = $this->getField($row, ['edad', 'c_edad', 'age'], 18);
            $edad = is_numeric($edadRaw) ? (int)$edadRaw : 18;
            $pais = $this->getField($row, ['pais', 'c_pais', 'country'], 'Ecuador');
            $ciudad = $this->getField($row, ['ciudad', 'c_ciudad', 'city'], 'General');
            $estado = $this->getField($row, ['estado', 'provincia', 'c_estado', 'state'], $ciudad ?: 'General');
            $codPostal = $this->getField($row, ['codigo_postal', 'cod_postal', 'c_codpostal', 'zip', 'zipcode'], '000000');
            $nApartamento = $this->getField($row, ['apartamento', 'n_apartamento', 'c_napartamento', 'apto', 'suite', 'depto'], 'N/A');
            $oficina = $this->getField($row, ['oficina', 'c_oficina_registro', 'sucursal', 'oficina_registro'], $this->oficinaDefault ?: 'General');

            // 4. Valores financieros (Deuda, Abono, Saldo)
            $deudaRaw = $this->getField($row, ['deuda', 'c_deuda', 'total_deuda', 'valor_deuda', 'monto_deuda'], 0);
            $abonadoRaw = $this->getField($row, ['abonado', 'c_abonado', 'abono', 'total_abonado', 'pagado'], 0);

            // Limpieza de caracteres de moneda si existen ($ , etc.)
            $deuda = (float) preg_replace('/[^0-9.]/', '', str_replace(',', '.', (string) $deudaRaw));
            $abonado = (float) preg_replace('/[^0-9.]/', '', str_replace(',', '.', (string) $abonadoRaw));
            $saldo = max(0, $deuda - $abonado);

            // 5. Verificar existencia en la base de datos
            $clienteExistente = Cliente::where('c_identificacion', $identificacion)->first();

            if ($clienteExistente) {
                if ($this->duplicados === 'omitir') {
                    $this->skippedCount++;
                    continue;
                }

                // Actualizar registro existente
                $clienteExistente->update([
                    'c_nombre' => $nombre,
                    'c_apellido' => $apellido,
                    'c_telefono' => ($telefono !== 'S/N') ? $telefono : ($clienteExistente->c_telefono ?: 'S/N'),
                    'c_email' => ($email !== 'sin_email@ejemplo.com') ? $email : ($clienteExistente->c_email ?: 'sin_email@ejemplo.com'),
                    'c_direccion' => ($direccion !== 'S/D') ? $direccion : ($clienteExistente->c_direccion ?: 'S/D'),
                    'c_edad' => $edad ?: ($clienteExistente->c_edad ?: 18),
                    'c_pais' => ($pais !== 'Ecuador') ? $pais : ($clienteExistente->c_pais ?: 'Ecuador'),
                    'c_ciudad' => ($ciudad !== 'General') ? $ciudad : ($clienteExistente->c_ciudad ?: 'General'),
                    'c_estado' => ($estado !== 'General') ? $estado : ($clienteExistente->c_estado ?: 'General'),
                    'c_codpostal' => ($codPostal !== '000000') ? $codPostal : ($clienteExistente->c_codpostal ?: '000000'),
                    'c_napartamento' => ($nApartamento !== 'N/A') ? $nApartamento : ($clienteExistente->c_napartamento ?: 'N/A'),
                    'c_oficina_registro' => $oficina ?: ($clienteExistente->c_oficina_registro ?: 'General'),
                    'c_deuda' => $deuda > 0 ? $deuda : $clienteExistente->c_deuda,
                    'c_abonado' => $abonado > 0 ? $abonado : $clienteExistente->c_abonado,
                    'c_saldo' => $deuda > 0 ? $saldo : $clienteExistente->c_saldo,
                ]);

                $this->updatedCount++;
            } else {
                // Crear nuevo cliente
                Cliente::create([
                    'c_identificacion' => $identificacion,
                    'c_nombre' => $nombre,
                    'c_apellido' => $apellido,
                    'c_telefono' => $telefono,
                    'c_email' => $email,
                    'c_direccion' => $direccion,
                    'c_edad' => $edad,
                    'c_pais' => $pais,
                    'c_ciudad' => $ciudad,
                    'c_estado' => $estado,
                    'c_codpostal' => $codPostal,
                    'c_napartamento' => $nApartamento,
                    'c_oficina_registro' => $oficina,
                    'c_register' => $this->userName,
                    'c_deuda' => $deuda,
                    'c_abonado' => $abonado,
                    'c_saldo' => $saldo,
                ]);

                $this->createdCount++;
            }
        }
    }

    public function getCreatedCount(): int
    {
        return $this->createdCount;
    }

    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    public function getTotalRows(): int
    {
        return $this->totalRows;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
