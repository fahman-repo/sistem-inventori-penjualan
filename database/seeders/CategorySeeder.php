<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed kategori contoh.
     */
    public function run(): void
    {
        $categories = ['Makanan', 'Minuman', 'ATK', 'Kebersihan', 'Elektronik'];

        foreach ($categories as $name) {
            Category::updateOrCreate(['name' => $name]);
        }
    }
}
