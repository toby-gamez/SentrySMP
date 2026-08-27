<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Database\Schema\Blueprint;

/**
 * One-time migration from the old EF Core schema to the new Laravel schema.
 * Run: php artisan ef:migrate
 */
class MigrateFromEf extends Command
{
    protected $signature   = 'ef:migrate';
    protected $description = 'Migrate data from the old EF Core schema to the new Laravel schema (one-time)';

    public function handle(): int
    {
        if (!Schema::hasTable('__efmigrationshistory')) {
            $this->error('No EF Core schema detected. Aborting.');
            return 1;
        }

        if (!$this->confirm('This will drop all old EF tables and replace them with the new Laravel schema. Continue?')) {
            return 0;
        }

        // ── 1. Read all old data ─────────────────────────────────────────────

        $this->info('Reading old data...');

        $oldServers      = DB::table('servers')->get();
        $oldKeys         = DB::table('keys')->get();
        $oldRanks        = DB::table('ranks')->get();
        $oldBundles      = DB::table('bundles')->get();
        $oldCoins        = DB::table('coins')->get();
        $oldBp           = DB::table('battlepasses')->get();
        $oldOther        = DB::table('others')->get();
        $oldCommands     = DB::table('commands')->get();
        $oldPurchases    = DB::table('userpurchaserecords')->get();
        $oldTxns         = DB::table('paymenttransactions')->get();
        $oldSettings     = DB::table('paymentsettings')->first();
        $oldTxQueue      = DB::table('command_queue')->get();
        $oldTeamRanks    = DB::table('teamranks')->get();
        $oldTeamCats     = DB::table('teamcategories')->get();
        $oldTeamMembers  = DB::table('teammembers')->get();

        $this->info("  keys:{$oldKeys->count()} ranks:{$oldRanks->count()} bundles:{$oldBundles->count()} coins:{$oldCoins->count()}");
        $this->info("  commands:{$oldCommands->count()} purchases:{$oldPurchases->count()} txns:{$oldTxns->count()}");
        $this->info("  teamRanks:{$oldTeamRanks->count()} teamCats:{$oldTeamCats->count()} teamMembers:{$oldTeamMembers->count()}");

        // ── 2. Drop all old EF tables ────────────────────────────────────────

        $this->info('Dropping old EF tables...');

        $drop = [
            'others', 'battlepasses', 'coins', 'bundles', 'ranks', 'keys',
            'commands', 'command_queue', 'userpurchaserecords',
            'vouchers', 'voucherusages',
            'paymenttransactions', 'paymentsettings',
            'teammembers', 'teamcategories', 'teamranks',
            'servers',
            '__efmigrationshistory',
        ];

        // Disable FK checks so drops succeed regardless of order
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($drop as $t) {
            Schema::dropIfExists($t);
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ── 3. Create new tables ─────────────────────────────────────────────

        $this->info('Creating new tables...');

        // servers
        Schema::create('servers', function (Blueprint $t) {
            $t->id();
            $t->string('name', 100);
            $t->timestamps();
        });

        // categories
        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->string('name', 100);
            $t->string('slug', 100)->unique();
            $t->string('color', 7)->default('#888888');
            $t->string('image', 255)->nullable();
            $t->timestamps();
        });

