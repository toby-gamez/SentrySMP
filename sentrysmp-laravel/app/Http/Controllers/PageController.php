<?php

namespace App\Http\Controllers;

use App\Models\PaymentTransaction;
use App\Models\TeamCategory;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function login() { return view('pages.login'); }
    public function about() { return view('pages.about'); }
    public function join(Request $r) { return view('pages.join', ['edition' => $r->query('edition', 'java')]); }
    public function news() { return view('pages.news'); }
    public function voteForUs() { return view('pages.vote-for-us'); }
    public function activePlayers() { return view('pages.active-players'); }
    public function banList() { return view('pages.banlist'); }
    public function support() { return view('pages.support'); }
    public function privacy() { return view('pages.privacy'); }
    public function terms() { return view('pages.terms'); }
    public function changelog() { return view('pages.changelog'); }
    public function discordRules() { return view('pages.discord-rules'); }
    public function mediaRules() { return view('pages.media-rules'); }
    public function staffRules() { return view('pages.staff-rules'); }
    public function minecraftRules() { return view('pages.minecraft-rules'); }

    public function ourTeam()
    {
        try {
            $categories = TeamCategory::with(['members.rank'])
                ->orderBy('SortOrder')
                ->get();
        } catch (\Throwable) {
            $categories = collect();
        }
        return view('pages.our-team', compact('categories'));
    }

    public function scoreboard(Request $request)
    {
        $period = $request->query('period', 'alltime');
        try {
            $query = PaymentTransaction::selectRaw('MinecraftUsername, SUM(Amount) as total_paid, COUNT(*) as transaction_count, MAX(CreatedAt) as last_payment')
                ->where(function ($q) {
                    $q->where('Status', 'like', '%captured%')
                      ->orWhere('Status', 'like', '%succeeded%')
                      ->orWhere('Status', 'like', '%paid%');
                })
                ->whereNotIn('MinecraftUsername', ['Taneq', 'webdev', '', '<unknown>'])
                ->where('MinecraftUsername', '!=', '')
                ->where('MinecraftUsername', 'not like', '.%');

            match ($period) {
                'today' => $query->whereDate('CreatedAt', now()->toDateString()),
                'week'  => $query->where('CreatedAt', '>=', now()->startOfWeek()),
                'month' => $query->where('CreatedAt', '>=', now()->startOfMonth()),
                default => null,
            };

            $entries = $query->groupBy('MinecraftUsername')
                ->orderByDesc('total_paid')
                ->limit(100)
                ->get();
        } catch (\Throwable) {
            $entries = collect();
        }
        return view('pages.scoreboard', compact('entries', 'period'));
    }

    public function profile(Request $request)
    {
        $username     = $request->query('username', '');
        $transactions = collect();

        if ($username) {
            try {
                $transactions = PaymentTransaction::where('MinecraftUsername', $username)
                    ->orderByDesc('CreatedAt')
                    ->limit(50)
                    ->get();
            } catch (\Throwable) {}
        }

        return view('pages.profile', compact('username', 'transactions'));
    }
}
