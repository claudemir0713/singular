<?php

/*
| Distribuicao de DF-e (NFeDistribuicaoDFe) e Recepcao de Evento (manifestacao) da SEFAZ - Ambiente Nacional.
| As URLs podem ser sobrescritas por .env (util para testes locais com um servidor simulado):
|   SEFAZ_URL_DISTRIBUICAO=...   SEFAZ_URL_EVENTO=...
| SEFAZ_CA_BUNDLE: caminho de um .pem com a cadeia ICP-Brasil/SEFAZ. Sem ele a validacao da cadeia TLS fica desligada
| (o servidor da SEFAZ usa cadeia ICP-Brasil, ausente dos pacotes de CA do PHP no Windows); a autenticacao do cliente
| continua sendo feita pelo certificado A1.
*/
return [
    'distribuicao' => [
        1 => env('SEFAZ_URL_DISTRIBUICAO', 'https://www1.nfe.fazenda.gov.br/NFeDistribuicaoDFe/NFeDistribuicaoDFe.asmx'),
        2 => env('SEFAZ_URL_DISTRIBUICAO_HOM', env('SEFAZ_URL_DISTRIBUICAO', 'https://hom1.nfe.fazenda.gov.br/NFeDistribuicaoDFe/NFeDistribuicaoDFe.asmx')),
    ],
    'evento' => [
        1 => env('SEFAZ_URL_EVENTO', 'https://www.nfe.fazenda.gov.br/NFeRecepcaoEvento4/NFeRecepcaoEvento4.asmx'),
        2 => env('SEFAZ_URL_EVENTO_HOM', env('SEFAZ_URL_EVENTO', 'https://hom.nfe.fazenda.gov.br/NFeRecepcaoEvento4/NFeRecepcaoEvento4.asmx')),
    ],
    'ca_bundle' => env('SEFAZ_CA_BUNDLE'),
    // executavel do OpenSSL usado so como reserva para ler PFX antigos (RC2/3DES, comuns na ICP-Brasil) que o OpenSSL 3 do PHP recusa
    'openssl_bin' => env('SEFAZ_OPENSSL_BIN'),
    // auto = cURL quando a extensao existir, senao streams do PHP; curl/stream forcam um dos dois
    'transporte' => env('SEFAZ_TRANSPORTE', 'auto'),
    'timeout' => (int) env('SEFAZ_TIMEOUT', 40),
    // intervalo minimo entre consultas depois de "nenhum documento" (cStat 137) ou consumo indevido (656): regra da SEFAZ
    'espera_minutos' => (int) env('SEFAZ_ESPERA_MINUTOS', 60),
    'max_paginas' => 6,
];