        // products
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->string('name', 100);
            $t->string('description', 500)->default('');
            $t->decimal('price', 8, 2);
            $t->integer('sale')->default(0);
            $t->string('image', 255)->nullable();
            $t->integer('global_max_order')->nullable();
            $t->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $t->timestamps();
        });

        // commands (new schema)
        Schema::create('commands', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $t->text('command_text');
            $t->timestamps();
        });

        // payment_transactions
        Schema::create('payment_transactions', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('provider', 50);
            $t->string('provider_transaction_id', 200)->default('');
            $t->decimal('amount', 18, 2)->default(0);
            $t->string('currency', 10)->default('EUR');
            $t->string('minecraft_username', 100)->default('');
            $t->text('items_json')->nullable();
            $t->string('status', 100)->default('');
            $t->text('raw_response')->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->index('minecraft_username');
            $t->index('created_at');
        });

        // command_queue
        Schema::create('command_queue', function (Blueprint $t) {
            $t->id();
            $t->foreignId('transaction_id')->nullable()->constrained('payment_transactions')->nullOnDelete();
            $t->string('player_name', 100);
            $t->text('command_text');
            $t->string('status', 20)->default('pending');
            $t->timestamps();
            $t->index('status');
        });

        // payment_settings
        Schema::create('payment_settings', function (Blueprint $t) {
            $t->id();
            $t->boolean('enable_payments')->default(true);
            $t->boolean('disable_stripe')->default(false);
            $t->boolean('disable_paypal')->default(false);
            $t->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        // user_purchase_records
        Schema::create('user_purchase_records', function (Blueprint $t) {
            $t->id();
            $t->string('minecraft_username', 100);
            $t->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $t->integer('total_quantity_purchased')->default(0);
            $t->timestamp('last_purchase_date')->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->index(['minecraft_username', 'product_id']);
        });

        // vouchers
        Schema::create('vouchers', function (Blueprint $t) {
            $t->id();
            $t->string('code', 50)->unique();
            $t->string('description', 500)->default('');
            $t->dateTime('start_date');
            $t->dateTime('expiration_date');
            $t->integer('max_uses')->nullable();
            $t->integer('current_uses')->default(0);
            $t->decimal('discount_percent', 5, 2)->default(0);
            $t->string('scope', 20)->default('All');
            $t->string('scope_category', 100)->nullable();
            $t->integer('scope_item_id')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        // voucher_usages
        Schema::create('voucher_usages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            $t->string('minecraft_username', 100);
            $t->timestamp('used_at')->useCurrent();
            $t->timestamps();
        });

        // team_ranks
        Schema::create('team_ranks', function (Blueprint $t) {
            $t->id();
            $t->string('name', 100);
            $t->string('hex_color', 20)->default('');
            $t->timestamps();
        });

        // team_categories
        Schema::create('team_categories', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('name', 100);
            $t->integer('sort_order')->default(0);
            $t->timestamps();
        });

        // team_members
        Schema::create('team_members', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('minecraft_name', 100);
            $t->foreignId('team_rank_id')->nullable()->constrained('team_ranks')->nullOnDelete();
            $t->string('skin_url', 500)->nullable();
            $t->integer('sort_order')->default(0);
            $t->uuid('team_category_id');
            $t->timestamps();
            $t->foreign('team_category_id')->references('id')->on('team_categories')->cascadeOnDelete();
        });

        // ── 4. Insert migrated data ──────────────────────────────────────────

        $this->info('Migrating data...');
        $now = now()->toDateTimeString();

        // Servers
        foreach ($oldServers as $s) {
            DB::table('servers')->insert(['id' => $s->Id, 'name' => $s->Name, 'created_at' => $now, 'updated_at' => $now]);
        }

        // Categories — one per old product type that has rows
        $typeDefs = [
            'Key'        => ['name' => 'Keys',         'slug' => 'keys',         'color' => '#e53935', 'items' => $oldKeys],
            'Rank'       => ['name' => 'Ranks',         'slug' => 'ranks',        'color' => '#43a047', 'items' => $oldRanks],
            'Bundle'     => ['name' => 'Bundles',       'slug' => 'bundles',      'color' => '#8e24aa', 'items' => $oldBundles],
            'Coin'       => ['name' => 'Coins',         'slug' => 'coins',        'color' => '#fdd835', 'items' => $oldCoins],
            'BattlePass' => ['name' => 'Battle Passes', 'slug' => 'battle-passes','color' => '#1e88e5', 'items' => $oldBp],
            'Other'      => ['name' => 'Other',         'slug' => 'other',        'color' => '#546e7a', 'items' => $oldOther],
        ];

        // Maps old type+id → new product id
        $productMap = []; // "Type:OldId" → newId

        foreach ($typeDefs as $type => $def) {
            if ($def['items']->isEmpty()) {
                continue;
            }

            $catId = DB::table('categories')->insertGetId([
                'name'       => $def['name'],
                'slug'       => $def['slug'],
                'color'      => $def['color'],
                'image'      => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($def['items'] as $item) {
                $productId = DB::table('products')->insertGetId([
                    'name'             => $item->Name,
                    'description'      => $item->Description ?? '',
                    'price'            => $item->Price,
                    'sale'             => (int) ($item->Sale ?? 0),
                    'image'            => $item->Image ?? null,
                    'global_max_order' => $item->GlobalMaxOrder ?? null,
                    'category_id'      => $catId,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);
                $productMap["{$type}:{$item->Id}"] = $productId;
            }
        }

        $this->info('  Created ' . count($productMap) . ' products across ' . count(array_filter($typeDefs, fn($d) => $d['items']->isNotEmpty())) . ' categories.');

        // Commands — remap Type+TypeId → product_id
        $cmdCount = 0;
        foreach ($oldCommands as $cmd) {
            $key = "{$cmd->Type}:{$cmd->TypeId}";
            if (!isset($productMap[$key])) {
                $this->warn("  Skipping command id={$cmd->Id}: no matching product for {$key}");
                continue;
            }
            DB::table('commands')->insert([
                'product_id'   => $productMap[$key],
                'command_text' => $cmd->CommandText,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
            $cmdCount++;
        }
        $this->info("  Migrated {$cmdCount} commands.");

        // Purchase records — remap ProductType+ProductId → product_id
        $purchaseCount = 0;
        foreach ($oldPurchases as $p) {
            $key = "{$p->ProductType}:{$p->ProductId}";
            if (!isset($productMap[$key])) {
                $this->warn("  Skipping purchase id={$p->Id}: no matching product for {$key}");
                continue;
            }
            DB::table('user_purchase_records')->insert([
                'minecraft_username'      => $p->MinecraftUsername,
                'product_id'              => $productMap[$key],
                'total_quantity_purchased'=> $p->TotalQuantityPurchased,
                'last_purchase_date'      => $p->LastPurchaseDate,
                'created_at'             => $p->CreatedAt ?? $now,
            ]);
            $purchaseCount++;
        }
        $this->info("  Migrated {$purchaseCount} purchase records.");

        // Payment transactions (large — chunk it)
        $txnCount = 0;
        foreach ($oldTxns->chunk(500) as $chunk) {
            $rows = $chunk->map(fn($t) => [
                'provider'                => $t->Provider,
                'provider_transaction_id' => $t->ProviderTransactionId ?? '',
                'amount'                  => $t->Amount ?? 0,
                'currency'                => $t->Currency ?? 'EUR',
                'minecraft_username'      => $t->MinecraftUsername ?? '',
                'items_json'              => $t->ItemsJson ?? null,
                'status'                  => $t->Status ?? '',
                'raw_response'            => $t->RawResponse ?? null,
                'created_at'             => $t->CreatedAt ?? $now,
            ])->all();
            DB::table('payment_transactions')->insert($rows);
            $txnCount += count($rows);
        }
        $this->info("  Migrated {$txnCount} payment transactions.");

        // Payment settings
        DB::table('payment_settings')->insert([
            'enable_payments' => $oldSettings ? (bool) $oldSettings->EnablePayments : true,
            'disable_stripe'  => $oldSettings ? (bool) $oldSettings->DisableStripe  : false,
            'disable_paypal'  => $oldSettings ? (bool) $oldSettings->DisablePayPal  : false,
            'updated_at'      => $now,
        ]);

        // Team ranks (integer IDs preserved — team_ranks uses bigint auto-increment, same values)
        $teamRankIdMap = []; // old int Id → new bigint id
        foreach ($oldTeamRanks as $r) {
            $newId = DB::table('team_ranks')->insertGetId([
                'name'       => $r->Name,
                'hex_color'  => $r->HexColor ?? '',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $teamRankIdMap[$r->Id] = $newId;
        }

        // Team categories (int Id → UUID)
        $teamCatIdMap = []; // old int Id → new UUID
        foreach ($oldTeamCats->sortBy('SortOrder') as $c) {
            $uuid = (string) Str::uuid();
            DB::table('team_categories')->insert([
                'id'         => $uuid,
                'name'       => $c->Name,
                'sort_order' => $c->SortOrder ?? 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $teamCatIdMap[$c->Id] = $uuid;
        }

        // Team members (int Id → UUID, remap FK references)
        foreach ($oldTeamMembers as $m) {
            $catUuid = $teamCatIdMap[$m->TeamCategoryId] ?? null;
            if (!$catUuid) {
                $this->warn("  Skipping team member id={$m->Id}: unknown TeamCategoryId={$m->TeamCategoryId}");
                continue;
            }
            $rankId = isset($m->TeamRankId) && $m->TeamRankId ? ($teamRankIdMap[$m->TeamRankId] ?? null) : null;
            DB::table('team_members')->insert([
                'id'               => (string) Str::uuid(),
                'minecraft_name'   => $m->MinecraftName,
                'team_rank_id'     => $rankId,
                'skin_url'         => $m->SkinUrl ?? null,
                'sort_order'       => $m->SortOrder ?? 0,
                'team_category_id' => $catUuid,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }
        $this->info('  Migrated team data.');

        // ── 5. Mark all migrations as run ────────────────────────────────────

        $this->info('Marking migrations as complete...');

        $migrationFiles = [
            '2024_01_01_000001_create_servers_table',
            '2024_01_01_000002_create_shop_products_tables',
            '2024_01_01_000003_create_commands_table',
            '2024_01_01_000004_create_payment_transactions_table',
            '2024_01_01_000005_create_command_queue_table',
            '2024_01_01_000006_create_payment_settings_table',
            '2024_01_01_000007_create_user_purchase_records_table',
            '2024_01_01_000008_create_vouchers_tables',
            '2024_01_01_000009_create_team_tables',
            '2024_01_01_000010_create_categories_and_products',
        ];

        // Ensure migrations table exists
        if (!Schema::hasTable('migrations')) {
            Schema::create('migrations', function (Blueprint $t) {
                $t->increments('id');
                $t->string('migration');
                $t->integer('batch');
            });
        }

        DB::table('migrations')->truncate();
        foreach ($migrationFiles as $file) {
            DB::table('migrations')->insert(['migration' => $file, 'batch' => 1]);
        }

        $this->info('');
        $this->info('✓ Migration complete. Run "php artisan migrate:status" to verify.');

        return 0;
    }
}
