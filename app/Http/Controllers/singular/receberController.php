<?php

namespace App\Http\Controllers\singular;

use App\Helpers\Receber;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FIN_CONTAS_TIPOS;

class receberController extends Controller
{
    // public function listAll(Request $request){
    //     $nivel = Auth::user()->nivel;
    //     $rep_cod = Auth::user()->part_codigo;

    //     // $tipo_pgto = Fin_contas_tipos::get();

    //     $dateForm = $request->except('_token');
    //     $dtIEmissa  = $request->dtIEmissa;
    //     $dtFEmissa  = $request->dtFEmissa;

    //     $dataI      = $request->dtI;
    //     $dataF      = $request->dtF;
    //     if(!$dataI){$dataI = date('Y-m-d');};
    //     if(!$dataF){$dataF = date('Y-m-d');};
    //     $cliente   = strtoupper($request->cliente);
    //     $vendedor  = strtoupper($request->vendedor);
    //     $tipo      = strtoupper($request->tipo);
    //     $status    = strtoupper($request->status);
    //     $nf        = strtoupper($request->nf);
    //     $receber   = Receber::receber($dataI,$dataF,$cliente,$vendedor,$tipo,$status,$nf,$dtIEmissa,$dtFEmissa,$nivel,$rep_cod);
    //     return view('receber.listAll',compact('receber','dateForm'));
    // }

    public function listAll(Request $request)
    {
        $nivel = Auth::user()->nivel;
        $rep_cod = Auth::user()->part_codigo;

        $dateForm = $request->except('_token');

        $dtIEmissa = $request->dtIEmissa;
        $dtFEmissa = $request->dtFEmissa;

        $dataI = $request->dtI;
        $dataF = $request->dtF;

        if (!$dataI) {
            $dataI = date('Y-m-d');
        }

        if (!$dataF) {
            $dataF = date('Y-m-d');
        }

        $cliente = strtoupper((string) $request->cliente);
        $vendedor = strtoupper((string) $request->vendedor);
        $tipo = strtoupper((string) $request->tipo);
        $status = strtoupper((string) $request->status);
        $nf = strtoupper((string) $request->nf);

        $tipoPgtoSelecionados = $request->input('tipo_pgto', []);

        if (!is_array($tipoPgtoSelecionados)) {
            $tipoPgtoSelecionados = [$tipoPgtoSelecionados];
        }

        $tipoPgtoSelecionados = array_values(array_filter(
            array_map(function ($tipoPgto) {
                return strtoupper(trim((string) $tipoPgto));
            }, $tipoPgtoSelecionados)
        ));

        $dateForm['tipo_pgto'] = $tipoPgtoSelecionados;

        $tiposPgto = FIN_CONTAS_TIPOS::query()
            ->select(
                'CON_TIPO_PAGAMENTO',
                'CON_TIPO_DESC',
                'CON_TIPO_ORDEM'
            )
            ->whereNotNull('CON_TIPO_DESC')
            ->where('CON_TIPO_DESC', '<>', '')
            ->orderBy('CON_TIPO_ORDEM')
            ->orderBy('CON_TIPO_DESC')
            ->get();

        $receber = Receber::receber(
            $dataI,
            $dataF,
            $cliente,
            $vendedor,
            $tipo,
            $status,
            $nf,
            $dtIEmissa,
            $dtFEmissa,
            $nivel,
            $rep_cod
        );

        $receber = collect($receber);

        if (!empty($tipoPgtoSelecionados)) {
            $receber = $receber
                ->filter(function ($item) use ($tipoPgtoSelecionados) {
                    $tipoPgtoItem = strtoupper(
                        trim((string) ($item->TIPO_PGTO ?? ''))
                    );

                    return in_array(
                        $tipoPgtoItem,
                        $tipoPgtoSelecionados,
                        true
                    );
                })
                ->values();
        }

        return view('receber.listAll', compact(
            'receber',
            'dateForm',
            'tiposPgto',
            'tipoPgtoSelecionados'
        ));
    }

