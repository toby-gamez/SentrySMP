{{--
    Product List
    Variables: $items (Collection<Product> with category, ordered by sort_order),
               $categories (Collection<Category> for filter dropdown),
               $categoryId (?int currently active filter)
    Supports category filtering via GET ?category_id=.
    Rows are reorderable within their filtered set via up/down buttons (admin.products.move).
--}}
@extends('layouts.admin')
@section('title', 'Products')
@section('content')
<style>
.sort-btn { background:none;border:1px solid #444;color:#aaa;border-radius:4px;width:28px;height:28px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;padding:0;transition:background .15s,color .15s; }
.sort-btn:hover:not(:disabled) { background:#333;color:#fff; }
.sort-btn:disabled { opacity:.3;cursor:default; }
.filter-bar { display:flex;align-items:center;gap:12px;margin-bottom:16px;flex-wrap:wrap; }
.filter-bar select { background:#1a1a1a;color:#ccc;border:1px solid #333;border-radius:6px;padding:6px 10px;font-size:14px; }
.filter-bar a { color:#aaa;font-size:13px;text-decoration:none; }
.filter-bar a:hover { color:#fff; }
</style>
{{-- ── Page Guide ─────────────────────────────────────────────────────── --}}
<div style="background:#0b1929;border:1px solid #1e3a5f;border-left:3px solid #3b82f6;border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:13px;">
    <div onclick="var n=this.nextElementSibling;n.hidden=!n.hidden;this.querySelector('.pg-ch').style.transform=n.hidden?'':'rotate(180deg)'"
         style="display:flex;align-items:center;gap:8px;cursor:pointer;user-select:none;">
        <i class="bi bi-book" style="color:#60a5fa;font-size:14px;"></i>
        <strong style="color:#93c5fd;">Product Management Guide</strong>
        <i class="bi bi-chevron-down pg-ch" style="color:#60a5fa;margin-left:auto;font-size:12px;transition:transform .2s;"></i>
    </div>
    <div hidden style="margin-top:12px;color:#94a3b8;line-height:1.75;">
        <ul style="margin:0;padding-left:18px;">
            <li><strong style="color:#c4d4e8;">Filter by category</strong> — use the dropdown to narrow the list to one category. Reordering only affects products within the active filter.</li>
            <li><strong style="color:#c4d4e8;">Reordering</strong> — use ▲/▼ to change a product's display position in the shop. Order updates immediately.</li>
            <li><strong style="color:#c4d4e8;">Click any row</strong> to open the product's edit page, where you can also manage its delivery commands.</li>
            <li><strong style="color:#c4d4e8;">Sale</strong> — a non-zero value shows a discounted price in the shop: <em>price × (1 − sale / 100)</em>.</li>
            <li><strong style="color:#c4d4e8;">Max/User</strong> — the maximum number of times one player can purchase this product. ∞ means unlimited.</li>
            <li><strong style="color:#c4d4e8;">Deleting</strong> a product is permanent. Existing transactions referencing it are unaffected (items are snapshotted at purchase time).</li>
        </ul>
    </div>
</div>
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">Products ({{ $items->count() }})</h2>
        <a href="{{ route('admin.products.create') }}" class="btn-admin btn-admin-primary"><i class="bi bi-plus-lg"></i> Add Product</a>
    </div>

    <div class="filter-bar">
        <label style="color:#aaa;font-size:14px;">Filter by category:</label>
        <select onchange="this.value ? window.location='{{ route('admin.products.index') }}?category_id='+this.value : window.location='{{ route('admin.products.index') }}'">
            <option value="">All categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        @if($categoryId)
            <a href="{{ route('admin.products.index') }}"><i class="bi bi-x-circle"></i> Clear filter</a>
        @endif
    </div>

    @if($items->isEmpty())
        <p style="color:#666;">No products{{ $categoryId ? ' in this category' : '' }} yet.</p>
    @else
    <table class="admin-table" id="products-table">
        <thead><tr><th style="width:40px;"></th><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Sale</th><th>Max/User</th><th>Actions</th></tr></thead>
        <tbody>
            @foreach($items as $item)
            <tr data-id="{{ $item->id }}" data-move-url="{{ route('admin.products.move', $item) }}"
                class="admin-table-row-link" data-href="{{ route('admin.products.edit', $item) }}">
                <td style="white-space:nowrap;padding-right:0;" class="no-row-click">
                    <button class="sort-btn move-up" title="Move up" {{ $loop->first ? 'disabled' : '' }}><i class="bi bi-chevron-up"></i></button>
                    <button class="sort-btn move-down" style="margin-top:2px;" title="Move down" {{ $loop->last ? 'disabled' : '' }}><i class="bi bi-chevron-down"></i></button>
                </td>
                <td>
                    @if($item->image)
                        <img src="{{ $item->image }}" alt="{{ $item->name }}" style="width:48px;height:48px;object-fit:cover;border-radius:6px;">
                    @else
                        <div style="width:48px;height:48px;border-radius:6px;background:#222;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-image" style="color:#555;"></i>
                        </div>
                    @endif
                </td>
                <td style="color:white;font-weight:600;">{{ $item->name }}</td>
                <td>
                    @if($item->category)
                        <span style="display:inline-flex;align-items:center;gap:6px;">
                            <span style="width:10px;height:10px;border-radius:50%;background:{{ $item->category->color }};display:inline-block;"></span>
                            {{ $item->category->name }}
                        </span>
                    @else
                        <span style="color:#555;">—</span>
                    @endif
                </td>
                <td>€{{ number_format($item->price, 2) }}</td>
                <td>{{ $item->sale > 0 ? $item->sale . '%' : '—' }}</td>
                <td>{{ $item->global_max_order ?? '∞' }}</td>
                <td class="no-row-click">
                    <a href="{{ route('admin.products.edit', $item) }}" class="btn-admin btn-admin-secondary"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('admin.products.destroy', $item) }}" style="display:inline;" onsubmit="return confirm('Delete this product?')">
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

    document.querySelectorAll('#products-table .move-up, #products-table .move-down').forEach(btn => {
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
