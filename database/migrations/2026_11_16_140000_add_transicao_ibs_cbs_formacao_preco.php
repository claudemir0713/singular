<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Transicao da reforma tributaria editavel pelo usuario.
 *
 * empresa_parametro.ibs_cbs_transicao: JSON {ano: {cbs_entra, ibs_entra, icms_sai, pis_cofins_sai}} (NULL = sugestao do sistema).
 * formacao_preco_item: foto das aliquotas cheias e dos % de entrada/saida usados, para a DRE e a memoria de calculo
 * continuarem auditaveis mesmo se a tabela de parametros mudar depois (ibs/cbs ja guardam a aliquota efetiva).
 */
return new class extends Migration
{
    private function adicionar(string $tabela, string $coluna, string $definicao): void
    {
        // SQL direto: o MySQL deste servidor e antigo e o hasColumn do Laravel consulta colunas que ele nao tem
        if (!DB::select("SHOW COLUMNS FROM {$tabela} LIKE '{$coluna}'")) {
            DB::statement("ALTER TABLE {$tabela} ADD COLUMN {$coluna} {$definicao}");
        }
    }

    public function up(): void
    {
        $this->adicionar('empresa_parametro', 'ibs_cbs_transicao', 'TEXT NULL');

        $this->adicionar('formacao_preco_item', 'cbs_referencia', 'DECIMAL(15,6) NOT NULL DEFAULT 0');
        $this->adicionar('formacao_preco_item', 'ibs_referencia', 'DECIMAL(15,6) NOT NULL DEFAULT 0');
        $this->adicionar('formacao_preco_item', 'cbs_entrada', 'DECIMAL(15,6) NOT NULL DEFAULT 0');
        $this->adicionar('formacao_preco_item', 'ibs_entrada', 'DECIMAL(15,6) NOT NULL DEFAULT 0');
        $this->adicionar('formacao_preco_item', 'icms_saida', 'DECIMAL(15,6) NOT NULL DEFAULT 0');
        $this->adicionar('formacao_preco_item', 'pis_cofins_saida', 'DECIMAL(15,6) NOT NULL DEFAULT 0');
    }

    public function down(): void
    {
        // intencionalmente vazio: parametros e fotos de formacoes salvas nao devem ser descartados por rollback
    }
};
