<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CONDICOES_PAGAMENTO extends Model
{
    use HasFactory;
    protected $connection;
    function __construct()
    {
        return $this->connection = env('APP_NAME');
    }
	protected $table = 'CONDICOES_PAGAMENTO';
    protected $primaryKey = 'CPA_CODIGO';
    public $timestamps = false;
    protected $fillable= [
        'CPA_CODIGO',
        ' CPA_DESCRICAO',
        ' CPA_TIPO',
        ' CPA_CONTROLA_CHEQUE_PRE',
        ' CPA_NUM_PARCELAS',
        ' CPA_DIAS_PRIMEIRO_VENCIMENTO',
        ' CPA_DIAS_PROXIMOS_VENCIMENTOS',
        ' CPA_VENC_PARTEM_DO_PRIMEIRO',
        ' CPA_TX_JUROS',
        ' CPA_COMPRAS',
        ' CPA_VENDAS',
        ' CPA_DESCONTO',
        ' CPA_ADEFINIR',
        ' PLA_CODIGO',
        ' CPA_DESCONTO_COPIA',
        ' CPA_IMPRIMIR_DAV',
        ' CPA_DESCONTO_SOMENTE_PARCELAS',
        ' CPA_ID_UMOV',
        ' CPA_EXPORT_WEB',
        ' CPA_APLICA_DESC_DOCS',
        ' CPA_IPIFRETE_PRIMEIRA_PARCELA'
	];
}
