<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nivel',
        'part_codigo'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(menu::class, 'menu_usuario', 'usuarioId', 'menuId')->orderBy('menu.ordem', );
    }

    public static function montarMenu()
    {
        $menu = '';
        $finalT = 0;
        $menu = '<aside class="">';
            $menu.='<div id="sidebar" class="nav-collapse">';
                $menu.='<ul class="sidebar-menu">';
                    $menu.='<li class="active">';
                        $menu.='<a class="" href="'.route('home').'">';
                            $menu.='<i class="fas fa-tachometer-alt"></i>';
                            $menu.='<span class="text">Dashboard</span>';
                        $menu.='</a>';
                    $menu.='</li>';
                    // dd(auth()->user()->menus);
                    foreach (auth()->user()->menus as $item){
                        if($item->tipo=='Título' && $finalT==0){
                            $menu.=' <li class="sub-menu">';
                                $menu.='<a href="javascript:;" class="">';
                                    $menu.='<i class="fa fa-angle-double-down"></i>';
                                    $menu.='<span class="text">'.$item->descricao.'</span>';
                                    $menu.='<span class="menu-arrow arrow_carrot-right"></span>';
                                $menu.='</a>';
                                $menu.='<ul class="sub">';
                        }elseif($item->tipo=='Título' && $finalT>0){
                            $menu.='</ul>';
                            $menu.='</li>';
                            $menu.=' <li class="sub-menu">';
                                $menu.='<a href="javascript:;" class="">';
                                    $menu.='<i class="fa fa-angle-double-down"></i>';
                                    $menu.='<span class="text">'.$item->descricao.'</span>';
                                    $menu.='<span class="menu-arrow arrow_carrot-right"></span>';
                                $menu.='</a>';
                                $menu.='<ul class="sub">';
                        }elseif($item->tipo=='Link' && $item->rota){
                            $menu.='<li>';
                                $menu.=' <a class="" href="'.route($item->rota).'">';
                                    $menu.='<i class="'.$item->icone.'"></i>';
                                    $menu.='<span class="text">'.$item->descricao.'</span>';
                                $menu.='</a>';
                            $menu.='</li>';
                        }elseif($item->tipo=='LinkTotvs' && $item->rota){
                            $menu.='<li>';
                                $menu.=' <a class="" href="'.$item->rota.'" target="_blank">';
                                    $menu.='<i class="'.$item->icone.'"></i>';
                                    $menu.='<span class="text">'.$item->descricao.'</span>';
                                $menu.='</a>';
                            $menu.='</li>';
                        }
                        $finalT++;

                        $item->descricao;
                    };
                $menu.='</ul>';
            $menu.='</div>';
        $menu .= '</aside>';
        return $menu;
    }

}
