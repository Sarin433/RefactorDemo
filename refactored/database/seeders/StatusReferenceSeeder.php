<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusReferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('status_references')->insertOrIgnore([
            ['status_id' => 1, 'status_name' => 'รอยืนยันคำสั่งซื้อ', 'created_at' => now(), 'updated_at' => now()],
            ['status_id' => 2, 'status_name' => 'ยืนยันคำสั่งซื้อ', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
