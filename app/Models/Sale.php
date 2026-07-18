<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'user_id',
        'sale_date',
        'total',
        'notes',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'total' => 'decimal:2',
    ];

    /**
     * Kasir yang melayani penjualan.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Detail item penjualan.
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
