<?php

namespace App\Providers;

use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Share top donors with the nav partial on every page load
        View::composer('layouts.partials.nav', function ($view) {
            try {
                $navTopDonors = PaymentTransaction::selectRaw('MinecraftUsername, SUM(Amount) as total_paid')
                    ->where(function ($q) {
                        $q->where('Status', 'like', '%captured%')
                          ->orWhere('Status', 'like', '%succeeded%')
                          ->orWhere('Status', 'like', '%paid%');
                    })
                    ->whereNotIn('MinecraftUsername', ['Taneq', 'webdev', '', '<unknown>'])
                    ->where('MinecraftUsername', '!=', '')
                    ->where('MinecraftUsername', 'not like', '.%')
                    ->groupBy('MinecraftUsername')
                    ->orderByDesc('total_paid')
                    ->limit(5)
                    ->get();
            } catch (\Throwable) {
                $navTopDonors = collect();
            }
            $view->with('navTopDonors', $navTopDonors);
        });
    }
}
