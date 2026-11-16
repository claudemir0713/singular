<?php

namespace App\Http\Controllers\nfe;

use App\Http\Controllers\Controller;
use App\Services\Fiscal\SefazDistribuicaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * NF-e recebidas: certificado A1, busca na SEFAZ, Ciencia da Operacao e download dos XMLs.
 * Toda a regra fica em SefazDistribuicaoService.
 */
class NfeRecebidasController extends Controller
{
    private function falha(string $msg, int $status = 422)
    {
        return response()->json(['ok' => false, 'erros' => [$msg]], $status);
    }

    private function chaves(Request $request): array
    {
        return array_values(array_filter((array) $request->input('chaves'), function ($c) {
            return preg_match('/^\d{44}$/', (string) $c);
        }));
    }

    public function index()
    {
        return view('nfe.recebidas');
    }

    public function status(SefazDistribuicaoService $svc)
    {
        return response()->json(['ok' => true] + $svc->status());
    }

    public function salvarCertificado(Request $request, SefazDistribuicaoService $svc)
    {
        $arq = $request->file('certificado');
        if (!$arq || !$arq->isValid()) {
            return $this->falha('Selecione o arquivo do certificado (.pfx ou .p12).');
        }
        if (!in_array(strtolower($arq->getClientOriginalExtension()), ['pfx', 'p12'], true)) {
            return $this->falha('O certificado A1 precisa ser um arquivo .pfx ou .p12.');
        }
        if ($arq->getSize() > 512 * 1024) {
            return $this->falha('Arquivo grande demais para um certificado A1.');
        }
        $senha = (string) $request->input('senha');
        if ($senha === '') {
            return $this->falha('Informe a senha do certificado.');
        }
        try {
            $status = $svc->salvarCertificado(file_get_contents($arq->getRealPath()), $senha, (int) $request->input('ambiente', 1));
            return response()->json(['ok' => true] + $status);
        } catch (\RuntimeException $e) {
            return $this->falha($e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Certificado A1: ' . get_class($e));      // sem mensagem: pode conter trecho da senha/arquivo
            return $this->falha('Não foi possível salvar o certificado.', 500);
        }
    }

    public function removerCertificado(SefazDistribuicaoService $svc)
    {
        $svc->remover();
        return response()->json(['ok' => true] + $svc->status());
    }

    public function consultar(SefazDistribuicaoService $svc)
    {
        try {
            return response()->json(['ok' => true] + $svc->consultar() + ['status_cert' => $svc->status()]);
        } catch (\RuntimeException $e) {
            return response()->json(['ok' => false, 'erros' => [$e->getMessage()], 'status_cert' => $svc->status()], 422);
        } catch (\Throwable $e) {
            Log::error('Consulta SEFAZ: ' . $e->getMessage());
            return $this->falha('Falha inesperada na consulta à SEFAZ: ' . mb_substr($e->getMessage(), 0, 200), 500);
        }
    }

    /** Traz do ERP Singular o que ele ja recebeu da SEFAZ (somente leitura no ERP). */
    public function importarErp(SefazDistribuicaoService $svc)
    {
        set_time_limit(0);
        try {
            return response()->json(['ok' => true] + $svc->importarDoErp());
        } catch (\Throwable $e) {
            Log::error('Importacao do ERP: ' . $e->getMessage());
            return $this->falha('Não foi possível importar do ERP: ' . mb_substr($e->getMessage(), 0, 200), 500);
        }
    }

    public function documentos(Request $request, SefazDistribuicaoService $svc)
    {
        $r = $svc->documentos([
            'busca' => $request->query('busca'), 'de' => $request->query('de'), 'ate' => $request->query('ate'), 'situacao' => $request->query('situacao'),
            'pagina' => $request->query('pagina', 1), 'por_pagina' => $request->query('por_pagina', 50), 'so_chaves' => $request->boolean('so_chaves'),
        ]);
        return response()->json(['ok' => true] + $r + ['status_cert' => $svc->status()]);
    }

    public function manifestarLote(Request $request, SefazDistribuicaoService $svc)
    {
        $chaves = $this->chaves($request);
        if (!$chaves) {
            return $this->falha('Nenhuma chave válida selecionada.');
        }
        try {
            return response()->json(['ok' => true] + $svc->manifestarLote($chaves));
        } catch (\Throwable $e) {
            Log::error('Manifestacao em lote SEFAZ: ' . $e->getMessage());
            return $this->falha('Falha inesperada ao manifestar: ' . mb_substr($e->getMessage(), 0, 200), 500);
        }
    }

    /** Busca o XML de uma nota especifica pela chave (independe do NSU). */
    public function buscarChave(Request $request, SefazDistribuicaoService $svc)
    {
        try {
            return response()->json(['ok' => true] + $svc->consultarChave((string) $request->chave));
        } catch (\RuntimeException $e) {
            return $this->falha($e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Consulta SEFAZ por chave: ' . $e->getMessage());
            return $this->falha('Falha inesperada na consulta da nota: ' . mb_substr($e->getMessage(), 0, 200), 500);
        }
    }

    /** Download de um XML completo. */
    public function xml(string $chave, SefazDistribuicaoService $svc)
    {
        if (!preg_match('/^\d{44}$/', $chave)) {
            abort(404);
        }
        $xml = $svc->xmlCompleto($chave);
        if ($xml === null) {
            abort(404, 'XML ainda não recebido. Dê ciência da operação e busque na SEFAZ.');
        }
        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8', 'Content-Disposition' => 'attachment; filename="' . $chave . '-nfe.xml"']);
    }

    /** Download em ZIP dos XMLs das chaves marcadas. */
    public function zip(Request $request, SefazDistribuicaoService $svc)
    {
        $chaves = $this->chaves($request);
        if (!$chaves) {
            abort(422, 'Nenhuma nota selecionada.');
        }
        try {
            [$arq, $n] = $svc->zipXmls($chaves);
        } catch (\RuntimeException $e) {
            abort(500, $e->getMessage());
        }
        if (!$arq) {
            abort(404, 'Nenhuma das notas selecionadas possui XML completo.');
        }
        return response()->download($arq, 'nfe-xml-' . date('Ymd-His') . '.zip')->deleteFileAfterSend(true);
    }
}
