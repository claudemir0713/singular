<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\base;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class usuarioController extends Controller
{
    public function listAll(Request $request)
    {
        $filtros=[];

        $filtroNome  = $request->nome;
        $filtroAtivo = $request->ativo;

        if($filtroNome){
            $filtros[]=['users.name','like','%'.$request->nome.'%'];
        }
        if($filtroAtivo){
            $filtros[]=['users.ativo','=',$request->ativo];
        }

        $filtroAtivo  = ($request->get('ativo'))? $request->get('ativo') : session('filtroAtivo');
        session()->put('filtroAtivo', $filtroAtivo);

        $usuarios    = User::where($filtros)->get();
        return view('auth.listAll',compact('usuarios','filtroNome','filtroAtivo'));
    }


    public function ativaUsuario(Request $request)
    {
        $error = 'success';
        try{
            $usuario = User::find($request->usuario_id);
            $usuario->ativo = $request->ativo;
            $usuario->save();
        }catch(\Exception $e){
            $error = $e;
        }
        return response()->json($error);
    }

    public function nivelUsuario(Request $request)
    {
        $error = 'success';
        try{
            $usuario = User::find($request->usuario_id);
            $usuario->nivel = $request->nivel;
            $usuario->save();
        }catch(\Exception $e){
            $error = $e;
        }
        return response()->json($error);
    }

    public function updateSenha(Request $request)
    {
        $usuario = Auth::user(); // resgata o usuario

        $usuario_id = $usuario->id;

        $error = 'success';
        try{
            $usuario = User::find($usuario_id);
            $usuario->password = bcrypt($request->novaSenha);
            $usuario->save();
        }catch(\Exception $e){
            $error = $e;
        }
        return response()->json($error);
    }

}
