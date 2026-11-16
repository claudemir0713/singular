<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Reforma tributaria (EC 132/2023, LC 214/2025): aliquotas de IBS e CBS na Formacao de Preco.
 *
 * empresa_parametro: aliquotas de referencia cheias (ibs, cbs), % do custo de materiais que gera credito
 *   (ibs_cbs_credito) e ano de vigencia forcado (ibs_cbs_ano, NULL = ano corrente).
 * formacao_preco_item: foto das aliquotas EFETIVAS usadas no calculo, do ano e do credito aplicado.
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
        $this->adicionar('empresa_parametro', 'ibs', 'DECIMAL(10,4) NOT NULL DEFAULT 0');
        $this->adicionar('empresa_parametro', 'cbs', 'DECIMAL(10,4) NOT NULL DEFAULT 0');
        $this->adicionar('empresa_parametro', 'ibs_cbs_credito', 'DECIMAL(10,4) NOT NULL DEFAULT 0');
        $this->adicionar('empresa_parametro', 'ibs_cbs_ano', 'SMALLINT NULL');

        $this->adicionar('formacao_preco_item', 'ibs', 'DECIMAL(15,6) NOT NULL DEFAULT 0');
        $this->adicionar('formacao_preco_item', 'cbs', 'DECIMAL(15,6) NOT NULL DEFAULT 0');
        $this->adicionar('formacao_preco_item', 'ibs_cbs_credito', 'DECIMAL(15,6) NOT NULL DEFAULT 0');
        $this->adicionar('formacao_preco_item', 'ibs_cbs_ano', 'SMALLINT NULL');
        $this->adicionar('formacao_preco_item', 'valor_credito_ibs_cbs', 'DECIMAL(15,6) NOT NULL DEFAULT 0');
    }

    public function down(): void
    {
        // intencionalmente vazio: parametros e fotos de formacoes salvas nao devem ser descartados por rollback
    }
};
