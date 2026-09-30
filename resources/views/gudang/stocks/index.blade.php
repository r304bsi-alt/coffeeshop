@extends('layouts.app')

@section('title', 'Stok Gudang - Gudang')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 glass-panel p-6 rounded-3xl">
        <div>
            <div class="text-xs font-bold text-blue-400 uppercase tracking-wider mb-1">
                <i class="fa-solid fa-boxes-stacked mr-1"></i> Warehouse Inventory
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Ketersediaan Stok Gudang Utama</h1>
            <p class="text-stone-400 text-xs sm:text-sm mt-1">Daftar saldo stok bahan baku yang tersimpan di gudang.</p>
        </div>
        <a href="{{ route('gudang.stocks.mutations') }}" class="px-4 py-2.5 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-300 font-semibold text-xs border border-stone-700 transition-all">
            <i class="fa-solid fa-clock-rotate-left mr-1.5"></i> Riwayat Mutasi Stok
        </a>
    </div>

    <!-- Stock Table -->
    <div class="bg-stone-900/90 border border-stone-800 rounded-3xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-stone-300">
                <thead class="bg-stone-800/80 text-[11px] uppercase tracking-wider text-stone-400 font-bold border-b border-stone-700/80">
                    <tr>
                        <th class="py-3.5 px-4 sm:px-6">Kode Bahan</th>
                        <th class="py-3.5 px-4">Nama Bahan Baku</th>
                        <th class="py-3.5 px-4 text-center">Satuan</th>
                        <th class="py-3.5 px-4 text-right">Saldo Stok Gudang</th>
                        <th class="py-3.5 px-4 text-right">Batas Minimum</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-800/80">
                    @forelse($ingredients as $ing)
                    @php $qty = $ing->gudang_quantity; @endphp
                    <tr class="hover:bg-stone-800/30 transition-colors">
                        <td class="py-4 px-4 sm:px-6 font-mono text-xs font-bold text-amber-400">
                            {{ $ing->code }}
                        </td>
                        <td class="py-4 px-4 font-bold text-white text-sm">
                            {{ $ing->name }}
                        </td>
                        <td class="py-4 px-4 text-center text-xs text-stone-400">
                            {{ $ing->unit }}
                        </td>
                        <td class="py-4 px-4 text-right font-mono font-bold text-blue-300 text-base">
                            {{ number_format($qty, 2, ',', '.') }}
                        </td>
                        <td class="py-4 px-4 text-right font-mono text-xs text-stone-400">
                            {{ number_format($ing->minimum_stock, 2, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-stone-500 text-xs">Belum ada data stok gudang.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
