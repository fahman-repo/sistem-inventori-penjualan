<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed produk contoh (stok bervariasi, beberapa di bawah min_stock
     * untuk testing indikator "stok menipis").
     */
    public function run(): void
    {
        $categories = Category::pluck('id', 'name');

        // [name, sku, category, unit, buy_price, sell_price, stock, min_stock]
        $products = [
            ['Indomie Goreng', 'MKN-001', 'Makanan', 'pcs', 2500, 3500, 120, 20],
            ['Beras Pandan 5kg', 'MKN-002', 'Makanan', 'karung', 60000, 68000, 8, 10],   // menipis
            ['Minyak Goreng 1L', 'MKN-003', 'Makanan', 'botol', 15000, 18000, 40, 15],
            ['Gula Pasir 1kg', 'MKN-004', 'Makanan', 'pcs', 12000, 14000, 5, 10],         // menipis
            ['Telur Ayam 1kg', 'MKN-005', 'Makanan', 'kg', 24000, 28000, 30, 10],

            ['Aqua 600ml', 'MNM-001', 'Minuman', 'botol', 2000, 3000, 200, 30],
            ['Teh Botol Sosro', 'MNM-002', 'Minuman', 'botol', 3000, 4500, 90, 24],
            ['Kopi Kapal Api', 'MNM-003', 'Minuman', 'pcs', 1000, 1500, 7, 20],           // menipis
            ['Susu Ultra 250ml', 'MNM-004', 'Minuman', 'pcs', 4000, 5500, 60, 15],
            ['Coca Cola 1.5L', 'MNM-005', 'Minuman', 'botol', 12000, 15000, 18, 12],

            ['Pulpen Standard', 'ATK-001', 'ATK', 'pcs', 1500, 2500, 150, 25],
            ['Buku Tulis 38 lbr', 'ATK-002', 'ATK', 'pcs', 3000, 4500, 80, 20],
            ['Pensil 2B', 'ATK-003', 'ATK', 'pcs', 1000, 2000, 4, 15],                    // menipis
            ['Penghapus', 'ATK-004', 'ATK', 'pcs', 800, 1500, 100, 20],
            ['Spidol Whiteboard', 'ATK-005', 'ATK', 'pcs', 6000, 9000, 25, 10],

            ['Sabun Mandi', 'KBR-001', 'Kebersihan', 'pcs', 2500, 3800, 70, 15],
            ['Sampo Sachet', 'KBR-002', 'Kebersihan', 'pcs', 500, 1000, 9, 30],           // menipis
            ['Detergen 800g', 'KBR-003', 'Kebersihan', 'pcs', 14000, 17500, 22, 10],

            ['Baterai AA (isi 4)', 'ELK-001', 'Elektronik', 'pak', 12000, 16000, 35, 10],
            ['Lampu LED 10W', 'ELK-002', 'Elektronik', 'pcs', 18000, 24000, 6, 10],       // menipis
        ];

        foreach ($products as [$name, $sku, $category, $unit, $buy, $sell, $stock, $minStock]) {
            Product::updateOrCreate(
                ['sku' => $sku],
                [
                    'category_id' => $categories[$category] ?? null,
                    'name' => $name,
                    'unit' => $unit,
                    'buy_price' => $buy,
                    'sell_price' => $sell,
                    'stock' => $stock,
                    'min_stock' => $minStock,
                ]
            );
        }
    }
}
