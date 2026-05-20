<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PulsaSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'sale_date',
        'product_id',
        'wallet_id',
        'customer_name',
        'target_number',
        'serial_number',
        'shift',
        'payment_method',
        'price',
        'cost_amount',
        'cash_paid',
        'change_amount',
        'status',
        'notes',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'datetime',
            'price' => 'decimal:2',
            'cost_amount' => 'decimal:2',
            'cash_paid' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'cancelled_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
