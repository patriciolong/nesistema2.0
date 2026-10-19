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
        $tramiteTables = [
            'tramites_personalizados',
            'tramite_poderes',
            'tramite_divorcio',
            'tramite_impuestos',
            'tramites_varios',
            'documentos_generados'
        ];

        foreach ($tramiteTables as $tbl) {
            if (Schema::hasTable($tbl)) {
                Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                    if (!Schema::hasColumn($tbl, 'documento_pdf')) {
                        $table->string('documento_pdf', 255)->nullable()->after('estado');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tramiteTables = [
            'tramites_personalizados',
            'tramite_poderes',
            'tramite_divorcio',
            'tramite_impuestos',
            'tramites_varios',
            'documentos_generados'
        ];

        foreach ($tramiteTables as $tbl) {
            if (Schema::hasTable($tbl)) {
                Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                    if (Schema::hasColumn($tbl, 'documento_pdf')) {
                        $table->dropColumn('documento_pdf');
                    }
                });
            }
        }
    }
};
