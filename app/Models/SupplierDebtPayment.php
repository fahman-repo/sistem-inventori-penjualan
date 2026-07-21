<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierDebtPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_debt_id',
        'user_id',
        'amount',
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /**
     * Utang yang dibayar melalui pembayaran ini.
     */
    public function supplierDebt(): BelongsTo
    {
        return $this->belongsTo(SupplierDebt::class);
    }

    /**
     * User (admin) yang mencatat pembayaran.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}