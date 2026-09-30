<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Procurement;
use App\Models\Stock;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class OwnerController extends Controller
{
    public function dashboard(): View
    {
        $today = Carbon::today();

        $todaySales = Order::where('payment_status', 'paid')
            ->whereDate('paid_at', $today)
            ->sum('total_amount');

        $todayOrdersCount = Order::where('payment_status', 'paid')
            ->whereDate('paid_at', $today)
            ->count();

        $pendingProcurementCount = Procurement::where('status', 'pending')->count();

        $lowStockIngredients = Ingredient::with(['stocks'])
            ->get()
            ->filter(fn($ing) => $ing->total_quantity <= $ing->minimum_stock);

        $recentOrders = Order::with('items')->latest()->take(5)->get();
        $pendingProcurements = Procurement::with('creator')->where('status', 'pending')->latest()->get();

        return view('owner.dashboard', compact(
            'todaySales',
            'todayOrdersCount',
            'pendingProcurementCount',
            'lowStockIngredients',
            'recentOrders',
            'pendingProcurements'
        ));
    }

    // ==================== USER MANAGEMENT ====================

    public function users(): View
    {
        $users = User::latest()->paginate(15);
        return view('owner.users.index', compact('users'));
    }

    public function createUser(): View
    {
        return view('owner.users.create');
    }

    public function storeUser(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route('owner.users.index')->with('success', "Pengguna {$data['name']} berhasil ditambahkan.");
    }

    public function editUser(User $user): View
    {
        return view('owner.users.edit', compact('user'));
    }

    public function updateUser(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('owner.users.index')->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    public function destroyUser(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('owner.users.index')->with('success', "Pengguna {$name} berhasil dihapus.");
    }

    public function toggleBanUser(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat memblokir/banned akun Anda sendiri.');
        }

        $user->update(['is_banned' => !$user->is_banned]);
        $status = $user->is_banned ? 'diblokir (BANNED)' : 'diaktifkan kembali';

        return back()->with('success', "Akun {$user->name} berhasil {$status}.");
    }

    // ==================== REPORTS ====================

    public function salesReport(Request $request): View
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        $orders = Order::with('items')
            ->where('payment_status', 'paid')
            ->whereDate('paid_at', '>=', $startDate)
            ->whereDate('paid_at', '<=', $endDate)
            ->latest('paid_at')
            ->get();

        $totalRevenue = $orders->sum('total_amount');
        $totalOrders = $orders->count();
        $totalItemsSold = $orders->sum(fn($o) => $o->items->sum('quantity'));

        $itemSales = OrderItem::whereHas('order', function ($q) use ($startDate, $endDate) {
            $q->where('payment_status', 'paid')
                ->whereDate('paid_at', '>=', $startDate)
                ->whereDate('paid_at', '<=', $endDate);
        })
            ->select('menu_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('menu_name')
            ->orderByDesc('total_qty')
            ->get();

        return view('owner.reports.sales', compact(
            'orders',
            'startDate',
            'endDate',
            'totalRevenue',
            'totalOrders',
            'totalItemsSold',
            'itemSales'
        ));
    }

    public function stockReport(): View
    {
        $ingredients = Ingredient::with(['stocks', 'recipes.menu'])->get();

        return view('owner.reports.stock', compact('ingredients'));
    }

    public function procurementReport(Request $request): View
    {
        $status = $request->input('status');
        $query = Procurement::with(['creator', 'approver', 'items.ingredient'])->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $procurements = $query->paginate(15);
        $totalSpend = Procurement::whereIn('status', ['approved', 'completed'])->sum('total_cost');

        return view('owner.reports.procurement', compact('procurements', 'totalSpend', 'status'));
    }

    public function profitLossReport(Request $request): View
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        $paidOrders = Order::with(['items.menu.recipes.ingredient'])
            ->where('payment_status', 'paid')
            ->whereDate('paid_at', '>=', $startDate)
            ->whereDate('paid_at', '<=', $endDate)
            ->get();

        $grossRevenue = $paidOrders->sum('total_amount');

        // Calculate Cost of Goods Sold (HPP) based on recipe ingredient cost
        $totalHpp = 0;
        foreach ($paidOrders as $order) {
            foreach ($order->items as $item) {
                if ($item->menu) {
                    foreach ($item->menu->recipes as $recipe) {
                        $costPerUnit = (float) ($recipe->ingredient->cost_per_unit ?? 0);
                        $amountNeeded = (float) $recipe->amount * $item->quantity;
                        $totalHpp += ($amountNeeded * $costPerUnit);
                    }
                }
            }
        }

        // Procurement expenses approved in this period
        $procurementExpenses = Procurement::whereIn('status', ['approved', 'completed'])
            ->whereDate('approved_at', '>=', $startDate)
            ->whereDate('approved_at', '<=', $endDate)
            ->sum('total_cost');

        $grossProfit = $grossRevenue - $totalHpp;
        $profitMargin = $grossRevenue > 0 ? ($grossProfit / $grossRevenue) * 100 : 0;

        return view('owner.reports.profit_loss', compact(
            'startDate',
            'endDate',
            'grossRevenue',
            'totalHpp',
            'grossProfit',
            'profitMargin',
            'procurementExpenses',
            'paidOrders'
        ));
    }

    // ==================== PROCUREMENT APPROVAL ====================

    public function pendingProcurements(): View
    {
        $procurements = Procurement::with(['creator', 'items.ingredient'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(15);

        return view('owner.procurement.index', compact('procurements'));
    }

    public function approveProcurement(Procurement $procurement): RedirectResponse
    {
        $procurement->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', "Permintaan Pengadaan #{$procurement->po_number} berhasil DISETUJUI.");
    }

    public function rejectProcurement(Request $request, Procurement $procurement): RedirectResponse
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $procurement->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'rejection_reason' => $request->input('reason'),
        ]);

        return back()->with('success', "Permintaan Pengadaan #{$procurement->po_number} berhasil DITOLAK.");
    }
}
