<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DAV_CARREGAMENTO extends Model
{
    use HasFactory;
    protected $connection;
    function __construct()
    {
        return $this->connection = env('APP_NAME');
    }
    protected $table = 'DAV_CARREGAMENTO';
    protected $primaryKey = 'DCA_CODIGO';
    public $timestamps = false;
    protected $fillable=[
        'DCA_CODIGO'
        , 'DOC_NUMERO'
        , 'DCA_DATA_CARREGAMENTO'
        , 'DCA_DATA_HORA'
        , 'DCA_SITUACAO'
        , 'DCA_OBSERVACOES'
        , 'USU_CODIGO'
        , 'USU_CODIGO_VENDEDOR'
        , 'REG_CODIGO'
        , 'ENT_CODIGO'
        , 'DCA_REAGENDADO'
        , 'DCA_EXPORTADO'
        , 'USU_COD_ENTREGADOR'
        , 'VEICULO_CODIGO'
        , 'VEICULO_PLACA'
        , 'DSP_CODIGO'
        , 'PRO_MESTRE'
        , 'PART_REPRESENTANTE_CODIGO'
    ];

}
