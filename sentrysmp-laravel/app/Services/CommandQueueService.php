<?php

namespace App\Services;

use App\Models\Command;
use App\Models\CommandQueue;
use App\Models\PaymentTransaction;
use App\Models\UserPurchaseRecord;
use Illuminate\Support\Facades\Log;

class CommandQueueService
{
    /**
     * Parse cart items, match commands, substitute %player%, and insert into command_queue.
     *
     * @param  PaymentTransaction  $tx
     * @param  array  $cartItems  e.g. [['type'=>'Key','id'=>1,'name'=>'Ruby Key','quantity'=>2,'price'=>5.99,'sale'=>0], ...]
     */
    public function dispatchForTransaction(PaymentTransaction $tx, array $cartItems): void
    {
        if (empty($cartItems)) {
            Log::warning("CommandQueueService: no cart items for transaction {$tx->id}");
            return;
        }

        $playerName = trim(str_replace(["\r", "\n"], '', $tx->minecraft_username));

        // Collect unique type+id combos so we load commands in one query
        $typeIds = [];
        foreach ($cartItems as $item) {
            $type = $item['type'] ?? null;
            $id   = (int) ($item['id'] ?? 0);
            if ($type && $id > 0) {
                $typeIds[$type][] = $id;
            }
        }

        if (empty($typeIds)) {
            Log::warning("CommandQueueService: cart items have no valid type/id for transaction {$tx->id}");
            return;
        }

        // Load all matching commands in one query
        $commandsQuery = Command::query();
        $first = true;
        foreach ($typeIds as $type => $ids) {
            if ($first) {
                $commandsQuery->where(function ($q) use ($type, $ids) {
                    $q->where('Type', $type)->whereIn('TypeId', $ids);
                });
                $first = false;
            } else {
                $commandsQuery->orWhere(function ($q) use ($type, $ids) {
                    $q->where('Type', $type)->whereIn('TypeId', $ids);
                });
            }
        }
        $allCommands = $commandsQuery->get()->groupBy(fn($c) => "{$c->Type}:{$c->TypeId}");

        $rows = [];
        $now  = now()->toDateTimeString();

        foreach ($cartItems as $item) {
            $type     = $item['type'] ?? null;
            $id       = (int) ($item['id'] ?? 0);
            $quantity = max(1, (int) ($item['quantity'] ?? 1));

            if (!$type || $id === 0) {
                continue;
            }

            $cmds = $allCommands["{$type}:{$id}"] ?? collect();

            if ($cmds->isEmpty()) {
                Log::warning("CommandQueueService: no commands defined for {$type} id={$id} (tx {$tx->id})");
                continue;
            }

            for ($q = 0; $q < $quantity; $q++) {
                foreach ($cmds as $cmd) {
                    $resolved = str_ireplace('%player%', $playerName, $cmd->CommandText);
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

            // Update purchase records
            try {
                $record = UserPurchaseRecord::where('MinecraftUsername', $playerName)
                    ->where('ProductType', $type)
                    ->where('ProductId', $id)
                    ->first();

                if ($record) {
                    $record->TotalQuantityPurchased += $quantity;
                    $record->LastPurchaseDate        = now();
                    $record->save();
                } else {
                    UserPurchaseRecord::create([
                        'MinecraftUsername'       => $playerName,
                        'ProductType'             => $type,
                        'ProductId'               => $id,
                        'TotalQuantityPurchased'  => $quantity,
                        'LastPurchaseDate'        => now(),
                        'CreatedAt'               => now(),
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
