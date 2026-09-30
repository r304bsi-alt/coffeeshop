@extends('layouts.app')

@section('title', 'Laporan Stok Barang - Owner')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 glass-panel p-6 rounded-3xl">
        <div>
            <div class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-1">
                <i class="fa-solid fa-boxes-stacked mr-1"></i> Inventory Tracking
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Laporan Stok Barang Terpadu</h1>
            <p class="text-stone-400 text-xs sm:text-sm mt-1">Perbandingan real-time inventaris di Gudang Utama dan Toko Kasir.</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-stone-800 hover:bg-stone-700 text-stone-200 font-semibold text-xs rounded-xl border border-stone-700 transition-all flex items-center gap-2">
                <i class="fa-solid fa-print"></i> Cetak Laporan
            </button>
        </div>
    </div>

    <!-- Inventory Table Card -->
    <div class="bg-stone-900/90 border border-stone-800 rounded-3xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-stone-300">
                <thead class="bg-stone-800/80 text-[11px] uppercase tracking-wider text-stone-400 font-bold border-b border-stone-700/80">
                    <tr>
                        <th class="py-3.5 px-4 sm:px-6">Kode & Bahan Baku</th>
                        <th class="py-3.5 px-4 text-center">Satuan</th>
                        <th class="py-3.5 px-4 text-right">Stok Gudang</th>
                        <th class="py-3.5 px-4 text-right">Stok Toko</th>
                        <th class="py-3.5 px-4 text-right">Total Fisik</th>
                        <th class="py-3.5 px-4 text-right">Batas Min.</th>
                        <th class="py-3.5 px-4 sm:px-6 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-800/80">
                    @forelse($ingredients as $ing)
                    @php
                        $gudangQty = $ing->gudang_quantity;
                        $tokoQty = $ing->toko_quantity;
                        $totalQty = $gudangQty + $tokoQty;
                        $isLow = $totalQty <= $ing->minimum_stock;
                        $isCritical = $totalQty <= ($ing->minimum_stock * 0.5);
                    @endphp
                    <tr class="hover:bg-stone-800/30 transition-colors {{ $isLow ? 'bg-red-950/10' : '' }}">
                        <td class="py-4 px-4 sm:px-6">
                            <span class="font-mono text-xs text-amber-400 font-semibold block">{{ $ing->code }}</span>
                            <span class="font-bold text-white text-sm">{{ $ing->name }}</span>
                        </td>
                        <td class="py-4 px-4 text-center text-xs font-semibold text-stone-400">
                            {{ $ing->unit }}
                        </td>
                        <td class="py-4 px-4 text-right font-mono text-xs font-bold text-blue-300">
                            {{ number_format($gudangQty, 2, ',', '.') }}
                        </td>
                        <td class="py-4 px-4 text-right font-mono text-xs font-bold text-emerald-300">
                            {{ number_format($tokoQty, 2, ',', '.') }}
                        </td>
                        <td class="py-4 px-4 text-right font-mono text-sm font-black text-white">
                            {{ number_format($totalQty, 2, ',', '.') }}
                        </td>
                        <td class="py-4 px-4 text-right font-mono text-xs text-stone-400">
                            {{ number_format($ing->minimum_stock, 2, ',', '.') }}
                        </td>
                        <td class="py-4 px-4 sm:px-6 text-center">
                            @if($isCritical)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-900/60 text-red-300 border border-red-700/50">
                                    <i class="fa-solid fa-triangle-exclamation mr-1"></i> KRITIS
                                </span>
                            @elseif($isLow)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-900/60 text-amber-300 border border-amber-700/50">
                                    <i class="fa-solid fa-circle-exclamation mr-1"></i> MENIPIS
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-900/60 text-emerald-300 border border-emerald-700/50">
                                    <i class="fa-solid fa-circle-check mr-1"></i> AMAN
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-stone-500 text-xs">Belum ada data bahan baku.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
