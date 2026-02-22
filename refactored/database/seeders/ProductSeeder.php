<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['product_number' => 'SKU-001', 'name' => 'แล็บท็อป Asus VivoBook 15', 'price' => 18900.00, 'stock_quantity' => 13],
            ['product_number' => 'SKU-002', 'name' => 'เมาส์ Logitech MX Master 3', 'price' => 3200.00, 'stock_quantity' => 48],
            ['product_number' => 'SKU-003', 'name' => 'คีย์บอร์ด Keychron K2 Wireless', 'price' => 2800.00, 'stock_quantity' => 30],
            ['product_number' => 'SKU-004', 'name' => 'จอมอนิเตอร์ Dell 24 นิ้ว FHD', 'price' => 5500.00, 'stock_quantity' => 12],
            ['product_number' => 'SKU-005', 'name' => 'หูฟัง Sony WH-1000XM5', 'price' => 9800.00, 'stock_quantity' => 20],
            ['product_number' => 'SKU-006', 'name' => 'เว็บแคม Logitech C920', 'price' => 2100.00, 'stock_quantity' => 0],
            ['product_number' => 'SKU-007', 'name' => 'SSD Samsung 1TB NVMe', 'price' => 2900.00, 'stock_quantity' => 45],
            ['product_number' => 'SKU-008', 'name' => 'กระเป๋าโน้ตบุ๊ค 15.6 นิ้ว', 'price' => 850.00, 'stock_quantity' => 60],
        ];

        foreach ($products as $product) {
            DB::table('products')->insertOrIgnore(array_merge($product, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
