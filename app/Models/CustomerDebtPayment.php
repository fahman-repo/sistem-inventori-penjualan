<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerDebtPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_debt_id',
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
     * Piutang yang dibayar melalui pembayaran ini.
     */
    public function customerDebt(): BelongsTo
    {
        return $this->belongsTo(CustomerDebt::class);
    }

    /**
     * User (admin/kasir) yang mencatat pembayaran.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
