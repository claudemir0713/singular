<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class mlc_hora extends Model
{
    use HasFactory;
    protected $connection;
    function __construct()
    {
        return $this->connection = 'mlc';
    }
    protected $table = 'hora';
    protected $primaryKey = 'N';
    public $timestamps = false;
    protected $fillable=[
        'N'
        ,'Usu'
        ,'Emp'
        ,'Ano'
        ,'CodConta'
        ,'CodCa'
        ,'PMed'
        ,'PJan'
        ,'PFev'
        ,'PMar'
        ,'PAbr'
        ,'PMai'
        ,'PJun'
        ,'PJul'
        ,'PAgo'
        ,'PSet'
        ,'POut'
        ,'PNov'
        ,'PDez'
        ,'RMed'
        ,'RJan'
        ,'RFev'
        ,'RMar'
        ,'RAbr'
        ,'RMai'
        ,'RJun'
        ,'RJul'
        ,'RAgo'
        ,'RSet'
        ,'ROut'
        ,'RNov'
        ,'RDez'
    ];

}
