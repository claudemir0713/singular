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
}
