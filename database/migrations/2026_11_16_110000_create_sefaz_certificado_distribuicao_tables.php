<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Certificado A1 da empresa e documentos recebidos pela distribuicao da SEFAZ (NFeDistribuicaoDFe), no MySQL do Singular.
 * O PFX fica em storage/app/certificados (fora da pasta publica) e a senha so existe criptografada.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('empresa_certificado')) {
            Schema::create('empresa_certificado', function (Blueprint $t) {
                $t->bigIncrements('id');
                $t->string('cnpj', 14)->index();
                $t->string('titular', 200)->nullable();
                $t->string('serial', 80)->nullable();
                $t->string('arquivo', 255);
                $t->string('hash_sha256', 64);
                $t->text('senha_cript');
                $t->dateTime('valido_de')->nullable();
                $t->dateTime('valido_ate')->nullable();
                $t->unsignedTinyInteger('ambiente')->default(1); // 1 producao, 2 homologacao
                $t->string('ultimo_nsu', 15)->default('000000000000000');
                $t->string('max_nsu', 15)->default('000000000000000');
                $t->dateTime('ultima_consulta')->nullable();
                $t->dateTime('proxima_consulta')->nullable();
                $t->string('ultimo_status', 255)->nullable();
                $t->boolean('ativo')->default(true);
                $t->unsignedInteger('usuario_id')->nullable();
                $t->timestamp('created_at')->nullable();
                $t->timestamp('updated_at')->nullable();
            });
        }
        if (!Schema::hasTable('nfe_distribuicao')) {
            Schema::create('nfe_distribuicao', function (Blueprint $t) {
                $t->bigIncrements('id');
                $t->string('nsu', 15)->unique();
                $t->string('chave', 44)->nullable()->index();
                $t->string('tipo', 20);                       // resNFe, procNFe, resEvento, procEvento
                $t->string('cnpj_emitente', 14)->nullable();
                $t->string('nome_emitente', 150)->nullable();
                $t->dateTime('dh_emissao')->nullable();
                $t->decimal('valor_nf', 15, 2)->default(0);
                $t->string('situacao', 2)->nullable();       // cSitNFe do resumo
                $t->string('tp_evento', 8)->nullable();
                $t->string('manifestacao', 30)->nullable();  // ex.: 210210 (ciencia)
                $t->dateTime('manifestada_em')->nullable();
                $t->string('xml_arquivo', 255)->nullable();  // XML completo (procNFe) arquivado
                $t->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        // intencionalmente vazio: certificado e XMLs recebidos nao devem ser descartados por rollback
    }
};
