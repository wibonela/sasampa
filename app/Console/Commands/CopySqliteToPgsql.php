<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One-off helper for moving Sasampa from SQLite to PostgreSQL.
 *
 *   DB_CONNECTION=pgsql ... php artisan db:copy-from-sqlite /path/to/database.sqlite
 *
 * The target must already be migrated and empty. Reads the SQLite file read-only, copies every
 * table in foreign-key order, fixes boolean values, resets id sequences and prints a
 * row-count + revenue comparison. Nothing in the SQLite file is changed.
 */
class CopySqliteToPgsql extends Command
{
    protected $signature = 'db:copy-from-sqlite {path : Path to the SQLite database file} {--chunk=500}';

    protected $description = 'Copy all data from a SQLite file into the (empty, migrated) PostgreSQL database';

    public function handle(): int
    {
        $path = $this->argument('path');
        if (! is_file($path)) {
            $this->error("No such file: $path");
            return self::FAILURE;
        }
        if (DB::connection()->getDriverName() !== 'pgsql') {
            $this->error('The default connection must be pgsql (set DB_CONNECTION=pgsql).');
            return self::FAILURE;
        }

        config(['database.connections.sqlite_source' => [
            'driver' => 'sqlite', 'database' => $path, 'prefix' => '', 'foreign_key_constraints' => false,
        ]]);
        $src = DB::connection('sqlite_source');
        $dst = DB::connection();

        $srcTables = collect($src->select("select name from sqlite_master where type='table' and name not like 'sqlite_%'"))
            ->pluck('name')->all();
        $dstTables = collect(Schema::getTableListing(schema: 'public', schemaQualified: false))->all();

        $tables = array_values(array_intersect($dstTables, $srcTables));
        $tables = array_values(array_diff($tables, ['migrations']));
        $missing = array_diff($srcTables, $dstTables, ['migrations']);
        if ($missing) {
            $this->warn('In SQLite but not in PostgreSQL (skipped): ' . implode(', ', $missing));
        }

        // Migrations pre-load these reference tables; the SQLite copy replaces them.
        foreach (['plans'] as $seeded) {
            if (in_array($seeded, $tables, true) && $src->table($seeded)->exists()) {
                $dst->statement("truncate table \"$seeded\" restart identity cascade");
            }
        }

        foreach ($tables as $t) {
            if ($dst->table($t)->exists()) {
                $this->error("Target table $t is not empty. Start from a freshly migrated database.");
                return self::FAILURE;
            }
        }

        if (! $this->preflight($src, $tables)) {
            return self::FAILURE;
        }

        $order = $this->dependencyOrder($tables);
        $bools = $this->booleanColumns();
        $chunk = (int) $this->option('chunk');

        $dst->beginTransaction();
        try {
            foreach ($order as $table) {
                $count = $this->copyTable($src, $dst, $table, $bools[$table] ?? [], $chunk);
                $this->line(sprintf('  %-45s %8d rows', $table, $count));
            }
            $this->resetSequences($order);
            $dst->commit();
        } catch (\Throwable $e) {
            $dst->rollBack();
            $this->error('Copy failed and was rolled back: ' . mb_substr(strtok($e->getMessage(), "\n"), 0, 160));
            return self::FAILURE;
        }

        return $this->verify($src, $dst, $order) ? self::SUCCESS : self::FAILURE;
    }

