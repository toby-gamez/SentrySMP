<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommandQueue;
use App\Models\PaymentSettings;
use App\Models\PaymentTransaction;

class DashboardController extends Controller
{
    public function index()
    {
        $resetAt = PaymentSettings::current()->stats_reset_at;

        $revenueQuery = PaymentTransaction::query();
        if ($resetAt) {
            $revenueQuery->where('created_at', '>=', $resetAt);
        }

        $totalTransactions  = PaymentTransaction::count();
        $totalRevenue       = (clone $revenueQuery)->sum('amount');
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
