@extends('layouts.admin')
@section('title', 'Team Management')
@section('content')

{{-- ── Ranks ─────────────────────────────────────────────────────────── --}}
<div class="admin-card" style="margin-bottom:20px;" id="ranks-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title"><i class="bi bi-award-fill"></i> Team Ranks</h2>
    </div>

    <div id="ranks-list" style="display:flex;flex-direction:column;gap:8px;margin-bottom:14px;">
        @foreach($ranks as $rank)
        <div class="rank-row" data-rank-id="{{ $rank->Id }}"
             style="display:flex;align-items:center;gap:8px;background:#1a1a1a;border:1px solid #2a2a2a;border-radius:8px;padding:8px 12px;">
            <input type="color" class="rank-color-input"
                   value="{{ $rank->HexColor ?: '#888888' }}"
                   style="width:36px;height:32px;padding:2px;background:none;border:1px solid #333;border-radius:4px;cursor:pointer;"
                   oninput="updateRankPreview(this)">
            <input type="text" class="rank-name-input"
                   value="{{ $rank->Name }}"
                   placeholder="Rank name"
                   style="flex:1;min-width:0;background:#111;border:1px solid #333;border-radius:6px;padding:6px 10px;color:#ccc;">
            <span class="rank-badge-preview"
                  style="font-size:12px;font-weight:700;padding:3px 10px;border-radius:4px;background:#1a1a1a;color:{{ $rank->HexColor ?: '#888' }};border:1px solid {{ $rank->HexColor ?: '#555' }};white-space:nowrap;">
                {{ $rank->Name }}
            </span>
            <button class="btn-admin btn-admin-secondary" style="padding:6px 12px;font-size:12px;"
                    onclick="saveRank({{ $rank->Id }}, this)">
                <i class="bi bi-floppy"></i> Save
            </button>
            <button class="btn-admin btn-admin-danger" style="padding:6px 12px;font-size:12px;"
                    onclick="deleteRank({{ $rank->Id }}, this)">
                <i class="bi bi-trash"></i>
            </button>
        </div>
        @endforeach
    </div>

    {{-- Add new rank --}}
    <div style="display:flex;align-items:center;gap:8px;background:#111;border:1px dashed #333;border-radius:8px;padding:10px 12px;">
        <input type="color" id="new-rank-color" value="#888888"
               style="width:36px;height:32px;padding:2px;background:none;border:1px solid #333;border-radius:4px;cursor:pointer;">
        <input type="text" id="new-rank-name" placeholder="New rank name…"
               style="flex:1;background:#1a1a1a;border:1px solid #333;border-radius:6px;padding:6px 10px;color:#ccc;">
        <button class="btn-admin btn-admin-primary" onclick="addRank()">
            <i class="bi bi-plus-lg"></i> Add Rank
        </button>
    </div>
</div>

{{-- ── Add Category ─────────────────────────────────────────────────── --}}
<div style="margin-bottom:20px;display:flex;gap:10px;align-items:flex-start;flex-wrap:wrap;">
    <div id="add-cat-form" style="display:flex;gap:8px;align-items:center;background:#1a1a1a;border:1px solid #2a2a2a;border-radius:8px;padding:10px 14px;">
        <strong style="color:#ccc;white-space:nowrap;"><i class="bi bi-plus-lg"></i> Add Category</strong>
        <form method="POST" action="{{ route('admin.team.categories.store') }}" style="display:flex;gap:8px;">
            @csrf
            <input type="text" name="Name" placeholder="Category name…" required
                   style="background:#111;border:1px solid #333;border-radius:6px;padding:6px 12px;color:#ccc;min-width:180px;">
            <button type="submit" class="btn-admin btn-admin-primary">Create</button>
        </form>
    </div>
</div>

