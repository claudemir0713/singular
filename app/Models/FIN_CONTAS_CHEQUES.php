<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FIN_CONTAS_CHEQUES extends Model
{
    use HasFactory;
    protected $connection;
    function __construct()
    {
        return $this->connection = env('APP_NAME');
    }
    protected $table = 'ENTIDADES';
    protected $primaryKey = 'ENT_CODIGO';
    public $timestamps = false;
    protected $fillable= [

	];

}
