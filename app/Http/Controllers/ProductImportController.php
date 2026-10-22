<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductImportController extends Controller
{
    private const COLUMNS = [
        'name', 'category', 'selling_price', 'cost_price', 'opening_stock',
        'barcode', 'sku', 'low_stock_threshold', 'tax_rate', 'description', 'is_active',
    ];

    // Friendly headers shown in the template; readCsv() maps them back to COLUMNS.
    private const TEMPLATE_HEADERS = [
        'Product Name (required)', 'Category', 'Selling Price (required)', 'Cost Price', 'Opening Stock',
        'Barcode', 'SKU', 'Low Stock Alert', 'Tax Rate %', 'Description', 'Active (yes/no)',
    ];

    private const MAX_ROWS = 2000;

    public function create(): View
    {
        return view('products.import', ['columns' => self::COLUMNS]);
    }

    public function template(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM so Excel reads UTF-8
            fputcsv($out, self::TEMPLATE_HEADERS);
            fputcsv($out, ['Soda 500ml', 'Vinywaji', 800, 500, 24, '6001234567890', 'SODA-500', 5, 0, 'Cold soda', 'yes']);
            fputcsv($out, ['Sugar 1kg', 'Groceries', 3000, 2500, 40, '', '', '', '', '', 'yes']);
            fclose($out);
        }, 'sasampa-products-template.csv', ['Content-Type' => 'text/csv']);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:5120']);

        $company = auth()->user()->company;
        $rows = $this->readCsv($request->file('file')->getRealPath());

        if (is_string($rows)) {
            return back()->with('error', $rows);
        }
        if (count($rows) === 0) {
            return back()->with('error', 'The file has no product rows.');
        }
        if (count($rows) > self::MAX_ROWS) {
            return back()->with('error', 'Too many rows. Upload at most ' . self::MAX_ROWS . ' products at a time.');
        }

        // SKU and barcode are unique across the whole table, not per company.
        $takenSku = Product::withoutGlobalScopes()->whereNotNull('sku')->pluck('sku')->flip()->all();
        $takenBarcode = Product::withoutGlobalScopes()->whereNotNull('barcode')->pluck('barcode')->flip()->all();

        $remaining = null;
        if ($company->billingEnforced() && ($limit = $company->effectivePlan()?->limitFor('products')) !== null) {
            $remaining = max(0, $limit - $company->resourceCount('products'));
        }

        $categories = Category::pluck('id', 'name')->mapWithKeys(fn ($id, $n) => [mb_strtolower($n) => $id])->all();

        $created = 0;
        $errors = [];

        DB::transaction(function () use ($rows, &$takenSku, &$takenBarcode, &$remaining, &$categories, &$created, &$errors) {
            foreach ($rows as $line => $row) {
                $problems = $this->validateRow($row, $takenSku, $takenBarcode);

                if (!$problems && $remaining !== null && $remaining <= 0) {
                    $problems[] = 'product limit for your plan reached';
                }
                if ($problems) {
                    $errors[] = "Row {$line}: " . implode('; ', $problems);
                    continue;
                }

                $categoryId = null;
                if ($row['category'] !== '') {
                    $key = mb_strtolower($row['category']);
                    $categoryId = $categories[$key] ??= Category::create(['name' => $row['category']])->id;
                }

                $product = Product::create([
                    'name' => $row['name'],
                    'sku' => $row['sku'] ?: null,
                    'barcode' => $row['barcode'] ?: null,
                    'category_id' => $categoryId,
                    'description' => $row['description'] ?: null,
                    'cost_price' => $this->number($row['cost_price']) ?? 0,
                    'selling_price' => $this->number($row['selling_price']),
                    'tax_rate' => $this->number($row['tax_rate']) ?? 0,
                    'is_active' => !in_array(mb_strtolower($row['is_active']), ['no', 'n', '0', 'false'], true),
                ]);

                Inventory::create([
                    'product_id' => $product->id,
                    'quantity' => (int) ($row['opening_stock'] ?: 0),
                    'low_stock_threshold' => $row['low_stock_threshold'] !== '' ? (int) $row['low_stock_threshold'] : 10,
                ]);

                if ($row['sku'] !== '') {
                    $takenSku[$row['sku']] = true;
                }
                if ($row['barcode'] !== '') {
                    $takenBarcode[$row['barcode']] = true;
                }
                if ($remaining !== null) {
                    $remaining--;
                }
                $created++;
            }
        });

        $redirect = $created > 0 && !$errors
            ? redirect()->route('products.index')->with('success', "{$created} products imported.")
            : back();

        if ($errors) {
            $redirect->with($created > 0 ? 'success' : 'error', "{$created} products imported, " . count($errors) . ' rows skipped.')
                ->with('import_errors', array_slice($errors, 0, 200));
        }

        return $redirect;
    }

    /** @return array<int, array<string, string>>|string rows keyed by spreadsheet line number, or an error message */
    private function readCsv(string $path): array|string
    {
        $handle = fopen($path, 'r');
        $first = fgets($handle);
        if ($first === false) {
            return 'The file is empty.';
        }
        $first = preg_replace('/^\xEF\xBB\xBF/', '', $first);
        // Excel in some locales saves with ; or tab instead of comma.
        $delimiter = collect([',', ';', "\t"])->sortByDesc(fn ($d) => substr_count($first, $d))->first();

        $header = array_map(
            fn ($h) => str_replace([' ', '-'], '_', trim(preg_replace('/\(.*?\)|%/', '', mb_strtolower((string) $h)))),
            str_getcsv($first, $delimiter)
        );

        $aliases = [
            'product_name' => 'name', 'selling_price' => 'selling_price', 'price' => 'selling_price',
            'stock' => 'opening_stock', 'quantity' => 'opening_stock',
            'low_stock_alert' => 'low_stock_threshold', 'active' => 'is_active',
        ];
        $header = array_map(fn ($h) => $aliases[$h] ?? $h, $header);

        foreach (['name', 'selling_price'] as $required) {
            if (!in_array($required, $header, true)) {
                return "Missing required column \"{$required}\". Download the template and use its header row.";
            }
        }

        $rows = [];
        $line = 1;
        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $line++;
            if (count(array_filter($data, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }
            $row = array_fill_keys(self::COLUMNS, '');
            foreach ($header as $i => $col) {
                if (in_array($col, self::COLUMNS, true)) {
                    $row[$col] = trim((string) ($data[$i] ?? ''));
                }
            }
            $rows[$line] = $row;
        }
        fclose($handle);

        return $rows;
    }

    private function number(string $value): ?float
    {
        $clean = str_replace([',', ' '], '', $value);

        return is_numeric($clean) ? (float) $clean : null;
    }

    private function validateRow(array $row, array $takenSku, array $takenBarcode): array
    {
        $problems = [];

        if ($row['name'] === '') {
            $problems[] = 'name is required';
        } elseif (mb_strlen($row['name']) > 255) {
            $problems[] = 'name is too long';
        }

        $selling = $this->number($row['selling_price']);
        if ($selling === null || $selling < 0) {
            $problems[] = 'selling_price must be a number, 0 or more';
        }
        if ($row['cost_price'] !== '' && (($c = $this->number($row['cost_price'])) === null || $c < 0)) {
            $problems[] = 'cost_price must be a number, 0 or more';
        }
        if ($row['tax_rate'] !== '' && (($t = $this->number($row['tax_rate'])) === null || $t < 0 || $t > 100)) {
            $problems[] = 'tax_rate must be between 0 and 100';
        }
        foreach (['opening_stock', 'low_stock_threshold'] as $f) {
            if ($row[$f] !== '' && !ctype_digit($row[$f])) {
                $problems[] = "{$f} must be a whole number";
            }
        }
        if (mb_strlen($row['category']) > 255) {
            $problems[] = 'category is too long';
        }
        foreach (['sku' => $takenSku, 'barcode' => $takenBarcode] as $f => $taken) {
            if ($row[$f] !== '' && mb_strlen($row[$f]) > 100) {
                $problems[] = "{$f} is too long";
            } elseif ($row[$f] !== '' && isset($taken[$row[$f]])) {
                $problems[] = "{$f} \"{$row[$f]}\" is already used";
            }
        }

        return $problems;
    }
}
