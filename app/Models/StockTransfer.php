<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class StockTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_number',
        'dispatched_by',
        'received_by',
        'status',
        'dispatch_date',
        'receipt_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'dispatch_date' => 'datetime',
            'receipt_date' => 'datetime',
        ];
    }

    public function dispatcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    /**
     * Store/Kasir confirms receipt of transferred stock.
     */
    public function markAsReceived(int $cashierUserId): void
    {
        if ($this->status === 'received') {
            return;
        }

        DB::transaction(function () use ($cashierUserId) {
            $this->update([
                'status' => 'received',
                'received_by' => $cashierUserId,
                'receipt_date' => now(),
            ]);

            foreach ($this->items as $item) {
                $stock = Stock::firstOrCreate(
                    ['ingredient_id' => $item->ingredient_id, 'location' => 'toko'],
                    ['quantity' => 0]
                );

                $balanceBefore = (float) $stock->quantity;
                $balanceAfter = $balanceBefore + (float) $item->quantity;

                $stock->update(['quantity' => $balanceAfter]);

                StockMutation::create([
                    'ingredient_id' => $item->ingredient_id,
                    'location' => 'toko',
                    'type' => 'transfer_in',
                    'reference_number' => $this->transfer_number,
                    'quantity_change' => (float) $item->quantity,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'user_id' => $cashierUserId,
                    'notes' => "Penerimaan pengiriman stok dari gudang (Transfer #{$this->transfer_number})",
                ]);
            }
        });
    }
}
