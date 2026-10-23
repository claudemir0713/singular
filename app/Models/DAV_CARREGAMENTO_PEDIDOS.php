<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DAV_CARREGAMENTO_PEDIDOS extends Model
{
    use HasFactory;
    protected $connection;
    function __construct()
    {
        return $this->connection = env('APP_NAME');
    }
    protected $table = 'DAV_CARREGAMENTO_PEDIDOS';
    protected $primaryKey = 'DCA_CODIGO';
    public $timestamps = false;
    protected $fillable=[
        'DCA_CODIGO', 'DAV_NUMERO', 'DCA_ORDEM_ENTREGA'
    ];
}
