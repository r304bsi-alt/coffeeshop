@extends('layouts.app')

@section('title', 'Laporan Laba Rugi - Owner')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 glass-panel p-6 rounded-3xl">
        <div>
            <div class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-1">
                <i class="fa-solid fa-scale-balanced mr-1"></i> Financial Performance
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Laporan Laba Rugi (P&L)</h1>
            <p class="text-stone-400 text-xs sm:text-sm mt-1">Perhitungan pendapatan bersih, estimasi HPP bahan baku (BOM), dan margin laba kotor.</p>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('owner.reports.profit_loss') }}" class="flex flex-wrap items-center gap-2">
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

    <!-- 4 Key Financial Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Revenue -->
        <div class="bg-stone-900/90 border border-stone-800 rounded-2xl p-5 shadow-lg">
            <span class="text-xs font-semibold text-stone-400">Total Omzet (Revenue)</span>
            <div class="text-2xl font-black text-white mt-2">
                Rp {{ number_format($grossRevenue, 0, ',', '.') }}
            </div>
            <span class="text-[11px] text-stone-500">{{ $paidOrders->count() }} transaksi lunas</span>
        </div>

        <!-- COGS / HPP -->
        <div class="bg-stone-900/90 border border-stone-800 rounded-2xl p-5 shadow-lg">
            <span class="text-xs font-semibold text-stone-400">Total HPP Bahan Baku (BOM)</span>
            <div class="text-2xl font-black text-red-400 mt-2">
                Rp {{ number_format($totalHpp, 0, ',', '.') }}
            </div>
            <span class="text-[11px] text-stone-500">Pemotongan gramasi presisi</span>
        </div>

        <!-- Gross Profit -->
        <div class="bg-stone-900/90 border border-stone-800 rounded-2xl p-5 shadow-lg">
            <span class="text-xs font-semibold text-stone-400">Estimasi Laba Kotor (Gross Profit)</span>
            <div class="text-2xl font-black text-emerald-400 mt-2">
                Rp {{ number_format($grossProfit, 0, ',', '.') }}
            </div>
            <span class="text-[11px] text-emerald-400 font-semibold">Omzet - HPP Bahan</span>
        </div>

        <!-- Margin % -->
        <div class="bg-stone-900/90 border border-stone-800 rounded-2xl p-5 shadow-lg">
            <span class="text-xs font-semibold text-stone-400">Gross Profit Margin</span>
            <div class="text-2xl font-black text-amber-400 mt-2">
                {{ number_format($profitMargin, 1, ',', '.') }}%
            </div>
            <span class="text-[11px] text-stone-500">Efisiensi margin operasional</span>
        </div>
    </div>

    <!-- Financial Statement Table -->
    <div class="bg-stone-900/90 border border-stone-800 rounded-3xl p-6 sm:p-8 shadow-2xl">
        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
            <i class="fa-solid fa-file-invoice text-amber-400"></i> Ringkasan Neraca Laba Rugi
        </h3>

        <div class="space-y-4 max-w-3xl">
            <!-- 1. Revenue -->
            <div class="flex items-center justify-between py-2 border-b border-stone-800">
                <span class="font-bold text-white text-sm">1. Pendapatan Penjualan Menu (Gross Revenue)</span>
                <span class="font-mono font-bold text-white text-sm">Rp {{ number_format($grossRevenue, 0, ',', '.') }}</span>
            </div>

            <!-- 2. Cost of Sales -->
            <div class="flex items-center justify-between py-2 border-b border-stone-800 pl-4 text-red-400">
                <span class="text-sm">2. Harga Pokok Penjualan (HPP) Komposisi Bahan Baku</span>
                <span class="font-mono text-sm">(Rp {{ number_format($totalHpp, 0, ',', '.') }})</span>
            </div>

            <!-- 3. Gross Profit -->
            <div class="flex items-center justify-between py-3 border-y-2 border-stone-700 bg-stone-800/40 px-3 rounded-xl">
                <div>
                    <span class="font-extrabold text-emerald-400 text-base block">3. LABA KOTOR (GROSS PROFIT)</span>
                    <span class="text-[11px] text-stone-400">Margin laba kotor: {{ number_format($profitMargin, 1) }}%</span>
                </div>
                <span class="font-mono font-black text-emerald-400 text-lg">Rp {{ number_format($grossProfit, 0, ',', '.') }}</span>
            </div>

            <!-- 4. Procurement Purchases -->
            <div class="flex items-center justify-between py-2 border-b border-stone-800 pl-4 text-stone-400 text-xs">
                <span>Catatan: Belanja Pengadaan Barang Supplier di Periode Ini</span>
                <span class="font-mono text-stone-300">Rp {{ number_format($procurementExpenses, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
