<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeamCategory;
use App\Models\TeamMember;
use App\Models\TeamRank;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TeamController extends Controller
{
    public function index()
    {
        $categories = TeamCategory::with(['members.rank'])->orderBy('SortOrder')->get();
        $ranks      = TeamRank::orderBy('Id')->get();
        return view('admin.team.index', compact('categories', 'ranks'));
    }

    // ─── Ranks (JSON) ────────────────────────────────────────────────────────

    public function storeRank(Request $request)
    {
        $data = $request->validate([
            'Name'     => 'required|string|max:100',
            'HexColor' => 'nullable|string|max:20',
        ]);
        $rank = TeamRank::create($data);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'rank' => $rank]);
        }
        return redirect()->route('admin.team.index')->with('success', 'Rank created.');
    }

    public function updateRank(Request $request, TeamRank $rank)
    {
        $data = $request->validate([
            'Name'     => 'required|string|max:100',
            'HexColor' => 'nullable|string|max:20',
        ]);
        $rank->update($data);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }
        return redirect()->route('admin.team.index')->with('success', 'Rank updated.');
    }

    public function destroyRank(TeamRank $rank)
    {
        $rank->delete();

        if (request()->expectsJson()) {
            return response()->json(['ok' => true]);
        }
        return redirect()->route('admin.team.index')->with('success', 'Rank deleted.');
    }

    // ─── Categories ──────────────────────────────────────────────────────────

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'Name'      => 'required|string|max:100',
            'SortOrder' => 'nullable|integer',
        ]);

        $max = TeamCategory::max('SortOrder') ?? 0;
        $data['SortOrder'] = $data['SortOrder'] ?? ($max + 1);
        $data['Id'] = (string) Str::uuid();

        TeamCategory::create($data);
        return redirect()->route('admin.team.index')->with('success', 'Category created.');
    }

    public function updateCategory(Request $request, TeamCategory $category)
    {
        $data = $request->validate([
            'Name'      => 'required|string|max:100',
            'SortOrder' => 'nullable|integer',
        ]);
        $category->update($data);
        return redirect()->route('admin.team.index')->with('success', 'Category updated.');
    }

    public function destroyCategory(TeamCategory $category)
    {
        $category->delete();
        return redirect()->route('admin.team.index')->with('success', 'Category deleted.');
    }

    public function moveCategory(Request $request, TeamCategory $category)
    {
        $direction  = $request->input('direction'); // 'up' | 'down'
        $categories = TeamCategory::orderBy('SortOrder')->get();
        $idx        = $categories->search(fn($c) => $c->Id === $category->Id);

        if ($direction === 'up' && $idx > 0) {
            $other = $categories[$idx - 1];
        } elseif ($direction === 'down' && $idx < $categories->count() - 1) {
            $other = $categories[$idx + 1];
        } else {
            return response()->json(['ok' => false, 'error' => 'Cannot move']);
        }

        [$category->SortOrder, $other->SortOrder] = [$other->SortOrder, $category->SortOrder];
        $category->save();
        $other->save();

        return response()->json(['ok' => true]);
    }

    // ─── Members ─────────────────────────────────────────────────────────────

    public function storeMember(Request $request)
    {
        $data = $request->validate([
            'MinecraftName'  => 'required|string|max:100',
            'TeamRankId'     => 'nullable|exists:teamranks,Id',
            'SkinUrl'        => 'nullable|url|max:500',
            'SortOrder'      => 'nullable|integer',
            'TeamCategoryId' => 'required|exists:teamcategories,Id',
        ]);

        $max = TeamMember::where('TeamCategoryId', $data['TeamCategoryId'])->max('SortOrder') ?? 0;
        $data['SortOrder'] = $data['SortOrder'] ?? ($max + 1);
        $data['Id'] = (string) Str::uuid();

        $member = TeamMember::create($data);
        $member->load('rank');

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'member' => $member, 'skinUrl' => $this->skinUrl($member)]);
        }
        return redirect()->route('admin.team.index')->with('success', 'Member added.');
    }

    public function updateMember(Request $request, TeamMember $member)
    {
        $data = $request->validate([
            'MinecraftName'  => 'required|string|max:100',
            'TeamRankId'     => 'nullable|exists:teamranks,Id',
            'SkinUrl'        => 'nullable|url|max:500',
            'SortOrder'      => 'nullable|integer',
            'TeamCategoryId' => 'required|exists:teamcategories,Id',
        ]);
        $member->update($data);
        $member->load('rank');

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'member' => $member, 'skinUrl' => $this->skinUrl($member)]);
        }
        return redirect()->route('admin.team.index')->with('success', 'Member updated.');
    }

    public function destroyMember(TeamMember $member)
    {
        $member->delete();

        if (request()->expectsJson()) {
            return response()->json(['ok' => true]);
        }
        return redirect()->route('admin.team.index')->with('success', 'Member removed.');
    }

    public function moveMember(Request $request, TeamMember $member)
    {
        $direction = $request->input('direction');
        $siblings  = TeamMember::where('TeamCategoryId', $member->TeamCategoryId)
                                ->orderBy('SortOrder')->get();
        $idx = $siblings->search(fn($m) => $m->Id === $member->Id);

        if ($direction === 'up' && $idx > 0) {
            $other = $siblings[$idx - 1];
        } elseif ($direction === 'down' && $idx < $siblings->count() - 1) {
            $other = $siblings[$idx + 1];
        } else {
            return response()->json(['ok' => false, 'error' => 'Cannot move']);
        }

        [$member->SortOrder, $other->SortOrder] = [$other->SortOrder, $member->SortOrder];
        $member->save();
        $other->save();

        return response()->json(['ok' => true]);
    }

    private function skinUrl(TeamMember $member): string
    {
        if (!empty($member->SkinUrl)) return $member->SkinUrl;
        $name = empty($member->MinecraftName) ? 'steve' : $member->MinecraftName;
        return 'https://minotar.net/helm/' . urlencode($name) . '/48';
    }
}
