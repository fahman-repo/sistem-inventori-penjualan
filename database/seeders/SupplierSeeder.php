<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Seed supplier contoh.
     */
    public function run(): void
    {
        $suppliers = [
            ['name' => 'PT Sumber Rejeki', 'phone' => '021-5551001', 'address' => 'Jl. Merdeka No. 10, Jakarta'],
            ['name' => 'CV Maju Jaya', 'phone' => '022-5552002', 'address' => 'Jl. Asia Afrika No. 25, Bandung'],
            ['name' => 'Toko Grosir Sentosa', 'phone' => '031-5553003', 'address' => 'Jl. Pahlawan No. 7, Surabaya'],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::updateOrCreate(['name' => $supplier['name']], $supplier);
        }
    }
}
