<?php

namespace App\Services\Fiscal;

use App\Models\EMPRESA;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Certificado digital A1 + distribuicao de documentos da SEFAZ (NFeDistribuicaoDFe, Ambiente Nacional)
 * + manifestacao "Ciencia da Operacao" (210210), que libera o download do XML completo.
 *
 * Seguranca: o PFX fica fora da pasta publica; a senha so e gravada criptografada (Crypt/APP_KEY) e nunca vai para log.
 * O PEM temporario usado pelo cURL vive so durante a chamada (arquivo 0600 apagado no finally).
 * Regras da SEFAZ respeitadas: ultNSU incremental, espera de 1h apos "nenhum documento" (137) ou consumo indevido (656).
 */
class SefazDistribuicaoService
{
    const NFE = 'http://www.portalfiscal.inf.br/nfe';
    const UF = ['RO' => 11, 'AC' => 12, 'AM' => 13, 'RR' => 14, 'PA' => 15, 'AP' => 16, 'TO' => 17, 'MA' => 21, 'PI' => 22, 'CE' => 23, 'RN' => 24, 'PB' => 25, 'PE' => 26, 'AL' => 27,
        'SE' => 28, 'BA' => 29, 'MG' => 31, 'ES' => 32, 'RJ' => 33, 'SP' => 35, 'PR' => 41, 'SC' => 42, 'RS' => 43, 'MS' => 50, 'MT' => 51, 'GO' => 52, 'DF' => 53];

    /** @var callable|null function(string $url, string $envelope, string $action, ?array $pem): array{0:int,1:string} */
    public $transporte = null;

    /** Tabelas do certificado e da distribuicao ficam no MySQL do proprio Singular (conexao padrao). */
    private function db()
    {
        return DB::connection(config('database.default'));
    }

    /** CNPJ e UF da empresa vem do ERP (Firebird). */
    private function empresa(): object
    {
        $e = EMPRESA::first(['EMPRESA_CNPJ', 'EMPRESA_UF']);
        return (object) ['cnpj' => $e ? $e->EMPRESA_CNPJ : '', 'uf' => $e ? $e->EMPRESA_UF : ''];
    }

    private function digitos($v): string
    {
        return preg_replace('/\D/', '', (string) $v);
    }

    /* ------------------------------------------------------------------ certificado */

    /** Certificado ativo da base (linha do banco) ou null. */
    public function ativo(): ?object
    {
        return $this->db()->table('empresa_certificado')->where('ativo', 1)->orderByDesc('id')->first();
    }

    /** Dados para a tela: nunca devolve senha nem caminho do arquivo. */
    public function status(): array
    {
        $c = $this->ativo();
        $docs = $this->db()->table('nfe_distribuicao');
        if (!$c) {
            return ['configurado' => false, 'valido' => false, 'motivo' => 'Nenhum certificado A1 cadastrado.', 'pendentes' => 0];
        }
        $agora = time();
        $ate = $c->valido_ate ? strtotime($c->valido_ate) : 0;
        $valido = $ate > $agora;
        $proxima = $c->proxima_consulta ? strtotime($c->proxima_consulta) : 0;
        return [
            'configurado' => true, 'valido' => $valido, 'motivo' => $valido ? null : 'Certificado vencido em ' . date('d/m/Y', $ate) . '.',
            'titular' => $c->titular, 'cnpj' => $c->cnpj, 'serial' => $c->serial, 'valido_de' => $c->valido_de, 'valido_ate' => $c->valido_ate,
            'dias_restantes' => $valido ? (int) floor(($ate - $agora) / 86400) : 0, 'ambiente' => (int) $c->ambiente,
            'ultimo_nsu' => $c->ultimo_nsu, 'max_nsu' => $c->max_nsu, 'ultima_consulta' => $c->ultima_consulta, 'proxima_consulta' => $c->proxima_consulta,
            'liberada' => $proxima <= $agora, 'ultimo_status' => $c->ultimo_status,
            'pendentes' => (int) $docs->count(),
        ];
    }

