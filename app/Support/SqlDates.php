<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Date-bucketing SQL that works on SQLite, PostgreSQL and MySQL, so reports do not
 * depend on one database's date functions. $col must be a trusted column name.
 */
class SqlDates
{
    public static function day(string $col): string
    {
        return "DATE({$col})"; // valid on all three
    }

    /** "2026-10" */
    public static function month(string $col): string
    {
        return match (DB::getDriverName()) {
            'pgsql' => "to_char({$col}, 'YYYY-MM')",
            'mysql', 'mariadb' => "DATE_FORMAT({$col}, '%Y-%m')",
            default => "strftime('%Y-%m', {$col})",
        };
    }

    /** Year + week number, e.g. "202641" */
    public static function week(string $col): string
    {
        return match (DB::getDriverName()) {
            'pgsql' => "to_char({$col}, 'IYYYIW')",
            'mysql', 'mariadb' => "DATE_FORMAT({$col}, '%x%v')",
            default => "strftime('%Y%W', {$col})",
        };
    }

    /** Hour of day 0-23 as an integer */
    public static function hour(string $col): string
    {
        return match (DB::getDriverName()) {
            'pgsql' => "CAST(EXTRACT(HOUR FROM {$col}) AS INTEGER)",
            'mysql', 'mariadb' => "HOUR({$col})",
            default => "CAST(strftime('%H', {$col}) AS INTEGER)",
        };
    }
}
