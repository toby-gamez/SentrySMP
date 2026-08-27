<?php

namespace App\Http\Controllers;

use App\Models\Category;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('sort_order')->get();
        return view('shop.home', compact('categories'));
    }
}
