<?php

namespace App\Http\Controllers\fechamento;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Ficha;
use App\Helpers\Mlc;
use App\Models\mlc_hora;
use App\Models\mlc_horaprod;
use App\Services\ProdutoCustoMpService;
use App\Services\ProdutoFichaFechamentoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class fechamentoController extends Controller
{
    public function listAll(Request $request){
        $dateForm = $request->except('_token');
        return view('fechamento.listAll',compact('dateForm'));
    }

    public function produtoComFicha(Request $request)
    {
        return view('fechamento.produtoComFicha');
    }

    public function fechamento(Request $request, ProdutoFichaFechamentoService $service)
    {
        $ano = (int) $request->ano;
        $mes = (int) $request->mes;
        $mostrar = $request->mostrar ?? 'N';

        try {
            $resultado = $service->executarFichaAtual($ano, $mes, $mostrar);

            if ($mostrar == 'S') {
                return response($resultado['html']);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Fechamento da ficha processado com sucesso.',
                    'ano' => $resultado['ano'],
                    'mes' => $resultado['mes'],
                    'produtos' => $resultado['produtos'],
                    'linhas_ficha' => $resultado['linhas_ficha'],
                    'total_produtos_sem_ficha' => $resultado['total_produtos_sem_ficha'],
                    'produtos_sem_ficha' => $resultado['produtos_sem_ficha'],
                ]);
            }

            return view('fechamento.produtos-sem-ficha', [
                'resultado' => $resultado,
            ]);

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao processar fechamento.',
                    'erro' => $e->getMessage(),
                ], 500);
            }

            return view('fechamento.erro', [
                'erro' => $e->getMessage(),
            ]);
        }
    }

    public function consutaProdutoComFicha(Request $request)
    {
        $pro_descr = strtoupper($request->pro_descr);
        $pro_descr = str_replace(' ','%',$pro_descr);
        $sql = "
            SELECT
                *
            FROM(
                SELECT
                    PRODUTOS.PRD_CODIGO
                    ,PRODUTOS.PRD_DESCRICAO
                    ,COUNT(PRODUTOS_COMPONENTES_PRODUCAO.PRCP_ITEM) QT_COMP
                FROM PRODUTOS
                LEFT JOIN PRODUTOS_COMPONENTES_PRODUCAO ON PRODUTOS_COMPONENTES_PRODUCAO.PRD_CODIGO = PRODUTOS.PRD_CODIGO
                WHERE PRODUTOS.PRD_TIPO_PRODUTO IN ('04')
                GROUP BY
                    PRODUTOS.PRD_CODIGO
                    ,PRODUTOS.PRD_DESCRICAO
            )DADOS_FICHA
            WHERE DADOS_FICHA.QT_COMP>0
            AND PRD_DESCRICAO LIKE UPPER('%$pro_descr%')
            ORDER BY PRD_DESCRICAO
        ";
        $produto = db::connection(env('APP_NAME'))->select($sql);
        return $produto;
    }

    public function importaHorasMlc()
    {
        return view('fechamento.importaHorasMlc');
    }

    public function gravaHorasMLC(Request $request)
    {
        $data = $request->texto;
        $texto = explode("\n",$data);
        $retorno = '';
        foreach($texto as $linhas){
            $linha          = explode("\t",$linhas);
            $ano            = intval(trim($linha[0]));
            $mes            = intval(trim($linha[1]));
            $CodCa          = intval(trim($linha[2]));
            $valor          = floatval(str_replace(',','.',str_replace('.','',$linha[3])));
            $CodConta       = intval(trim($linha[4]));

            $Existehora = mlc_hora::where('Ano',$ano)->where('CodConta',$CodConta)->where('CodCa',$CodCa)->count();
            $Existehoraprod = mlc_horaprod::where('Ano',$ano)->where('CodCa',$CodCa)->count();

            if($Existehora==0){
                Mlc::horasInsert($ano,$mes,$CodCa,$CodConta,$valor);
            }else{
                Mlc::horasUpd($ano,$mes,$CodCa,$CodConta,$valor);
            }

            if($Existehoraprod==0){
                Mlc::horasProdInsert($ano,$mes,$CodCa,$CodConta,$valor);
            }else{
                Mlc::horasProdUpd($ano,$mes,$CodCa,$CodConta,$valor);
            }

        }
        return response()->json(($retorno));
    }

}
