<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ProdutoEmbalagemService
{
    private const LOTE_INSERT = 500;

    public function importarDoErp(?callable $callback = null): array
    {
        $produtos = $this->buscarProdutosEmbalagemErp();

        $produtosLidos = count($produtos);
        $produtosImportados = 0;

        $totalA = 0;
        $totalB = 0;
        $totalC = 0;

        $agora = now();
        $lote = [];

        if ($callback) {
            $callback([
                'tipo' => 'embalagem_inicio',
                'total' => $produtosLidos,
            ]);
        }

        DB::transaction(function () use (
            $produtos,
            $agora,
            &$lote,
            &$produtosImportados,
            &$totalA,
            &$totalB,
            &$totalC,
            $callback
        ) {
            DB::table('produto_embalagem_mysql')->delete();

            foreach ($produtos as $item) {
                $codProd = trim((string) ($item->COD_PROD ?? ''));
                $desProd = trim((string) ($item->DES_PROD ?? ''));

                if ($codProd == '') {
                    continue;
                }

                $embalagem = $this->identificarEmbalagem($desProd);
                $custoPercentual = $this->percentualEmbalagem($embalagem);

                $lote[] = [
                    'cod_prod' => $codProd,
                    'des_prod' => $desProd,
                    'embalagem' => $embalagem,
                    'custo_percentual' => $custoPercentual,
                    'origem' => 'ERP_PRODUTOS',
                    'created_at' => $agora,
                    'updated_at' => $agora,
                ];

                $produtosImportados++;

                if ($embalagem == 'B') {
                    $totalB++;
                } elseif ($embalagem == 'C') {
                    $totalC++;
                } else {
                    $totalA++;
                }

                if ($callback) {
                    $callback([
                        'tipo' => 'embalagem_item',
                        'codigo' => $codProd,
                        'produto' => $desProd,
                        'embalagem' => $embalagem,
                        'custo_percentual' => $custoPercentual,
                    ]);
                }

                if (count($lote) >= self::LOTE_INSERT) {
                    DB::table('produto_embalagem_mysql')->insert($lote);

                    if ($callback) {
                        $callback([
                            'tipo' => 'embalagem_lote',
                            'linhas' => count($lote),
                        ]);
                    }

                    $lote = [];
                }
            }

            if (!empty($lote)) {
                DB::table('produto_embalagem_mysql')->insert($lote);

                if ($callback) {
                    $callback([
                        'tipo' => 'embalagem_lote',
                        'linhas' => count($lote),
                    ]);
                }

                $lote = [];
            }
        });

        if ($callback) {
            $callback([
                'tipo' => 'embalagem_fim',
            ]);
        }

        return [
            'produtos_lidos' => $produtosLidos,
            'produtos_importados' => $produtosImportados,
            'total_a' => $totalA,
            'total_b' => $totalB,
            'total_c' => $totalC,
        ];
    }

    private function buscarProdutosEmbalagemErp(): array
    {
        $sql = "
            SELECT
                PRD_CODIGO AS COD_PROD,
                PRD_DESCRICAO AS DES_PROD
            FROM PRODUTOS
            WHERE PRD_TIPO_PRODUTO = '04'
            ORDER BY PRD_CODIGO
        ";

        return DB::connection(env('ERP_CONNECTION', 'Decorbras'))->select($sql);
    }

    private function identificarEmbalagem(string $descricao): string
    {
        $texto = strtoupper($descricao);
        $texto = preg_replace('/\s+/', ' ', $texto);
        $texto = ' ' . trim($texto) . ' ';

        if (strpos($texto, ' B ') !== false) {
            return 'B';
        }

        if (strpos($texto, ' C ') !== false) {
            return 'C';
        }

        return 'A';
    }

    private function percentualEmbalagem(string $embalagem): float
    {
        if ($embalagem == 'B') {
            return 0.70;
        }

        if ($embalagem == 'C') {
            return 0.50;
        }

        return 1.00;
    }
}
