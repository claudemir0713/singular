<?php

namespace App\Http\Controllers\singular;

use App\Helpers\Comissao;
use App\Http\Controllers\Controller;
use App\Models\FIN_CONTAS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class comissaoController extends Controller
{
    public function listAll(Request $request){
        $dateForm = $request->except('_token');

        $nf         = $request->nf;
        $dataI      = $request->dtI;
        $dataF      = $request->dtF;
        if(!$dataI){$dataI = date('Y-m-d');};
        if(!$dataF){$dataF = date('Y-m-d');};

        $vendedor   = strtoupper($request->vendedor);
        $cliente    = strtoupper($request->cliente);

        $comissoes = Comissao::comissao($dataI,$dataF,$cliente,$vendedor,$nf);
        return view('comissao.listAll',compact('comissoes','dateForm'));
    }

    public function alteraBase(Request $request)
    {
        $return = 'success';
        try{
            $fin_contas = FIN_CONTAS::find($request->con_codigo);
            $fin_contas->CON_BC_COMISSAO = $request->con_bc_comissao;
            $fin_contas->save();
        }catch(\Exception $e){
            $return = $e;
        }
        return response()->json($return);
    }

    public function comissaoPagar(Request $request){
        $nivel = Auth::user()->nivel;
        $rep_cod = Auth::user()->part_codigo;

        $dateForm = $request->except('_token');

        $nf         = $request->nf;
        $parcela    = $request->parcela;
        $dataI      = $request->dtI;
        $dataF      = $request->dtF;
        if(!$dataI){$dataI = date('Y-m-d');};
        if(!$dataF){$dataF = date('Y-m-d');};

        $vendedor   = strtoupper($request->vendedor);
        $cliente    = strtoupper($request->cliente);

        $comissoes = Comissao::comissaoPagar($dataI,$dataF,$cliente,$vendedor,$nf,$parcela,$nivel,$rep_cod);
        return view('comissao.comissaoPagar',compact('comissoes','dateForm'));
    }

    public function imprimirComissaoPagar(Request $request){
        $nivel = Auth::user()->nivel;
        $rep_cod = Auth::user()->part_codigo;

        $nf         = $request->nf;
        $parcela    = $request->parcela;
        $dataI      = $request->dtI;
        $dataF      = $request->dtF;
        if(!$dataI){$dataI = date('Y-m-d');};
        if(!$dataF){$dataF = date('Y-m-d');};

        $vendedor   = strtoupper($request->vendedor);
        $cliente    = strtoupper($request->cliente);

        $comissoes = Comissao::comissaoPagar($dataI,$dataF,$cliente,$vendedor,$nf,$parcela,$nivel,$rep_cod);
        $fileName = 'Comissão pagar.pdf';
        $mpdf = new \Mpdf\Mpdf([
            'format' => 'A4',
            'margin_left'   => 15,
            'margin_rigth'  => 10,
            'margin_top'    => 20,
            'margin_bottom' => 15,
            'margin_header' => 5,
            'margin_footer' => 5
        ]);

        $cabecalho = '<table width="100%">';
        $cabecalho .='<tr>';
        $cabecalho .='<td width="10%" align="center"><img src="'.asset('img/'.env('APP_NAME').'.png').'" height="30"></td>';
        $cabecalho .= '<td width="90%" align="center"><span style="font-size:20px"><b>Comissões a pagar</b></span></td>';
        $cabecalho .='</tr>';
        $cabecalho .='</table><hr>';

        $rodape = '<hr><table width="100%">';
        $rodape .='<tr>';
        $rodape .='<td width="80%" align="center"></td>';
        $rodape .= '<td width="20%" align="right"><span style="font-size:10px">Página {PAGENO} de {nb}</span></td>';
        $rodape .='</tr>';
        $rodape .='</table>';

        $html = view('comissao.imprimePdfComissaoPagar', compact('comissoes'));
        $html->render();
        $mpdf->SetHTMLHeader($cabecalho);
        $mpdf->SetHTMLFooter($rodape);
        $mpdf->AddPage('P');
        $mpdf->WriteHTML($html);
        $mpdf->Output($fileName, 'I');
        exit();
    }

}
