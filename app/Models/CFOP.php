<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CFOP extends Model
{
    use HasFactory;
    protected $connection;
    function __construct()
    {
        return $this->connection = env('APP_NAME');
    }
	protected $table = 'CFOP';
    protected $primaryKey = 'CFOP_CODIGO_NF';
    public $timestamps = false;
    protected $fillable= [
        'CFOP_CODIGO_NF',
        ' CFOP_CODIGO',
        ' CFOP_CODIGO_PAI',
        ' CFOP_DESCRICAO_NF',
        ' CFOP_DESCRICAO',
        ' CFOP_OBSERVACAO',
        ' CFOP_TIPO',
        ' CFGR_CODIGO',
        ' CFOP_GERA_COMISSAO',
        ' CFOP_FATURA_PRECO_CUSTO',
        ' CFOP_CAMPO_CUSTO',
        ' CFOP_RECALCULA_CUSTO_PRODUTO',
        ' CFOP_GERAR_PARCELAS',
        ' CFOP_CF_DEVOLUCAO',
        ' CFOP_SEM_MOVIMENTO_ESTOQUE',
        ' CFOP_NAO_GERAR_CONTAS_RECEBER',
        ' CFOP_INCIDE_IPI',
        ' REG_CODIGO',
        ' CFOP_ATIVO',
        ' CFOP_GERAR_PENDENCIA',
        ' CFOP_CALCULA_FUNRURAL',
        ' CFOP_ATIVO_TRANSPORTE',
        ' CFOP_SOMA_IPI_BASE_CALC_ICMS',
        ' CFOP_SOMA_FRETE_BASE_CALC_ICMS',
        ' CFOP_PARTILHA_ICMS',
        ' CFOP_SIMPLES_NACIONAL_MENSAGEM',
        ' CFOP_BLOQUEAR_EMISSAO',
        ' CFOP_DESC_PEDAG_CTE_BC_ICMS',
        ' CFOP_PREENCHE_MES_REFERENCIA',
        ' CFOP_DESC_ICMS_BC_PIS_COFINS',
        ' CFOP_DESONERA_IMPOSTOS',
        ' ALIMENTAR_LOTE_AUTOMATICAMENTE',
        ' CFOP_DESC_VALOR_IRRF'
	];
}
