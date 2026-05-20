<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function wallets(): BelongsToMany
    {
        return $this->belongsToMany(Wallet::class);
    }

    public function electricProducts(): HasMany
    {
        return $this->products()->where('product_type', 'pulsa');
    }
}
