{{--
    Voucher List
    Variables: $items (Collection<Voucher>)
    Displays code, discount %, scope, usage count, expiry, and active status.
    Clicking a row navigates to the edit page.
--}}
@extends('layouts.admin')
@section('title', 'Vouchers')
@section('content')
{{-- ── Page Guide ─────────────────────────────────────────────────────── --}}
<div style="background:#0b1929;border:1px solid #1e3a5f;border-left:3px solid #3b82f6;border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:13px;">
    <div onclick="var n=this.nextElementSibling;n.hidden=!n.hidden;this.querySelector('.pg-ch').style.transform=n.hidden?'':'rotate(180deg)'"
         style="display:flex;align-items:center;gap:8px;cursor:pointer;user-select:none;">
        <i class="bi bi-book" style="color:#60a5fa;font-size:14px;"></i>
        <strong style="color:#93c5fd;">Voucher Management Guide</strong>
        <i class="bi bi-chevron-down pg-ch" style="color:#60a5fa;margin-left:auto;font-size:12px;transition:transform .2s;"></i>
    </div>
    <div hidden style="margin-top:12px;color:#94a3b8;line-height:1.75;">
        <ul style="margin:0;padding-left:18px;">
            <li>Vouchers give players a discount code they enter at checkout.</li>
            <li><strong style="color:#c4d4e8;">Scope</strong> — <em>All</em> applies to the whole cart, <em>Category</em> applies only to products in a specific category, <em>Product</em> applies to one specific product.</li>
            <li><strong style="color:#c4d4e8;">Uses</strong> — current / max. <em>∞</em> means no usage cap.</li>
            <li><strong style="color:#c4d4e8;">Active</strong> — inactive codes are rejected at checkout even if all other conditions are met.</li>
            <li><strong style="color:#c4d4e8;">Click any row</strong> to edit the voucher.</li>
        </ul>
    </div>
</div>
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">Vouchers ({{ $items->count() }})</h2>
        <a href="{{ route('admin.vouchers.create') }}" class="btn-admin btn-admin-primary"><i class="bi bi-plus-lg"></i> Add Voucher</a>
    </div>
    @if($items->isEmpty())
        <p style="color:#666;">No vouchers yet.</p>
    @else
    <table class="admin-table">
        <thead><tr><th>Code</th><th>Discount</th><th>Scope</th><th>Uses</th><th>Expires</th><th>Active</th><th>Actions</th></tr></thead>
        <tbody>
            @foreach($items as $item)
            <tr class="admin-table-row-link" data-href="{{ route('admin.vouchers.edit', $item) }}">
                <td style="font-family:monospace;font-weight:700;color:white;">{{ $item->Code }}</td>
                <td>{{ $item->DiscountPercent }}%</td>
                <td>{{ $item->Scope === 'Item' ? 'Product' : $item->Scope }}{{ $item->ScopeCategory ? ' · ' . $item->ScopeCategory : '' }}{{ $item->ScopeItemId ? ' #' . $item->ScopeItemId : '' }}</td>
                <td>{{ $item->CurrentUses }}{{ $item->MaxUses ? ' / ' . $item->MaxUses : ' / ∞' }}</td>
                <td style="color:#888;font-size:12px;">{{ $item->ExpirationDate?->format('Y-m-d') ?? '—' }}</td>
                <td>{!! $item->IsActive
                    ? '<span class="badge" style="background:#1e3a20;color:#6be895;">Active</span>'
                    : '<span class="badge" style="background:#2a1a1a;color:#ff6b7a;">Inactive</span>' !!}</td>
                <td class="no-row-click">
                    <a href="{{ route('admin.vouchers.edit', $item) }}" class="btn-admin btn-admin-secondary"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('admin.vouchers.destroy', $item) }}" style="display:inline;" onsubmit="return confirm('Delete voucher?')">
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
@endsection
