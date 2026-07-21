<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'user_id',
        'supplier_id',
        'purchase_date',
        'total',
        'notes',
        'payment_status',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'total' => 'decimal:2',
    ];

    /**
     * Payment status labels for display.
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'cash'   => 'Cash (Lunas)',
            'credit' => 'Credit (Utang)',
            default  => ucfirst($this->payment_status),
        };
    }

    /**
     * Check if payment status is credit (has debt).
     */
    public function getIsCreditAttribute(): bool
    {
        return $this->payment_status === 'credit';
    }

    /**
     * User (admin) yang menginput pembelian.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Supplier pembelian (opsional).
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Detail item pembelian.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Utang yang timbul dari pembelian ini (jika payment_status = credit).
     */
    public function supplierDebt(): HasOne
    {
        return $this->hasOne(SupplierDebt::class);
    }
}
