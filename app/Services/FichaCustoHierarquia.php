<?php

namespace App\Services;

/**
 * Regras de hierarquia da ficha de custo "explodida" (produto_ficha_custo_mysql).
 *
 * A ficha traz todos os níveis da estrutura. Quando um componente já possui
 * custo próprio (matéria-prima com preço), os itens abaixo dele (ex.: madeira
 * VERDE e ESTUFA abaixo da madeira SECA) não podem ser somados novamente,
 * senão o mesmo custo é contado duas vezes.
 */
class FichaCustoHierarquia
{
    /**
     * Retorna, para cada índice da lista, se o item deve entrar no custo.
     *
     * @param  array $itens  objetos/arrays com cod_pai, cod_comp, tipo_custo, custo_mp_unit
     * @return bool[]        mesma chave da lista recebida
     */
    public static function considerados(array $itens): array
    {
        $bloqueados = [];

        // Componentes que já possuem custo próprio: não se desce abaixo deles.
        foreach ($itens as $item) {
            if (
                trim((string) self::v($item, 'tipo_custo')) === 'MATÉRIA-PRIMA'
                && (float) self::v($item, 'custo_mp_unit') > 0
            ) {
                $bloqueados[trim((string) self::v($item, 'cod_comp'))] = true;
            }
        }

        $excluidos = [];

        do {
            $mudou = false;

            foreach ($itens as $chave => $item) {
                if (isset($excluidos[$chave])) {
                    continue;
                }

                $pai = trim((string) self::v($item, 'cod_pai'));

                if ($pai !== '' && isset($bloqueados[$pai])) {
                    $excluidos[$chave] = true;
                    $bloqueados[trim((string) self::v($item, 'cod_comp'))] = true;
                    $mudou = true;
                }
            }
        } while ($mudou);

        $resultado = [];

        foreach ($itens as $chave => $item) {
            $resultado[$chave] = !isset($excluidos[$chave]);
        }

        return $resultado;
    }

    /**
     * Divisor para converter o custo da peça (ficha) para a unidade de venda.
     * Ex.: ficha por chapa de 3,52 m² e produto vendido em M2 => divide por 3,52.
     */
    /**
     * Fator 1.0 = sem conversão (unidade não é M2/M3 ou dimensões desconhecidas).
     * Em M3 o fator pode ser menor que 1 (volume da peça).
     */
    public static function semFator(float $fator): bool
    {
        return abs($fator - 1.0) < 0.0000001;
    }

    public static function fatorUnidade(?string $unidade, $baseM2, $baseM3): float
    {
        $unidade = strtoupper(trim((string) $unidade));

        if ($unidade === 'M2' && (float) $baseM2 > 0) {
            return (float) $baseM2;
        }

        if ($unidade === 'M3' && (float) $baseM3 > 0) {
            return (float) $baseM3;
        }

        return 1.0;
    }

    private static function v($item, string $campo)
    {
        return is_array($item) ? ($item[$campo] ?? null) : ($item->{$campo} ?? null);
    }

    /**
     * Dimensões da chapa (m² e m³) deduzidas da descrição do produto e, se ela
     * não trouxer as 3 dimensões, de um componente com a mesma espessura
     * (ex.: PAINEL ... 15MM -> TRIPLAY SF 2200X1600X15MM => 3,52 m² e 0,0528 m³).
     *
     * @param  string[] $descricoesComponentes
     * @return array{base_m2: float, base_m3: float, comprimento_mm: float, largura_mm: float, espessura_mm: float}
     */
    public static function dimensoesChapa(string $descricaoProduto, array $descricoesComponentes): array
    {
        $padrao = '/(\d+(?:\.\d+)?)\s*X\s*(\d+(?:\.\d+)?)\s*X\s*(\d+(?:\.\d+)?)\s*MM/';

        $norm = function ($t) {
            return strtoupper(str_replace(',', '.', (string) $t));
        };

        // 1) Dimensões na própria descrição do produto (ex.: 2440X1220X18MM, 13X68X1000MM).
        if (preg_match($padrao, $norm($descricaoProduto), $d)) {
            $m2 = ((float) $d[1] / 1000) * ((float) $d[2] / 1000);

            if ($m2 > 0) {
                return [
                    'base_m2' => $m2,
                    'base_m3' => $m2 * ((float) $d[3] / 1000),
                    'comprimento_mm' => (float) $d[1],
                    'largura_mm' => (float) $d[2],
                    'espessura_mm' => (float) $d[3],
                ];
            }
        }

        // 2) Senão, de um componente da ficha com a mesma espessura do produto.
        $espessuraProduto = 0.0;

        if (preg_match('/(\d+(?:\.\d+)?)\s*MM/', $norm($descricaoProduto), $m)) {
            $espessuraProduto = (float) $m[1];
        }

        foreach ($descricoesComponentes as $desc) {
            if (!preg_match($padrao, $norm($desc), $m)) {
                continue;
            }

            if ($espessuraProduto > 0 && abs((float) $m[3] - $espessuraProduto) > 0.001) {
                continue;
            }

            $m2 = ((float) $m[1] / 1000) * ((float) $m[2] / 1000);

            if ($m2 <= 0) {
                continue;
            }

            $espessura = ($espessuraProduto > 0 ? $espessuraProduto : (float) $m[3]) / 1000;

            return [
                'base_m2' => $m2,
                'base_m3' => $m2 * $espessura,
                'comprimento_mm' => (float) $m[1],
                'largura_mm' => (float) $m[2],
                'espessura_mm' => $espessura * 1000,
            ];
        }

        return ['base_m2' => 0.0, 'base_m3' => 0.0, 'comprimento_mm' => 0.0, 'largura_mm' => 0.0, 'espessura_mm' => 0.0];
    }
}
