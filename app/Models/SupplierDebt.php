<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierDebt extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'supplier_id',
        'total_amount',
        'paid_amount',
        'due_date',
        'status',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    /**
     * Get the remaining debt amount.
     */
    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }

    /**
     * Get status label for display.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'unpaid'  => 'Belum Dibayar',
            'partial' => 'Sebagian',
            'paid'    => 'Lunas',
            default   => ucfirst($this->status),
        };
    }

    /**
     * Get status badge color for display.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'unpaid'  => 'danger',
            'partial' => 'warning',
            'paid'    => 'success',
            default   => 'secondary',
        };
    }

    /**
     * Transaksi pembelian yang menjadi sumber utang.
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Supplier yang menerima pembayaran utang.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Riwayat pembayaran cicilan utang ini.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(SupplierDebtPayment::class);
    }
}