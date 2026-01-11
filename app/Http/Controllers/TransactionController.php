<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Transaction::with('user');

        if ($request->filled('search')) {
            $query->where('transaction_number', 'like', "%{$request->search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->latest()->paginate(25);

        return view('transactions.index', compact('transactions'));
    }

    public function show(Transaction $transaction): View
    {
        $transaction->load('items.product', 'user');
        return view('transactions.show', compact('transaction'));
    }

    public function void(Transaction $transaction): RedirectResponse
    {
        if ($transaction->status !== 'completed') {
            return redirect()->route('transactions.show', $transaction)
                ->with('error', 'This transaction cannot be voided.');
        }

        $transaction->update(['status' => 'voided']);

        // Restore inventory
        foreach ($transaction->items as $item) {
            if ($item->product && $item->product->inventory) {
                $item->product->inventory->increment('quantity', $item->quantity);
            }
        }

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Transaction voided successfully. Stock has been restored.');
    }
}
