<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RestoreRemainingDataSeeder extends Seeder
{
    private const BACKUP = '/home/tobias/Downloads/db46608_20260826_2136_7.sql';

    // Maps old (Type, TypeId) → new product_id
    private const PRODUCT_MAP = [
        'KEY-10'    => 1,   // Spawner Key
        'KEY-11'    => 2,   // Sentry Key
        'KEY-20'    => 3,   // Summer Key
        'COIN-9'    => 4,   // 500 Coins
        'COIN-10'   => 5,   // 1500 Coins
        'COIN-11'   => 6,   // 3000 Coins
        'COIN-12'   => 7,   // 6000 Coins
        'COIN-13'   => 8,   // 13500 Coins
        'COIN-14'   => 9,   // 20 000 Coins
        'RANK-3'    => 10,  // VIP Rank
        'RANK-4'    => 11,  // Hunter Rank
        'RANK-5'    => 12,  // Lord Rank
        'RANK-6'    => 13,  // Custom Rank
        'BUNDLE-4'  => 14,  // Summer Bundle
    ];

    public function run(): void
    {
        $now = now();

        // ── Payment settings ──────────────────────────────────────────────────
        DB::table('payment_settings')->upsert(
            [['id' => 1, 'enable_payments' => true, 'disable_stripe' => false, 'disable_paypal' => false, 'updated_at' => '2026-08-26 20:27:54']],
            ['id'],
            ['enable_payments', 'disable_stripe', 'disable_paypal', 'updated_at'],
        );

        // ── Team ranks ────────────────────────────────────────────────────────
        DB::table('team_ranks')->insert([
            ['id' => 1,  'name' => 'OWNER',    'hex_color' => '#db3444', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2,  'name' => 'COOWNER',  'hex_color' => '#db4d34', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3,  'name' => 'MANAGER',  'hex_color' => '#ff4500', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4,  'name' => 'LEADER',   'hex_color' => '#c23a00', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5,  'name' => 'DEV',      'hex_color' => '#800080', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6,  'name' => 'WEBDEV',   'hex_color' => '#663399', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7,  'name' => 'MOD',      'hex_color' => '#51d3fd', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 8,  'name' => 'MOD+',     'hex_color' => '#12b4e8', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 9,  'name' => 'BUILDER',  'hex_color' => '#5ce54c', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 10, 'name' => 'BUILDER+', 'hex_color' => '#35d622', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 11, 'name' => 'MEDIA',    'hex_color' => '#db0b54', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 12, 'name' => 'ADMIN',    'hex_color' => '#ccbb08', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 13, 'name' => 'JRMOD',    'hex_color' => '#86f3e6', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 14, 'name' => 'HELPER',   'hex_color' => '#11bb79', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 15, 'name' => 'SR.MOD',   'hex_color' => '#1203e2', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 16, 'name' => 'ADMIN',    'hex_color' => '#c20000', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 17, 'name' => 'LORD',     'hex_color' => '#c90000', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 18, 'name' => 'DEV+',     'hex_color' => '#671b67', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 19, 'name' => 'CITIZEN',  'hex_color' => '#77767b', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 20, 'name' => 'GUARD',    'hex_color' => '#14ff00', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 21, 'name' => 'KNIGHT',   'hex_color' => '#1a5fb4', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 22, 'name' => 'LEGION',   'hex_color' => '#ff7800', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 23, 'name' => 'WARLORD',  'hex_color' => '#e01b24', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 24, 'name' => 'ROYAL',    'hex_color' => '#c061cb', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 25, 'name' => 'MONARCH',  'hex_color' => '#ffa348', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 26, 'name' => 'EMPERIOR', 'hex_color' => '#c01c28', 'created_at' => $now, 'updated_at' => $now],
        ]);
        DB::statement('ALTER TABLE team_ranks AUTO_INCREMENT = 27');

        // ── Team categories ───────────────────────────────────────────────────
        DB::table('team_categories')->insert([
            ['id' => 'ab625731-d807-4568-a21e-348bf0b8e77a', 'name' => 'Owner',      'sort_order' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'd74a6cbb-b821-4137-9b5b-ec092bbdcb47', 'name' => 'Manager',    'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'f55ba525-bb20-45db-9e1e-589d69480bb3', 'name' => 'Developers', 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'fe4253c9-c104-4992-a8e6-dc4c71e04341', 'name' => 'Jr. Mod',    'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'c98df7ea-84bf-47b5-894a-7f079b9188f0', 'name' => 'Helpers',    'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // ── Team members ──────────────────────────────────────────────────────
        DB::table('team_members')->insert([
            ['id' => '4b715a55-8a36-4136-89d1-db7e9aa23b4b', 'minecraft_name' => 'sskerixx19',    'team_rank_id' => 1,  'skin_url' => null, 'sort_order' => 0, 'team_category_id' => 'ab625731-d807-4568-a21e-348bf0b8e77a', 'created_at' => $now, 'updated_at' => $now],
            ['id' => '1e153d1b-d34f-4f64-bb91-e4c2ea984b20', 'minecraft_name' => 'Noobisbest2233','team_rank_id' => 3,  'skin_url' => null, 'sort_order' => 0, 'team_category_id' => 'd74a6cbb-b821-4137-9b5b-ec092bbdcb47', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'c5fad9fc-399a-4469-a83b-dfee3c8244e6', 'minecraft_name' => 'pepeno01',      'team_rank_id' => 5,  'skin_url' => null, 'sort_order' => 0, 'team_category_id' => 'f55ba525-bb20-45db-9e1e-589d69480bb3', 'created_at' => $now, 'updated_at' => $now],
            ['id' => '054b39c5-a20c-4d00-9824-cfa28ba34230', 'minecraft_name' => 'Taneq',         'team_rank_id' => 6,  'skin_url' => null, 'sort_order' => 1, 'team_category_id' => 'f55ba525-bb20-45db-9e1e-589d69480bb3', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'f499879e-e2cc-40f8-a5cc-7c4d6d896ab3', 'minecraft_name' => 'davee261',      'team_rank_id' => 5,  'skin_url' => null, 'sort_order' => 2, 'team_category_id' => 'f55ba525-bb20-45db-9e1e-589d69480bb3', 'created_at' => $now, 'updated_at' => $now],
            ['id' => '48d078bd-b5fd-4fad-b77a-7bb3cec5f783', 'minecraft_name' => 'tom_kriz',      'team_rank_id' => 13, 'skin_url' => null, 'sort_order' => 0, 'team_category_id' => 'fe4253c9-c104-4992-a8e6-dc4c71e04341', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'f3d8cd4d-2083-45fd-abc1-b1b6b412f7f2', 'minecraft_name' => 'ren',           'team_rank_id' => 13, 'skin_url' => null, 'sort_order' => 1, 'team_category_id' => 'fe4253c9-c104-4992-a8e6-dc4c71e04341', 'created_at' => $now, 'updated_at' => $now],
            ['id' => '28293291-ccc1-4faf-826e-e12a2108504f', 'minecraft_name' => 'symxk_',        'team_rank_id' => 13, 'skin_url' => null, 'sort_order' => 2, 'team_category_id' => 'fe4253c9-c104-4992-a8e6-dc4c71e04341', 'created_at' => $now, 'updated_at' => $now],
            ['id' => '51c106ef-b26e-4b7c-a201-dcb7abee3eed', 'minecraft_name' => 'Vantryx_',      'team_rank_id' => 14, 'skin_url' => null, 'sort_order' => 0, 'team_category_id' => 'c98df7ea-84bf-47b5-894a-7f079b9188f0', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // ── User purchase records (with type→product_id mapping) ──────────────
        $purchaseRows = [
            // id, minecraft_username, product_id, total_quantity, last_purchase_date, created_at
            [2,  'Taneq',           11, 1,  '2026-02-28 21:22:51', '2026-02-28 21:22:51'], // Hunter Rank
            [3,  'Taneq',            2, 26, '2026-05-07 20:50:39', '2026-02-28 21:22:51'], // Sentry Key
            [4,  'Taneq',            1, 57, '2026-05-07 20:38:53', '2026-02-28 21:22:51'], // Spawner Key
            [5,  'NinjaaaMC',        1, 1,  '2026-03-01 20:01:27', '2026-03-01 20:01:27'], // Spawner Key
            [7,  '7enc',            10, 1,  '2026-03-02 21:16:24', '2026-03-02 21:16:24'], // VIP Rank
            [9,  'MatejkoYT',       10, 1,  '2026-03-04 17:56:48', '2026-03-04 17:56:48'], // VIP Rank
            [10, 'itsProsto',       12, 1,  '2026-03-05 21:32:22', '2026-03-05 21:32:22'], // Lord Rank
            [12, 'junipxr_james',   10, 1,  '2026-03-13 16:36:17', '2026-03-13 16:36:17'], // VIP Rank
            [13, '_Nicos_',          2, 1,  '2026-04-02 17:10:30', '2026-04-02 17:10:30'], // Sentry Key
            [14, 'Zerovic',         10, 1,  '2026-04-20 15:29:56', '2026-04-20 15:29:56'], // VIP Rank
            [15, 'ItsFlameTom',     12, 1,  '2026-04-23 18:54:55', '2026-04-23 18:54:55'], // Lord Rank
            [16, 'Dokoh658',        12, 1,  '2026-04-24 16:02:38', '2026-04-24 16:02:38'], // Lord Rank
            [17, 'Taneq',            4, 18, '2026-05-05 12:54:31', '2026-05-05 12:23:45'], // 500 Coins
            [19, 'Taneq',            5, 23, '2026-05-07 20:50:39', '2026-05-05 13:15:29'], // 1500 Coins
            [21, 'Taneq',            8, 60, '2026-05-07 20:38:08', '2026-05-07 20:21:12'], // 13500 Coins
            [22, 'Taneq',            6, 1,  '2026-05-07 20:50:39', '2026-05-07 20:50:39'], // 3000 Coins
            [23, 'gear7skillpvp',   12, 1,  '2026-05-17 17:12:29', '2026-05-17 17:12:29'], // Lord Rank
            [24, 'kankan',           2, 1,  '2026-05-31 12:16:21', '2026-05-31 12:16:21'], // Sentry Key
            [25, 'burtik511',        1, 1,  '2026-06-06 09:56:05', '2026-06-06 09:56:05'], // Spawner Key
            [27, 'zryxq_',           2, 2,  '2026-07-26 11:15:48', '2026-07-26 11:15:48'], // Sentry Key
            [28, '1tzwhy',          12, 3,  '2026-07-31 10:36:54', '2026-07-30 17:49:15'], // Lord Rank
            [29, '1tzwhy',          13, 3,  '2026-07-31 10:36:54', '2026-07-30 17:49:15'], // Custom Rank
            [30, 'EMMA745',         11, 1,  '2026-08-26 18:48:31', '2026-08-26 18:48:31'], // Hunter Rank
        ];

        foreach ($purchaseRows as [$id, $username, $productId, $qty, $lastDate, $createdAt]) {
            DB::table('user_purchase_records')->insert([
                'id'                     => $id,
                'minecraft_username'     => $username,
                'product_id'             => $productId,
                'total_quantity_purchased' => $qty,
                'last_purchase_date'     => $lastDate,
                'created_at'             => $createdAt,
            ]);
        }
        DB::statement('ALTER TABLE user_purchase_records AUTO_INCREMENT = 31');

        // ── Payment transactions (streamed from backup file) ───────────────────
        $this->restoreTransactions();
    }

    private function restoreTransactions(): void
    {
        $handle = fopen(self::BACKUP, 'r');
        if (!$handle) {
            $this->command->warn('Backup file not found — skipping transactions.');
            return;
        }

        // Old column order: Id, Provider, ProviderTransactionId, Amount, Currency,
        //                   MinecraftUsername, ItemsJson, Status, CreatedAt, RawResponse
        // New table order:  id, provider, provider_transaction_id, amount, currency,
        //                   minecraft_username, items_json, status, raw_response, created_at
        // → specify columns explicitly so positional order matches old dump (created_at before raw_response)
        $cols = '(`id`,`provider`,`provider_transaction_id`,`amount`,`currency`,'
              . '`minecraft_username`,`items_json`,`status`,`created_at`,`raw_response`)';

        $inBlock = false;
        $chunk   = [];

        $flush = function () use (&$chunk, $cols): void {
            if (empty($chunk)) return;
            DB::unprepared(
                "INSERT INTO `payment_transactions` $cols VALUES " . implode(',', $chunk)
            );
            $chunk = [];
        };

        while (!feof($handle)) {
            $line = rtrim(fgets($handle));

            if (str_starts_with($line, 'INSERT INTO `paymenttransactions`')) {
                $inBlock = true;
                continue;
            }

            if (!$inBlock) continue;

            if (str_starts_with($line, 'UNLOCK') || str_starts_with($line, '/*!')) {
                $inBlock = false;
                $flush();
                continue;
            }

            if (!str_starts_with($line, '(')) continue;

            $chunk[] = rtrim($line, ',;');

            if (count($chunk) >= 200) $flush();
        }

        $flush();
        fclose($handle);

        $count = DB::table('payment_transactions')->count();
        DB::statement("ALTER TABLE payment_transactions AUTO_INCREMENT = 37616");

        if (isset($this->command)) {
            $this->command->info("Restored $count payment transactions.");
        }
    }
}
