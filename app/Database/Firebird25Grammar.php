<?php

namespace App\Database;

use HarryGulliford\Firebird\Query\Grammars\FirebirdGrammar;
use Illuminate\Database\Query\Builder;

/**
 * Gramática para Firebird 2.5.
 *
 * O pacote harrygulliford/laravel-firebird gera "OFFSET n ROWS FETCH FIRST n ROWS ONLY"
 * (Firebird 3/4+). No Firebird 2.5 a sintaxe é "SELECT FIRST n SKIP m ...".
 */
class Firebird25Grammar extends FirebirdGrammar
{
    protected function compileLimit(Builder $query, $limit)
    {
        return '';
    }

    protected function compileOffset(Builder $query, $offset)
    {
        return '';
    }

    protected function compileColumns(Builder $query, $columns)
    {
        $sql = parent::compileColumns($query, $columns);

        if (!is_string($sql) || stripos($sql, 'select') !== 0) {
            return $sql;
        }

        $paginacao = '';
        if (!is_null($query->limit)) {
            $paginacao .= ' first ' . (int) $query->limit;
        }
        if (!is_null($query->offset) && (int) $query->offset > 0) {
            $paginacao .= ' skip ' . (int) $query->offset;
        }

        return $paginacao === '' ? $sql : 'select' . $paginacao . substr($sql, 6);
    }
}
