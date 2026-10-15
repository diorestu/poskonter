<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'product_type',
        'provider_id',
        'category',
        'variant',
        'serial_number',
        'image_path',
        'unit',
        'price',
        'cost_price',
        'stock',
        'minimum_stock',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function pulsaSales(): HasMany
    {
        return $this->hasMany(PulsaSale::class);
    }

    public function sourceWallets(): BelongsToMany
    {
        return $this->belongsToMany(Wallet::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function wallets(): BelongsToMany
    {
        return $this->sourceWallets();
    }

    public function isPulsa(): bool
    {
        return $this->product_type === 'pulsa';
    }

    public function isVoucher(): bool
    {
        return $this->product_type === 'voucher'
            || str_contains(strtolower($this->category ?? ''), 'voucher')
            || str_contains(strtolower($this->name ?? ''), 'voucher');
    }
}