    public function imprimir(Request $request)
    {
        $nivel = Auth::user()->nivel;
        $rep_cod = Auth::user()->part_codigo;

        $dateForm = $request->except('_token');

        $dtIEmissa = $request->dtIEmissa;
        $dtFEmissa = $request->dtFEmissa;

        $dataI = $request->dtI;
        $dataF = $request->dtF;

        if (!$dataI) {
            $dataI = date('Y-m-d');
        }

        if (!$dataF) {
            $dataF = date('Y-m-d');
        }

        $cliente = strtoupper((string) $request->cliente);
        $vendedor = strtoupper((string) $request->vendedor);
        $tipo = strtoupper((string) $request->tipo);
        $status = strtoupper((string) $request->status);
        $nf = strtoupper((string) $request->nf);

        $tipoPgtoSelecionados = $request->input('tipo_pgto', []);

        if (!is_array($tipoPgtoSelecionados)) {
            $tipoPgtoSelecionados = [$tipoPgtoSelecionados];
        }

        $tipoPgtoSelecionados = array_map(function ($tipoPgto) {
            return strtoupper(trim((string) $tipoPgto));
        }, $tipoPgtoSelecionados);

        $tipoPgtoSelecionados = array_filter($tipoPgtoSelecionados);

        $tipoPgtoSelecionados = array_unique($tipoPgtoSelecionados);

        $tipoPgtoSelecionados = array_values($tipoPgtoSelecionados);

        $dateForm['tipo_pgto'] = $tipoPgtoSelecionados;

        $receber = Receber::receber(
            $dataI,
            $dataF,
            $cliente,
            $vendedor,
            $tipo,
            $status,
            $nf,
            $dtIEmissa,
            $dtFEmissa,
            $nivel,
            $rep_cod
        );

        $receber = collect($receber);

        if (!empty($tipoPgtoSelecionados)) {
            $receber = $receber
                ->filter(function ($item) use ($tipoPgtoSelecionados) {
                    $tipoPgtoItem = strtoupper(
                        trim((string) ($item->TIPO_PGTO ?? ''))
                    );

                    return in_array(
                        $tipoPgtoItem,
                        $tipoPgtoSelecionados,
                        true
                    );
                })
                ->values();
        }

        $fileName = 'Contas a receber.pdf';

        $mpdf = new \Mpdf\Mpdf([
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 10,
            'margin_top' => 20,
            'margin_bottom' => 15,
            'margin_header' => 5,
            'margin_footer' => 5
        ]);

        $cabecalho = '<table width="100%">';
        $cabecalho .= '<tr>';
        $cabecalho .= '<td width="10%" align="center">';
        $cabecalho .= '' . asset('img/logo.png') . '';
        $cabecalho .= '</td>';
        $cabecalho .= '<td width="90%" align="center">';
        $cabecalho .= '<span style="font-size:20px"><b>Contas a receber</b></span>';
        $cabecalho .= '</td>';
        $cabecalho .= '</tr>';
        $cabecalho .= '</table><hr>';

        $rodape = '<hr><table width="100%">';
        $rodape .= '<tr>';
        $rodape .= '<td width="80%" align="center"></td>';
        $rodape .= '<td width="20%" align="right">';
        $rodape .= '<span style="font-size:10px">Página {PAGENO} de {nb}</span>';
        $rodape .= '</td>';
        $rodape .= '</tr>';
        $rodape .= '</table>';

        $html = view('receber.imprimePdf', compact(
            'receber',
            'dateForm',
            'tipoPgtoSelecionados'
        ))->render();

        $mpdf->SetHTMLHeader($cabecalho);
        $mpdf->SetHTMLFooter($rodape);
        $mpdf->AddPage('L');
        $mpdf->WriteHTML($html);
        $mpdf->Output($fileName, 'I');
    }
}