{{-- ── Category Cards ───────────────────────────────────────────────── --}}
<div id="categories-container">
@foreach($categories as $catIdx => $category)
<div class="admin-card category-card" id="cat-{{ $category->Id }}" data-cat-id="{{ $category->Id }}"
     style="margin-bottom:20px;">
    <div class="admin-card-header" style="flex-wrap:wrap;gap:8px;">
        {{-- Reorder buttons --}}
        <div style="display:flex;gap:4px;">
            <button class="btn-admin btn-admin-secondary" style="padding:4px 10px;"
                    onclick="moveCategory('{{ $category->Id }}', 'up')"
                    {{ $catIdx === 0 ? 'disabled' : '' }} title="Move up">▲</button>
            <button class="btn-admin btn-admin-secondary" style="padding:4px 10px;"
                    onclick="moveCategory('{{ $category->Id }}', 'down')"
                    {{ $catIdx === $categories->count() - 1 ? 'disabled' : '' }} title="Move down">▼</button>
        </div>

        {{-- Category name inline edit --}}
        <form method="POST" action="{{ route('admin.team.categories.update', $category) }}"
              style="display:flex;gap:6px;flex:1;min-width:0;">
            @csrf @method('PUT')
            <input type="text" name="Name" value="{{ $category->Name }}" required
                   style="flex:1;min-width:0;background:#111;border:1px solid #333;border-radius:6px;padding:6px 12px;color:#fff;font-size:15px;font-weight:600;">
            <button type="submit" class="btn-admin btn-admin-secondary" style="padding:6px 12px;font-size:12px;">
                <i class="bi bi-floppy"></i> Rename
            </button>
        </form>

        {{-- Delete category --}}
        <form method="POST" action="{{ route('admin.team.categories.destroy', $category) }}"
              onsubmit="return confirm('Delete category "{{ addslashes($category->Name) }}" and all its members?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-admin btn-admin-danger"><i class="bi bi-trash"></i> Delete</button>
        </form>
    </div>

    {{-- Members grid --}}
    <div class="members-grid" id="members-{{ $category->Id }}"
         style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:10px;margin-top:16px;">
        @php $sortedMembers = $category->members->sortBy('SortOrder'); @endphp
        @foreach($sortedMembers as $mIdx => $member)
        <div class="member-card" id="member-{{ $member->Id }}"
             style="background:#111;border:1px solid #2a2a2a;border-radius:8px;padding:10px;">
            <div style="display:flex;gap:8px;align-items:flex-start;margin-bottom:8px;">
                {{-- Skin --}}
                <img src="{{ $member->SkinUrl ?: 'https://minotar.net/helm/'.urlencode($member->MinecraftName ?: 'steve').'/48' }}"
                     class="member-skin" style="width:48px;height:48px;object-fit:cover;border-radius:4px;flex-shrink:0;image-rendering:pixelated;">
                {{-- Name + Rank --}}
                <div style="flex:1;min-width:0;">
                    <input type="text" class="member-name-input"
                           value="{{ $member->MinecraftName }}"
                           placeholder="Minecraft IGN"
                           style="width:100%;background:#1a1a1a;border:1px solid #333;border-radius:6px;padding:5px 8px;color:#fff;font-weight:600;font-size:13px;box-sizing:border-box;margin-bottom:5px;"
                           oninput="refreshSkin(this)">
                    <select class="member-rank-select"
                            style="width:100%;background:#1a1a1a;border:1px solid #333;border-radius:6px;padding:5px 8px;color:#ccc;font-size:12px;box-sizing:border-box;">
                        <option value="">— No rank —</option>
                        @foreach($ranks as $r)
                        <option value="{{ $r->Id }}" {{ $member->TeamRankId == $r->Id ? 'selected' : '' }}
                                style="color:{{ $r->HexColor ?: 'inherit' }}">{{ $r->Name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            {{-- Rank badge --}}
            <div class="member-rank-badge" style="margin-bottom:8px;min-height:20px;">
                @if($member->rank)
                <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:4px;background:#1a1a1a;color:{{ $member->rank->HexColor ?: '#888' }};border:1px solid {{ $member->rank->HexColor ?: '#555' }};">{{ $member->rank->Name }}</span>
                @endif
            </div>
            {{-- Actions --}}
            <div style="display:flex;gap:6px;align-items:center;">
                <button class="btn-admin btn-admin-secondary" style="padding:4px 8px;font-size:11px;" title="Move up"
                        onclick="moveMember('{{ $member->Id }}', 'up', this)" {{ $mIdx === 0 ? 'disabled' : '' }}>▲</button>
                <button class="btn-admin btn-admin-secondary" style="padding:4px 8px;font-size:11px;" title="Move down"
                        onclick="moveMember('{{ $member->Id }}', 'down', this)" {{ $mIdx === $sortedMembers->count() - 1 ? 'disabled' : '' }}>▼</button>
                <button class="btn-admin btn-admin-secondary" style="padding:4px 10px;font-size:11px;flex:1;"
                        onclick="saveMember('{{ $member->Id }}', this)">
                    <i class="bi bi-floppy"></i> Save
                </button>
                <button class="btn-admin btn-admin-danger" style="padding:4px 10px;font-size:11px;"
                        onclick="deleteMember('{{ $member->Id }}', this)">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
        @endforeach
    </div>

    @if($category->members->isEmpty())
    <p style="color:#555;margin-top:14px;font-size:13px;">No members yet.</p>
    @endif

    {{-- Add Member form --}}
    <div style="margin-top:14px;padding-top:14px;border-top:1px solid #222;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <img src="https://minotar.net/helm/steve/32" id="new-skin-{{ $category->Id }}"
             style="width:32px;height:32px;border-radius:4px;image-rendering:pixelated;flex-shrink:0;">
        <input type="text" id="new-member-name-{{ $category->Id }}" placeholder="Minecraft IGN…"
               style="flex:1;min-width:130px;background:#111;border:1px solid #333;border-radius:6px;padding:6px 10px;color:#fff;font-size:13px;"
               oninput="previewNewSkin(this, '{{ $category->Id }}')">
        <select id="new-member-rank-{{ $category->Id }}"
                style="background:#111;border:1px solid #333;border-radius:6px;padding:6px 10px;color:#ccc;font-size:12px;">
            <option value="">— No rank —</option>
            @foreach($ranks as $r)
            <option value="{{ $r->Id }}">{{ $r->Name }}</option>
            @endforeach
        </select>
        <button class="btn-admin btn-admin-primary" style="padding:6px 14px;font-size:12px;"
                onclick="addMember('{{ $category->Id }}')">
            <i class="bi bi-person-plus-fill"></i> Add
        </button>
    </div>
</div>
@endforeach
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const BASE_RANKS    = '{{ url('/admin/team/ranks') }}';
const BASE_CATS     = '{{ url('/admin/team/categories') }}';
const BASE_MEMBERS  = '{{ url('/admin/team/members') }}';

function json(url, method, body) {
    return fetch(url, {
        method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: body ? JSON.stringify(body) : undefined,
    }).then(r => r.json());
}

// ── Ranks ──────────────────────────────────────────────────────────────────

function updateRankPreview(colorInput) {
    var row   = colorInput.closest('.rank-row');
    var nameEl = row.querySelector('.rank-name-input');
    var badge  = row.querySelector('.rank-badge-preview');
    var color  = colorInput.value;
    badge.style.color  = color;
    badge.style.borderColor = color;
    badge.textContent = nameEl.value || '…';
}

function saveRank(id, btn) {
    var row   = btn.closest('.rank-row');
    var name  = row.querySelector('.rank-name-input').value.trim();
    var color = row.querySelector('.rank-color-input').value;
    if (!name) return;

    btn.disabled = true;
    json(BASE_RANKS + '/' + id, 'PUT', { Name: name, HexColor: color })
        .then(d => {
            btn.disabled = false;
            if (d.ok) {
                var badge = row.querySelector('.rank-badge-preview');
                badge.style.color = color;
                badge.style.borderColor = color;
                badge.textContent = name;
                flashBtn(btn, '✓');
            }
        }).catch(() => { btn.disabled = false; });
}

function deleteRank(id, btn) {
    if (!confirm('Delete this rank?')) return;
    var row = btn.closest('.rank-row');
    json(BASE_RANKS + '/' + id, 'DELETE')
        .then(d => { if (d.ok) row.remove(); });
}

function addRank() {
    var nameInput  = document.getElementById('new-rank-name');
    var colorInput = document.getElementById('new-rank-color');
    var name  = nameInput.value.trim();
    var color = colorInput.value;
    if (!name) { nameInput.focus(); return; }

    fetch(BASE_RANKS, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ Name: name, HexColor: color }),
    }).then(r => r.json()).then(d => {
        if (!d.ok || !d.rank) return;
        var rank = d.rank;
        var html = `<div class="rank-row" data-rank-id="${rank.Id}"
             style="display:flex;align-items:center;gap:8px;background:#1a1a1a;border:1px solid #2a2a2a;border-radius:8px;padding:8px 12px;">
            <input type="color" class="rank-color-input"
                   value="${rank.HexColor || '#888888'}"
                   style="width:36px;height:32px;padding:2px;background:none;border:1px solid #333;border-radius:4px;cursor:pointer;"
                   oninput="updateRankPreview(this)">
            <input type="text" class="rank-name-input"
                   value="${escHtml(rank.Name)}"
                   placeholder="Rank name"
                   style="flex:1;min-width:0;background:#111;border:1px solid #333;border-radius:6px;padding:6px 10px;color:#ccc;">
            <span class="rank-badge-preview"
                  style="font-size:12px;font-weight:700;padding:3px 10px;border-radius:4px;background:#1a1a1a;color:${rank.HexColor || '#888'};border:1px solid ${rank.HexColor || '#555'};white-space:nowrap;">
                ${escHtml(rank.Name)}
            </span>
            <button class="btn-admin btn-admin-secondary" style="padding:6px 12px;font-size:12px;"
                    onclick="saveRank(${rank.Id}, this)">
                <i class="bi bi-floppy"></i> Save
            </button>
            <button class="btn-admin btn-admin-danger" style="padding:6px 12px;font-size:12px;"
                    onclick="deleteRank(${rank.Id}, this)">
                <i class="bi bi-trash"></i>
            </button>
        </div>`;
        document.getElementById('ranks-list').insertAdjacentHTML('beforeend', html);

        // Also append the new rank option to all category member dropdowns
        document.querySelectorAll('.member-rank-select, [id^="new-member-rank-"]').forEach(sel => {
            var opt = document.createElement('option');
            opt.value = rank.Id;
            opt.textContent = rank.Name;
            sel.appendChild(opt);
        });

        nameInput.value  = '';
        colorInput.value = '#888888';
    });
}

