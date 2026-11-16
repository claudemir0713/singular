<?php

namespace App\Http\Controllers\singular;

use App\Helpers\Vendas;
use App\Helpers\Papeletas;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\DAV;
use App\Models\DAV_CARREGAMENTO;
use App\Models\DAV_CARREGAMENTO_PEDIDOS;
use App\Models\papeleta;
use App\Models\papeleta_dav;
use App\Models\papeleta_parcela;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class vendasController extends Controller
{
    public function listAll(Request $request){
        $dateForm = $request->except('_token');

        $dataI      = $request->dtI;
        $dataF      = $request->dtF;
        if(!$dataI){$dataI = date('Y-m-01');};
        if(!$dataF){$dataF = date('Y-m-d');};
        $dateForm['dtI'] = $dataI;
        $dateForm['dtF'] = $dataF;
        $esc        = function ($v) { return strtoupper(str_replace("'", "''", trim((string) $v))); };
        $vendedor   = $esc($request->vendedor);
        $cliente    = $esc($request->cliente);
        $nf         = $request->nf;
        $extra      = [
            'tipo'      => in_array($request->tipo, ['NF', 'PD'], true) ? $request->tipo : '',
            'uf'        => $esc($request->uf),
            'municipio' => $esc($request->municipio),
            'forma'     => $esc($request->forma),
            'produto'   => $esc($request->produto),
        ];

        $nivel = Auth::user()->nivel;
        $rep_cod = Auth::user()->part_codigo;

        $vendas = Vendas::vendas($dataI,$dataF,$cliente,$vendedor,$nf,$nivel,$rep_cod,$extra);

        $formas = [];
        $vendedores = [];
        try {
            $conn = DB::connection(env('APP_NAME'));
            $formas = array_map(function ($r) { return trim($r->CPA_DESCRICAO); }, $conn->select("SELECT CPA_DESCRICAO FROM CONDICOES_PAGAMENTO WHERE CPA_DESCRICAO IS NOT NULL ORDER BY 1"));
            if ($nivel != 'restrito') {
                $vendedores = array_map(function ($r) { return trim($r->PART_NOME); }, $conn->select("SELECT DISTINCT PART_NOME FROM PARTICIPANTE WHERE PART_REPRESENTANTE_CODIGO IS NOT NULL AND PART_NOME IS NOT NULL ORDER BY 1"));
            }
        } catch (\Throwable $e) {
            report($e);
        }
        return view('vendas.listAll',compact('vendas','dateForm','formas','vendedores'));
    }

    /** Itens (produtos) de um documento do relatório de vendas, para o modal de auditoria. */
    public function itens(Request $request){
        $tipo = $request->tipo === 'PD' ? 'PD' : 'NF';
        $data = $request->data ? date('Y-m-d', strtotime($request->data)) : date('Y-m-d');
        $dataF = date('Y-m-d', strtotime($data . ' +1 day'));
        $nivel = Auth::user()->nivel;
        $rep_cod = Auth::user()->part_codigo;
        try {
            $rows = collect(Vendas::vendas($data,$dataF,'','','',$nivel,$rep_cod,[],['tipo' => $tipo, 'doc' => $request->doc, 'cli' => $request->cli]));
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'Não foi possível carregar os itens.'], 500);
        }
        $itens = $rows->map(function ($r) {
            $qtd = (float) $r->QUANTIDADE;
            $base = (float) $r->FAT_BRUTO + (float) $r->FAT_BRUTO_2;
            return [
                'codigo' => trim((string) $r->COD_PRODUTO),
                'produto' => trim((string) $r->PRODUTO),
                'un' => trim((string) $r->UN_MEDIDA),
                'qtd' => $qtd,
                'unitario' => $qtd ? $base / $qtd : 0,
                'fat1' => (float) $r->FAT_BRUTO,
                'fat2' => (float) $r->FAT_BRUTO_2,
                'desconto' => (float) $r->DESCONTO,
                'ipi' => (float) $r->IPI,
                'total' => $base + (float) $r->IPI,
            ];
        })->values();
        return response()->json(['success' => true, 'itens' => $itens]);
    }

    public function papeleta()
    {
        return view('vendas.papeleta');
    }

    public function papeletaDados(Request $request){
        $dadosPapeleta = [];
        $nr_carga = $request->carga;
        $vendedor = $request->vendedor;

        $dadosPapeleta = Papeletas::gravaCargaMysql($nr_carga);
        $dadosMySql = Papeletas::dadosMySql($nr_carga, $vendedor);
        return $dadosMySql;
    }

    public function atualiza_papeleta(Request $request)
    {
        $papeleta = papeleta::where('carga',$request->carga)->first();
        $papeleta->placa = $request->placa;
        $papeleta->motorista = $request->motorista;
        $papeleta->save();
    }
    public function atualiza_papeleta_frete(Request $request)
    {
        $papeleta = papeleta_dav::where('dav',$request->id_dav)->first();
        $papeleta->frete_valor  = Helper::formata_valor($request->frete);
        $papeleta->cobranca     = $request->cobranca;
        $papeleta->save();
    }
    public function atualiza_papeleta_parcela(Request $request)
    {
        $papeleta = papeleta_parcela::find($request->parcela_id);
        $papeleta->considera  = $request->considera;
        $papeleta->save();
    }

    public function cargas_pdf(Request $request, $cargaId)
    {
        $carga      = [];
        $dav        = [];
        $vendedor   = trim((string) $request->query('vendedor', ''));

        $papeleta   = papeleta::where('carga',$cargaId)->first();
        $papeleta_dav = papeleta_dav::where('carga',$cargaId)
            ->when($vendedor !== '', function ($q) use ($vendedor) {
                $q->where('vendedor', 'like', '%' . $vendedor . '%');
            })
            ->get();

        foreach($papeleta_dav as $item){
            $dav_singular = DAV::where('DAV_NUMERO_AUXILIAR',$item->dav)->first();
            if($dav_singular){
                $pedido_cli = $dav_singular->DAV_CONTROLE_USUARIO;
            }else{
                $pedido_cli = '';
            };
            $papeleta_parcela = papeleta_parcela::where('papeleta_dav_id',$item->id)->where('considera','S')->get();
            $parcelas   = [];
            foreach($papeleta_parcela as $item_p){
                $parcelas[]=[
                    'parcela'       =>$item_p->parcela
                    , 'tipo'        =>$item_p->tipo
                    , 'vencimento'  =>$item_p->vencimento
                    , 'valor'       =>$item_p->valor
                ];
            }
            $dav[]=[
                'dav'           => $item->dav
                , 'cliente'     => $item->cliente
                , 'frete_valor' => $item->frete_valor
                , 'cobranca'    => $item->cobranca
                , 'parcelas'    => $parcelas
                , 'pedido_cli'  => $pedido_cli
            ];
        }

        $carga=[
            'carga'         => $papeleta->carga
            ,'placa'        => $papeleta->placa
            ,'motorista'    => $papeleta->motorista
            ,'dav'          => $dav
        ];

        // ====== Gerar PDF com mPDF ======
        $mpdf = new Mpdf([
            'mode'        => 'utf-8',
            'format'      => 'A4',
            'margin_left' => 10,
            'margin_right'=> 10,
            'margin_top'  => 10,
            'margin_bottom'=> 10,
            'default_font' => 'arial',
            'default_font_size' => 9,

        ]);
        $fileName = "carga.$cargaId.pdf";
        $html = view('vendas.carga', compact('carga'));
        $html->render();
        $mpdf->AddPage('P');
        $mpdf->WriteHTML($html);
        $mpdf->Output($fileName, 'I');
        exit();        // HTML da view

    }


}