    /**
     * SQLite ignores column lengths, PostgreSQL enforces them. Find every value that would not
     * fit before anything is copied, and report them all at once (lengths only, never the data).
     */
    private function preflight($src, array $tables): bool
    {
        $bad = [];
        $cols = DB::select("select table_name, column_name, character_maximum_length as len
                            from information_schema.columns
                            where table_schema = 'public' and character_maximum_length is not null");
        foreach ($cols as $c) {
            if (! in_array($c->table_name, $tables, true)) {
                continue;
            }
            $max = (int) ($src->selectOne(
                'select max(length("' . $c->column_name . '")) as m from "' . $c->table_name . '"'
            )->m ?? 0);
            if ($max > (int) $c->len) {
                $bad[] = "{$c->table_name}.{$c->column_name}: longest value is $max characters, column allows {$c->len}";
            }
        }
        if ($bad) {
            $this->error('Some values do not fit their PostgreSQL column:');
            foreach ($bad as $b) {
                $this->line('  ' . $b);
            }

            return false;
        }

        return true;
    }

    private function copyTable($src, $dst, string $table, array $boolCols, int $chunk): int
    {
        $selfFk = $this->selfReference($table);

        $flush = function (array $rows) use ($dst, $table, $boolCols, $selfFk) {
            if (! $rows) {
                return;
            }
            $rows = array_map(function ($r) use ($boolCols) {
                $r = (array) $r;
                foreach ($boolCols as $c) {
                    if (array_key_exists($c, $r) && $r[$c] !== null) {
                        $r[$c] = (bool) $r[$c];
                    }
                }
                return $r;
            }, $rows);

            if ($selfFk) {
                // Parents before children: insert rows whose parent is already stored (or null)
                $stored = [];
                while ($rows) {
                    $next = [];
                    $batch = [];
                    foreach ($rows as $r) {
                        $parent = $r[$selfFk] ?? null;
                        if ($parent === null || isset($stored[$parent]) || $dst->table($table)->where('id', $parent)->exists()) {
                            $batch[] = $r;
                            $stored[$r['id']] = true;
                        } else {
                            $next[] = $r;
                        }
                    }
                    if (! $batch) {
                        throw new \RuntimeException("Unresolvable parent references in $table");
                    }
                    foreach (array_chunk($batch, 200) as $b) {
                        $dst->table($table)->insert($b);
                    }
                    $rows = $next;
                }
            } else {
                foreach (array_chunk($rows, 200) as $b) {
                    $dst->table($table)->insert($b);
                }
            }
        };

        $read = 0;
        if ($selfFk) {
            // Hierarchies (e.g. parent_id) need every row at once to order parents first.
            $all = $src->table($table)->get()->all();
            $read = count($all);
            $flush($all);

            return $read;
        }

        $buffer = [];
        foreach ($src->table($table)->cursor() as $row) {
            $buffer[] = $row;
            if (count($buffer) >= $chunk) {
                $read += count($buffer);
                $flush($buffer);
                $buffer = [];
            }
        }
        $read += count($buffer);
        $flush($buffer);

        return $read;
    }

    /** Tables ordered so every table comes after the tables it has foreign keys to. */
    private function dependencyOrder(array $tables): array
    {
        $deps = [];
        $rows = DB::select("
            select c.conrelid::regclass::text as child, c.confrelid::regclass::text as parent
            from pg_constraint c where c.contype = 'f'
              and c.connamespace = 'public'::regnamespace");
        foreach ($tables as $t) {
            $deps[$t] = [];
        }
        foreach ($rows as $r) {
            $child = trim($r->child, '"');
            $parent = trim($r->parent, '"');
            if ($child !== $parent && isset($deps[$child]) && isset($deps[$parent])) {
                $deps[$child][$parent] = true;
            }
        }

        $ordered = [];
        while ($deps) {
            $ready = array_keys(array_filter($deps, fn ($d) => ! array_diff_key($d, array_flip($ordered))));
            if (! $ready) {
                throw new \RuntimeException('Circular foreign keys between: ' . implode(', ', array_keys($deps)));
            }
            sort($ready);
            foreach ($ready as $t) {
                $ordered[] = $t;
                unset($deps[$t]);
            }
        }

        return $ordered;
    }

    private function selfReference(string $table): ?string
    {
        $row = DB::selectOne("
            select a.attname as col from pg_constraint c
            join pg_attribute a on a.attrelid = c.conrelid and a.attnum = any(c.conkey)
            where c.contype = 'f' and c.conrelid = ?::regclass and c.confrelid = c.conrelid
            limit 1", [$table]);

        return $row->col ?? null;
    }

    private function booleanColumns(): array
    {
        $out = [];
        foreach (DB::select("select table_name, column_name from information_schema.columns
                              where table_schema = 'public' and data_type = 'boolean'") as $r) {
            $out[$r->table_name][] = $r->column_name;
        }

        return $out;
    }

    private function resetSequences(array $tables): void
    {
        foreach ($tables as $t) {
            if (! Schema::hasColumn($t, 'id')) {
                continue;
            }
            $seq = DB::selectOne('select pg_get_serial_sequence(?, ?) as s', [$t, 'id'])->s ?? null;
            if ($seq) {
                DB::statement("select setval('$seq', coalesce((select max(id) from \"$t\"), 0) + 1, false)");
            }
        }
    }

    private function verify($src, $dst, array $tables): bool
    {
        $ok = true;
        $this->newLine();
        foreach ($tables as $t) {
            $a = $src->table($t)->count();
            $b = $dst->table($t)->count();
            if ($a !== $b) {
                $this->error("MISMATCH $t: sqlite=$a pgsql=$b");
                $ok = false;
            }
        }
        if (Schema::hasTable('transactions')) {
            $a = round((float) $src->table('transactions')->sum('total'), 2);
            $b = round((float) $dst->table('transactions')->sum('total'), 2);
            $this->line("transactions total: sqlite=$a pgsql=$b");
            $ok = $ok && $a === $b;
        }
        $this->{$ok ? 'info' : 'error'}($ok ? 'Verified: every table has the same row count.' : 'Verification FAILED.');

        return $ok;
    }
}
