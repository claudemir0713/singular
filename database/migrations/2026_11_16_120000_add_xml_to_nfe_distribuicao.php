<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Guarda o XML completo (procNFe) no banco, que e compartilhado entre servidores; o arquivo em storage/app/nfe_dist
 * so existe no servidor que o gravou. Copia para o banco os XMLs ja arquivados em disco.
 */
return new class extends Migration
{
    public function up(): void
    {
        // SQL direto: o MySQL deste servidor e antigo e o hasColumn do Laravel consulta colunas que ele nao tem
        if (!DB::select("SHOW COLUMNS FROM nfe_distribuicao LIKE 'xml'")) {
            DB::statement('ALTER TABLE nfe_distribuicao ADD COLUMN xml LONGTEXT NULL AFTER xml_arquivo');
        }
        DB::table('nfe_distribuicao')->where('tipo', 'procNFe')->whereNull('xml')->whereNotNull('xml_arquivo')
            ->orderBy('id')->select(['id', 'xml_arquivo'])->chunkById(200, function ($linhas) {
                foreach ($linhas as $l) {
                    $f = storage_path('app/' . $l->xml_arquivo);
                    if (is_file($f)) {
                        DB::table('nfe_distribuicao')->where('id', $l->id)->update(['xml' => file_get_contents($f)]);
                    }
                }
            });
    }

    public function down(): void
    {
        // intencionalmente vazio: XMLs recebidos nao devem ser descartados por rollback
    }
};
