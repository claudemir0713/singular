<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormacaoPreco extends Model
{
    use SoftDeletes;

    protected $connection = 'mysql';
    protected $table = 'formacao_preco';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        // Empresa
        'empresa_id',

        // Origem DAV
        'dav_numero',
        'dav_numero_auxiliar',
        'dav_tipo',
        'documento_numero',

        // Datas do DAV
        'dav_data',
        'dav_data_emissao',
        'dav_data_confirmacao',
        'dav_data_competencia',

        // Cliente/adquirente
        'entidade_codigo',
        'adquirente_nome',
        'adquirente_cpf_cnpj',
        'adquirente_ie_rg',
        'representante_codigo',

        // Condição de pagamento
        'tipo_pagamento',
        'condicao_pagamento_codigo',
        'condicao_pagamento_descricao',
        'numero_parcelas',
        'dias_primeiro_vencimento',
        'dias_proximos_vencimentos',
        'prazo_medio_condicao',

        // Valores originais do DAV
        'dav_valor',
        'dav_valor_produtos',
        'dav_valor_frete',
        'dav_percentual_desconto',
        'dav_valor_ipi',
        'dav_valor_liquido',

        // Totais consolidados da Formação de Preço
        'custo_total',
        'valor_venda',
        'valor_margem_contribuicao',
        'percentual_margem_contribuicao',
        'valor_resultado_bruto',
        'percentual_resultado_bruto',
        'valor_lucro',
        'percentual_lucro',

        // Controle
        'situacao',
        'observacao',
        'usuario_criacao_id',
        'usuario_alteracao_id',
    ];

    /**
     * Conversões automáticas dos campos.
     *
     * Os casts decimal retornam strings numéricas para preservar
     * a precisão dos valores financeiros.
     */
    protected $casts = [
        // Identificação
        'id' => 'integer',
        'empresa_id' => 'integer',

        // Origem DAV
        'dav_numero' => 'integer',
        'documento_numero' => 'integer',
        'entidade_codigo' => 'integer',
        'representante_codigo' => 'integer',

        // Datas
        'dav_data' => 'date',
        'dav_data_emissao' => 'date',
        'dav_data_confirmacao' => 'date',
        'dav_data_competencia' => 'date',

        // Condição de pagamento
        'condicao_pagamento_codigo' => 'integer',
        'numero_parcelas' => 'integer',
        'dias_primeiro_vencimento' => 'integer',
        'dias_proximos_vencimentos' => 'integer',
        'prazo_medio_condicao' => 'decimal:2',

        // Valores do DAV
        'dav_valor' => 'decimal:6',
        'dav_valor_produtos' => 'decimal:6',
        'dav_valor_frete' => 'decimal:6',
        'dav_percentual_desconto' => 'decimal:6',
        'dav_valor_ipi' => 'decimal:6',
        'dav_valor_liquido' => 'decimal:6',

        // Totais da Formação de Preço
        'custo_total' => 'decimal:6',
        'valor_venda' => 'decimal:6',

        'valor_margem_contribuicao' => 'decimal:6',
        'percentual_margem_contribuicao' => 'decimal:6',

        'valor_resultado_bruto' => 'decimal:6',
        'percentual_resultado_bruto' => 'decimal:6',

        'valor_lucro' => 'decimal:6',
        'percentual_lucro' => 'decimal:6',

        // Usuários
        'usuario_criacao_id' => 'integer',
        'usuario_alteracao_id' => 'integer',

        // Exclusão lógica
        'deleted_at' => 'datetime',
    ];

    /**
     * Itens pertencentes à Formação de Preço.
     */
    public function itens()
    {
        return $this->hasMany(
            FormacaoPrecoItem::class,
            'formacao_preco_id',
            'id'
        );
    }

    /**
     * Retorna somente formações em situação de rascunho.
     */
    public function scopeRascunho($query)
    {
        return $query->where('situacao', 'RASCUNHO');
    }

    /**
     * Retorna somente formações calculadas.
     */
    public function scopeCalculada($query)
    {
        return $query->where('situacao', 'CALCULADA');
    }

    /**
     * Retorna somente formações aprovadas.
     */
    public function scopeAprovada($query)
    {
        return $query->where('situacao', 'APROVADA');
    }

    /**
     * Retorna somente formações canceladas.
     */
    public function scopeCancelada($query)
    {
        return $query->where('situacao', 'CANCELADA');
    }
}
