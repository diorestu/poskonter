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
        $code = strtoupper(trim($this->code ?? ''));

        return $this->product_type === 'pulsa'
            || $this->provider_id !== null
            || str_starts_with($code, 'PLS');
    }

    public function isPerdana(): bool
    {
        if ($this->isPulsa()) {
            return false;
        }

        $type = strtolower($this->product_type ?? '');
        $cat = strtolower($this->category ?? '');
        $name = strtolower($this->name ?? '');

        return str_contains($type, 'perdana')
            || str_contains($cat, 'perdana')
            || str_contains($name, 'perdana')
            || str_contains($name, 'sim card');
    }

    public function isVoucher(): bool
    {
        if ($this->isPulsa()) {
            return false;
        }

        $type = strtolower($this->product_type ?? '');
        $cat = strtolower($this->category ?? '');
        $name = strtolower($this->name ?? '');

        return $type === 'voucher'
            || str_contains($cat, 'voucher')
            || str_contains($cat, 'kuota')
            || str_contains($cat, 'data')
            || str_contains($cat, 'paket')
            || str_contains($cat, 'internet')
            || str_starts_with($name, 'voucher')
            || str_contains($name, 'voucher')
            || str_contains($name, 'kuota')
            || str_contains($name, 'paket data');
    }

    public function isVoucherOrPerdana(): bool
    {
        return $this->isVoucher() || $this->isPerdana();
    }
}
