<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Services\CommandQueueService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(private readonly CommandQueueService $commandQueue) {}

    public function index(Request $request)
    {
        $search   = $request->query('search', '');
        $status   = $request->query('status', '');
        $provider = $request->query('provider', '');
        $dateFrom = $request->query('date_from', '');
        $dateTo   = $request->query('date_to', '');

        $query = PaymentTransaction::orderByDesc('created_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('minecraft_username', 'like', "%{$search}%")
                  ->orWhere('provider_transaction_id', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', 'like', "%{$status}%");
        }

        if ($provider) {
            $query->where('provider', $provider);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $providers = PaymentTransaction::distinct()->orderBy('provider')->pluck('provider');
        $items     = $query->paginate(30)->withQueryString();

        return view('admin.transactions.index', compact('items', 'search', 'status', 'provider', 'providers', 'dateFrom', 'dateTo'));
    }

    public function show(PaymentTransaction $transaction)
    {
        $commands = $transaction->commandQueue()->orderBy('id')->get();
        return view('admin.transactions.show', compact('transaction', 'commands'));
    }

    public function retryDispatch(PaymentTransaction $transaction)
    {
        if (!$transaction->items_json) {
            return back()->withErrors(['error' => 'No items JSON available for this transaction.']);
        }

        try {
            $cartItems = json_decode($transaction->items_json, true, 512, JSON_THROW_ON_ERROR);
            $this->commandQueue->dispatchForTransaction($transaction, $cartItems);
            return back()->with('success', 'Commands re-dispatched to queue.');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Dispatch failed: ' . $e->getMessage()]);
        }
    }
}
