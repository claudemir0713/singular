<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class menuUsuario extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable= [
        'id'
        , 'usuarioId'
        , 'menuId'
        , 'deleted_at'
        , 'created_at'
        , 'updated_at'
    ];
    protected $primaryKey = 'id';
    protected $table = 'menu_usuario';
}


