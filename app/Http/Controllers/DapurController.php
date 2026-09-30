<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DapurController extends Controller
{
    public function dashboard(): View
    {
        // First In First Out (FIFO) - earliest paid order first
        $activeOrders = Order::with(['items.menu.recipes.ingredient'])
            ->where('payment_status', 'paid')
            ->where('order_status', 'in_kitchen')
            ->orderBy('paid_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $completedToday = Order::with('items')
            ->where('order_status', ['ready', 'completed'])
            ->whereDate('kitchen_done_at', now()->toDateString())
            ->latest('kitchen_done_at')
            ->take(10)
            ->get();

        return view('dapur.dashboard', compact('activeOrders', 'completedToday'));
    }

    public function markDone(Order $order): RedirectResponse
    {
        if ($order->order_status !== 'in_kitchen') {
            return back()->with('info', "Pesanan #{$order->order_number} tidak berada dalam antrean dapur.");
        }

        $order->markAsKitchenDone();

        return back()->with('success', "Pesanan #{$order->order_number} untuk {$order->table_number} telah SELESAI. Notifikasi pemanggilan telah dikirim ke Kasir!");
    }

    /**
     * API endpoint for Kitchen Display to check for new incoming orders in real-time.
     */
    public function apiActiveOrders(): JsonResponse
    {
        $orders = Order::with(['items'])
            ->where('payment_status', 'paid')
            ->where('order_status', 'in_kitchen')
            ->orderBy('paid_at', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'table_number' => $order->table_number,
                    'customer_name' => $order->customer_name,
                    'order_type' => $order->order_type,
                    'paid_at' => $order->paid_at?->diffForHumans(),
                    'minutes_elapsed' => $order->paid_at ? (int) $order->paid_at->diffInMinutes(now()) : 0,
                    'notes' => $order->notes,
                    'items' => $order->items->map(fn($item) => [
                        'name' => $item->menu_name,
                        'quantity' => $item->quantity,
                        'notes' => $item->notes,
                    ]),
                ];
            });

        return response()->json([
            'count' => $orders->count(),
            'orders' => $orders,
        ]);
    }
}
