<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\PaymentTransaction;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Seasonal theme (server-side): admin setting, then config, otherwise by month
        View::composer('layouts.app', function ($view) {
            $chosen = SiteSetting::get('season');
            $season = ($chosen && $chosen !== 'auto' ? $chosen : config('shop.season')) ?: match ((int) now()->format('n')) {
                10 => 'halloween',
                12 => 'christmas',
                default => 'none',
            };
            $season = in_array($season, ['halloween', 'christmas'], true) ? $season : '';
            $view->with('season', $season);
        });

        // Share top donors with the nav partial on every page load
        View::composer('layouts.partials.nav', function ($view) {
            try {
                $navTopDonors = PaymentTransaction::selectRaw('minecraft_username, SUM(amount) as total_paid')
                    ->paid()
                    ->whereNotIn('minecraft_username', ['Taneq', 'DebugPlayer', 'webdev', '', '<unknown>'])
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
                $navCategories = Category::orderBy('sort_order')->get();
            } catch (\Throwable) {
                $navCategories = collect();
            }
            $view->with('navCategories', $navCategories);
        });
    }
}
