<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'email',
    ];

    /**
     * Transaksi penjualan dari pelanggan ini.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Piutang pelanggan ini.
     */
    public function debts(): HasMany
    {
        return $this->hasMany(CustomerDebt::class);
    }
}
