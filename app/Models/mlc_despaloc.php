<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class mlc_despaloc extends Model
{
    protected $connection = 'mlc';

    protected $table = 'despaloc';

    protected $primaryKey = 'N';

    public $timestamps = false;

    protected $fillable = [
        'Usu',
        'Emp',
        'Ano',
        'CodConta',
        'CodCa',

        'PMed',
        'PJan',
        'PFev',
        'PMar',
        'PAbr',
        'PMai',
        'PJun',
        'PJul',
        'PAgo',
        'PSet',
        'POut',
        'PNov',
        'PDez',

        'RMed',
        'RJan',
        'RFev',
        'RMar',
        'RAbr',
        'RMai',
        'RJun',
        'RJul',
        'RAgo',
        'RSet',
        'ROut',
        'RNov',
        'RDez',
    ];

    protected $casts = [
        'N' => 'integer',
        'Emp' => 'integer',
        'CodConta' => 'integer',
        'CodCa' => 'integer',

        'PMed' => 'float',
        'PJan' => 'float',
        'PFev' => 'float',
        'PMar' => 'float',
        'PAbr' => 'float',
        'PMai' => 'float',
        'PJun' => 'float',
        'PJul' => 'float',
        'PAgo' => 'float',
        'PSet' => 'float',
        'POut' => 'float',
        'PNov' => 'float',
        'PDez' => 'float',

        'RMed' => 'float',
        'RJan' => 'float',
        'RFev' => 'float',
        'RMar' => 'float',
        'RAbr' => 'float',
        'RMai' => 'float',
        'RJun' => 'float',
        'RJul' => 'float',
        'RAgo' => 'float',
        'RSet' => 'float',
        'ROut' => 'float',
        'RNov' => 'float',
        'RDez' => 'float',
    ];
}
