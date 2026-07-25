<?php

namespace Database\Seeders;

use App\Models\Tax;
use Illuminate\Database\Seeder;

class TaxSeeder extends Seeder
{
    public function run(): void
    {
        Tax::create([
            'name' => 'PPN 11%',
            'rate' => 11.00,
            'is_active' => true,
        ]);
    }
}
