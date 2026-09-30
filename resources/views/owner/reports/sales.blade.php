@extends('layouts.app')

@section('title', 'Laporan Penjualan - Owner')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 glass-panel p-6 rounded-3xl">
        <div>
            <div class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-1">
                <i class="fa-solid fa-chart-line mr-1"></i> Sales Analytics
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Laporan Penjualan</h1>
            <p class="text-stone-400 text-xs sm:text-sm mt-1">Pantau omzet pendapatan, volume pesanan, dan menu terlaris.</p>
        </div>
        
        <!-- Filter Form -->
        <form method="GET" action="{{ route('owner.reports.sales') }}" class="flex flex-wrap items-center gap-2">
            <div>
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-2 bg-stone-800 border border-stone-700 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500">
            </div>
            <span class="text-stone-500 text-xs">s/d</span>
            <div>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-2 bg-stone-800 border border-stone-700 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-stone-950 font-bold text-xs rounded-xl transition-all">
                Filter
            </button>
        </form>
    </div>

    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-stone-900/90 border border-stone-800 rounded-2xl p-5 shadow-lg">
            <span class="text-xs font-semibold text-stone-400">Total Omzet Penjualan</span>
            <div class="text-2xl font-black text-emerald-400 mt-2">
                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
            </div>
            <span class="text-[11px] text-stone-500">Periode: {{ $startDate }} - {{ $endDate }}</span>
        </div>

        <div class="bg-stone-900/90 border border-stone-800 rounded-2xl p-5 shadow-lg">
            <span class="text-xs font-semibold text-stone-400">Total Transaksi Lunas</span>
            <div class="text-2xl font-black text-white mt-2">
                {{ $totalOrders }} Pesanan
            </div>
            <span class="text-[11px] text-stone-500">Berhasil diproses</span>
        </div>

        <div class="bg-stone-900/90 border border-stone-800 rounded-2xl p-5 shadow-lg">
            <span class="text-xs font-semibold text-stone-400">Total Cup/Porsi Terjual</span>
            <div class="text-2xl font-black text-amber-400 mt-2">
                {{ $totalItemsSold }} Item
            </div>
            <span class="text-[11px] text-stone-500">Semua kategori menu</span>
        </div>
    </div>

    <!-- Top Selling Menu Items -->
    <div class="bg-stone-900/90 border border-stone-800 rounded-3xl p-6 shadow-xl">
        <h3 class="font-bold text-white text-base mb-4 flex items-center gap-2">
            <i class="fa-solid fa-trophy text-amber-400"></i> Menu Terlaris (Top Selling Items)
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-stone-300">
                <thead class="bg-stone-800/80 text-[11px] uppercase tracking-wider text-stone-400 font-bold border-b border-stone-700/80">
                    <tr>
                        <th class="py-3 px-4">Ranking</th>
                        <th class="py-3 px-4">Nama Menu</th>
                        <th class="py-3 px-4 text-center">Porsi Terjual</th>
                        <th class="py-3 px-4 text-right">Total Pendapatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-800/80">
                    @forelse($itemSales as $idx => $item)
                    <tr class="hover:bg-stone-800/30">
                        <td class="py-3 px-4 font-bold text-amber-400 text-xs">#{{ $idx + 1 }}</td>
                        <td class="py-3 px-4 font-semibold text-white">{{ $item->menu_name }}</td>
                        <td class="py-3 px-4 text-center font-bold text-amber-300">{{ $item->total_qty }} porsi</td>
                        <td class="py-3 px-4 text-right font-bold text-emerald-400">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-xs text-stone-500">Belum ada data penjualan pada periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detailed Transactions -->
    <div class="bg-stone-900/90 border border-stone-800 rounded-3xl p-6 shadow-xl">
        <h3 class="font-bold text-white text-base mb-4 flex items-center gap-2">
            <i class="fa-solid fa-receipt text-emerald-400"></i> Rincian Transaksi
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-stone-300">
                <thead class="bg-stone-800/80 text-[11px] uppercase tracking-wider text-stone-400 font-bold border-b border-stone-700/80">
                    <tr>
                        <th class="py-3 px-4">No. Order</th>
                        <th class="py-3 px-4">Pelanggan</th>
                        <th class="py-3 px-4">Meja</th>
                        <th class="py-3 px-4">Metode Bayar</th>
                        <th class="py-3 px-4">Waktu Bayar</th>
                        <th class="py-3 px-4 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-800/80">
                    @forelse($orders as $o)
                    <tr class="hover:bg-stone-800/30">
                        <td class="py-3 px-4 font-mono text-xs font-bold text-white">{{ $o->order_number }}</td>
                        <td class="py-3 px-4 text-xs font-medium">{{ $o->customer_name }}</td>
                        <td class="py-3 px-4 text-xs text-stone-400">{{ $o->table_number ?? 'Take Away' }}</td>
                        <td class="py-3 px-4 text-xs">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-stone-800 border border-stone-700 uppercase">
                                {{ $o->payment_method }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-xs text-stone-400">{{ $o->paid_at?->format('d/m/Y H:i') }}</td>
                        <td class="py-3 px-4 text-right font-bold text-white">Rp {{ number_format($o->total_amount, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-xs text-stone-500">Tidak ada riwayat transaksi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
