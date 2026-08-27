<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\DiscordService;

class HomeController extends Controller
{
    public function index(DiscordService $discord)
    {
        $categories    = Category::orderBy('sort_order')->get();
        $announcements = $discord->getAnnouncements(3);

        return view('shop.home', compact('categories', 'announcements'));
    }
}
