<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerOrderRequest;
use App\Models\CafeTable;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function menu(Request $request): View
    {
        $tableParam = $request->input('table', 'Meja 01');
        $selectedCategory = $request->input('category');

        $categories = Category::where('is_active', true)->get();
        $tables = CafeTable::all();

        $menusQuery = Menu::with(['category', 'recipes.ingredient.stocks'])
            ->where('is_available', true);

        if ($selectedCategory) {
            $menusQuery->where('category_id', $selectedCategory);
        }

        $menus = $menusQuery->get();

        return view('customer.menu', compact('categories', 'menus', 'tableParam', 'tables', 'selectedCategory'));
    }

    public function placeOrder(StoreCustomerOrderRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $order = DB::transaction(function () use ($validated) {
            $totalAmount = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $menu = Menu::findOrFail($item['menu_id']);
                $subtotal = $menu->price * (int) $item['quantity'];
                $totalAmount += $subtotal;

                $itemsData[] = [
                    'menu_id' => $menu->id,
                    'menu_name' => $menu->name,
                    'price' => $menu->price,
                    'quantity' => (int) $item['quantity'],
                    'subtotal' => $subtotal,
                    'notes' => $item['notes'] ?? null,
                ];
            }

            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            $paymentToken = ($validated['payment_method'] === 'cash' ? 'CASH-' : 'MDT-') . strtoupper(Str::random(10));

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => Auth::id(),
                'customer_name' => $validated['customer_name'],
                'table_number' => $validated['table_number'],
                'order_type' => $validated['order_type'],
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'pending',
                'order_status' => 'pending_payment',
                'payment_token' => $paymentToken,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($itemsData as $item) {
                $order->items()->create($item);
            }

            return $order;
        });

        if ($order->payment_method === 'midtrans_qris') {
            return redirect()->route('customer.midtrans.pay', $order);
        }

        return redirect()->route('customer.cash.voucher', $order);
    }

    /**
     * Midtrans Payment View / Simulator
     */
    public function midtransPay(Order $order): View|RedirectResponse
    {
        if ($order->payment_status === 'paid') {
            return redirect()->route('customer.order.status', $order)->with('info', 'Pesanan ini sudah dibayar.');
        }

        $order->load('items');
        return view('customer.payment_midtrans', compact('order'));
    }

    /**
     * Simulate Midtrans QRIS Webhook / Payment Success
     */
    public function midtransSimulateSuccess(Request $request, Order $order): RedirectResponse
    {
        $ref = 'MIDTRANS-QRIS-' . strtoupper(Str::random(12));
        $order->markAsPaid($ref);

        return redirect()->route('customer.order.status', $order)->with('success', 'Pembayaran QRIS Midtrans berhasil! Pesanan Anda diteruskan ke dapur.');
    }

    /**
     * Cash Payment Voucher screen (shows QR Code to be scanned by cashier)
     */
    public function cashVoucher(Order $order): View
    {
        $order->load('items');
        return view('customer.payment_cash', compact('order'));
    }

    /**
     * Live Order Status Tracker
     */
    public function orderStatus(Order $order): View
    {
        $order->load('items');
        return view('customer.order_status', compact('order'));
    }

    /**
     * JSON Order Status endpoint for live polling
     */
    public function apiOrderStatus(Order $order): JsonResponse
    {
        return response()->json([
            'order_number' => $order->order_number,
            'payment_status' => $order->payment_status,
            'order_status' => $order->order_status,
            'paid_at' => $order->paid_at?->toIso8601String(),
            'kitchen_done_at' => $order->kitchen_done_at?->toIso8601String(),
            'cashier_called_at' => $order->cashier_called_at?->toIso8601String(),
        ]);
    }
}