// ── Categories ─────────────────────────────────────────────────────────────

function moveCategory(id, dir) {
    json(BASE_CATS + '/' + id + '/move', 'POST', { direction: dir })
        .then(d => { if (d.ok) location.reload(); });
}

// ── Members ────────────────────────────────────────────────────────────────

function saveMember(id, btn) {
    var card  = btn.closest('.member-card');
    var name  = card.querySelector('.member-name-input').value.trim();
    var rankId = card.querySelector('.member-rank-select').value;
    if (!name) return;

    btn.disabled = true;
    json(BASE_MEMBERS + '/' + id, 'PUT', {
        MinecraftName:  name,
        TeamRankId:     rankId || null,
        TeamCategoryId: card.closest('.category-card').dataset.catId,
    }).then(d => {
        btn.disabled = false;
        if (!d.ok) return;
        var skin = card.querySelector('.member-skin');
        skin.src = d.member.SkinUrl || 'https://minotar.net/helm/' + encodeURIComponent(name) + '/48';
        updateMemberBadge(card, d.member, d.member.rank);
        flashBtn(btn, '✓');
    }).catch(() => { btn.disabled = false; });
}

function deleteMember(id, btn) {
    if (!confirm('Remove this member?')) return;
    var card = btn.closest('.member-card');
    json(BASE_MEMBERS + '/' + id, 'DELETE')
        .then(d => { if (d.ok) card.remove(); });
}

