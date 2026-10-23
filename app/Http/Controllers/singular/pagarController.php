<?php

namespace App\Http\Controllers\singular;

use App\Helpers\Pagar;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class pagarController extends Controller
{
    public function listAll(Request $request){
        $dateForm = $request->except('_token');

        $dataI      = $request->dtI;
        $dataF      = $request->dtF;
        if(!$dataI){$dataI = date('Y-m-d');};
        if(!$dataF){$dataF = date('Y-m-d');};
        $forencedor   = strtoupper($request->forencedor);
        $tipo         = strtoupper($request->tipo);


        $pagar = Pagar::pagar($dataI,$dataF,$forencedor,$tipo);
        return view('pagar.listAll',compact('pagar','dateForm'));
    }
}
