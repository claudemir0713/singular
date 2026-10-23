<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->middleware('auth');
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request)
    {
        $usuario = Auth::user();
        $usuarioRestrito = $this->usuarioRestrito($usuario);
        $filtros = $this->montarFiltros($request, $usuario);
        $dashboard = $this->dashboardService->obterDashboard($filtros);
        $representantes = $usuarioRestrito ? collect() : $this->obterRepresentantes();
        $clientes = $this->obterClienteSelecionado($filtros['part_codigo']);

        return view('dashboard.index', compact('dashboard', 'filtros', 'representantes', 'clientes', 'usuarioRestrito'));
    }

    public function dados(Request $request)
    {
        try {
            return response()->json(['success' => true, 'dashboard' => $this->dashboardService->obterDashboard($this->montarFiltros($request, Auth::user()))]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'Não foi possível atualizar o dashboard.'], 500);
        }
    }

    protected function montarFiltros(Request $request, $usuario)
    {
        $hoje = Carbon::today();
        $dataInicial = $request->input('data_inicial', $hoje->copy()->startOfMonth()->format('Y-m-d'));
        $dataFinal = $request->input('data_final', $hoje->format('Y-m-d'));
        $vencimentoInicial = $request->input('vencimento_inicial', $hoje->copy()->subDays(90)->format('Y-m-d'));
        $vencimentoFinal = $request->input('vencimento_final', $hoje->copy()->addDays(90)->format('Y-m-d'));

        try { $dataInicial = Carbon::parse($dataInicial)->format('Y-m-d'); } catch (\Throwable $e) { $dataInicial = $hoje->copy()->startOfMonth()->format('Y-m-d'); }
        try { $dataFinal = Carbon::parse($dataFinal)->format('Y-m-d'); } catch (\Throwable $e) { $dataFinal = $hoje->format('Y-m-d'); }
        try { $vencimentoInicial = Carbon::parse($vencimentoInicial)->format('Y-m-d'); } catch (\Throwable $e) { $vencimentoInicial = $hoje->copy()->subDays(90)->format('Y-m-d'); }
        try { $vencimentoFinal = Carbon::parse($vencimentoFinal)->format('Y-m-d'); } catch (\Throwable $e) { $vencimentoFinal = $hoje->copy()->addDays(90)->format('Y-m-d'); }

        if (Carbon::parse($dataInicial)->gt(Carbon::parse($dataFinal))) {
            $temporaria = $dataInicial; $dataInicial = $dataFinal; $dataFinal = $temporaria;
        }
        if (Carbon::parse($vencimentoInicial)->gt(Carbon::parse($vencimentoFinal))) {
            $temporaria = $vencimentoInicial; $vencimentoInicial = $vencimentoFinal; $vencimentoFinal = $temporaria;
        }

        $filtros = ['data_inicial' => $dataInicial, 'data_final' => $dataFinal, 'vencimento_inicial' => $vencimentoInicial, 'vencimento_final' => $vencimentoFinal, 'rep_codigo' => $this->inteiro($request->input('rep_codigo')), 'part_codigo' => $this->inteiro($request->input('part_codigo')), 'usuario_restrito' => false];

        if ($this->usuarioRestrito($usuario)) {
            $filtros['usuario_restrito'] = true;
            $filtros['rep_codigo'] = $this->obterCodigoRepresentante($this->inteiro($usuario->part_codigo));
        }

        return $filtros;
    }

    protected function obterCodigoRepresentante($partCodigo)
    {
        if (!$partCodigo) return -1;
        $resultado = DB::connection(env('APP_NAME'))->select('SELECT FIRST 1 PART_REPRESENTANTE_CODIGO FROM PARTICIPANTE WHERE PART_CODIGO = ?', [$partCodigo]);
        return !empty($resultado) && !empty($resultado[0]->PART_REPRESENTANTE_CODIGO) ? (int) $resultado[0]->PART_REPRESENTANTE_CODIGO : -1;
    }

    protected function usuarioRestrito($usuario)
    {
        return $usuario && strtolower(trim((string) $usuario->nivel)) === 'restrito';
    }

    protected function obterRepresentantes()
    {
        return collect(DB::connection(env('APP_NAME'))->select('SELECT PART_REPRESENTANTE_CODIGO AS CODIGO, MAX(PART_NOME) AS NOME FROM PARTICIPANTE WHERE PART_REPRESENTANTE_CODIGO > 0 GROUP BY PART_REPRESENTANTE_CODIGO ORDER BY 2'));
    }

    protected function obterClienteSelecionado($partCodigo)
    {
        if (!$partCodigo) return collect();
        return collect(DB::connection(env('APP_NAME'))->select('SELECT FIRST 1 PART_CODIGO AS CODIGO, PART_NOME AS NOME FROM PARTICIPANTE WHERE PART_CODIGO = ?', [$partCodigo]));
    }

    protected function inteiro($valor)
    {
        if ($valor === null || $valor === '') return null;
        $valor = filter_var($valor, FILTER_VALIDATE_INT);
        return $valor !== false ? (int) $valor : null;
    }
}
