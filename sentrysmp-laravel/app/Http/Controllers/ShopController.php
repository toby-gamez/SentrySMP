<?php

namespace App\Http\Controllers;

use App\Models\BattlePass;
use App\Models\Bundle;
use App\Models\Coin;
use App\Models\Key;
use App\Models\Other;
use App\Models\PaymentSettings;
use App\Models\Rank;

class ShopController extends Controller
{
    public function keys()
    {
        $items = Key::with('server')->get();
        return view('shop.keys', ['items' => $items, 'type' => 'Key']);
    }

    public function ranks()
    {
        $items = Rank::all();
        return view('shop.ranks', ['items' => $items, 'type' => 'Rank']);
    }

    public function bundles()
    {
        $items = Bundle::with('server')->get();
        return view('shop.bundles', ['items' => $items, 'type' => 'Bundle']);
    }

    public function coins()
    {
        $items = Coin::with('server')->get();
        return view('shop.coins', ['items' => $items, 'type' => 'Coin']);
    }

    public function battlepasses()
    {
        $items = BattlePass::with('server')->get();
        return view('shop.battlepasses', ['items' => $items, 'type' => 'BattlePass']);
    }

    public function other()
    {
        $items = Other::with('server')->get();
        return view('shop.other', ['items' => $items, 'type' => 'Other']);
    }

    public function checkout()
    {
        $settings = PaymentSettings::current();
        $status   = request('status');
        return view('shop.checkout', compact('settings', 'status'));
    }
}
