<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Procurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'created_by',
        'supplier_name',
        'supplier_email',
        'supplier_phone',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'total_cost',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_cost' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProcurementItem::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return in_array($this->status, ['approved', 'ordered', 'received', 'completed']);
    }

    /**
     * Warehouse receives incoming items from supplier matching this PO.
     */
    public function receiveItemsInWarehouse(array $receivedQuantities, int $warehouseUserId): void
    {
        DB::transaction(function () use ($receivedQuantities, $warehouseUserId) {
            $allCompleted = true;

            foreach ($this->items as $item) {
                $qty = (float) ($receivedQuantities[$item->id] ?? $item->quantity_requested);
                $item->update(['quantity_received' => $qty]);

                if ($qty > 0) {
                    $stock = Stock::firstOrCreate(
                        ['ingredient_id' => $item->ingredient_id, 'location' => 'gudang'],
                        ['quantity' => 0]
                    );

                    $balanceBefore = (float) $stock->quantity;
                    $balanceAfter = $balanceBefore + $qty;

                    $stock->update(['quantity' => $balanceAfter]);

                    StockMutation::create([
                        'ingredient_id' => $item->ingredient_id,
                        'location' => 'gudang',
                        'type' => 'inbound_supplier',
                        'reference_number' => $this->po_number,
                        'quantity_change' => $qty,
                        'balance_before' => $balanceBefore,
                        'balance_after' => $balanceAfter,
                        'user_id' => $warehouseUserId,
                        'notes' => "Penerimaan barang dari supplier {$this->supplier_name} untuk PO {$this->po_number}",
                    ]);
                }

                if ($item->quantity_received < $item->quantity_requested) {
                    $allCompleted = false;
                }
            }

            $this->update([
                'status' => $allCompleted ? 'completed' : 'received',
            ]);
        });
    }
}
