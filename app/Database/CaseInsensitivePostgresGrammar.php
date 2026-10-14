<?php

namespace App\Database;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\PostgresGrammar;

/**
 * SQLite and MySQL match LIKE case-insensitively; PostgreSQL does not. The app's searches
 * (products, customers, transactions...) rely on the former, so on PostgreSQL every
 * "like" becomes "ilike".
 */
class CaseInsensitivePostgresGrammar extends PostgresGrammar
{
    protected function whereBasic(Builder $query, $where)
    {
        if (strtolower((string) $where['operator']) === 'like') {
            $where['operator'] = 'ilike';
        }

        return parent::whereBasic($query, $where);
    }
}
