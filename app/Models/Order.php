<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_name',
        'table_number',
        'order_type',
        'total_amount',
        'cash_tendered',
        'change_amount',
        'payment_method',
        'payment_status',
        'order_status',
        'payment_token',
        'payment_reference',
        'paid_at',
        'kitchen_done_at',
        'cashier_called_at',
        'cashier_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'cash_tendered' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'kitchen_done_at' => 'datetime',
            'cashier_called_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Mark order as paid, deduct precise ingredients from store stock,
     * and notify kitchen.
     */
    public function markAsPaid(?string $reference = null, ?int $cashierId = null): void
    {
        if ($this->payment_status === 'paid') {
            return;
        }

        DB::transaction(function () use ($reference, $cashierId) {
            $this->update([
                'payment_status' => 'paid',
                'order_status' => 'in_kitchen',
                'payment_reference' => $reference ?? $this->payment_reference ?? 'PAY-' . strtoupper(uniqid()),
                'paid_at' => now(),
                'cashier_id' => $cashierId ?? $this->cashier_id,
            ]);

            // Deduct precise store stocks based on recipe BOM
            $this->loadMissing('items.menu.recipes.ingredient');

            foreach ($this->items as $item) {
                if (!$item->menu) {
                    continue;
                }

                foreach ($item->menu->recipes as $recipe) {
                    $ingredient = $recipe->ingredient;
                    if (!$ingredient) {
                        continue;
                    }

                    $totalDeduction = (float) $recipe->amount * (int) $item->quantity;

                    // Get or create toko stock record
                    $stock = Stock::firstOrCreate(
                        ['ingredient_id' => $ingredient->id, 'location' => 'toko'],
                        ['quantity' => 0]
                    );

                    $balanceBefore = (float) $stock->quantity;
                    $balanceAfter = $balanceBefore - $totalDeduction;

                    $stock->update(['quantity' => $balanceAfter]);

                    // Record audit mutation log
                    StockMutation::create([
                        'ingredient_id' => $ingredient->id,
                        'location' => 'toko',
                        'type' => 'order_deduction',
                        'reference_number' => $this->order_number,
                        'quantity_change' => -$totalDeduction,
                        'balance_before' => $balanceBefore,
                        'balance_after' => $balanceAfter,
                        'user_id' => $cashierId ?? $this->user_id,
                        'notes' => "Pemotongan bahan {$ingredient->name} untuk Menu: {$item->menu_name} (x{$item->quantity}) [{$recipe->amount} {$ingredient->unit}/porsi]",
                    ]);
                }
            }

            // Create notification for kitchen
            ShopNotification::create([
                'type' => 'order_paid_for_kitchen',
                'title' => "Pesanan Baru Masuk #{$this->order_number}",
                'message' => "Pesanan #{$this->order_number} (Meja {$this->table_number}) telah dibayar dan siap diproses di dapur.",
                'target_role' => 'dapur',
                'data' => [
                    'order_id' => $this->id,
                    'order_number' => $this->order_number,
                    'table_number' => $this->table_number,
                    'customer_name' => $this->customer_name,
                    'total_amount' => $this->total_amount,
                    'items_count' => $this->items->sum('quantity'),
                    'created_at' => now()->toIso8601String(),
                ],
            ]);
        });
    }

    /**
     * Mark order as done in kitchen and notify cashier to call customer.
     */
    public function markAsKitchenDone(): void
    {
        $this->update([
            'order_status' => 'ready',
            'kitchen_done_at' => now(),
        ]);

        ShopNotification::create([
            'type' => 'order_ready_for_cashier',
            'title' => "Pesanan Siap Dipanggil #{$this->order_number}",
            'message' => "Pesanan #{$this->order_number} untuk Meja {$this->table_number} telah SELESAI disiapkan dapur. Silakan panggil customer!",
            'target_role' => 'kasir',
            'data' => [
                'order_id' => $this->id,
                'order_number' => $this->order_number,
                'table_number' => $this->table_number,
                'customer_name' => $this->customer_name,
                'items' => $this->items->map(fn($item) => "{$item->quantity}x {$item->menu_name}")->join(', '),
                'kitchen_done_at' => now()->toIso8601String(),
            ],
        ]);
    }
}
