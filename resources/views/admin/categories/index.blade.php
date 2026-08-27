{{--
    Category List
    Variables: $items (Collection<Category> with products_count, ordered by sort_order)
    Rows are reorderable via up/down buttons that POST to admin.categories.move.
    Clicking a row navigates to the edit page; the Actions column is exempt from that click.
--}}
@extends('layouts.admin')
@section('title', 'Categories')
@section('content')
<style>
.sort-btn { background:none;border:1px solid #444;color:#aaa;border-radius:4px;width:28px;height:28px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;padding:0;transition:background .15s,color .15s; }
.sort-btn:hover:not(:disabled) { background:#333;color:#fff; }
.sort-btn:disabled { opacity:.3;cursor:default; }
.admin-table tbody tr { transition:none; }
.admin-table tbody tr.row-moving { position:relative;z-index:2; }
</style>
{{-- ── Page Guide ─────────────────────────────────────────────────────── --}}
<div style="background:#0b1929;border:1px solid #1e3a5f;border-left:3px solid #3b82f6;border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:13px;">
    <div onclick="var n=this.nextElementSibling;n.hidden=!n.hidden;this.querySelector('.pg-ch').style.transform=n.hidden?'':'rotate(180deg)'"
         style="display:flex;align-items:center;gap:8px;cursor:pointer;user-select:none;">
        <i class="bi bi-book" style="color:#60a5fa;font-size:14px;"></i>
        <strong style="color:#93c5fd;">Category Management Guide</strong>
        <i class="bi bi-chevron-down pg-ch" style="color:#60a5fa;margin-left:auto;font-size:12px;transition:transform .2s;"></i>
    </div>
    <div hidden style="margin-top:12px;color:#94a3b8;line-height:1.75;">
        <ul style="margin:0;padding-left:18px;">
            <li>Categories group your shop products (e.g. <em>Keys</em>, <em>Ranks</em>, <em>Bundles</em>). Players see them in the order displayed here.</li>
            <li><strong style="color:#c4d4e8;">Reordering</strong> — use the ▲/▼ buttons to change display order. The position updates immediately via the server; no page reload needed.</li>
            <li><strong style="color:#c4d4e8;">Click any row</strong> to open that category's edit page.</li>
            <li><strong style="color:#c4d4e8;">Deleting</strong> a category also <em>permanently deletes all products inside it</em> — this cannot be undone.</li>
            <li>Each category gets a <strong style="color:#c4d4e8;">colour</strong> shown as an accent on the shop page and a <strong style="color:#c4d4e8;">slug</strong> used in product image folder names.</li>
        </ul>
    </div>
</div>
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">Categories ({{ $items->count() }})</h2>
        <a href="{{ route('admin.categories.create') }}" class="btn-admin btn-admin-primary"><i class="bi bi-plus-lg"></i> Add Category</a>
    </div>
    @if($items->isEmpty())
        <p style="color:#666;">No categories yet.</p>
    @else
    <table class="admin-table" id="categories-table">
        <thead><tr><th style="width:40px;"></th><th>Color</th><th>Image</th><th>Name</th><th>Slug</th><th>Products</th><th>Actions</th></tr></thead>
        <tbody>
            @foreach($items as $item)
            <tr data-id="{{ $item->id }}" data-move-url="{{ route('admin.categories.move', $item) }}"
                class="admin-table-row-link" data-href="{{ route('admin.categories.edit', $item) }}">
                <td style="white-space:nowrap;padding-right:0;" class="no-row-click">
                    <button class="sort-btn move-up" title="Move up" {{ $loop->first ? 'disabled' : '' }}><i class="bi bi-chevron-up"></i></button>
                    <button class="sort-btn move-down" style="margin-top:2px;" title="Move down" {{ $loop->last ? 'disabled' : '' }}><i class="bi bi-chevron-down"></i></button>
                </td>
                <td>
                    <div style="width:36px;height:36px;border-radius:6px;background:{{ $item->color }};border:2px solid #333;"></div>
                </td>
                <td>
                    @if($item->image)
                        <img src="{{ $item->image }}" alt="{{ $item->name }}" style="width:48px;height:48px;object-fit:cover;border-radius:6px;">
                    @else
                        <span style="color:#555;">—</span>
                    @endif
                </td>
                <td style="color:white;font-weight:600;">{{ $item->name }}</td>
                <td style="color:#888;font-size:13px;">{{ $item->slug }}</td>
                <td>{{ $item->products_count }}</td>
                <td class="no-row-click">
                    <a href="{{ route('admin.categories.edit', $item) }}" class="btn-admin btn-admin-secondary"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('admin.categories.destroy', $item) }}" style="display:inline;" onsubmit="return confirm('Delete this category and all its products?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-admin btn-admin-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    function updateButtonStates(tbody) {
        const rows = [...tbody.querySelectorAll('tr')];
        rows.forEach((row, idx) => {
            row.querySelector('.move-up').disabled = idx === 0;
            row.querySelector('.move-down').disabled = idx === rows.length - 1;
        });
    }

    function animateSwap(rowA, rowB, direction, tbody) {
        const hA = rowA.offsetHeight;
        const hB = rowB.offsetHeight;
        const dy = direction === 'up' ? -hB : hB;

        rowA.style.transition = 'transform 0.22s cubic-bezier(.4,0,.2,1)';
        rowB.style.transition = 'transform 0.22s cubic-bezier(.4,0,.2,1)';
        rowA.style.transform = `translateY(${dy}px)`;
        rowB.style.transform = `translateY(${-dy}px)`;

        setTimeout(() => {
            rowA.style.transition = '';
            rowB.style.transition = '';
            rowA.style.transform = '';
            rowB.style.transform = '';

            if (direction === 'up') {
                tbody.insertBefore(rowA, rowB);
            } else {
                tbody.insertBefore(rowB, rowA);
            }

            updateButtonStates(tbody);
        }, 230);
    }

    document.querySelectorAll('#categories-table .move-up, #categories-table .move-down').forEach(btn => {
        btn.addEventListener('click', async function () {
            const row = this.closest('tr');
            const tbody = row.parentElement;
            const rows = [...tbody.querySelectorAll('tr')];
            const idx = rows.indexOf(row);
            const direction = this.classList.contains('move-up') ? 'up' : 'down';
            const sibling = direction === 'up' ? rows[idx - 1] : rows[idx + 1];
            if (!sibling) return;

            tbody.querySelectorAll('.sort-btn').forEach(b => b.disabled = true);

            animateSwap(row, sibling, direction, tbody);

            try {
                await fetch(row.dataset.moveUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ direction })
                });
            } catch (e) {}
        });
    });
})();
</script>
@endsection
