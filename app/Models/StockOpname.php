<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockOpname extends Model
{
    use HasFactory;

    protected $fillable = [
        'opname_number',
        'user_id',
        'opname_date',
        'notes',
    ];

    protected $casts = [
        'opname_date' => 'date',
    ];

    /**
     * User (admin) yang melakukan stock opname.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Detail item stock opname.
     */
    public function items(): HasMany
    {
        return $this->hasMany(StockOpnameItem::class);
    }
}