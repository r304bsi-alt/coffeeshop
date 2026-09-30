<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'unit',
        'minimum_stock',
        'cost_per_unit',
    ];

    protected function casts(): array
    {
        return [
            'minimum_stock' => 'decimal:2',
            'cost_per_unit' => 'decimal:2',
        ];
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function gudangStock(): HasOne
    {
        return $this->hasOne(Stock::class)->where('location', 'gudang');
    }

    public function tokoStock(): HasOne
    {
        return $this->hasOne(Stock::class)->where('location', 'toko');
    }

    public function getGudangQuantityAttribute(): float
    {
        return (float) ($this->stocks->firstWhere('location', 'gudang')?->quantity ?? 0);
    }

    public function getTokoQuantityAttribute(): float
    {
        return (float) ($this->stocks->firstWhere('location', 'toko')?->quantity ?? 0);
    }

    public function getTotalQuantityAttribute(): float
    {
        return $this->gudang_quantity + $this->toko_quantity;
    }

    public function recipes(): HasMany
    {
        return $this->hasMany(MenuRecipe::class);
    }

    public function mutations(): HasMany
    {
        return $this->hasMany(StockMutation::class);
    }
}