    /**
     * Valida e grava o certificado. Confere senha, validade e se o CNPJ do certificado e de uma empresa cadastrada.
     * @throws \RuntimeException mensagem segura para exibir ao usuario
     */
    public function salvarCertificado(string $pfxBytes, string $senha, int $ambiente): array
    {
        $info = $this->lerPfx($pfxBytes, $senha);
        if ($info['valido_ate'] <= time()) {
            throw new \RuntimeException('O certificado está vencido (válido até ' . date('d/m/Y', $info['valido_ate']) . ').');
        }
        $cnpjs = array_filter([$this->digitos($this->empresa()->cnpj)]);
        if (!$info['cnpj']) {
            throw new \RuntimeException('Não foi possível identificar o CNPJ no certificado. Use um certificado A1 de pessoa jurídica (e-CNPJ).');
        }
        if ($cnpjs && !in_array($info['cnpj'], $cnpjs, true)) {
            throw new \RuntimeException('O CNPJ do certificado (' . $this->formataCnpj($info['cnpj']) . ') não é o da empresa cadastrada nesta base.');
        }

        if (!empty($info['legado'])) {
            // regrava o A1 com criptografia atual (mesma chave e senha): dai em diante o PHP le o arquivo sem depender do OpenSSL externo
            $novo = '';
            if (@openssl_pkcs12_export($info['cert'], $novo, $info['pkey'], $senha, ['extracerts' => $info['extra']]) && $novo !== '') {
                $pfxBytes = $novo;
            }
        }
        $hash = hash('sha256', $pfxBytes);
        $rel = 'certificados/' . $hash . '.pfx';
        $abs = storage_path('app/' . $rel);
        if (!is_dir(dirname($abs))) {
            mkdir(dirname($abs), 0700, true);
        }
        file_put_contents($abs, $pfxBytes);
        @chmod($abs, 0600);

        $db = $this->db();
        $db->table('empresa_certificado')->where('ativo', 1)->update(['ativo' => 0, 'updated_at' => now()]);
        $mesmo = $db->table('empresa_certificado')->where('hash_sha256', $hash)->where('cnpj', $info['cnpj'])->first();
        $nsu = $mesmo ? $mesmo->ultimo_nsu : '000000000000000';   // mesmo certificado reenviado: nao recomeca a distribuicao
        $db->table('empresa_certificado')->insert([
            'cnpj' => $info['cnpj'], 'titular' => mb_substr($info['titular'], 0, 200), 'serial' => $info['serial'], 'arquivo' => $rel, 'hash_sha256' => $hash,
            'senha_cript' => Crypt::encryptString($senha), 'valido_de' => date('Y-m-d H:i:s', $info['valido_de']), 'valido_ate' => date('Y-m-d H:i:s', $info['valido_ate']),
            'ambiente' => $ambiente === 2 ? 2 : 1, 'ultimo_nsu' => $nsu, 'max_nsu' => $mesmo->max_nsu ?? '000000000000000', 'ativo' => 1,
            'usuario_id' => Auth::id(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        Log::info('Certificado A1 cadastrado', ['cnpj' => $info['cnpj'], 'valido_ate' => date('Y-m-d', $info['valido_ate']), 'usuario' => Auth::id()]);
        return $this->status();
    }

    public function remover(): void
    {
        $db = $this->db();
        foreach ($db->table('empresa_certificado')->where('ativo', 1)->get() as $c) {
            $f = storage_path('app/' . $c->arquivo);
            // o arquivo so e apagado se nenhum outro registro (historico de NSU) o usa
            $usos = $db->table('empresa_certificado')->where('arquivo', $c->arquivo)->where('id', '<>', $c->id)->count();
            if (!$usos && is_file($f)) { @unlink($f); }
        }
        $db->table('empresa_certificado')->where('ativo', 1)->update(['ativo' => 0, 'senha_cript' => '', 'updated_at' => now()]);
        Log::info('Certificado A1 removido', ['usuario' => Auth::id()]);
    }

    private function formataCnpj(string $c): string
    {
        return strlen($c) === 14 ? substr($c, 0, 2) . '.' . substr($c, 2, 3) . '.' . substr($c, 5, 3) . '/' . substr($c, 8, 4) . '-' . substr($c, 12) : $c;
    }

    /** @return array{cert:string,pkey:string,cnpj:?string,titular:string,serial:string,valido_de:int,valido_ate:int} */
    public function lerPfx(string $bytes, string $senha): array
    {
        if ($bytes === '') {
            throw new \RuntimeException('Arquivo de certificado vazio.');
        }
        $certs = [];
        $viaLegado = false;
        while (openssl_error_string()) { /* limpa a fila de erros */ }
        if (!@openssl_pkcs12_read($bytes, $certs, $senha) || empty($certs['cert']) || empty($certs['pkey'])) {
            $msg = '';
            while ($e = openssl_error_string()) { $msg .= $e; }
            $legado = $this->lerPfxPeloOpenSsl($bytes, $senha);      // PFX com criptografia antiga: o OpenSSL 3 do PHP recusa
            if ($legado === 'senha') {
                throw new \RuntimeException('Senha do certificado incorreta.');
            }
            if (is_array($legado)) {
                $certs = $legado;
                $viaLegado = true;
            } else {
                if (stripos($msg, 'mac verify') !== false || stripos($msg, 'password') !== false || stripos($msg, 'decrypt') !== false) {
                    throw new \RuntimeException('Senha do certificado incorreta.');
                }
                throw new \RuntimeException('Não foi possível ler o certificado. Envie um arquivo A1 (.pfx ou .p12) válido e confira a senha.' . (stripos($msg, 'unsupported') !== false ? ' O arquivo usa criptografia antiga e não há um OpenSSL compatível no servidor (defina SEFAZ_OPENSSL_BIN no .env).' : ''));
            }
        }
        $x = openssl_x509_parse($certs['cert']);
        if (!$x) {
            throw new \RuntimeException('Certificado ilegível.');
        }
        $cn = (string) ($x['subject']['CN'] ?? '');
        $cnpj = null;
        if (preg_match('/:(\d{14})\b/', $cn, $m)) {
            $cnpj = $m[1];
        } elseif (preg_match('/\b(\d{14})\b/', $cn . ' ' . json_encode($x['subject']) . ' ' . ($x['extensions']['subjectAltName'] ?? ''), $m)) {
            $cnpj = $m[1];
        }
        return [
            'cert' => $certs['cert'], 'pkey' => $certs['pkey'], 'cnpj' => $cnpj,
            'titular' => $cn ?: json_encode($x['subject']), 'serial' => (string) ($x['serialNumberHex'] ?? $x['serialNumber'] ?? ''),
            'valido_de' => (int) $x['validFrom_time_t'], 'valido_ate' => (int) $x['validTo_time_t'],
            'legado' => $viaLegado, 'extra' => $certs['extracerts'] ?? [],
        ];
    }

    /**
     * Reserva para PKCS#12 antigo (RC2-40/3DES), padrao em muitos e-CNPJ A1, que o OpenSSL 3 do PHP nao abre.
     * Chama o executavel do OpenSSL (sem shell; a senha vai por variavel de ambiente, nunca na linha de comando)
     * e devolve ['cert','pkey','extracerts'], 'senha' (senha incorreta) ou null (nao foi possivel).
     * @return array|string|null
     */
    private function lerPfxPeloOpenSsl(string $bytes, string $senha)
    {
        if (!function_exists('proc_open')) {
            return null;
        }
        $candidatos = array_values(array_unique(array_filter([
            config('sefaz.openssl_bin'), 'C:/xampp82/apache/bin/openssl.exe', 'C:/xampp/apache/bin/openssl.exe',
            'C:/Program Files/Git/mingw64/bin/openssl.exe', 'C:/Program Files/Git/usr/bin/openssl.exe', '/usr/bin/openssl', '/usr/local/bin/openssl', 'openssl',
        ])));
        $dir = storage_path('app/tmp_sefaz');
        if (!is_dir($dir)) { mkdir($dir, 0700, true); }
        $tmp = $dir . '/' . bin2hex(random_bytes(8)) . '.pfx';
        file_put_contents($tmp, $bytes);
        @chmod($tmp, 0600);
        $senhaErrada = false;
        try {
            foreach ($candidatos as $bin) {
                if ($bin !== 'openssl' && !is_file($bin)) { continue; }
                foreach (['', '-legacy'] as $flag) {
                    $amb = ['SEFAZ_PFX_SENHA' => $senha];
                    $cmd = [$bin, 'pkcs12'];
                    if ($flag) {
                        $cmd[] = $flag;
                        // o modulo legacy costuma ficar ao lado do executavel (XAMPP) ou em ossl-modules; o padrao compilado pode apontar para outra pasta
                        foreach ([dirname($bin), dirname($bin) . '/ossl-modules', dirname($bin) . '/../lib/ossl-modules'] as $dm) {
                            if (is_file($dm . '/legacy.dll') || is_file($dm . '/legacy.so')) { $cmd[] = '-provider-path'; $cmd[] = $dm; $amb['OPENSSL_MODULES'] = $dm; break; }
                        }
                    }
                    array_push($cmd, '-in', $tmp, '-nodes', '-passin', 'env:SEFAZ_PFX_SENHA');
                    $proc = @proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, array_merge(getenv() ?: [], $amb));
                    if (!is_resource($proc)) { continue; }
                    $out = stream_get_contents($pipes[1]); $err = stream_get_contents($pipes[2]);
                    fclose($pipes[1]); fclose($pipes[2]);
                    $rc = proc_close($proc);
                    if ($rc === 0 && strpos($out, 'PRIVATE KEY') !== false) {
                        $r = $this->separaPem($out);
                        if ($r) { return $r; }
                    }
                    if (stripos($err, 'mac verify') !== false || stripos($err, 'invalid password') !== false) { $senhaErrada = true; }
                }
            }
        } finally {
            @unlink($tmp);
        }
        return $senhaErrada ? 'senha' : null;
    }

    /** Separa chave e certificados do PEM do OpenSSL e acha o certificado que casa com a chave. */
    private function separaPem(string $pem): ?array
    {
        if (!preg_match('/-----BEGIN (?:RSA |EC )?PRIVATE KEY-----.*?-----END (?:RSA |EC )?PRIVATE KEY-----/s', $pem, $k)) {
            return null;
        }
        preg_match_all('/-----BEGIN CERTIFICATE-----.*?-----END CERTIFICATE-----/s', $pem, $c);
        $cert = null; $extras = [];
        foreach ($c[0] as $one) {
            if ($cert === null && @openssl_x509_check_private_key($one, $k[0])) { $cert = $one . "\n"; }
            else { $extras[] = $one . "\n"; }
        }
        return $cert ? ['cert' => $cert, 'pkey' => $k[0] . "\n", 'extracerts' => $extras] : null;
    }

    private function cert(): array
    {
        $c = $this->ativo();
        if (!$c) {
            throw new \RuntimeException('Cadastre o certificado A1 antes de consultar a SEFAZ.');
        }
        if (strtotime($c->valido_ate) <= time()) {
            throw new \RuntimeException('O certificado A1 está vencido. Cadastre um certificado válido.');
        }
        $abs = storage_path('app/' . $c->arquivo);
        if (!is_file($abs)) {
            throw new \RuntimeException('Arquivo do certificado não encontrado. Cadastre o certificado novamente.');
        }
        try {
            $senha = Crypt::decryptString($c->senha_cript);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Não foi possível abrir a senha do certificado (chave da aplicação mudou). Cadastre o certificado novamente.');
        }
        return ['linha' => $c, 'info' => $this->lerPfx(file_get_contents($abs), $senha)];
    }

    /* ------------------------------------------------------------------ transporte SOAP 1.2 */

    private function soap(string $url, string $action, string $dadosXml, string $wsdlNs, array $info): string
    {
        $envelope = '<?xml version="1.0" encoding="utf-8"?><soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">'
            . '<soap12:Body>' . $dadosXml . '</soap12:Body></soap12:Envelope>';

        if ($this->transporte) {
            [$http, $corpo] = call_user_func($this->transporte, $url, $envelope, $action, null);
        } else {
            $dir = storage_path('app/tmp_sefaz');
            if (!is_dir($dir)) { mkdir($dir, 0700, true); }
            $base = $dir . '/' . bin2hex(random_bytes(8));
            $fc = $base . '.crt.pem'; $fk = $base . '.key.pem';
            file_put_contents($fc, $info['cert']); file_put_contents($fk, $info['pkey']);
            @chmod($fc, 0600); @chmod($fk, 0600);
            try {
                $modo = config('sefaz.transporte', 'auto');
                $usaCurl = $modo === 'curl' || ($modo === 'auto' && function_exists('curl_init'));
                [$http, $corpo, $erro] = $usaCurl ? $this->soapPorCurl($url, $action, $envelope, $fc, $fk) : $this->soapPorStream($url, $action, $envelope, $fc, $fk);
            } finally {
                @unlink($fc); @unlink($fk);
            }
            if ($corpo === false) {
                throw new \RuntimeException('Falha de comunicação com a SEFAZ: ' . ($erro ?: 'sem resposta') . '.');
            }
        }
        if ($http < 200 || $http >= 300) {
            throw new \RuntimeException('A SEFAZ respondeu HTTP ' . $http . '. Confira o certificado/ambiente e tente novamente.');
        }
        return (string) $corpo;
    }

    /** @return array{0:int,1:string|false,2:string} */
    private function soapPorCurl(string $url, string $action, string $envelope, string $fc, string $fk): array
    {
        $ch = curl_init($url);
        $ca = config('sefaz.ca_bundle');
        curl_setopt_array($ch, [
            CURLOPT_POST => true, CURLOPT_POSTFIELDS => $envelope, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => config('sefaz.timeout'),
            CURLOPT_CONNECTTIMEOUT => 15, CURLOPT_SSLCERT => $fc, CURLOPT_SSLKEY => $fk, CURLOPT_SSLCERTTYPE => 'PEM',
            CURLOPT_SSL_VERIFYPEER => (bool) $ca, CURLOPT_SSL_VERIFYHOST => $ca ? 2 : 0,
            CURLOPT_HTTPHEADER => ['Content-Type: application/soap+xml; charset=utf-8; action="' . $action . '"', 'Accept: application/soap+xml'],
        ]);
        if ($ca) { curl_setopt($ch, CURLOPT_CAINFO, $ca); }
        $corpo = curl_exec($ch);
        $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $erro = curl_error($ch);
        curl_close($ch);
        return [$http, $corpo, $erro];
    }

    /** Mesmo POST SOAP sem a extensao curl: streams do PHP (precisa de allow_url_fopen e da extensao openssl). */
    private function soapPorStream(string $url, string $action, string $envelope, string $fc, string $fk): array
    {
        $ca = config('sefaz.ca_bundle');
        $ssl = ['local_cert' => $fc, 'local_pk' => $fk, 'verify_peer' => (bool) $ca, 'verify_peer_name' => (bool) $ca, 'allow_self_signed' => !$ca];
        if ($ca) { $ssl['cafile'] = $ca; }
        $ctx = stream_context_create([
            'http' => ['method' => 'POST', 'content' => $envelope, 'timeout' => (float) config('sefaz.timeout'), 'ignore_errors' => true,
                'header' => "Content-Type: application/soap+xml; charset=utf-8; action=\"" . $action . "\"\r\nAccept: application/soap+xml\r\nContent-Length: " . strlen($envelope) . "\r\n"],
            'ssl' => $ssl,
        ]);
        $erro = '';
        set_error_handler(function ($no, $msg) use (&$erro) { $erro = $msg; return true; });
        try {
            $corpo = file_get_contents($url, false, $ctx);
        } finally {
            restore_error_handler();
        }
        $http = 0;
        foreach ($http_response_header ?? [] as $h) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#', $h, $m)) { $http = (int) $m[1]; }
        }
        return [$http, $corpo, $erro];
    }

    private function dom(string $xml): \DOMDocument
    {
        $d = new \DOMDocument();
        $ant = libxml_use_internal_errors(true);
        $ok = $d->loadXML($xml, LIBXML_NONET);
        libxml_clear_errors(); libxml_use_internal_errors($ant);
        if (!$ok) {
            throw new \RuntimeException('Resposta da SEFAZ ilegível.');
        }
        return $d;
    }

    private function no(\DOMDocument $d, string $nome): ?string
    {
        $l = $d->getElementsByTagNameNS('*', $nome);
        return $l->length ? trim($l->item(0)->textContent) : null;
    }

    /* ------------------------------------------------------------------ distribuicao */

    /** Consulta incremental por NSU. Retorna resumo para a tela. */
    public function consultar(): array
    {
        $dados = $this->cert();
        $c = $dados['linha'];
        $db = $this->db();
        if ($c->proxima_consulta && strtotime($c->proxima_consulta) > time()) {
            throw new \RuntimeException('A SEFAZ só permite nova consulta a partir das ' . date('d/m/Y H:i', strtotime($c->proxima_consulta)) . ' (regra de consumo da distribuição).');
        }
        $emp = $this->empresa();
        $uf = strtoupper(trim((string) $emp->uf));
        $cUf = self::UF[$uf] ?? null;
        if (!$cUf) {
            throw new \RuntimeException('UF da empresa não cadastrada; ela é necessária para consultar a SEFAZ.');
        }
        $url = config('sefaz.distribuicao.' . ((int) $c->ambiente === 2 ? 2 : 1));
        $ult = str_pad($this->digitos($c->ultimo_nsu), 15, '0', STR_PAD_LEFT);
        $max = $c->max_nsu;
        $novos = ['resNFe' => 0, 'procNFe' => 0, 'eventos' => 0];
        $status = '';
        $aviso = null;
        $proxima = null;

        for ($pag = 0; $pag < config('sefaz.max_paginas'); $pag++) {
            $msg = '<distDFeInt xmlns="' . self::NFE . '" versao="1.01"><tpAmb>' . ((int) $c->ambiente === 2 ? 2 : 1) . '</tpAmb><cUFAutor>' . $cUf . '</cUFAutor><CNPJ>' . $c->cnpj . '</CNPJ><distNSU><ultNSU>' . $ult . '</ultNSU></distNSU></distDFeInt>';
            $corpo = $this->soap($url, 'http://www.portalfiscal.inf.br/nfe/wsdl/NFeDistribuicaoDFe/nfeDistDFeInteresse',
                '<nfeDistDFeInteresse xmlns="http://www.portalfiscal.inf.br/nfe/wsdl/NFeDistribuicaoDFe"><nfeDadosMsg>' . $msg . '</nfeDadosMsg></nfeDistDFeInteresse>',
                'http://www.portalfiscal.inf.br/nfe/wsdl/NFeDistribuicaoDFe', $dados['info']);
            $d = $this->dom($corpo);
            $cStat = $this->no($d, 'cStat');
            $motivo = $this->no($d, 'xMotivo');
            $status = $cStat . ' - ' . $motivo;
            $ret = $this->no($d, 'ultNSU');
            $mx = $this->no($d, 'maxNSU');
            if ($mx) { $max = $mx; }

            if ($cStat === '137') {            // nenhum documento localizado
                $proxima = date('Y-m-d H:i:s', time() + config('sefaz.espera_minutos') * 60);
                $aviso = 'Nenhum documento novo. Nova consulta liberada em ' . config('sefaz.espera_minutos') . ' minutos.';
                break;
            }
            if ($cStat === '656') {            // consumo indevido
                $proxima = date('Y-m-d H:i:s', time() + config('sefaz.espera_minutos') * 60);
                $this->atualizarControle($c->id, $ult, $max, $status, $proxima);
                throw new \RuntimeException('A SEFAZ bloqueou temporariamente a consulta (consumo indevido). Aguarde ' . config('sefaz.espera_minutos') . ' minutos.');
            }
            if ($cStat !== '138') {
                $this->atualizarControle($c->id, $ult, $max, $status, null);
                throw new \RuntimeException('SEFAZ: ' . $status);
            }
            foreach ($d->getElementsByTagNameNS('*', 'docZip') as $z) {
                $nsu = str_pad($this->digitos($z->getAttribute('NSU')), 15, '0', STR_PAD_LEFT);
                $xml = @gzdecode(base64_decode(trim($z->textContent)));
                if ($xml === false || $xml === '') { continue; }
                $this->gravarDocumento($nsu, $xml, $novos);
            }
            if ($ret && $ret >= $ult) { $ult = $ret; }
            if (!$ret || $ult >= $max) { break; }
        }
        $this->atualizarControle($c->id, $ult, $max, $status, $proxima);
        return ['status' => $status, 'novos' => $novos, 'aviso' => $aviso, 'ultimo_nsu' => $ult, 'max_nsu' => $max];
    }

    /**
     * Consulta direta de UMA nota pela chave (distribuicao consChNFe). Nao depende do ultNSU:
     * serve para notas cuja ciencia ja foi registrada mas o XML nao chegou pela consulta incremental.
     */
    public function consultarChave(string $chave): array
    {
        if (!preg_match('/^\d{44}$/', $chave)) {
            throw new \RuntimeException('Chave de acesso inválida.');
        }
        $dados = $this->cert();
        $c = $dados['linha'];
        $emp = $this->empresa();
        $cUf = self::UF[strtoupper(trim((string) $emp->uf))] ?? null;
        if (!$cUf) {
            throw new \RuntimeException('UF da empresa não cadastrada; ela é necessária para consultar a SEFAZ.');
        }
        $amb = (int) $c->ambiente === 2 ? 2 : 1;
        $msg = '<distDFeInt xmlns="' . self::NFE . '" versao="1.01"><tpAmb>' . $amb . '</tpAmb><cUFAutor>' . $cUf . '</cUFAutor><CNPJ>' . $c->cnpj . '</CNPJ><consChNFe><chNFe>' . $chave . '</chNFe></consChNFe></distDFeInt>';
        $corpo = $this->soap(config('sefaz.distribuicao.' . $amb), 'http://www.portalfiscal.inf.br/nfe/wsdl/NFeDistribuicaoDFe/nfeDistDFeInteresse',
            '<nfeDistDFeInteresse xmlns="http://www.portalfiscal.inf.br/nfe/wsdl/NFeDistribuicaoDFe"><nfeDadosMsg>' . $msg . '</nfeDadosMsg></nfeDistDFeInteresse>',
            'http://www.portalfiscal.inf.br/nfe/wsdl/NFeDistribuicaoDFe', $dados['info']);
        $d = $this->dom($corpo);
        $cStat = (string) $this->no($d, 'cStat');
        $motivo = (string) $this->no($d, 'xMotivo');
        $novos = ['resNFe' => 0, 'procNFe' => 0, 'eventos' => 0];
        $recebidos = [];
        if ($cStat === '138') {
            foreach ($d->getElementsByTagNameNS('*', 'docZip') as $z) {
                $nsu = str_pad($this->digitos($z->getAttribute('NSU')), 15, '0', STR_PAD_LEFT);
                $xml = @gzdecode(base64_decode(trim($z->textContent)));
                if ($xml === false || $xml === '') { continue; }
                $recebidos[] = $z->getAttribute('schema') ?: 'desconhecido';
                $this->gravarDocumento($nsu, $xml, $novos);
            }
        }
        $tem = $this->xmlCompleto($chave) !== null;
        return ['cStat' => $cStat, 'motivo' => $motivo, 'novos' => $novos, 'xml' => $tem, 'recebidos' => $recebidos];
    }

    private function atualizarControle(int $id, string $ult, string $max, string $status, ?string $proxima): void
    {
        $this->db()->table('empresa_certificado')->where('id', $id)->update([
            'ultimo_nsu' => $ult, 'max_nsu' => $max, 'ultima_consulta' => now(), 'proxima_consulta' => $proxima, 'ultimo_status' => mb_substr($status, 0, 250), 'updated_at' => now(),
        ]);
    }

    /** Guarda um documento entregue (resumo, NF-e completa ou evento). */
    private function gravarDocumento(string $nsu, string $xml, array &$novos): void
    {
        if (stripos($xml, '<!DOCTYPE') !== false || stripos($xml, '<!ENTITY') !== false) {
            return;
        }
        $d = new \DOMDocument();
        $ant = libxml_use_internal_errors(true);
        $ok = $d->loadXML($xml, LIBXML_NONET);
        libxml_clear_errors(); libxml_use_internal_errors($ant);
        if (!$ok) { return; }
        $raiz = $d->documentElement->localName;
        $db = $this->db();
        if ($db->table('nfe_distribuicao')->where('nsu', $nsu)->exists()) { return; }

        if ($raiz === 'resNFe') {
            $chave = $this->no($d, 'chNFe');
            $db->table('nfe_distribuicao')->insert([
                'nsu' => $nsu, 'chave' => $chave, 'tipo' => 'resNFe', 'cnpj_emitente' => $this->digitos($this->no($d, 'CNPJ') ?? $this->no($d, 'CPF')), 'nome_emitente' => mb_substr((string) $this->no($d, 'xNome'), 0, 150),
                'dh_emissao' => $this->dataHora($this->no($d, 'dhEmi')), 'valor_nf' => (float) $this->no($d, 'vNF'), 'situacao' => $this->no($d, 'cSitNFe'), 'created_at' => now(),
            ]);
            $novos['resNFe']++;
        } elseif ($raiz === 'nfeProc') {
            $chave = $this->no($d, 'chNFe');
            if (!$chave) {
                $inf = $d->getElementsByTagNameNS('*', 'infNFe');
                $chave = $inf->length ? preg_replace('/^NFe/', '', $inf->item(0)->getAttribute('Id')) : null;
            }
            $dir = storage_path('app/nfe_dist');
            if (!is_dir($dir)) { mkdir($dir, 0775, true); }
            $rel = 'nfe_dist/' . $chave . '.xml';
            file_put_contents(storage_path('app/' . $rel), $xml);
            $emit = $d->getElementsByTagNameNS('*', 'emit')->item(0);
            $db->table('nfe_distribuicao')->insert([
                'nsu' => $nsu, 'chave' => $chave, 'tipo' => 'procNFe', 'cnpj_emitente' => $emit ? $this->digitos($this->filho($emit, 'CNPJ') ?? $this->filho($emit, 'CPF')) : null,
                'nome_emitente' => $emit ? mb_substr((string) $this->filho($emit, 'xNome'), 0, 150) : null, 'dh_emissao' => $this->dataHora($this->no($d, 'dhEmi')),
                'valor_nf' => (float) $this->no($d, 'vNF'), 'situacao' => '1', 'xml_arquivo' => $rel, 'created_at' => now(),
            ]);
            // o resumo da mesma chave passa a apontar para o XML completo
            $db->table('nfe_distribuicao')->where('chave', $chave)->where('tipo', 'resNFe')->update(['xml_arquivo' => $rel]);
            $novos['procNFe']++;
        } else {                                   // resEvento / procEventoNFe
            $db->table('nfe_distribuicao')->insert([
                'nsu' => $nsu, 'chave' => $this->no($d, 'chNFe'), 'tipo' => $raiz === 'resEvento' ? 'resEvento' : 'procEvento', 'tp_evento' => $this->no($d, 'tpEvento'),
                'dh_emissao' => $this->dataHora($this->no($d, 'dhEvento')), 'created_at' => now(),
            ]);
            $novos['eventos']++;
        }
    }

    private function filho(\DOMElement $e, string $nome): ?string
    {
        $l = $e->getElementsByTagNameNS('*', $nome);
        return $l->length ? trim($l->item(0)->textContent) : null;
    }

    private function dataHora(?string $v): ?string
    {
        return $v && strtotime($v) ? date('Y-m-d H:i:s', strtotime($v)) : null;
    }

    /**
     * Documentos de NF-e para a tela (um por chave; prefere a versao completa), com filtro e paginacao.
     * Filtros: busca (emitente, CNPJ, numero da NF ou trecho da chave), de/ate (emissao), situacao (todas|sem_ciencia|aguardando|com_xml), pagina, por_pagina, so_chaves.
     */
    public function documentos(array $f = []): array
    {
        $db = $this->db();
        $q = $db->table('nfe_distribuicao')->whereIn('tipo', ['resNFe', 'procNFe']);
        $busca = trim((string) ($f['busca'] ?? ''));
        if ($busca !== '') {
            $dig = $this->digitos($busca);
            $q->where(function ($w) use ($busca, $dig) {
                $w->where('nome_emitente', 'like', '%' . str_replace(['%', '_'], ['\\%', '\\_'], $busca) . '%');
                if ($dig !== '') {
                    $w->orWhere('cnpj_emitente', 'like', '%' . $dig . '%')->orWhere('chave', 'like', '%' . $dig . '%');
                    if (strlen($dig) <= 9) { $w->orWhereRaw('CAST(SUBSTRING(chave, 26, 9) AS UNSIGNED) = ?', [(int) $dig]); }
                }
            });
        }
        if (!empty($f['de']) && strtotime($f['de'])) { $q->where('dh_emissao', '>=', date('Y-m-d', strtotime($f['de'])) . ' 00:00:00'); }
        if (!empty($f['ate']) && strtotime($f['ate'])) { $q->where('dh_emissao', '<=', date('Y-m-d', strtotime($f['ate'])) . ' 23:59:59'); }
        $linhas = $q->orderByDesc('dh_emissao')->orderByDesc('id')->limit(20000)->get();

        // cada chave vira uma linha; a manifestacao e o XML de qualquer registro da chave valem para ela
        $porChave = [];
        foreach ($linhas as $l) {
            $k = $l->chave ?: ('nsu' . $l->nsu);
            if (!isset($porChave[$k])) { $porChave[$k] = $l; continue; }
            $a = $porChave[$k];
            if ($l->tipo === 'procNFe' && $a->tipo !== 'procNFe') {
                $l->manifestacao = $l->manifestacao ?: $a->manifestacao;
                $porChave[$k] = $a = $l;
            }
            if (!$a->manifestacao && $l->manifestacao) { $a->manifestacao = $l->manifestacao; }
            if (!$a->xml_arquivo && $l->xml_arquivo) { $a->xml_arquivo = $l->xml_arquivo; }
        }
        $situacao = (string) ($f['situacao'] ?? '');
        $todos = [];
        foreach ($porChave as $l) {
            $completo = $l->tipo === 'procNFe' || !empty($l->xml_arquivo);
            $estado = $completo ? 'com_xml' : ($l->manifestacao ? 'aguardando' : 'sem_ciencia');
            if ($situacao !== '' && $situacao !== 'todas' && $situacao !== $estado) { continue; }
            $todos[] = [
                'chave' => $l->chave, 'completo' => $completo, 'estado' => $estado, 'emitente' => $l->nome_emitente, 'cnpj' => $l->cnpj_emitente,
                'emissao' => $l->dh_emissao, 'valor' => (float) $l->valor_nf, 'manifestacao' => $l->manifestacao,
                'numero' => ($l->chave && strlen($l->chave) === 44) ? (string) (int) substr($l->chave, 25, 9) : null,
            ];
        }
        if (!empty($f['so_chaves'])) {   // resumos ainda sem ciencia (para "selecionar todos do filtro")
            $ch = [];
            foreach ($todos as $d) { if ($d['estado'] === 'sem_ciencia' && $d['chave']) { $ch[] = $d['chave']; } }
            return ['chaves' => array_slice($ch, 0, 1000), 'total_chaves' => count($ch)];
        }
        $contagem = ['sem_ciencia' => 0, 'aguardando' => 0, 'com_xml' => 0];
        foreach ($todos as $d) { $contagem[$d['estado']]++; }
        $porPagina = max(10, min(200, (int) ($f['por_pagina'] ?? 50)));
        $total = count($todos);
        $paginas = max(1, (int) ceil($total / $porPagina));
        $pagina = max(1, min($paginas, (int) ($f['pagina'] ?? 1)));
        return ['documentos' => array_slice($todos, ($pagina - 1) * $porPagina, $porPagina), 'total' => $total, 'pagina' => $pagina, 'paginas' => $paginas, 'por_pagina' => $porPagina, 'contagem' => $contagem];
    }

    /**
     * Pacote ZIP com os XMLs completos das chaves informadas (as sem XML sao ignoradas).
     * @return array{0:?string,1:int} caminho do zip temporario (apagar apos o envio) e quantidade de arquivos
     */
    public function zipXmls(array $chaves): array
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new \RuntimeException('A extensão zip do PHP não está habilitada no servidor.');
        }
        $dir = storage_path('app/tmp_sefaz');
        if (!is_dir($dir)) { mkdir($dir, 0700, true); }
        $tmp = $dir . '/' . bin2hex(random_bytes(8)) . '.zip';
        $zip = new \ZipArchive();
        if ($zip->open($tmp, \ZipArchive::CREATE) !== true) {
            throw new \RuntimeException('Não foi possível montar o arquivo ZIP.');
        }
        $n = 0;
        foreach (array_slice(array_values(array_unique($chaves)), 0, 2000) as $chave) {
            if (!preg_match('/^\d{44}$/', (string) $chave)) { continue; }
            $xml = $this->xmlCompleto($chave);
            if ($xml !== null) { $zip->addFromString($chave . '-nfe.xml', $xml); $n++; }
        }
        $zip->close();
        if ($n === 0) { @unlink($tmp); return [null, 0]; }
        return [$tmp, $n];
    }

    /**
     * Traz do ERP Singular (Firebird: NFE_DESTINADA_CHAVE / DFE_PARAMETROS) o que ele ja recebeu da SEFAZ:
     * resumos, XMLs completos, ciencia e o ultimo NSU. Assim a consulta daqui continua de onde o ERP parou
     * (sem recomecar do NSU zero, que causa o bloqueio 656). Idempotente: NSU ja importado e ignorado.
     * @param callable|null $progresso function(int $lidos)
     * @return array{lidos:int,resumos:int,completos:int,ignorados:int,ciencias:int,nsu:string}
     */
    public function importarDoErp(?callable $progresso = null): array
    {
        $erp = DB::connection(env('ERP_CONNECTION', env('APP_NAME')));
        $db = $this->db();
        $r = ['lidos' => 0, 'resumos' => 0, 'completos' => 0, 'ignorados' => 0, 'ciencias' => 0, 'nsu' => '000000000000000'];
        $ciencia = [];
        $ultimo = '';
        do {
            $sql = 'select first 200 NFE_DES_NSU, NFE_DES_CHAVE, NFE_DES_SITUACAO_MANIFESTACAO, NFE_DES_XML from NFE_DESTINADA_CHAVE'
                . ' where NFE_DES_XML is not null and NFE_DES_NSU > ? order by NFE_DES_NSU';
            $lote = $erp->select($sql, [$ultimo]);
            foreach ($lote as $l) {
                $nsu = str_pad($this->digitos($l->NFE_DES_NSU), 15, '0', STR_PAD_LEFT);
                $ultimo = $l->NFE_DES_NSU;
                $xml = $l->NFE_DES_XML;
                if (is_resource($xml)) { $xml = stream_get_contents($xml); }
                $r['lidos']++;
                if ($nsu > $r['nsu']) { $r['nsu'] = $nsu; }
                if ((string) $xml === '') { continue; }
                $novos = ['resNFe' => 0, 'procNFe' => 0, 'eventos' => 0];
                $this->gravarDocumento($nsu, (string) $xml, $novos);
                $r['resumos'] += $novos['resNFe']; $r['completos'] += $novos['procNFe'];
                if (!array_sum($novos)) { $r['ignorados']++; }
                if ((int) $l->NFE_DES_SITUACAO_MANIFESTACAO === 1 && preg_match('/^\d{44}$/', (string) $l->NFE_DES_CHAVE)) { $ciencia[$l->NFE_DES_CHAVE] = true; }
            }
            if ($progresso) { call_user_func($progresso, $r['lidos']); }
        } while (count($lote) === 200);

        foreach (array_chunk(array_keys($ciencia), 500) as $ch) {
            $r['ciencias'] += $db->table('nfe_distribuicao')->whereIn('chave', $ch)->whereNull('manifestacao')->update(['manifestacao' => '210210', 'manifestada_em' => now()]);
        }
        // ponteiro de NSU: nunca recua; a consulta seguinte parte do ultimo NSU que o ERP ja leu
        $par = $erp->selectOne('select DFE_PROXIMO_NSU, DFE_NSU_MAXIMO from DFE_PARAMETROS');
        $nsuErp = $par ? str_pad($this->digitos($par->DFE_PROXIMO_NSU), 15, '0', STR_PAD_LEFT) : '000000000000000';
        if ($nsuErp > $r['nsu']) { $r['nsu'] = $nsuErp; }
        $c = $this->ativo();
        if ($c && $r['nsu'] > $c->ultimo_nsu) {
            $db->table('empresa_certificado')->where('id', $c->id)->update(['ultimo_nsu' => $r['nsu'], 'updated_at' => now()]);
        }
        return $r;
    }

    /** Conteudo do XML completo arquivado para uma chave. */
    public function xmlCompleto(string $chave): ?string
    {
        $l = $this->db()->table('nfe_distribuicao')->where('chave', $chave)->whereNotNull('xml_arquivo')->orderByDesc('id')->first();
        $abs = $l ? storage_path('app/' . $l->xml_arquivo) : null;
        return ($abs && is_file($abs)) ? file_get_contents($abs) : null;
    }

    /* ------------------------------------------------------------------ manifestacao do destinatario */

    /** Ciencia da Operacao (210210): a SEFAZ passa a entregar o XML completo nas proximas consultas. */
    public function manifestarCiencia(string $chave): array
    {
        if (!preg_match('/^\d{44}$/', $chave)) {
            throw new \RuntimeException('Chave de acesso inválida.');
        }
        $dados = $this->cert();
        $c = $dados['linha'];
        $tp = '210210';
        $id = 'ID' . $tp . $chave . '01';
        $amb = (int) $c->ambiente === 2 ? 2 : 1;
        $dh = date('Y-m-d\TH:i:sP');

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->preserveWhiteSpace = false;
        $env = $doc->createElementNS(self::NFE, 'envEvento');
        $env->setAttribute('versao', '1.00');
        $doc->appendChild($env);
        $env->appendChild($doc->createElementNS(self::NFE, 'idLote', (string) time()));
        $evento = $doc->createElementNS(self::NFE, 'evento');
        $evento->setAttribute('versao', '1.00');
        $env->appendChild($evento);
        $inf = $doc->createElementNS(self::NFE, 'infEvento');
        $inf->setAttribute('Id', $id);
        $evento->appendChild($inf);
        foreach (['cOrgao' => '91', 'tpAmb' => (string) $amb, 'CNPJ' => $c->cnpj, 'chNFe' => $chave, 'dhEvento' => $dh, 'tpEvento' => $tp, 'nSeqEvento' => '1', 'verEvento' => '1.00'] as $n => $v) {
            $inf->appendChild($doc->createElementNS(self::NFE, $n, $v));
        }
        $det = $doc->createElementNS(self::NFE, 'detEvento');
        $det->setAttribute('versao', '1.00');
        $det->appendChild($doc->createElementNS(self::NFE, 'descEvento', 'Ciencia da Operacao'));
        $inf->appendChild($det);
        $this->assinar($doc, $evento, $inf, $id, $dados['info']);

        $url = config('sefaz.evento.' . $amb);
        $corpo = $this->soap($url, 'http://www.portalfiscal.inf.br/nfe/wsdl/NFeRecepcaoEvento4/nfeRecepcaoEvento',
            '<nfeDadosMsg xmlns="http://www.portalfiscal.inf.br/nfe/wsdl/NFeRecepcaoEvento4">' . $doc->saveXML($env) . '</nfeDadosMsg>',
            'http://www.portalfiscal.inf.br/nfe/wsdl/NFeRecepcaoEvento4', $dados['info']);
        $r = $this->dom($corpo);
        $lote = $this->no($r, 'cStat');
        if ($lote !== '128') {
            throw new \RuntimeException('SEFAZ: ' . $lote . ' - ' . $this->no($r, 'xMotivo'));
        }
        // cStat do evento: o ultimo cStat do retorno (dentro de infEvento)
        $stats = $r->getElementsByTagNameNS('*', 'cStat');
        $cs = $stats->length > 1 ? trim($stats->item($stats->length - 1)->textContent) : $lote;
        $mot = '';
        $ms = $r->getElementsByTagNameNS('*', 'xMotivo');
        if ($ms->length > 1) { $mot = trim($ms->item($ms->length - 1)->textContent); }
        \Illuminate\Support\Facades\Log::info('Ciencia SEFAZ ' . $chave . ': ' . $cs . ' - ' . $mot);
        if ($cs === '596') {                                    // fora do prazo de 10 dias: a marca local estava errada
            $this->db()->table('nfe_distribuicao')->where('chave', $chave)->update(['manifestacao' => null, 'manifestada_em' => null]);
        }
        if (!in_array($cs, ['135', '136', '573'], true)) {      // 573 = duplicidade (ciencia ja registrada)
            throw new \RuntimeException('Manifestação não registrada: ' . $cs . ' - ' . $mot);
        }
        $this->db()->table('nfe_distribuicao')->where('chave', $chave)->update(['manifestacao' => $tp, 'manifestada_em' => now()]);
        // nova consulta liberada: a SEFAZ passa a entregar o XML completo
        $this->db()->table('empresa_certificado')->where('id', $c->id)->update(['proxima_consulta' => null, 'updated_at' => now()]);
        return ['cStat' => $cs, 'motivo' => $mot];
    }

    /**
     * Ciencia da operacao em varias notas (uma a uma, cada evento assinado). Para no primeiro erro de comunicacao/certificado;
     * rejeicoes de uma nota especifica nao interrompem as demais.
     * @return array{registradas:string[],ignoradas:string[],falhas:array<string,string>,interrompido:?string}
     */
    public function manifestarLote(array $chaves): array
    {
        $r = ['registradas' => [], 'ignoradas' => [], 'falhas' => [], 'interrompido' => null];
        $db = $this->db();
        foreach (array_slice(array_values(array_unique($chaves)), 0, 50) as $chave) {
            $chave = (string) $chave;
            $ja = $db->table('nfe_distribuicao')->where('chave', $chave)->where(function ($w) { $w->whereNotNull('manifestacao')->orWhereNotNull('xml_arquivo'); })->exists();
            if ($ja) { $r['ignoradas'][] = $chave; continue; }
            try {
                $this->manifestarCiencia($chave);
                $r['registradas'][] = $chave;
            } catch (\RuntimeException $e) {
                $msg = $e->getMessage();
                if (preg_match('/^(Falha de comunica|A SEFAZ respondeu HTTP|Cadastre o certificado|O certificado A1 est|Arquivo do certificado|N.o foi poss.vel abrir a senha|Falha ao assinar)/u', $msg)) {
                    $r['interrompido'] = $msg;
                    break;
                }
                $r['falhas'][$chave] = $msg;
            }
        }
        return $r;
    }

    /** XMLDSig (enveloped, C14N, RSA-SHA1, digest SHA1) exigido pela NF-e. */
    private function assinar(\DOMDocument $doc, \DOMElement $pai, \DOMElement $alvo, string $id, array $info): void
    {
        $dsig = 'http://www.w3.org/2000/09/xmldsig#';
        $digest = base64_encode(sha1($alvo->C14N(false, false), true));

        $sig = $doc->createElementNS($dsig, 'Signature');
        $pai->appendChild($sig);
        $si = $doc->createElementNS($dsig, 'SignedInfo');
        $sig->appendChild($si);
        $cm = $doc->createElementNS($dsig, 'CanonicalizationMethod'); $cm->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315'); $si->appendChild($cm);
        $sm = $doc->createElementNS($dsig, 'SignatureMethod'); $sm->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1'); $si->appendChild($sm);
        $ref = $doc->createElementNS($dsig, 'Reference'); $ref->setAttribute('URI', '#' . $id); $si->appendChild($ref);
        $tr = $doc->createElementNS($dsig, 'Transforms'); $ref->appendChild($tr);
        $t1 = $doc->createElementNS($dsig, 'Transform'); $t1->setAttribute('Algorithm', $dsig . 'enveloped-signature'); $tr->appendChild($t1);
        $t2 = $doc->createElementNS($dsig, 'Transform'); $t2->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315'); $tr->appendChild($t2);
        $dm = $doc->createElementNS($dsig, 'DigestMethod'); $dm->setAttribute('Algorithm', $dsig . 'sha1'); $ref->appendChild($dm);
        $ref->appendChild($doc->createElementNS($dsig, 'DigestValue', $digest));

        if (!openssl_sign($si->C14N(false, false), $assinatura, $info['pkey'], OPENSSL_ALGO_SHA1)) {
            throw new \RuntimeException('Falha ao assinar o evento com o certificado.');
        }
        $sig->appendChild($doc->createElementNS($dsig, 'SignatureValue', base64_encode($assinatura)));
        $ki = $doc->createElementNS($dsig, 'KeyInfo'); $sig->appendChild($ki);
        $x509 = $doc->createElementNS($dsig, 'X509Data'); $ki->appendChild($x509);
        $der = preg_replace('/-----(BEGIN|END) CERTIFICATE-----|\s+/', '', $info['cert']);
        $x509->appendChild($doc->createElementNS($dsig, 'X509Certificate', $der));
    }
}
