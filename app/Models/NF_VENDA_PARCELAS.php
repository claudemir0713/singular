<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NF_VENDA_PARCELAS extends Model
{
    use HasFactory;
    protected $connection;
    function __construct()
    {
        return $this->connection = env('APP_NAME');
    }
    protected $table = 'NF_VENDA_PARCELAS';
    protected $primaryKey = ['NF_NUMERO', 'NP_ITEM'];
    public $timestamps = false;
    protected $fillable= [
        'NF_NUMERO',
        ' NP_ITEM',
        ' NP_ITEM_NFCE',
        ' NP_NUMERO_DIAS',
        ' NP_DT_VENCIMENTO',
        ' NP_VALOR',
        ' CON_TIPO_PAGAMENTO',
        ' NP_TX_JUROS_MORA',
        ' NP_TX_MULTA',
        ' NP_MANUAL',
        ' NP_MANUAL_VALOR',
        ' NP_MANUAL_CONDICAO',
        ' NP_VALOR_COMISSAO',
        ' CPA_CODIGO',
        ' NFFP_NUMERO',
        ' CBA_CODIGO'
	];

}
