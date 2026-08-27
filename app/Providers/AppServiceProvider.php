<?php

namespace App\Providers;

use App\Models\Category;
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
                $navTopDonors = PaymentTransaction::selectRaw('minecraft_username, SUM(amount) as total_paid')
                    ->where(function ($q) {
                        $q->where('status', 'like', '%captured%')
                          ->orWhere('status', 'like', '%succeeded%')
                          ->orWhere('status', 'like', '%paid%');
                    })
                    ->whereNotIn('minecraft_username', ['Taneq', 'webdev', '', '<unknown>'])
                    ->where('minecraft_username', '!=', '')
                    ->where('minecraft_username', 'not like', '.%')
                    ->groupBy('minecraft_username')
                    ->orderByDesc('total_paid')
                    ->limit(5)
                    ->get();
            } catch (\Throwable) {
                $navTopDonors = collect();
            }
            $view->with('navTopDonors', $navTopDonors);

            try {
                $navCategories = Category::orderBy('name')->get();
            } catch (\Throwable) {
                $navCategories = collect();
            }
            $view->with('navCategories', $navCategories);
        });
    }
}
