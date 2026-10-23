<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PLANNER_ESTOQUE_BLOCO_K extends Model
{
    use HasFactory;
    protected $connection;
    function __construct()
    {
        return $this->connection = env('APP_NAME');
        // return $this->connection = 'Decorbras';

    }
	protected $table = 'PLANNER_ESTOQUE_BLOCO_K';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $fillable= [
        'ID'
        ,' PRD_CODIGO'
        ,' PRD_DESCRI'
        ,' QTD'
        ,' VALOR'
        ,' DATA'
	];
}
