<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommandQueue;
use App\Models\PaymentTransaction;

class DashboardController extends Controller
{
    public function index()
    {
        $totalTransactions  = PaymentTransaction::count();
        $totalRevenue       = PaymentTransaction::sum('amount');
        $pendingCommands    = CommandQueue::where('status', 'pending')->count();
        $recentTransactions = PaymentTransaction::orderByDesc('created_at')->limit(10)->get();

        return view('admin.dashboard', compact(
            'totalTransactions',
            'totalRevenue',
            'pendingCommands',
            'recentTransactions',
        ));
    }
}
