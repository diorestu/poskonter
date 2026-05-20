<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'balance',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function pulsaSales(): HasMany
    {
        return $this->hasMany(PulsaSale::class);
    }

    public function electricProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->where('product_type', 'pulsa');
    }

    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(Provider::class);
    }

    public function electricProviders(): BelongsToMany
    {
        return $this->providers();
    }

    public function products(): BelongsToMany
    {
        return $this->electricProducts();
    }
}
