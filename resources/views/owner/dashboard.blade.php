@extends('layouts.app')

@section('title', 'Owner Dashboard - Kopi Senja')

@section('content')
<div class="space-y-6">
    
    <!-- Top Welcome Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 glass-panel p-6 rounded-3xl">
        <div>
            <div class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-1">
                <i class="fa-solid fa-crown mr-1"></i> Owner Portal
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                Selamat Datang, {{ auth()->user()->name }}
            </h1>
            <p class="text-stone-400 text-xs sm:text-sm mt-1">
                Ringkasan performa penjualan, ketersediaan inventaris, dan persetujuan pengadaan hari ini.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('owner.reports.sales') }}" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-stone-950 font-bold text-xs shadow-md transition-all">
                <i class="fa-solid fa-chart-pie mr-1.5"></i> Laporan Lengkap
            </a>
            <a href="{{ route('owner.users.create') }}" class="px-4 py-2 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-200 font-semibold text-xs border border-stone-700 transition-all">
                <i class="fa-solid fa-user-plus mr-1.5"></i> Tambah User
            </a>
        </div>
    </div>

    <!-- 4 Key Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Sales Today -->
        <div class="bg-stone-900/90 border border-stone-800 rounded-2xl p-5 shadow-lg relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-stone-400">Penjualan Hari Ini</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-rupiah-sign"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-white mt-3">
                Rp {{ number_format($todaySales, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-emerald-400 font-medium mt-1 flex items-center gap-1">
                <i class="fa-solid fa-receipt"></i> {{ $todayOrdersCount }} transaksi lunas
            </div>
        </div>

        <!-- Orders Count -->
        <div class="bg-stone-900/90 border border-stone-800 rounded-2xl p-5 shadow-lg relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-stone-400">Total Transaksi</span>
                <div class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-mug-hot"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-white mt-3">
                {{ $todayOrdersCount }}
            </div>
            <div class="text-[11px] text-stone-400 mt-1">
                Data real-time hari ini
            </div>
        </div>

        <!-- Pending PO Approvals -->
        <div class="bg-stone-900/90 border border-stone-800 rounded-2xl p-5 shadow-lg relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-stone-400">Menunggu Approval PO</span>
                <div class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-file-signature"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-white mt-3">
                {{ $pendingProcurementCount }}
            </div>
            <div class="text-[11px] text-amber-400 font-medium mt-1">
                @if($pendingProcurementCount > 0)
                    <a href="{{ route('owner.procurements.index') }}" class="underline hover:text-amber-300">Tinjau sekarang &rarr;</a>
                @else
                    Semua PO telah diproses
                @endif
            </div>
        </div>

        <!-- Low Stocks Alert -->
        <div class="bg-stone-900/90 border border-stone-800 rounded-2xl p-5 shadow-lg relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-stone-400">Peringatan Stok Rendah</span>
                <div class="w-8 h-8 rounded-lg bg-red-500/20 text-red-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-white mt-3">
                {{ $lowStockIngredients->count() }}
            </div>
            <div class="text-[11px] text-red-400 font-medium mt-1">
                Bahan di bawah batas minimum
            </div>
        </div>
    </div>

    <!-- Quick Navigation Shortcuts -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('owner.reports.sales') }}" class="p-4 rounded-2xl bg-stone-900/80 border border-stone-800 hover:border-amber-500/50 hover:bg-stone-800/80 transition-all text-center group">
            <div class="w-10 h-10 mx-auto rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-lg mb-2 group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <span class="text-xs font-bold text-stone-200 group-hover:text-white">Laporan Penjualan</span>
        </a>

        <a href="{{ route('owner.reports.stock') }}" class="p-4 rounded-2xl bg-stone-900/80 border border-stone-800 hover:border-amber-500/50 hover:bg-stone-800/80 transition-all text-center group">
            <div class="w-10 h-10 mx-auto rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-lg mb-2 group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <span class="text-xs font-bold text-stone-200 group-hover:text-white">Laporan Stok Barang</span>
        </a>

        <a href="{{ route('owner.reports.profit_loss') }}" class="p-4 rounded-2xl bg-stone-900/80 border border-stone-800 hover:border-amber-500/50 hover:bg-stone-800/80 transition-all text-center group">
            <div class="w-10 h-10 mx-auto rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center text-lg mb-2 group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>
            <span class="text-xs font-bold text-stone-200 group-hover:text-white">Laporan Laba Rugi</span>
        </a>

        <a href="{{ route('owner.users.index') }}" class="p-4 rounded-2xl bg-stone-900/80 border border-stone-800 hover:border-amber-500/50 hover:bg-stone-800/80 transition-all text-center group">
            <div class="w-10 h-10 mx-auto rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-lg mb-2 group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-users"></i>
            </div>
            <span class="text-xs font-bold text-stone-200 group-hover:text-white">Kelola Pengguna</span>
        </a>
    </div>

    <!-- Dual Columns: Pending POs & Recent Transactions -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left: Pending Procurement Approvals -->
        <div class="lg:col-span-6 bg-stone-900/90 border border-stone-800 rounded-3xl p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4 border-b border-stone-800 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-file-circle-question text-amber-400 text-lg"></i>
                    <h3 class="font-bold text-white text-base">Permintaan Pengadaan Menunggu Approval</h3>
                </div>
                <a href="{{ route('owner.procurements.index') }}" class="text-xs text-amber-400 hover:underline font-semibold">Lihat Semua</a>
            </div>

            @if($pendingProcurements->isEmpty())
                <div class="py-8 text-center text-stone-500 text-xs">
                    <i class="fa-solid fa-circle-check text-stone-600 text-3xl mb-2"></i>
                    <p>Tidak ada permintaan pengadaan yang menunggu persetujuan.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($pendingProcurements as $po)
                    <div class="p-4 rounded-2xl bg-stone-800/60 border border-stone-700/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-white text-sm">{{ $po->po_number }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-400">PENDING</span>
                            </div>
                            <div class="text-xs text-stone-400 mt-1">
                                Supplier: <strong class="text-stone-300">{{ $po->supplier_name }}</strong>
                            </div>
                            <div class="text-[11px] text-stone-500">
                                Dibuat oleh: {{ $po->creator?->name }} &bull; {{ $po->created_at->diffForHumans() }}
                            </div>
                        </div>
                        <div class="text-right flex sm:flex-col items-center sm:items-end justify-between gap-2">
                            <div class="text-sm font-bold text-white">
                                Rp {{ number_format($po->total_cost, 0, ',', '.') }}
                            </div>
                            <div class="flex items-center gap-1.5">
                                <form action="{{ route('owner.procurements.approve', $po) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition-colors">
                                        Setujui
                                    </button>
                                </form>
                                <a href="{{ route('owner.procurements.index') }}" class="px-2.5 py-1 rounded-lg bg-stone-700 hover:bg-stone-600 text-stone-200 text-xs">
                                    Detail
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Right: Recent Sales Transactions -->
        <div class="lg:col-span-6 bg-stone-900/90 border border-stone-800 rounded-3xl p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4 border-b border-stone-800 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-emerald-400 text-lg"></i>
                    <h3 class="font-bold text-white text-base">Transaksi Penjualan Terkini</h3>
                </div>
                <a href="{{ route('owner.reports.sales') }}" class="text-xs text-amber-400 hover:underline font-semibold">Laporan Lengkap</a>
            </div>

            <div class="divide-y divide-stone-800/80">
                @forelse($recentOrders as $ord)
                <div class="py-3 flex items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-white text-xs">{{ $ord->order_number }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                                {{ $ord->payment_status === 'paid' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400' }}">
                                {{ strtoupper($ord->payment_status) }}
                            </span>
                        </div>
                        <div class="text-xs text-stone-400 mt-0.5">
                            {{ $ord->customer_name }} ({{ $ord->table_number ?? 'Take Away' }})
                        </div>
                        <div class="text-[10px] text-stone-500">
                            {{ $ord->created_at->diffForHumans() }} &bull; Metode: {{ strtoupper($ord->payment_method) }}
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="font-bold text-white text-sm">Rp {{ number_format($ord->total_amount, 0, ',', '.') }}</span>
                        <div class="text-[10px] text-stone-500">{{ $ord->items->sum('quantity') }} item</div>
                    </div>
                </div>
                @empty
                <div class="py-8 text-center text-stone-500 text-xs">Belum ada transaksi hari ini.</div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
