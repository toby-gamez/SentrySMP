<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

// ─── Shop ────────────────────────────────────────────────────────────────────

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/shop/{category:slug}', [ShopController::class, 'category'])->name('shop.category');
Route::get('/checkout', [ShopController::class, 'checkout'])->name('checkout');

// ─── Pages ───────────────────────────────────────────────────────────────────

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/our-team', [PageController::class, 'ourTeam'])->name('our-team');
Route::get('/join', [PageController::class, 'join'])->name('join');
Route::get('/news', [PageController::class, 'news'])->name('news');
Route::get('/vote-for-us', [PageController::class, 'voteForUs'])->name('vote-for-us');
Route::get('/active-players', [PageController::class, 'activePlayers'])->name('active-players');
Route::get('/banlist', [PageController::class, 'banList'])->name('banlist');
Route::get('/support', [PageController::class, 'support'])->name('support');
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms-of-use', [PageController::class, 'terms'])->name('terms');
Route::get('/changelog', [PageController::class, 'changelog'])->name('changelog');
Route::get('/discord-rules', [PageController::class, 'discordRules'])->name('discord-rules');
Route::get('/media-rules', [PageController::class, 'mediaRules'])->name('media-rules');
Route::get('/staff-rules', [PageController::class, 'staffRules'])->name('staff-rules');
Route::get('/minecraft-rules', [PageController::class, 'minecraftRules'])->name('minecraft-rules');
Route::get('/scoreboard', [PageController::class, 'scoreboard'])->name('scoreboard');
Route::get('/profile', [PageController::class, 'profile'])->name('profile');
Route::get('/login', [PageController::class, 'login'])->name('player.login');

// ─── Payment ─────────────────────────────────────────────────────────────────

Route::prefix('payment')->group(function () {
    Route::post('paypal/create-order', [PaymentController::class, 'paypalCreateOrder'])->name('payment.paypal.create');
    Route::get('paypal/return', [PaymentController::class, 'paypalReturn'])->name('payment.paypal.return');
    Route::post('stripe/create-session', [PaymentController::class, 'stripeCreateSession'])->name('payment.stripe.create');
    Route::get('stripe/return', [PaymentController::class, 'stripeReturn'])->name('payment.stripe.return');
    Route::post('voucher/validate', [PaymentController::class, 'validateVoucher'])->name('payment.voucher.validate');
});

// ─── Admin ───────────────────────────────────────────────────────────────────

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [Admin\AuthController::class, 'loginForm'])->name('login');
    Route::post('login', [Admin\AuthController::class, 'login']);
    Route::post('logout', [Admin\AuthController::class, 'logout'])->name('logout');

    Route::middleware('auth.admin')->group(function () {
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

        // Categories & Products
        Route::resource('categories', Admin\CategoryController::class)->names('categories')->except(['show']);
        Route::post('categories/{category}/move', [Admin\CategoryController::class, 'move'])->name('categories.move');
        Route::resource('products', Admin\ProductController::class)->names('products')->except(['show']);
        Route::post('products/{product}/move', [Admin\ProductController::class, 'move'])->name('products.move');

        // Commands
        Route::get('commands', [Admin\CommandController::class, 'index'])->name('commands.index');
        Route::post('commands', [Admin\CommandController::class, 'store'])->name('commands.store');
        Route::put('commands/{command}', [Admin\CommandController::class, 'update'])->name('commands.update');
        Route::delete('commands/{command}', [Admin\CommandController::class, 'destroy'])->name('commands.destroy');

        // Command Queue
        Route::get('command-queue', [Admin\CommandQueueController::class, 'index'])->name('command-queue.index');
        Route::post('command-queue/{commandQueue}/executed', [Admin\CommandQueueController::class, 'markExecuted'])->name('command-queue.executed');
        Route::post('command-queue/{commandQueue}/failed', [Admin\CommandQueueController::class, 'markFailed'])->name('command-queue.failed');
        Route::post('command-queue/{commandQueue}/pending', [Admin\CommandQueueController::class, 'markPending'])->name('command-queue.pending');

        // Vouchers
        Route::resource('vouchers', Admin\VoucherController::class)->names('vouchers')->except(['show']);

        // Transactions
        Route::get('transactions', [Admin\TransactionController::class, 'index'])->name('transactions.index');
        Route::get('transactions/{transaction}', [Admin\TransactionController::class, 'show'])->name('transactions.show');
        Route::post('transactions/{transaction}/retry', [Admin\TransactionController::class, 'retryDispatch'])->name('transactions.retry');

        // Team
        Route::get('team', [Admin\TeamController::class, 'index'])->name('team.index');

        Route::post('team/categories', [Admin\TeamController::class, 'storeCategory'])->name('team.categories.store');
        Route::put('team/categories/{category}', [Admin\TeamController::class, 'updateCategory'])->name('team.categories.update');
        Route::delete('team/categories/{category}', [Admin\TeamController::class, 'destroyCategory'])->name('team.categories.destroy');
        Route::post('team/categories/{category}/move', [Admin\TeamController::class, 'moveCategory'])->name('team.categories.move');

        Route::post('team/members', [Admin\TeamController::class, 'storeMember'])->name('team.members.store');
        Route::put('team/members/{member}', [Admin\TeamController::class, 'updateMember'])->name('team.members.update');
        Route::delete('team/members/{member}', [Admin\TeamController::class, 'destroyMember'])->name('team.members.destroy');
        Route::post('team/members/{member}/move', [Admin\TeamController::class, 'moveMember'])->name('team.members.move');

        Route::post('team/ranks', [Admin\TeamController::class, 'storeRank'])->name('team.ranks.store');
        Route::put('team/ranks/{rank}', [Admin\TeamController::class, 'updateRank'])->name('team.ranks.update');
        Route::delete('team/ranks/{rank}', [Admin\TeamController::class, 'destroyRank'])->name('team.ranks.destroy');

        // Payment settings
        Route::get('settings/payment', [Admin\PaymentSettingsController::class, 'index'])->name('settings.payment');
        Route::post('settings/payment', [Admin\PaymentSettingsController::class, 'update'])->name('settings.payment.update');

        // Images
        Route::get('images', [Admin\ImageController::class, 'index'])->name('images.index');
        Route::post('images/upload', [Admin\ImageController::class, 'upload'])->name('images.upload');
        Route::delete('images/{filename}', [Admin\ImageController::class, 'destroy'])->name('images.destroy')->where('filename', '.+');
    });
});
