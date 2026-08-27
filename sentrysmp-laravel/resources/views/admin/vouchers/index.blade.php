@extends('layouts.admin')
@section('title', 'Vouchers')
@section('content')
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
