<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FIN_CONTAS_TIPOS extends Model
{
    use HasFactory;

    protected $connection = 'Decorbras';

    protected $table = 'FIN_CONTAS_TIPOS';

    protected $primaryKey = 'CON_TIPO_PAGAMENTO';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'int';

    protected $fillable = [
        'CON_TIPO_PAGAMENTO',
        'CON_TIPO_DESC',
        'CON_TIPO_INTERNO',
        'CON_TIPO_LIBERADO',
        'CON_TIPO_ORDEM',
        'CON_TIPO_AVISTA',
        'CON_TIPO_TEF',
        'FCTP_CODIGO_PADRAO',
        'CON_TIPO_IMPRESSO',
        'CON_TIPO_DESC_PAF',
        'CON_TIPO_TEF_MODELO',
        'CON_IMPRIME_COMPROVANTE',
        'CON_IMPRIME_CARNE',
        'CON_IMPRIME_RESUMO_PAGAMENTOS',
        'CONN_CODIGO',
        'CON_UTILIZA_CONSIG',
        'CON_NAO_DIGITAL',
        'INST_CODIGO',
        'CON_IMAGEM',
        'CON_PERMITE_NEGOCIAR_JUROS',
        'CON_IMPRIME_DUPLICATA',
        'CON_IMPRIME_PROMISSORIA',
        'CON_GERA_CASHBACK',
    ];
}
