<?php

namespace App\Http\Controllers\produtoCusto;

use App\Http\Controllers\Controller;
use App\Models\produto_custos_mp;
use App\Services\ProdutoCustoEstruturaService;
use App\Services\ProdutoCustoMpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProdutoCustoMpController extends Controller
{

    public function listAll(Request $request)
    {
        $ano = (int) ($request->ano ?? date('Y'));
        $mes = (int) ($request->mes ?? date('m'));

        $busca = trim((string) ($request->busca ?? ''));

        $utilizada = $request->utilizada ?? 'S';

        $query = produto_custos_mp::where('ano', $ano)
            ->where('mes', $mes);

        if ($busca != '') {
            $query->where(function ($q) use ($busca) {
                $q->where('cod_mp', 'like', '%' . $busca . '%')
                    ->orWhere('desc_mp', 'like', '%' . $busca . '%');
            });
        }

        if ($utilizada == 'S') {
            $query->where('utilizada_estrutura', 'S');
        }

        if ($utilizada == 'N') {
            $query->where('utilizada_estrutura', 'N');
        }

        $custos = $query
            ->orderByRaw("CASE WHEN utilizada_estrutura = 'S' THEN 0 ELSE 1 END")
            ->orderBy('desc_mp')
            ->orderBy('cod_mp')
            ->get();

        if ($request->ajax()) {
            return view('custos-mp._table', [
                'ano' => $ano,
                'mes' => $mes,
                'busca' => $busca,
                'utilizada' => $utilizada,
                'custos' => $custos,
            ]);
        }

        return view('custos-mp.listAll', [
            'ano' => $ano,
            'mes' => $mes,
            'busca' => $busca,
            'utilizada' => $utilizada,
            'custos' => $custos,
        ]);
    }

    public function salvarManual(Request $request, ProdutoCustoMpService $service)
    {
        try {
            $custo = $service->salvarCustoManual($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Custo manual salvo com sucesso.',
                'custo' => $custo,
                'linhas_ficha_atualizadas' => $custo->linhas_ficha_atualizadas ?? 0,
                'produtos_estrutura_atualizados' => $custo->produtos_estrutura_atualizados ?? 0,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar custo manual.',
                'erro' => $e->getMessage(),
            ], 500);
        }
    }

    public function importarManualLote(
        Request $request,
        ProdutoCustoMpService $service,
        ProdutoCustoEstruturaService $estruturaService
    ) {
        try {
            $ano = (int) ($request->ano ?? date('Y'));
            $mes = (int) ($request->mes ?? date('m'));
            $texto = (string) ($request->texto ?? '');

            $resultadoImportacao = $service->importarCustosManuaisTexto($ano, $mes, $texto);

            $resultadoAplicacao = $service->aplicarCustosMpNaFicha($ano, $mes);

            $resultadoEstrutura = $estruturaService->consolidar($ano, $mes);

            return response()->json([
                'success' => true,
                'message' => 'Custos manuais importados com sucesso.',
                'importacao' => $resultadoImportacao,
                'aplicacao_mp' => $resultadoAplicacao,
                'estrutura' => $resultadoEstrutura,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao importar custos manuais.',
                'erro' => $e->getMessage(),
            ], 500);
        }
    }


}
