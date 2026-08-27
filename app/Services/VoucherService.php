<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use Illuminate\Support\Facades\DB;

class VoucherService
{
    /**
     * Validate a voucher code against cart items.
     *
     * @param  string  $code
     * @param  array   $items  [['id' => 1, 'unit_price' => 5.99, 'quantity' => 1], ...]
     */
    public function validate(string $code, array $items): array
    {
        $code    = strtoupper(trim($code));
        $voucher = Voucher::where('code', $code)->first();

        if (!$voucher) {
            return $this->invalid('Voucher code not found.');
        }

        if (!$voucher->is_active) {
            return $this->invalid('This voucher is no longer active.');
        }

        $now = now();
        if ($now->lt($voucher->start_date)) {
            return $this->invalid("This voucher is not yet valid (valid from {$voucher->start_date->format('Y-m-d')}).");
        }

        if ($now->gt($voucher->expiration_date)) {
            return $this->invalid('This voucher has expired.');
        }

        if ($voucher->max_uses !== null && $voucher->current_uses >= $voucher->max_uses) {
            return $this->invalid('This voucher has reached its maximum number of uses.');
        }

        // For category-scoped vouchers, load product→category slugs in one query
        $categoryByProductId = [];
        if ($voucher->scope === 'Category') {
            $productIds = collect($items)->pluck('id')->filter()->unique()->values()->all();
            if (!empty($productIds)) {
                Product::with('category')
                    ->whereIn('id', $productIds)
                    ->get()
                    ->each(function ($p) use (&$categoryByProductId) {
                        $categoryByProductId[$p->id] = $p->category?->slug;
                    });
            }
        }

        $applicableTotal = 0.0;
        foreach ($items as $item) {
            $id = (int) ($item['id'] ?? 0);

            $applies = match ($voucher->scope) {
                'All'      => true,
                'Category' => ($categoryByProductId[$id] ?? null) === $voucher->scope_category,
                'Product'  => $id === (int) $voucher->scope_item_id,
                default    => false,
            };

            if ($applies) {
                $applicableTotal += ((float) ($item['unit_price'] ?? 0)) * ((int) ($item['quantity'] ?? 1));
            }
        }

        $discountPercent = $applicableTotal > 0 ? (float) $voucher->discount_percent : 0.0;
        $discountAmount  = round($applicableTotal * $discountPercent / 100, 2);

        return [
            'valid'            => true,
            'message'          => $discountPercent > 0 ? "{$voucher->discount_percent}% discount applied!" : 'No applicable items in cart',
            'discount_percent' => $discountPercent,
            'discount_amount'  => $discountAmount,
            'scope'            => $voucher->scope,
            'scope_category'   => $voucher->scope_category,
            'scope_item_id'    => $voucher->scope_item_id,
            'code'             => $voucher->code,
        ];
    }

    /**
     * Record a voucher use, re-checking max_uses under a DB lock to prevent TOCTOU races (G8).
     * Returns false if the voucher no longer has uses available.
     */
    public function recordUsage(string $code, string $username): bool
    {
        $code = strtoupper(trim($code));

        return DB::transaction(function () use ($code, $username) {
            $voucher = Voucher::where('code', $code)->lockForUpdate()->first();

            if (!$voucher) {
                return false;
            }

            // Re-check under lock — another concurrent request may have consumed the last use
            if ($voucher->max_uses !== null && $voucher->current_uses >= $voucher->max_uses) {
                return false;
            }

            VoucherUsage::create([
                'voucher_id'         => $voucher->id,
                'minecraft_username' => $username,
                'used_at'            => now(),
            ]);

            $voucher->increment('current_uses');

            return true;
        });
    }

    private function invalid(string $message): array
    {
        return [
            'valid'            => false,
            'message'          => $message,
            'discount_percent' => 0.0,
            'discount_amount'  => 0.0,
            'scope'            => 'All',
            'scope_category'   => null,
            'scope_item_id'    => null,
            'code'             => '',
        ];
    }
}
