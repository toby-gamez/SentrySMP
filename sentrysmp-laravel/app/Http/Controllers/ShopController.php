<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\PaymentSettings;

class ShopController extends Controller
{
    public function category(Category $category)
    {
        $items = $category->products()->orderBy('sort_order')->get();
        return view('shop.category', compact('category', 'items'));
    }

    public function checkout()
    {
        $settings = PaymentSettings::current();
        $status   = request('status');
        return view('shop.checkout', compact('settings', 'status'));
    }
}
