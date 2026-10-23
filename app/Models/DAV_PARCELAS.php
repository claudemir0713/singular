<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DAV_PARCELAS extends Model
{
    use HasFactory;
    protected $connection;
    function __construct()
    {
        return $this->connection = env('APP_NAME');
    }
    protected $table = 'DAV_PARCELAS';
    protected $primaryKey = ['DAV_NUMERO', 'DAVP_ITEM'];
    public $timestamps = false;
    protected $fillable= [
        ' DAVP_ITEM',
        ' DAVP_NUMERO_DIAS',
        ' DAVP_VENCIMENTO',
        ' DAVP_VALOR',
        ' DAVP_MANUAL',
        ' DAVP_MANUAL_VALOR',
        ' CON_TIPO_PAGAMENTO',
        ' DAVP_VALOR_COMISSAO'
	];
}
