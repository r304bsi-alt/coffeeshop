<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReceiveProcurementRequest;
use App\Http\Requests\StoreStockTransferRequest;
use App\Models\Ingredient;
use App\Models\Procurement;
use App\Models\Stock;
use App\Models\StockMutation;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GudangController extends Controller
{
    public function dashboard(): View
    {
        $gudangStocks = Ingredient::with(['stocks' => fn($q) => $q->where('location', 'gudang')])->get();

        $inboundPendingCount = Procurement::whereIn('status', ['approved', 'ordered'])->count();
        $dispatchedTransfersCount = StockTransfer::where('status', 'dispatched')->count();

        $recentInbound = StockMutation::where('location', 'gudang')
            ->where('type', 'inbound_supplier')
            ->with(['ingredient', 'user'])
            ->latest()
            ->take(5)
            ->get();

        $recentTransfers = StockTransfer::with(['dispatcher', 'receiver', 'items.ingredient'])
            ->latest()
            ->take(5)
            ->get();

        return view('gudang.dashboard', compact(
            'gudangStocks',
            'inboundPendingCount',
            'dispatchedTransfersCount',
            'recentInbound',
            'recentTransfers'
        ));
    }

    // ==================== INBOUND (SUPPLIER -> GUDANG) ====================

    public function inboundIndex(): View
    {
        $procurements = Procurement::with(['items.ingredient', 'creator', 'approver'])
            ->whereIn('status', ['approved', 'ordered', 'received', 'completed'])
            ->latest()
            ->paginate(10);

        return view('gudang.inbound.index', compact('procurements'));
    }

    public function inboundShow(Procurement $procurement): View
    {
        $procurement->load(['items.ingredient.stocks', 'creator', 'approver']);
        return view('gudang.inbound.show', compact('procurement'));
    }

    public function receiveInbound(ReceiveProcurementRequest $request, Procurement $procurement): RedirectResponse
    {
        if (!in_array($procurement->status, ['approved', 'ordered', 'received'])) {
            return back()->with('error', 'Status pesanan pengadaan tidak valid untuk penerimaan barang.');
        }

        $received = $request->validated()['received'];
        $procurement->receiveItemsInWarehouse($received, auth()->id());

        return redirect()->route('gudang.inbound.index')->with('success', "Penerimaan barang dari supplier untuk PO #{$procurement->po_number} berhasil dicatat ke stok gudang.");
    }

    // ==================== OUTBOUND (GUDANG -> TOKO) ====================

    public function transfersIndex(): View
    {
        $transfers = StockTransfer::with(['dispatcher', 'receiver', 'items.ingredient'])
            ->latest()
            ->paginate(15);

        return view('gudang.transfers.index', compact('transfers'));
    }

    public function createTransfer(): View
    {
        $ingredients = Ingredient::with(['stocks' => fn($q) => $q->where('location', 'gudang')])->get();
        return view('gudang.transfers.create', compact('ingredients'));
    }

    public function storeTransfer(StoreStockTransferRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $transfer = StockTransfer::create([
                'transfer_number' => 'TRF-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4)),
                'dispatched_by' => auth()->id(),
                'received_by' => null,
                'status' => 'dispatched',
                'dispatch_date' => now(),
                'receipt_date' => null,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'ingredient_id' => $item['ingredient_id'],
                    'quantity' => $item['quantity'],
                ]);

                // Deduct from gudang stock
                $stock = Stock::where('ingredient_id', $item['ingredient_id'])
                    ->where('location', 'gudang')
                    ->first();

                $balanceBefore = (float) ($stock?->quantity ?? 0);
                $balanceAfter = $balanceBefore - (float) $item['quantity'];

                $stock->update(['quantity' => $balanceAfter]);

                StockMutation::create([
                    'ingredient_id' => $item['ingredient_id'],
                    'location' => 'gudang',
                    'type' => 'transfer_out',
                    'reference_number' => $transfer->transfer_number,
                    'quantity_change' => -(float) $item['quantity'],
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'user_id' => auth()->id(),
                    'notes' => "Pengeluaran stok dari gudang menuju kasir toko (#{$transfer->transfer_number})",
                ]);
            }
        });

        return redirect()->route('gudang.transfers.index')->with('success', 'Pengeluaran stok barang ke toko berhasil dicatat. Menunggu konfirmasi penerimaan oleh kasir.');
    }

    public function showTransfer(StockTransfer $transfer): View
    {
        $transfer->load(['dispatcher', 'receiver', 'items.ingredient']);
        return view('gudang.transfers.show', compact('transfer'));
    }

    // ==================== WAREHOUSE STOCKS ====================

    public function stocks(): View
    {
        $ingredients = Ingredient::with(['stocks' => fn($q) => $q->where('location', 'gudang')])->get();
        return view('gudang.stocks.index', compact('ingredients'));
    }

    public function mutations(): View
    {
        $mutations = StockMutation::where('location', 'gudang')
            ->with(['ingredient', 'user'])
            ->latest()
            ->paginate(20);

        return view('gudang.stocks.mutations', compact('mutations'));
    }
}
