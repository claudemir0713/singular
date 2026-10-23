<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FIN_CONTAS extends Model
{
    use HasFactory;
    protected $connection;
    function __construct()
    {
        return $this->connection = env('APP_NAME');
    }
    protected $table = 'FIN_CONTAS';
    protected $primaryKey = 'CON_CODIGO';
    public $timestamps = false;
    protected $fillable= [
        'CON_CODIGO',
        ' CON_TIPO',
        ' CON_NUMERO',
        ' CON_SEQUENCIA',
        ' CON_DT_INCLUSAO',
        ' CON_DT_PAGAMENTO',
        ' CON_DT_VENCIMENTO',
        ' ENT_CODIGO',
        ' ENT_TIPO',
        ' USU_COD',
        ' CON_VALOR_ORIGINAL',
        ' CON_VALOR_JUROS',
        ' CON_VALOR_MULTA',
        ' CON_VALOR_DESCONTO',
        ' CON_VALOR_OUTRASDESPESAS',
        ' CON_VALOR_TOTAL_PAGO',
        ' CON_VALOR_CORRIGIDO',
        ' CON_SITUACAO',
        ' CON_PREVISAO',
        ' CON_TIPO_PAGAMENTO',
        ' CON_IMPRESSO',
        ' CON_TX_JUROS_MORA',
        ' CON_CARENCIA_JUROS_MORA',
        ' CON_TX_MULTA',
        ' CON_CARENCIA_MULTA',
        ' CON_MULTA_JUROS_ORIGEM',
        ' CON_OBS',
        ' PLA_CODIGO',
        ' CON_COMISSAO',
        ' CON_ORIGEM',
        ' CON_REMESSA',
        ' CON_REMESSA_DATA',
        ' CON_BAIXA_BANCO',
        ' CBA_CODIGO',
        ' CON_DESPESAS_COBRANCA',
        ' CON_VALOR_CREDITO',
        ' CON_DUPLICATA_RECEBIDA',
        ' CON_ECF_SERIE',
        ' CON_DT_LIMITE_DESCONTO',
        ' REM_CODIGO',
        ' RET_CODIGO',
        ' CON_VALOR_DESCONTO_CONDICIONAL',
        ' CON_DT_VENCIMENTO_DESCONTO',
        ' CON_EMAIL',
        ' CON_OBSERVACAO_IMPRESSAO',
        ' DOC_NUMERO',
        ' DOC_NUMERO_ORIGEM',
        ' DOC_NUMERO_DESTINO',
        ' DOC_NUMERO_SUBSTITUICAO',
        ' CBA_NOSSO_NUMERO',
        ' CBA_CODIGO_CEDENTE',
        ' CBA_CODIGO_BARRAS',
        ' CBA_LINHA_DIGITAVEL',
        ' CBA_INSTRUCOES',
        ' CON_DT_COMPETENCIA',
        ' PART_CODIGO',
        ' CON_EMAIL_COBRANCA',
        ' CTER_CODIGO',
        ' CON_SINCRONIZA_WEB',
        ' CON_BC_COMISSAO',
        ' CON_SEMPRE_IMPRIMIR',
        ' PROJ_CODIGO',
        ' CON_APROVADA',
        ' CX_CODIGO_CREDITO',
        ' CCP_CODIGO_ORIGEM_CREDITO',
        ' CON_SMS_COBRANCA',
        ' CON_JUSTIFICATIVA_EXCLUSAO',
        ' CON_CRED_BLOQ_PAGAMENTO',
        ' CON_CRED_BLOQ_ABATIMENTO',
        ' CBA_NOSSO_NUMERO_BASE',
        ' CBO_CODIGO',
        ' DAV_NUMERO',
        ' DAV_DESPESA_ITEM',
        ' CON_MULTA_JUROS_EXCESSAO',
        ' NOT_DATA_HORA_ALTER_SITUACAO',
        ' CON_CONTA_BONIFICADA',
        ' CON_VALOR_BONIFICADO',
        ' CON_GERA_CASHBACK'
	];
}
