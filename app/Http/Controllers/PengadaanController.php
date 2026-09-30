<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProcurementRequest;
use App\Models\Ingredient;
use App\Models\Procurement;
use App\Models\ProcurementItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PengadaanController extends Controller
{
    public function dashboard(): View
    {
        $ingredients = Ingredient::with('stocks')->get();
        $lowStocks = $ingredients->filter(fn($i) => $i->total_quantity <= $i->minimum_stock);

        $pendingApprovals = Procurement::where('status', 'pending')->count();
        $approvedCount = Procurement::where('status', 'approved')->count();
        $recentProcurements = Procurement::with('items.ingredient')->latest()->take(5)->get();

        return view('pengadaan.dashboard', compact(
            'ingredients',
            'lowStocks',
            'pendingApprovals',
            'approvedCount',
            'recentProcurements'
        ));
    }

    public function checkStock(): View
    {
        $ingredients = Ingredient::with(['stocks', 'recipes.menu'])->get();
        return view('pengadaan.stock_check', compact('ingredients'));
    }

    public function index(): View
    {
        $procurements = Procurement::with(['creator', 'approver', 'items.ingredient'])
            ->latest()
            ->paginate(15);

        return view('pengadaan.index', compact('procurements'));
    }

    public function create(): View
    {
        $ingredients = Ingredient::orderBy('name')->get();
        return view('pengadaan.create', compact('ingredients'));
    }

    public function store(StoreProcurementRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $totalCost = 0;
            foreach ($validated['items'] as $item) {
                $totalCost += ((float) $item['quantity_requested'] * (float) $item['unit_price']);
            }

            $po = Procurement::create([
                'po_number' => 'PO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4)),
                'created_by' => auth()->id(),
                'supplier_name' => $validated['supplier_name'],
                'supplier_email' => $validated['supplier_email'] ?? null,
                'supplier_phone' => $validated['supplier_phone'] ?? null,
                'status' => 'pending',
                'total_cost' => $totalCost,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $subtotal = (float) $item['quantity_requested'] * (float) $item['unit_price'];

                ProcurementItem::create([
                    'procurement_id' => $po->id,
                    'ingredient_id' => $item['ingredient_id'],
                    'quantity_requested' => $item['quantity_requested'],
                    'quantity_received' => 0,
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                ]);
            }
        });

        return redirect()->route('pengadaan.index')->with('success', 'Permintaan pengadaan berhasil dibuat dan dikirim ke Owner untuk persetujuan.');
    }

    public function show(Procurement $procurement): View
    {
        $procurement->load(['creator', 'approver', 'items.ingredient']);
        return view('pengadaan.show', compact('procurement'));
    }

    public function print(Procurement $procurement): View
    {
        $procurement->load(['creator', 'approver', 'items.ingredient']);
        return view('pengadaan.print', compact('procurement'));
    }

    public function sendEmailToSupplier(Request $request, Procurement $procurement): RedirectResponse
    {
        if ($procurement->status !== 'approved' && $procurement->status !== 'ordered') {
            return back()->with('error', 'Dokumen pengadaan harus disetujui terlebih dahulu oleh Owner sebelum dapat dikirimkan ke supplier.');
        }

        $procurement->update(['status' => 'ordered']);

        return back()->with('success', "Purchase Order #{$procurement->po_number} telah berhasil dikirimkan via email ke supplier ({$procurement->supplier_email}).");
    }
}