function moveMember(id, dir, btn) {
    var card = btn.closest('.member-card');
    var grid = card.closest('.members-grid');
    json(BASE_MEMBERS + '/' + id + '/move', 'POST', { direction: dir })
        .then(d => {
            if (!d.ok) return;
            var cards = Array.from(grid.querySelectorAll('.member-card'));
            var idx   = cards.indexOf(card);
            if (dir === 'up' && idx > 0) {
                grid.insertBefore(card, cards[idx - 1]);
            } else if (dir === 'down' && idx < cards.length - 1) {
                grid.insertBefore(cards[idx + 1], card);
            }
        });
}

function addMember(catId) {
    var nameInput = document.getElementById('new-member-name-' + catId);
    var rankSel   = document.getElementById('new-member-rank-' + catId);
    var name      = nameInput.value.trim();
    if (!name) { nameInput.focus(); return; }

    fetch(BASE_MEMBERS, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
            MinecraftName:  name,
            TeamRankId:     rankSel.value || null,
            TeamCategoryId: catId,
        }),
    }).then(r => r.json()).then(d => {
        if (!d.ok) return;
        var m = d.member;
        var skinSrc = d.skinUrl || 'https://minotar.net/helm/' + encodeURIComponent(name) + '/48';
        var rankOpts = Array.from(document.querySelectorAll('.member-rank-select')[0]?.options || [])
            .map(o => `<option value="${escHtml(o.value)}"${m.TeamRankId == o.value ? ' selected' : ''}>${escHtml(o.text)}</option>`)
            .join('');

        var rankBadge = '';
        if (m.rank) {
            rankBadge = `<span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:4px;background:#1a1a1a;color:${m.rank.HexColor||'#888'};border:1px solid ${m.rank.HexColor||'#555'};">${escHtml(m.rank.Name)}</span>`;
        }

        var html = `<div class="member-card" id="member-${m.Id}"
             style="background:#111;border:1px solid #2a2a2a;border-radius:8px;padding:10px;">
            <div style="display:flex;gap:8px;align-items:flex-start;margin-bottom:8px;">
                <img src="${skinSrc}" class="member-skin"
                     style="width:48px;height:48px;object-fit:cover;border-radius:4px;flex-shrink:0;image-rendering:pixelated;">
                <div style="flex:1;min-width:0;">
                    <input type="text" class="member-name-input" value="${escHtml(m.MinecraftName)}"
                           placeholder="Minecraft IGN"
                           style="width:100%;background:#1a1a1a;border:1px solid #333;border-radius:6px;padding:5px 8px;color:#fff;font-weight:600;font-size:13px;box-sizing:border-box;margin-bottom:5px;"
                           oninput="refreshSkin(this)">
                    <select class="member-rank-select"
                            style="width:100%;background:#1a1a1a;border:1px solid #333;border-radius:6px;padding:5px 8px;color:#ccc;font-size:12px;box-sizing:border-box;">
                        <option value="">— No rank —</option>
                        ${rankOpts}
                    </select>
                </div>
            </div>
            <div class="member-rank-badge" style="margin-bottom:8px;min-height:20px;">${rankBadge}</div>
            <div style="display:flex;gap:6px;align-items:center;">
                <button class="btn-admin btn-admin-secondary" style="padding:4px 8px;font-size:11px;" title="Move up"
                        onclick="moveMember('${m.Id}', 'up', this)">▲</button>
                <button class="btn-admin btn-admin-secondary" style="padding:4px 8px;font-size:11px;" title="Move down"
                        onclick="moveMember('${m.Id}', 'down', this)">▼</button>
                <button class="btn-admin btn-admin-secondary" style="padding:4px 10px;font-size:11px;flex:1;"
                        onclick="saveMember('${m.Id}', this)">
                    <i class="bi bi-floppy"></i> Save
                </button>
                <button class="btn-admin btn-admin-danger" style="padding:4px 10px;font-size:11px;"
                        onclick="deleteMember('${m.Id}', this)">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>`;

        var grid = document.getElementById('members-' + catId);
        grid.insertAdjacentHTML('beforeend', html);
        nameInput.value = '';
        rankSel.value   = '';
        document.getElementById('new-skin-' + catId).src = 'https://minotar.net/helm/steve/32';
    });
}

