<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Debt extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'debt_date',
        'customer_name',
        'customer_phone',
        'amount',
        'paid_amount',
        'status',
        'shift',
        'description',
        'sale_id',
        'pulsa_sale_id',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'debt_date' => 'datetime',
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function pulsaSale(): BelongsTo
    {
        return $this->belongsTo(PulsaSale::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(DebtPayment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
