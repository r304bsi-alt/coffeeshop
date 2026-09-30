<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessCashPaymentRequest;
use App\Http\Requests\StoreCustomerOrderRequest;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Models\CafeTable;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\MenuRecipe;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShopNotification;
use App\Models\StockTransfer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class KasirController extends Controller
{
    public function dashboard(): View
    {
        $today = now()->toDateString();

        $todaySales = Order::where('payment_status', 'paid')
            ->whereDate('paid_at', $today)
            ->sum('total_amount');

        $todayOrdersCount = Order::where('payment_status', 'paid')
            ->whereDate('paid_at', $today)
            ->count();

        // Pending inbound transfers from warehouse
        $pendingTransfers = StockTransfer::with(['dispatcher', 'items.ingredient'])
            ->where('status', 'dispatched')
            ->get();

        // Orders ready from kitchen waiting to be called
        $readyOrders = Order::with('items')
            ->where('order_status', 'ready')
            ->latest('kitchen_done_at')
            ->get();

        return view('kasir.dashboard', compact(
            'todaySales',
            'todayOrdersCount',
            'pendingTransfers',
            'readyOrders'
        ));
    }

    // ==================== INBOUND STOCK (TERIMA DARI GUDANG) ====================

    public function transfersIndex(): View
    {
        $transfers = StockTransfer::with(['dispatcher', 'receiver', 'items.ingredient'])
            ->latest()
            ->paginate(15);

        return view('kasir.transfers.index', compact('transfers'));
    }

    public function receiveTransfer(StockTransfer $transfer): RedirectResponse
    {
        if ($transfer->status === 'received') {
            return back()->with('info', 'Pengiriman stok ini telah diterima sebelumnya.');
        }

        $transfer->markAsReceived(auth()->id());

        return back()->with('success', "Stok pengiriman #{$transfer->transfer_number} telah berhasil DITERIMA. Stok toko otomatis bertambah.");
    }

    // ==================== MENU & RECIPE (BOM) MANAGEMENT ====================

    public function menuIndex(): View
    {
        $menus = Menu::with(['category', 'recipes.ingredient.stocks'])->latest()->paginate(15);
        $categories = Category::all();

        return view('kasir.menus.index', compact('menus', 'categories'));
    }

    public function createMenu(): View
    {
        $categories = Category::where('is_active', true)->get();
        $ingredients = Ingredient::orderBy('name')->get();

        return view('kasir.menus.create', compact('categories', 'ingredients'));
    }

    public function storeMenu(StoreMenuRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('menus', 'public');
        }

        DB::transaction(function () use ($validated, $imagePath) {
            $menu = Menu::create([
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']) . '-' . Str::random(5),
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'image' => $imagePath,
                'is_available' => $validated['is_available'] ?? true,
            ]);

            foreach ($validated['recipes'] as $recipe) {
                MenuRecipe::create([
                    'menu_id' => $menu->id,
                    'ingredient_id' => $recipe['ingredient_id'],
                    'amount' => $recipe['amount'],
                ]);
            }
        });

        return redirect()->route('kasir.menus.index')->with('success', "Menu {$validated['name']} beserta resep bahan bakunya berhasil disimpan.");
    }

    public function editMenu(Menu $menu): View
    {
        $menu->load('recipes');
        $categories = Category::where('is_active', true)->get();
        $ingredients = Ingredient::orderBy('name')->get();

        return view('kasir.menus.edit', compact('menu', 'categories', 'ingredients'));
    }

    public function updateMenu(UpdateMenuRequest $request, Menu $menu): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            if ($menu->image && Storage::disk('public')->exists($menu->image)) {
                Storage::disk('public')->delete($menu->image);
            }
            $menu->image = $request->file('image')->store('menus', 'public');
        }

        $menu->category_id = $validated['category_id'];
        $menu->name = $validated['name'];
        $menu->description = $validated['description'] ?? null;
        $menu->price = $validated['price'];
        $menu->is_available = $request->boolean('is_available', true);
        $menu->save();

        if (isset($validated['recipes'])) {
            $menu->recipes()->delete();
            foreach ($validated['recipes'] as $recipe) {
                if (!empty($recipe['ingredient_id']) && !empty($recipe['amount'])) {
                    MenuRecipe::create([
                        'menu_id' => $menu->id,
                        'ingredient_id' => $recipe['ingredient_id'],
                        'amount' => $recipe['amount'],
                    ]);
                }
            }
        }

        return redirect()->route('kasir.menus.index')->with('success', "Menu {$menu->name} berhasil diperbarui.");
    }

    public function toggleMenuAvailability(Menu $menu): RedirectResponse
    {
        $menu->update(['is_available' => !$menu->is_available]);
        $status = $menu->is_available ? 'TERSEDIA' : 'TIDAK TERSEDIA (HABIS)';

        return back()->with('success', "Status menu {$menu->name} diubah menjadi: {$status}.");
    }

    public function destroyMenu(Menu $menu): RedirectResponse
    {
        $name = $menu->name;
        if ($menu->image && Storage::disk('public')->exists($menu->image)) {
            Storage::disk('public')->delete($menu->image);
        }
        $menu->delete();

        return redirect()->route('kasir.menus.index')->with('success', "Menu {$name} berhasil dihapus.");
    }

    // ==================== POS (POINT OF SALE) ====================

    public function pos(): View
    {
        $categories = Category::with(['menus' => fn($q) => $q->where('is_available', true)->with('recipes.ingredient.stocks')])
            ->where('is_active', true)
            ->get();
        $tables = CafeTable::all();

        return view('kasir.pos', compact('categories', 'tables'));
    }

    public function createManualOrder(StoreCustomerOrderRequest $request): RedirectResponse
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

            $cashTendered = null;
            $changeAmount = null;

            if ($validated['payment_method'] === 'cash') {
                $cashTendered = isset($validated['cash_tendered']) && $validated['cash_tendered'] !== ''
                    ? (float) $validated['cash_tendered']
                    : $totalAmount;
                $changeAmount = max(0, $cashTendered - $totalAmount);
            }

            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            $paymentToken = 'CASH-' . strtoupper(Str::random(8));

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => null,
                'customer_name' => $validated['customer_name'],
                'table_number' => $validated['table_number'],
                'order_type' => $validated['order_type'],
                'total_amount' => $totalAmount,
                'cash_tendered' => $cashTendered,
                'change_amount' => $changeAmount,
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'pending',
                'order_status' => 'pending_payment',
                'payment_token' => $paymentToken,
                'cashier_id' => auth()->id(),
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($itemsData as $item) {
                $order->items()->create($item);
            }

            return $order;
        });

        // If paying immediately with cash
        if ($validated['payment_method'] === 'cash') {
            $order->markAsPaid('CASH-POS-' . strtoupper(uniqid()), auth()->id());
            $formattedTotal = 'Rp ' . number_format($order->total_amount, 0, ',', '.');
            $formattedTendered = 'Rp ' . number_format($order->cash_tendered, 0, ',', '.');
            $formattedChange = 'Rp ' . number_format($order->change_amount, 0, ',', '.');

            return redirect()->route('kasir.pos')->with('success', "Pesanan #{$order->order_number} berhasil dibuat & DIBAYAR LUNAS (Total: {$formattedTotal} | Bayar: {$formattedTendered} | Kembalian: {$formattedChange}). Orderan diteruskan ke Dapur.");
        }

        return redirect()->route('kasir.pos')->with('success', "Pesanan #{$order->order_number} berhasil dicatat.");
    }

    // ==================== CASH PAYMENT SCANNING & PROCESSING ====================

    public function cashPaymentScan(): View
    {
        return view('kasir.payment.scan');
    }

    public function lookupOrder(Request $request): JsonResponse
    {
        $code = trim($request->input('code'));

        $order = Order::with('items')
            ->where(function ($q) use ($code) {
                $q->where('payment_token', $code)
                  ->orWhere('order_number', $code);
            })
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Pesanan dengan kode QR tersebut tidak ditemukan.'], 404);
        }

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'table_number' => $order->table_number,
                'order_type' => $order->order_type === 'dine_in' ? 'Dine In' : 'Take Away',
                'total_amount' => (float) $order->total_amount,
                'formatted_total' => 'Rp ' . number_format($order->total_amount, 0, ',', '.'),
                'payment_status' => $order->payment_status,
                'items' => $order->items->map(fn($i) => [
                    'name' => $i->menu_name,
                    'qty' => $i->quantity,
                    'price' => 'Rp ' . number_format($i->price, 0, ',', '.'),
                    'subtotal' => 'Rp ' . number_format($i->subtotal, 0, ',', '.'),
                    'notes' => $i->notes,
                ]),
            ],
        ]);
    }

    public function processCashPayment(ProcessCashPaymentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $code = $validated['order_identifier'];

        $order = Order::where('payment_token', $code)
            ->orWhere('order_number', $code)
            ->firstOrFail();

        if ($order->payment_status === 'paid') {
            return back()->with('info', "Pesanan #{$order->order_number} sudah pernah dibayar sebelumnya.");
        }

        $cashTendered = isset($validated['cash_tendered']) && $validated['cash_tendered'] !== ''
            ? (float) $validated['cash_tendered']
            : (float) $order->total_amount;
        $changeAmount = max(0, $cashTendered - (float) $order->total_amount);

        $order->cash_tendered = $cashTendered;
        $order->change_amount = $changeAmount;
        $order->save();

        $order->markAsPaid('CASH-MANUAL-' . strtoupper(uniqid()), auth()->id());

        $formattedTotal = 'Rp ' . number_format($order->total_amount, 0, ',', '.');
        $formattedTendered = 'Rp ' . number_format($cashTendered, 0, ',', '.');
        $formattedChange = 'Rp ' . number_format($changeAmount, 0, ',', '.');

        return back()->with('success', "Pembayaran TUNAI Pesanan #{$order->order_number} (Meja {$order->table_number}) SELESAI (Total: {$formattedTotal} | Bayar: {$formattedTendered} | Kembalian: {$formattedChange}). Stok toko berkurang & pesanan masuk ke Dapur.");
    }

    // ==================== NOTIFICATIONS & CALLING CUSTOMER ====================

    public function callCustomer(Order $order): RedirectResponse
    {
        $order->update([
            'order_status' => 'completed',
            'cashier_called_at' => now(),
            'cashier_id' => auth()->id(),
        ]);

        return back()->with('success', "Customer Pesanan #{$order->order_number} ({$order->table_number} - {$order->customer_name}) telah dipanggil dan pesanan diserahkan!");
    }
}
