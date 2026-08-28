<?php

namespace App\Http\Controllers;

use App\Models\ActivePlayer;
use App\Models\Ban;
use App\Models\PaymentTransaction;
use App\Models\TeamCategory;
use App\Models\TeamRank;
use App\Services\DiscordService;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function login() { return view('pages.login'); }
    public function about() { return view('pages.about'); }
    public function join(Request $r) { return view('pages.join', ['edition' => $r->query('edition', 'java')]); }
    public function news(DiscordService $discord)
    {
        $announcements = $discord->getAnnouncements(100);
        return view('pages.news', compact('announcements'));
    }
    public function voteForUs() { return view('pages.vote-for-us'); }
    public function activePlayers()
    {
        $players = ActivePlayer::orderBy('username')->get();
        $rankColors = TeamRank::all()->keyBy(fn($r) => strtolower($r->Name))->map(fn($r) => $r->HexColor);
        return view('pages.active-players', compact('players', 'rankColors'));
    }

    public function banList()
    {
        $bans = Ban::orderBy('player')->get();
        return view('pages.banlist', compact('bans'));
    }
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
            $query = PaymentTransaction::selectRaw('minecraft_username, SUM(amount) as total_paid, COUNT(*) as transaction_count, MAX(created_at) as last_payment')
                ->where(function ($q) {
                    $q->where('status', 'like', '%captured%')
                      ->orWhere('status', 'like', '%succeeded%')
                      ->orWhere('status', 'like', '%paid%');
                })
                ->whereNotIn('minecraft_username', ['Taneq', 'webdev', '', '<unknown>'])
                ->where('minecraft_username', '!=', '')
                ->where('minecraft_username', 'not like', '.%');

            match ($period) {
                'today' => $query->whereDate('created_at', now()->toDateString()),
                'week'  => $query->where('created_at', '>=', now()->startOfWeek()),
                'month' => $query->where('created_at', '>=', now()->startOfMonth()),
                default => null,
            };

            $entries = $query->groupBy('minecraft_username')
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
        $activePlayer = null;
        $rankHex      = null;

        if ($username) {
            try {
                $transactions = PaymentTransaction::where('minecraft_username', $username)
                    ->orderByDesc('created_at')
                    ->limit(50)
                    ->get();
            } catch (\Throwable) {}

            try {
                $activePlayer = ActivePlayer::whereRaw('LOWER(username) = ?', [strtolower($username)])->first();
                if ($activePlayer?->rank) {
                    $cleanRank = strtolower(trim(preg_replace('/&[0-9a-fk-or]/i', '', $activePlayer->rank)));
                    $rank = TeamRank::whereRaw('LOWER(Name) = ?', [$cleanRank])->first();
                    $rankHex = $rank?->HexColor;
                }
            } catch (\Throwable) {}
        }

        return view('pages.profile', compact('username', 'transactions', 'activePlayer', 'rankHex'));
    }
}
