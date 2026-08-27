<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RestoreDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ── Categories ────────────────────────────────────────────────────────
        DB::table('categories')->insert([
            ['id' => 1, 'name' => 'Keys',    'slug' => 'keys',    'color' => '#888888', 'image' => null, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Coins',   'slug' => 'coins',   'color' => '#888888', 'image' => null, 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'Ranks',   'slug' => 'ranks',   'color' => '#888888', 'image' => null, 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'Bundles', 'slug' => 'bundles', 'color' => '#888888', 'image' => null, 'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // ── Products ──────────────────────────────────────────────────────────
        DB::table('products')->insert([
            // Keys (category 1)
            ['id' => 1,  'name' => 'Spawner Key',            'description' => '',  'price' => 1.50,  'sale' => 0,  'image' => 'https://images.sentrysmp.eu/uploads/keys/c1b495c6-aef1-43ee-8748-409bf14bd575.png', 'global_max_order' => null, 'category_id' => 1, 'sort_order' => 1,  'created_at' => $now, 'updated_at' => $now],
            ['id' => 2,  'name' => 'Sentry Key',              'description' => '',  'price' => 6.00,  'sale' => 0,  'image' => 'https://images.sentrysmp.eu/uploads/keys/1a2940e0-4726-42dd-a53a-53dbe76570a3.png', 'global_max_order' => null, 'category_id' => 1, 'sort_order' => 2,  'created_at' => $now, 'updated_at' => $now],
            ['id' => 3,  'name' => 'Summer Key',              'description' => '',  'price' => 8.00,  'sale' => 0,  'image' => 'https://images.sentrysmp.eu/uploads/keys/9484e220-051c-4d75-85e2-c5e52a969bdc.png', 'global_max_order' => null, 'category_id' => 1, 'sort_order' => 3,  'created_at' => $now, 'updated_at' => $now],
            // Coins (category 2)
            ['id' => 4,  'name' => '500 Coins',               'description' => '',  'price' => 5.00,  'sale' => 0,  'image' => 'https://images.sentrysmp.eu/uploads/coins/ee922364-15cb-4d51-884d-08de25fba9a5.png', 'global_max_order' => null, 'category_id' => 2, 'sort_order' => 4,  'created_at' => $now, 'updated_at' => $now],
            ['id' => 5,  'name' => '1500 Coins',              'description' => '',  'price' => 13.00, 'sale' => 0,  'image' => 'https://images.sentrysmp.eu/uploads/coins/54bf2d75-91dd-4a67-a91b-de4ab44c543a.png', 'global_max_order' => null, 'category_id' => 2, 'sort_order' => 5,  'created_at' => $now, 'updated_at' => $now],
            ['id' => 6,  'name' => '3000 Coins',              'description' => '',  'price' => 25.00, 'sale' => 0,  'image' => 'https://images.sentrysmp.eu/uploads/coins/c033e4dd-dabf-4cc0-9f64-7dcd49123029.png', 'global_max_order' => null, 'category_id' => 2, 'sort_order' => 6,  'created_at' => $now, 'updated_at' => $now],
            ['id' => 7,  'name' => '6000 Coins',              'description' => '',  'price' => 45.00, 'sale' => 0,  'image' => 'https://images.sentrysmp.eu/uploads/coins/0664de67-0d0c-482e-8ab0-e1212c1c974e.png', 'global_max_order' => null, 'category_id' => 2, 'sort_order' => 7,  'created_at' => $now, 'updated_at' => $now],
            ['id' => 8,  'name' => '13500 Coins',             'description' => '',  'price' => 90.00, 'sale' => 20, 'image' => 'https://images.sentrysmp.eu/uploads/coins/c203fb5f-7151-4bbc-83c5-85b7639fa85f.png', 'global_max_order' => null, 'category_id' => 2, 'sort_order' => 8,  'created_at' => $now, 'updated_at' => $now],
            ['id' => 9,  'name' => '20 000 Coins',            'description' => '',  'price' => 100.00,'sale' => 10, 'image' => 'https://images.sentrysmp.eu/uploads/coins/0664de67-0d0c-482e-8ab0-e1212c1c974e.png', 'global_max_order' => null, 'category_id' => 2, 'sort_order' => 9,  'created_at' => $now, 'updated_at' => $now],
            // Ranks (category 3)
            ['id' => 10, 'name' => 'VIP Rank + Sentry Key',  'description' => '',  'price' => 10.00, 'sale' => 0,  'image' => 'https://images.sentrysmp.eu/uploads/ranks/6d402329-d4e6-402e-b2b6-489adc11ef54.png', 'global_max_order' => null, 'category_id' => 3, 'sort_order' => 10, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 11, 'name' => 'Hunter Rank + Sentry Key','description' => '', 'price' => 15.00, 'sale' => 0,  'image' => 'https://images.sentrysmp.eu/uploads/ranks/6d402329-d4e6-402e-b2b6-489adc11ef54.png', 'global_max_order' => null, 'category_id' => 3, 'sort_order' => 11, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 12, 'name' => 'Lord Rank + Sentry Key',  'description' => '', 'price' => 25.00, 'sale' => 0,  'image' => 'https://images.sentrysmp.eu/uploads/ranks/50b02f55-af1f-460a-9b96-935c47c7c552.png', 'global_max_order' => null, 'category_id' => 3, 'sort_order' => 12, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 13, 'name' => 'Custom Rank - Forever',   'description' => '', 'price' => 50.00, 'sale' => 20, 'image' => 'https://images.sentrysmp.eu/uploads/ranks/634a89c3-37e4-49ba-a75e-0abeb4575144.png', 'global_max_order' => null, 'category_id' => 3, 'sort_order' => 13, 'created_at' => $now, 'updated_at' => $now],
            // Bundles (category 4)
            ['id' => 14, 'name' => 'Summer Bundle',           'description' => "- Summer Mask\n- Summer Chestplate\n- Summer Leggings\n- Summer Boots\n- Summer Sword\n- Summer Pickaxe\n- Summer Axe\n- Summer Mace (windburst)\n- Summer Mace (breach)", 'price' => 50.00, 'sale' => 0, 'image' => null, 'global_max_order' => null, 'category_id' => 4, 'sort_order' => 14, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // Fix auto-increment so new inserts don't collide
        DB::statement('ALTER TABLE categories AUTO_INCREMENT = 5');
        DB::statement('ALTER TABLE products AUTO_INCREMENT = 15');

        // ── Commands ──────────────────────────────────────────────────────────
        // Mapped from old (Type, TypeId) to new product_id
        DB::table('commands')->insert([
            // Keys
            ['product_id' => 1,  'command_text' => 'crate key give %player% spawner 1'],    // Spawner Key
            ['product_id' => 2,  'command_text' => 'crate key give %player% sentry 1'],     // Sentry Key
            ['product_id' => 3,  'command_text' => 'crate key give %player% summer 1'],     // Summer Key
            // Coins
            ['product_id' => 4,  'command_text' => 'points give %player% 500'],             // 500 Coins
            ['product_id' => 5,  'command_text' => 'points give %player% 1500'],            // 1500 Coins
            ['product_id' => 6,  'command_text' => 'points give %player% 3000'],            // 3000 Coins
            ['product_id' => 7,  'command_text' => 'points give %player% 6000'],            // 6000 Coins
            ['product_id' => 8,  'command_text' => 'points give %player% 13500'],           // 13500 Coins
            ['product_id' => 9,  'command_text' => 'points give %player% 20000'],           // 20 000 Coins
            // Ranks — each rank runs 2 commands (set rank + give sentry key)
            ['product_id' => 10, 'command_text' => 'lp user %player% parent set vip'],      // VIP Rank
            ['product_id' => 10, 'command_text' => 'crate key give %player% sentry 1'],     // VIP Rank (key)
            ['product_id' => 11, 'command_text' => 'lp user %player% parent set hunter'],   // Hunter Rank
            ['product_id' => 11, 'command_text' => 'crate key give %player% sentry 1'],     // Hunter Rank (key)
            ['product_id' => 12, 'command_text' => 'lp user %player% parent set lord'],     // Lord Rank
            ['product_id' => 12, 'command_text' => 'crate key give %player% sentry 1'],     // Lord Rank (key)
            ['product_id' => 13, 'command_text' => ''],                                     // Custom Rank (manual)
            // Bundles
            ['product_id' => 14, 'command_text' => 'playerkits give summerbundle %player%'], // Summer Bundle
        ]);
    }
}
