<?php

namespace App\Services;

use App\Models\Command;
use App\Models\CommandQueue;
use App\Models\PaymentTransaction;
use App\Models\UserPurchaseRecord;
use Illuminate\Support\Facades\Log;

class CommandQueueService
{
    public function dispatchForTransaction(PaymentTransaction $tx, array $cartItems): void
    {
        if (empty($cartItems)) {
            Log::warning("CommandQueueService: no cart items for transaction {$tx->id}");
            return;
        }

        $playerName = trim(str_replace(["\r", "\n"], '', $tx->minecraft_username));

        $productIds = collect($cartItems)
            ->pluck('id')
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (empty($productIds)) {
            Log::warning("CommandQueueService: cart items have no valid product ids for transaction {$tx->id}");
            return;
        }

        $allCommands = Command::whereIn('product_id', $productIds)
            ->get()
            ->groupBy('product_id');

        $rows = [];
        $now  = now()->toDateTimeString();

        foreach ($cartItems as $item) {
            $id       = (int) ($item['id'] ?? 0);
            $quantity = max(1, (int) ($item['quantity'] ?? 1));

            if ($id === 0) {
                continue;
            }

            $cmds = $allCommands[$id] ?? collect();

            if ($cmds->isEmpty()) {
                Log::warning("CommandQueueService: no commands defined for product id={$id} (tx {$tx->id})");
                continue;
            }

            for ($q = 0; $q < $quantity; $q++) {
                foreach ($cmds as $cmd) {
                    $resolved = str_ireplace('%player%', $playerName, $cmd->command_text);
                    $rows[]   = [
                        'transaction_id' => $tx->id,
                        'player_name'    => $playerName,
                        'command_text'   => $resolved,
                        'status'         => 'pending',
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ];
                }
            }

            try {
                $record = UserPurchaseRecord::where('minecraft_username', $playerName)
                    ->where('product_id', $id)
                    ->first();

                if ($record) {
                    $record->total_quantity_purchased += $quantity;
                    $record->last_purchase_date        = now();
                    $record->save();
                } else {
                    UserPurchaseRecord::create([
                        'minecraft_username'       => $playerName,
                        'product_id'               => $id,
                        'total_quantity_purchased'  => $quantity,
                        'last_purchase_date'        => now(),
                        'created_at'               => now(),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning("CommandQueueService: failed to record purchase for tx {$tx->id}: {$e->getMessage()}");
            }
        }

        if (!empty($rows)) {
            CommandQueue::insert($rows);
            Log::info("CommandQueueService: inserted " . count($rows) . " command(s) for transaction {$tx->id} player={$playerName}");
        } else {
            Log::warning("CommandQueueService: no commands queued for transaction {$tx->id}");
        }
    }
}