// ── Helpers ────────────────────────────────────────────────────────────────

function refreshSkin(input) {
    var card = input.closest('.member-card');
    if (!card) return;
    var name = input.value.trim() || 'steve';
    clearTimeout(input._skinTimer);
    input._skinTimer = setTimeout(() => {
        card.querySelector('.member-skin').src = 'https://minotar.net/helm/' + encodeURIComponent(name) + '/48';
    }, 600);
}

function previewNewSkin(input, catId) {
    var name = input.value.trim() || 'steve';
    clearTimeout(input._skinTimer);
    input._skinTimer = setTimeout(() => {
        document.getElementById('new-skin-' + catId).src = 'https://minotar.net/helm/' + encodeURIComponent(name) + '/32';
    }, 600);
}

function updateMemberBadge(card, member, rank) {
    var badge = card.querySelector('.member-rank-badge');
    if (rank) {
        badge.innerHTML = `<span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:4px;background:#1a1a1a;color:${rank.HexColor||'#888'};border:1px solid ${rank.HexColor||'#555'};">${escHtml(rank.Name)}</span>`;
    } else {
        badge.innerHTML = '';
    }
}

function flashBtn(btn, text) {
    var orig = btn.innerHTML;
    btn.innerHTML = text;
    setTimeout(() => { btn.innerHTML = orig; }, 1200);
}

function escHtml(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

@endsection
