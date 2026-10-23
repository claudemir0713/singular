<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class formacaoPrecoItem extends Model
{
    protected $connection = 'mysql';
    protected $table = 'formacao_preco_item';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'formacao_preco_id',
        'item',
        'dav_numero',
        'dav_tipo',
        'cod_produto',
        'prd_codigo_digitado',
        'descricao_produto',
        'unid_med',
        'quantidade',

        /*
         * Valores originais do DAV Item.
         */
        'davi_vl_unit',
        'davi_vl_total_bruto',
        'davi_vl_total',

        /*
         * Situação e tributação original do DAV Item.
         */
        'davi_totalizador',
        'davi_cancelado',
        'davi_aliquota',

        /*
         * Quantidades originais adicionais.
         */
        'davi_qtde_entrega',
        'davi_qtde_volumes',
        'davi_qtde_produzir',

        /*
         * Precisão utilizada no DAV.
         */
        'davi_casas_dec_valor_unitario',

        /*
         * Regra tributária.
         */
        'reg_codigo',

        /*
         * ICMS original do DAV Item.
         */
        'davi_icms_cst',
        'davi_icms_bc',
        'davi_icms_aliquota',
        'davi_icms_valor',

        /*
         * PIS original do DAV Item.
         */
        'davi_pis_aliquota',
        'davi_pis_valor',

        /*
         * COFINS original do DAV Item.
         */
        'davi_cofins_aliquota',
        'davi_cofins_valor',

        /*
         * Comissão original do DAV Item.
         */
        'davi_comissao_origem',
        'davi_comissao_perc',

        /*
         * Simples Nacional original.
         */
        'davi_icms_sn_bc',

        /*
         * Valores originais de negociação.
         */
        'davi_vl_unit_negociacao',
        'davi_vl_total_negociacao',

        /*
         * Volume original do DAV Item.
         */
        'davi_volume_item_unit',
        'davi_volume_item_total',

        /*
         * Origem do custo utilizado na Formação de Preço.
         */
        'tipo_item_custo',
        'origem_custo',
        'ano_custo',
        'mes_custo',
        'ano_mes_custo',
        'embalagem',
        'custo_percentual',
        'tipo_mp',

        /*
         * Custos utilizados na formação.
         */
        'custo_materiais',
        'custo_moi',
        'custo_moe',
        'vl_frete',
        'custo_total',

        /*
         * Entradas do cálculo.
         */
        'prazo',
        'forma_calculo',
        'margem',
        'preco_informado',

        /*
         * Parâmetros utilizados na simulação.
         */
        'comissao',
        'taxa_financeira',
        'icms',
        'simples',
        'pis',
        'cofins',
        'ir_csll',
        'difal',
        'frete_percentual',
        'despesa_fixa',
        'outros',

        /*
         * Preço final.
         */
        'valor_venda',

        /*
         * Memória de cálculo.
         */
        'valor_impostos',
        'valor_outras_variaveis',
        'valor_custo_financeiro',

        /*
         * Margem de contribuição.
         */
        'valor_margem_contribuicao',
        'percentual_margem_contribuicao',

        /*
         * Resultado bruto.
         */
        'valor_resultado_bruto',
        'percentual_resultado_bruto',

        /*
         * Lucro líquido.
         */
        'valor_lucro',
        'percentual_lucro',
    ];

    protected $casts = [
        /*
         * Identificação.
         */
        'id' => 'integer',
        'formacao_preco_id' => 'integer',
        'item' => 'integer',

        /*
         * Origem DAV.
         */
        'dav_numero' => 'integer',

        /*
         * Quantidades.
         */
        'quantidade' => 'decimal:6',
        'davi_qtde_entrega' => 'decimal:6',
        'davi_qtde_volumes' => 'decimal:6',
        'davi_qtde_produzir' => 'decimal:6',

        /*
         * Valores originais do DAV.
         */
        'davi_vl_unit' => 'decimal:6',
        'davi_vl_total_bruto' => 'decimal:6',
        'davi_vl_total' => 'decimal:6',
        'davi_aliquota' => 'decimal:6',

        'davi_casas_dec_valor_unitario' => 'integer',

        /*
         * ICMS original.
         */
        'davi_icms_bc' => 'decimal:6',
        'davi_icms_aliquota' => 'decimal:6',
        'davi_icms_valor' => 'decimal:6',

        /*
         * PIS original.
         */
        'davi_pis_aliquota' => 'decimal:6',
        'davi_pis_valor' => 'decimal:6',

        /*
         * COFINS original.
         */
        'davi_cofins_aliquota' => 'decimal:6',
        'davi_cofins_valor' => 'decimal:6',

        /*
         * Comissão original.
         */
        'davi_comissao_perc' => 'decimal:6',

        /*
         * Simples Nacional original.
         */
        'davi_icms_sn_bc' => 'decimal:6',

        /*
         * Valores de negociação.
         */
        'davi_vl_unit_negociacao' => 'decimal:6',
        'davi_vl_total_negociacao' => 'decimal:6',

        /*
         * Volumes.
         */
        'davi_volume_item_unit' => 'decimal:6',
        'davi_volume_item_total' => 'decimal:6',

        /*
         * Referência do custo.
         */
        'ano_custo' => 'integer',
        'mes_custo' => 'integer',
        'custo_percentual' => 'decimal:6',

        /*
         * Custos.
         */
        'custo_materiais' => 'decimal:6',
        'custo_moi' => 'decimal:6',
        'custo_moe' => 'decimal:6',
        'vl_frete' => 'decimal:6',
        'custo_total' => 'decimal:6',

        /*
         * Entradas do cálculo.
         */
        'prazo' => 'decimal:2',
        'margem' => 'decimal:6',
        'preco_informado' => 'decimal:6',

        /*
         * Parâmetros utilizados.
         */
        'comissao' => 'decimal:6',
        'taxa_financeira' => 'decimal:6',
        'icms' => 'decimal:6',
        'simples' => 'decimal:6',
        'pis' => 'decimal:6',
        'cofins' => 'decimal:6',
        'ir_csll' => 'decimal:6',
        'difal' => 'decimal:6',
        'frete_percentual' => 'decimal:6',
        'despesa_fixa' => 'decimal:6',
        'outros' => 'decimal:6',

        /*
         * Valores calculados.
         */
        'valor_venda' => 'decimal:6',
        'valor_impostos' => 'decimal:6',
        'valor_outras_variaveis' => 'decimal:6',
        'valor_custo_financeiro' => 'decimal:6',

        /*
         * Resultados.
         */
        'valor_margem_contribuicao' => 'decimal:6',
        'percentual_margem_contribuicao' => 'decimal:6',

        'valor_resultado_bruto' => 'decimal:6',
        'percentual_resultado_bruto' => 'decimal:6',

        'valor_lucro' => 'decimal:6',
        'percentual_lucro' => 'decimal:6',
    ];

    /**
     * Cabeçalho da Formação de Preço.
     */
    public function formacaoPreco()
    {
        return $this->belongsTo(
            FormacaoPreco::class,
            'formacao_preco_id',
            'id'
        );
    }

    /**
     * Filtra os itens vinculados a um DAV.
     */
    public function scopeDoDav($query, $davNumero)
    {
        return $query->where('dav_numero', $davNumero);
    }

    /**
     * Filtra os itens por produto.
     */
    public function scopeDoProduto($query, $codigoProduto)
    {
        return $query->where('cod_produto', $codigoProduto);
    }

    /**
     * Filtra os itens que não foram cancelados no DAV.
     */
    public function scopeNaoCancelado($query)
    {
        return $query->where(function ($subQuery) {
            $subQuery
                ->whereNull('davi_cancelado')
                ->orWhere('davi_cancelado', '<>', 'S');
        });
    }
}
