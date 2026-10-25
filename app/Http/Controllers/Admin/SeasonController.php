<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SeasonController extends Controller
{
    public const OPTIONS = [
        'auto'      => 'Automatic (October: Halloween, December: Christmas)',
        'halloween' => 'Halloween',
        'christmas' => 'Christmas',
        'none'      => 'None (default look)',
    ];

    public function index()
    {
        $season = SiteSetting::get('season', 'auto');
        return view('admin.settings.season', ['season' => $season, 'options' => self::OPTIONS]);
    }

    public function update(Request $request)
    {
        $data = $request->validate(['season' => ['required', 'in:' . implode(',', array_keys(self::OPTIONS))]]);
        SiteSetting::put('season', $data['season']);

        return back()->with('success', 'Season saved.');
    }
}
