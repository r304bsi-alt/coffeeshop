@extends('layouts.app')

@section('title', 'Kasir Dashboard - Kopi Senja')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 glass-panel p-6 rounded-3xl">
        <div>
            <div class="text-xs font-bold text-emerald-400 uppercase tracking-wider mb-1">
                <i class="fa-solid fa-cash-register mr-1"></i> Store & Cashier Operations
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Kasir & Operasional Toko</h1>
            <p class="text-stone-400 text-xs sm:text-sm mt-1">Penerimaan stok gudang, input pesanan (POS), scan bayar tunai, dan panggilan pesanan jadi.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('kasir.payment.scan') }}" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-stone-950 font-bold text-xs shadow-md transition-all flex items-center gap-1.5">
                <i class="fa-solid fa-qrcode"></i> Scan Bayar Tunai
            </a>
            <a href="{{ route('kasir.pos') }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition-all flex items-center gap-1.5">
                <i class="fa-solid fa-plus"></i> POS Kasir Baru
            </a>
        </div>
    </div>

    <!-- Alert / Call Customer Board for Ready Orders from Kitchen -->
    <div class="bg-gradient-to-r from-emerald-950/80 to-stone-900 border-2 border-emerald-500/60 rounded-3xl p-6 shadow-2xl">
        <div class="flex items-center justify-between mb-4 border-b border-emerald-800/50 pb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-bullhorn animate-pulse"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-white text-base">Panggilan Pesanan Siap (Dari Dapur)</h3>
                    <p class="text-[11px] text-stone-400">Pesanan yang sudah matang/selesai dibuat oleh Barista dan siap dipanggil ke meja.</p>
                </div>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/40">
                {{ $readyOrders->count() }} Siap Dipanggil
            </span>
        </div>

        @if($readyOrders->isEmpty())
            <div class="py-6 text-center text-stone-500 text-xs">
                <i class="fa-solid fa-bell-slash text-stone-600 text-2xl mb-1 block"></i>
                Belum ada pesanan yang selesai dari dapur saat ini.
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($readyOrders as $ro)
                <div class="p-4 rounded-2xl bg-stone-900/90 border border-emerald-700/50 flex items-center justify-between gap-3 shadow-lg">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-bold text-emerald-400 text-sm">{{ $ro->order_number }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500 text-stone-950">
                                {{ $ro->table_number ?? 'Take Away' }}
                            </span>
                        </div>
                        <div class="text-xs font-bold text-white mt-1">{{ $ro->customer_name }}</div>
                        <div class="text-[11px] text-stone-400 mt-0.5 truncate max-w-xs">
                            {{ $ro->items->map(fn($it) => "{$it->quantity}x {$it->menu_name}")->join(', ') }}
                        </div>
                        <div class="text-[10px] text-stone-500 mt-1">Selesai di dapur: {{ $ro->kitchen_done_at?->diffForHumans() }}</div>
                    </div>
                    <div>
                        <form action="{{ route('kasir.orders.call', $ro) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition-all flex items-center gap-1.5 whitespace-nowrap">
                                <i class="fa-solid fa-volume-high"></i> Panggil & Serahkan
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Quick Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Sales Today -->
        <div class="bg-stone-900/90 border border-stone-800 rounded-2xl p-5 shadow-lg">
            <span class="text-xs font-semibold text-stone-400">Omzet Penjualan Hari Ini</span>
            <div class="text-2xl font-black text-white mt-2">
                Rp {{ number_format($todaySales, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-emerald-400 mt-1">{{ $todayOrdersCount }} transaksi lunas</div>
        </div>

        <!-- Inbound Pending from Gudang -->
        <div class="bg-stone-900/90 border border-stone-800 rounded-2xl p-5 shadow-lg">
            <span class="text-xs font-semibold text-stone-400">Pengiriman Stok Masuk Gudang</span>
            <div class="text-2xl font-black text-amber-400 mt-2">
                {{ $pendingTransfers->count() }} Pengiriman
            </div>
            <div class="text-[11px] text-amber-400 mt-1">
                @if($pendingTransfers->count() > 0)
                    <a href="{{ route('kasir.transfers.index') }}" class="underline hover:text-amber-300">Konfirmasi terima sekarang &rarr;</a>
                @else
                    Semua stok gudang telah diterima
                @endif
            </div>
        </div>

        <!-- POS Quick Link -->
        <a href="{{ route('kasir.pos') }}" class="bg-stone-900/90 border border-stone-800 hover:border-emerald-500/50 rounded-2xl p-5 shadow-lg transition-all group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-stone-400 group-hover:text-emerald-400">Point of Sale (POS)</span>
                <i class="fa-solid fa-arrow-right text-stone-500 group-hover:text-emerald-400 text-xs"></i>
            </div>
            <div class="text-lg font-bold text-white mt-2 group-hover:text-emerald-300">
                Buka Layar Kasir Toko &rarr;
            </div>
            <div class="text-[11px] text-stone-500 mt-1">Buat pesanan langsung meja & takeaway</div>
        </a>
    </div>

    <!-- Navigation tiles for Kasir features -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('kasir.pos') }}" class="p-4 rounded-2xl bg-stone-900/80 border border-stone-800 hover:border-emerald-500/50 hover:bg-stone-800/80 transition-all text-center group">
            <div class="w-10 h-10 mx-auto rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-lg mb-2 group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-cash-register"></i>
            </div>
            <span class="text-xs font-bold text-stone-200 group-hover:text-white">POS Order</span>
        </a>

        <a href="{{ route('kasir.payment.scan') }}" class="p-4 rounded-2xl bg-stone-900/80 border border-stone-800 hover:border-amber-500/50 hover:bg-stone-800/80 transition-all text-center group">
            <div class="w-10 h-10 mx-auto rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-lg mb-2 group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-qrcode"></i>
            </div>
            <span class="text-xs font-bold text-stone-200 group-hover:text-white">Scan Bayar Tunai</span>
        </a>

        <a href="{{ route('kasir.menus.index') }}" class="p-4 rounded-2xl bg-stone-900/80 border border-stone-800 hover:border-purple-500/50 hover:bg-stone-800/80 transition-all text-center group">
            <div class="w-10 h-10 mx-auto rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center text-lg mb-2 group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-utensils"></i>
            </div>
            <span class="text-xs font-bold text-stone-200 group-hover:text-white">Menu & Resep (BOM)</span>
        </a>

        <a href="{{ route('kasir.transfers.index') }}" class="p-4 rounded-2xl bg-stone-900/80 border border-stone-800 hover:border-blue-500/50 hover:bg-stone-800/80 transition-all text-center group">
            <div class="w-10 h-10 mx-auto rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-lg mb-2 group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-truck-ramp-box"></i>
            </div>
            <span class="text-xs font-bold text-stone-200 group-hover:text-white">Terima Stok Gudang</span>
        </a>
    </div>

</div>
@endsection
