<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerCreditTransaction;
use App\Models\Setting;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\WhatsappReceiptLog;
use App\Services\EfdmsService;
use App\Services\WhatsappReceiptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    /**
     * Process a checkout/sale.
     *
     * POST /api/v1/pos/checkout
     */
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,card,mobile,bank_transfer,credit',
            'amount_paid' => 'required|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_tin' => 'nullable|string|max:50',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'offline_id' => 'nullable|string|max:100', // For offline sync
        ]);

        try {
            $transaction = DB::transaction(function () use ($validated, $request) {
                $subtotal = 0;
                $taxAmount = 0;
                $items = [];

                // Process each item
                foreach ($validated['items'] as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    $quantity = $item['quantity'];

                    // Check stock
                    if ($product->stock_quantity < $quantity) {
                        throw new \Exception("Insufficient stock for {$product->name}. Available: {$product->stock_quantity}");
                    }

                    $itemSubtotal = $product->selling_price * $quantity;
                    $effectiveTaxRate = $product->effective_tax_rate;
                    $itemTax = $itemSubtotal * ($effectiveTaxRate / 100);

                    $items[] = [
                        'product' => $product,
                        'quantity' => $quantity,
                        'unit_price' => $product->selling_price,
                        'cost_price' => $product->cost_price ?? 0,
                        'tax_rate' => $effectiveTaxRate,
                        'tax_category' => $product->tax_category ?? 'standard',
                        'tax_amount' => $itemTax,
                        'subtotal' => $itemSubtotal + $itemTax,
                    ];

                    $subtotal += $itemSubtotal;
                    $taxAmount += $itemTax;
                }

                $discountAmount = $validated['discount_amount'] ?? 0;
                $total = $subtotal + $taxAmount - $discountAmount;
                $amountPaid = $validated['amount_paid'];
                $changeGiven = max(0, $amountPaid - $total);

                $user = $request->user();

                // Resolve customer info
                $customerId = $validated['customer_id'] ?? null;
                $customerName = $validated['customer_name'] ?? null;
                $customerPhone = $validated['customer_phone'] ?? null;
                $customerTin = $validated['customer_tin'] ?? null;

                $customer = null;
                if ($customerId) {
                    $customer = Customer::findOrFail($customerId);
                    $customerName = $customerName ?: $customer->name;
                    $customerPhone = $customerPhone ?: $customer->phone;
                    $customerTin = $customerTin ?: $customer->tin;
                } elseif ($customerName && $customerPhone) {
                    // Auto-create or find customer when name + phone provided
                    $customer = Customer::firstOrCreate(
                        ['phone' => $customerPhone, 'company_id' => $user->company_id],
                        [
                            'name' => $customerName,
                            'tin' => $customerTin,
                            'credit_limit' => 0,
                            'current_balance' => 0,
                        ]
                    );
                    $customerId = $customer->id;
                }

                // Credit payment validation
                if ($validated['payment_method'] === 'credit') {
                    if (!$customer) {
                        throw new \Exception('A customer must be selected for credit sales.');
                    }
                    if ($customer->credit_limit <= 0) {
                        throw new \Exception('This customer has no credit limit.');
                    }
                    if ($customer->available_credit < $total) {
                        throw new \Exception("Insufficient credit. Available: " . number_format($customer->available_credit, 2));
                    }
                }

                // Create transaction
                $transaction = Transaction::create([
                    'company_id' => $user->company_id,
                    'branch_id' => $user->currentBranch()?->id,
                    'customer_id' => $customerId,
                    'transaction_number' => Transaction::generateTransactionNumber(),
                    'user_id' => $user->id,
                    'customer_name' => $customerName,
                    'customer_phone' => $customerPhone,
                    'customer_tin' => $customerTin,
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'discount_amount' => $discountAmount,
                    'total' => $total,
                    'payment_method' => $validated['payment_method'],
                    'amount_paid' => $validated['payment_method'] === 'credit' ? 0 : $amountPaid,
                    'change_given' => $validated['payment_method'] === 'credit' ? 0 : $changeGiven,
                    'status' => 'completed',
                    'notes' => $validated['notes'] ?? null,
                ]);

                // Create transaction items and update inventory
                foreach ($items as $item) {
                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $item['product']->id,
                        'product_name' => $item['product']->name,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'cost_price' => $item['cost_price'],
                        'tax_rate' => $item['tax_rate'],
                        'tax_amount' => $item['tax_amount'],
                        'tax_category' => $item['tax_category'],
                        'subtotal' => $item['subtotal'],
                    ]);

                    // Update inventory
                    $inventory = $item['product']->inventory;
                    if ($inventory) {
                        $quantityBefore = $inventory->quantity;
                        $quantityAfter = $quantityBefore - $item['quantity'];

                        StockAdjustment::create([
                            'company_id' => $user->company_id,
                            'product_id' => $item['product']->id,
                            'user_id' => $user->id,
                            'type' => 'sold',
                            'quantity_change' => -$item['quantity'],
                            'quantity_before' => $quantityBefore,
                            'quantity_after' => $quantityAfter,
                            'reason' => 'Sale: ' . $transaction->transaction_number,
                        ]);

                        $inventory->update(['quantity' => $quantityAfter]);
                    }
                }

                // Handle credit sale — update customer balance
                if ($validated['payment_method'] === 'credit' && $customer) {
                    $customer = Customer::lockForUpdate()->findOrFail($customer->id);
                    $balanceBefore = (float) $customer->current_balance;
                    $balanceAfter = $balanceBefore + $total;

                    CustomerCreditTransaction::create([
                        'customer_id' => $customer->id,
                        'transaction_id' => $transaction->id,
                        'type' => 'sale_on_credit',
                        'amount' => $total,
                        'balance_before' => $balanceBefore,
                        'balance_after' => $balanceAfter,
                        'notes' => 'Credit sale: ' . $transaction->transaction_number,
                        'user_id' => $user->id,
                    ]);

                    $customer->update(['current_balance' => $balanceAfter]);
                }

                return $transaction;
            });

            $transaction->load('items.product');

            // Attempt EFDMS fiscal signing after DB commit
            $fiscalData = null;
            $company = $request->user()->company;
            if ($company && $company->isEfdEnabled()) {
                try {
                    $efdmsService = app(EfdmsService::class);
                    $result = $efdmsService->signReceipt($transaction);

                    if ($result['success']) {
                        $transaction->update([
                            'fiscal_receipt_number' => $result['fiscal_receipt_number'],
                            'fiscal_verification_code' => $result['fiscal_verification_code'],
                            'fiscal_qr_code' => $result['fiscal_qr_code'],
                            'fiscal_receipt_time' => $result['fiscal_receipt_time'],
                            'fiscal_submitted' => true,
                            'fiscal_submission_error' => null,
                        ]);
                        $transaction->refresh();
                    } else {
                        $transaction->update([
                            'fiscal_submitted' => false,
                            'fiscal_submission_error' => $result['message'],
                        ]);
                    }
                } catch (\Exception $e) {
                    $transaction->update([
                        'fiscal_submitted' => false,
                        'fiscal_submission_error' => $e->getMessage(),
                    ]);
                }
            }

            // Auto-send WhatsApp receipt if enabled and mode is automatic
            $whatsappStatus = null;
            if (Setting::get('whatsapp_receipts_enabled', false)
                && Setting::get('whatsapp_receipts_mode', 'prompted') === 'automatic'
                && ($transaction->customer_phone || $transaction->customer?->phone)) {
                try {
                    $whatsappService = app(WhatsappReceiptService::class);
                    $waResult = $whatsappService->sendReceipt($transaction);
                    $whatsappStatus = $waResult['success'] ? 'pending' : 'failed';
                } catch (\Exception $e) {
                    // Non-blocking — don't fail the sale
                }
            }

            $txData = $this->formatTransaction($transaction);
            if ($whatsappStatus) {
                $txData['whatsapp_receipt_status'] = $whatsappStatus;
            }

            return response()->json([
                'success' => true,
                'message' => 'Sale completed successfully.',
                'data' => $txData,
                'offline_id' => $validated['offline_id'] ?? null,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'offline_id' => $validated['offline_id'] ?? null,
            ], 422);
        }
    }

    /**
     * Void a transaction.
     *
     * POST /api/v1/pos/transactions/{id}/void
     */
    public function voidTransaction(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        // Check permission
        if (!$user->isCompanyOwner() && !$user->hasPermission('void_transactions')) {
            return response()->json([
                'message' => 'You do not have permission to void transactions.',
            ], 403);
        }

        $transaction = Transaction::where('id', $id)
            ->where('company_id', $user->company_id)
            ->first();

        if (!$transaction) {
            return response()->json([
                'message' => 'Transaction not found.',
            ], 404);
        }

        if ($transaction->status === 'voided') {
            return response()->json([
                'message' => 'Transaction is already voided.',
            ], 400);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($transaction, $user, $validated) {
                // Restore inventory for each item
                foreach ($transaction->items as $item) {
                    $inventory = $item->product?->inventory;
                    if ($inventory) {
                        $quantityBefore = $inventory->quantity;
                        $quantityAfter = $quantityBefore + $item->quantity;

                        StockAdjustment::create([
                            'company_id' => $user->company_id,
                            'product_id' => $item->product_id,
                            'user_id' => $user->id,
                            'type' => 'returned',
                            'quantity_change' => $item->quantity,
                            'quantity_before' => $quantityBefore,
                            'quantity_after' => $quantityAfter,
                            'reason' => 'Voided sale: ' . $transaction->transaction_number . '. Reason: ' . $validated['reason'],
                        ]);

                        $inventory->update(['quantity' => $quantityAfter]);
                    }
                }

                // Update transaction status
                $transaction->update([
                    'status' => 'voided',
                    'notes' => ($transaction->notes ? $transaction->notes . "\n" : '') .
                        "Voided by {$user->name}: {$validated['reason']}",
                ]);
            });

            $transaction->refresh();
            $transaction->load('items.product');

            return response()->json([
                'success' => true,
                'message' => 'Transaction voided successfully.',
                'data' => $this->formatTransaction($transaction),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get receipt data for a transaction.
     *
     * GET /api/v1/pos/transactions/{id}/receipt
     */
    public function receipt(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $transaction = Transaction::where('id', $id)
            ->where('company_id', $user->company_id)
            ->with(['items.product', 'user', 'branch'])
            ->first();

        if (!$transaction) {
            return response()->json([
                'message' => 'Transaction not found.',
            ], 404);
        }

        return response()->json([
            'data' => $this->formatReceipt($transaction),
        ]);
    }

    /**
     * Format transaction for API response.
     */
    protected function formatTransaction(Transaction $transaction): array
    {
        $data = [
            'id' => $transaction->id,
            'transaction_number' => $transaction->transaction_number,
            'status' => $transaction->status,
            'subtotal' => (float) $transaction->subtotal,
            'tax_amount' => (float) $transaction->tax_amount,
            'discount_amount' => (float) $transaction->discount_amount,
            'total' => (float) $transaction->total,
            'payment_method' => $transaction->payment_method,
            'amount_paid' => (float) $transaction->amount_paid,
            'change_given' => (float) $transaction->change_given,
            'customer_name' => $transaction->customer_name,
            'customer_phone' => $transaction->customer_phone,
            'customer_tin' => $transaction->customer_tin,
            'notes' => $transaction->notes,
            'items' => $transaction->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'tax_rate' => (float) $item->tax_rate,
                'tax_amount' => (float) $item->tax_amount,
                'tax_category' => $item->tax_category ?? 'standard',
                'subtotal' => (float) $item->subtotal,
            ])->toArray(),
            'cashier' => [
                'id' => $transaction->user_id,
                'name' => $transaction->user?->name,
            ],
            'branch' => $transaction->branch ? [
                'id' => $transaction->branch->id,
                'name' => $transaction->branch->name,
            ] : null,
            'created_at' => $transaction->created_at->toIso8601String(),
        ];

        // Include fiscal data if present
        if ($transaction->fiscal_receipt_number) {
            $data['fiscal'] = [
                'receipt_number' => $transaction->fiscal_receipt_number,
                'verification_code' => $transaction->fiscal_verification_code,
                'qr_code' => $transaction->fiscal_qr_code,
                'receipt_time' => $transaction->fiscal_receipt_time?->toIso8601String(),
                'submitted' => (bool) $transaction->fiscal_submitted,
            ];
        }

        // Include WhatsApp receipt status if any
        $waLog = WhatsappReceiptLog::withoutGlobalScope('company')
            ->where('transaction_id', $transaction->id)
            ->latest()
            ->first();

        if ($waLog) {
            $data['whatsapp_receipt_status'] = $waLog->status;
        }

        return $data;
    }

    /**
     * Format receipt data for printing.
     */
    protected function formatReceipt(Transaction $transaction): array
    {
        $company = $transaction->user?->company;

        return [
            'company' => [
                'name' => $company?->name ?? 'Company',
                'address' => $company?->address,
                'phone' => $company?->phone,
                'email' => $company?->email,
                'logo' => $this->getCompanyLogo($company),
                'tin' => $company?->tin,
                'vrn' => $company?->vrn,
            ],
            'branch' => $transaction->branch ? [
                'name' => $transaction->branch->name,
                'address' => $transaction->branch->address,
                'phone' => $transaction->branch->phone,
            ] : null,
            'transaction' => [
                'number' => $transaction->transaction_number,
                'date' => $transaction->created_at->format('d/m/Y'),
                'time' => $transaction->created_at->format('H:i'),
                'cashier' => $transaction->user?->name,
                'status' => $transaction->status,
            ],
            'customer' => [
                'name' => $transaction->customer_name,
                'phone' => $transaction->customer_phone,
                'tin' => $transaction->customer_tin,
            ],
            'items' => $transaction->items->map(fn ($item) => [
                'name' => $item->product_name,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'subtotal' => (float) ($item->unit_price * $item->quantity),
                'tax' => (float) $item->tax_amount,
            ])->toArray(),
            'totals' => [
                'subtotal' => (float) $transaction->subtotal,
                'tax' => (float) $transaction->tax_amount,
                'discount' => (float) $transaction->discount_amount,
                'total' => (float) $transaction->total,
            ],
            'payment' => [
                'method' => $transaction->payment_method,
                'method_label' => ucfirst(str_replace('_', ' ', $transaction->payment_method)),
                'amount_paid' => (float) $transaction->amount_paid,
                'change' => (float) $transaction->change_given,
            ],
            'fiscal' => $transaction->fiscal_receipt_number ? [
                'receipt_number' => $transaction->fiscal_receipt_number,
                'verification_code' => $transaction->fiscal_verification_code,
                'qr_code' => $transaction->fiscal_qr_code,
                'receipt_time' => $transaction->fiscal_receipt_time?->toIso8601String(),
            ] : null,
            'footer' => [
                'message' => 'Thank you for your purchase!',
            ],
        ];
    }

    protected function getCompanyLogo($company): ?string
    {
        if (!$company) {
            return null;
        }

        if ($company->logo) {
            return asset('storage/' . $company->logo);
        }

        $storeLogo = Setting::withoutGlobalScope('company')
            ->where('key', 'store_logo')
            ->where('company_id', $company->id)
            ->value('value');

        if ($storeLogo && $storeLogo !== '') {
            $company->update(['logo' => $storeLogo]);
            return asset('storage/' . $storeLogo);
        }

        return null;
    }
}
