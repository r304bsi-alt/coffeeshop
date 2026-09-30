@extends('layouts.app')

@section('title', 'Riwayat Mutasi Stok Gudang - Gudang')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 glass-panel p-6 rounded-3xl">
        <div>
            <div class="text-xs font-bold text-blue-400 uppercase tracking-wider mb-1">
                <i class="fa-solid fa-clock-rotate-left mr-1"></i> Stock Mutation Logs
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Riwayat Mutasi Stok Gudang</h1>
            <p class="text-stone-400 text-xs sm:text-sm mt-1">Audit trail lengkap pergerakan barang masuk supplier dan keluar ke toko.</p>
        </div>
        <a href="{{ route('gudang.stocks.index') }}" class="px-3 py-1.5 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-300 text-xs font-semibold">
            &larr; Kembali ke Stok
        </a>
    </div>

    <!-- Mutations Table -->
    <div class="bg-stone-900/90 border border-stone-800 rounded-3xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-stone-300">
                <thead class="bg-stone-800/80 text-[11px] uppercase tracking-wider text-stone-400 font-bold border-b border-stone-700/80">
                    <tr>
                        <th class="py-3.5 px-4 sm:px-6">Waktu & Ref</th>
                        <th class="py-3.5 px-4">Bahan Baku</th>
                        <th class="py-3.5 px-4">Tipe Mutasi</th>
                        <th class="py-3.5 px-4 text-right">Perubahan</th>
                        <th class="py-3.5 px-4 text-right">Saldo Akhir</th>
                        <th class="py-3.5 px-4">Operator</th>
                        <th class="py-3.5 px-4 sm:px-6">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-800/80">
                    @forelse($mutations as $m)
                    <tr class="hover:bg-stone-800/30 transition-colors">
                        <td class="py-4 px-4 sm:px-6">
                            <span class="font-mono text-xs font-bold text-white block">{{ $m->reference_number ?? '-' }}</span>
                            <span class="text-[10px] text-stone-500">{{ $m->created_at->format('d/m/Y H:i') }}</span>
                        </td>
                        <td class="py-4 px-4 font-bold text-white text-xs">
                            {{ $m->ingredient?->name }}
                        </td>
                        <td class="py-4 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                {{ $m->type === 'inbound_supplier' ? 'bg-emerald-900/60 text-emerald-300 border border-emerald-700/50' : 'bg-amber-900/60 text-amber-300 border border-amber-700/50' }}">
                                {{ $m->type === 'inbound_supplier' ? 'MASUK SUPPLIER' : 'KELUAR TOKO' }}
                            </span>
                        </td>
                        <td class="py-4 px-4 text-right font-mono font-bold text-xs {{ $m->quantity_change > 0 ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $m->quantity_change > 0 ? '+' : '' }}{{ number_format($m->quantity_change, 2) }} {{ $m->ingredient?->unit }}
                        </td>
                        <td class="py-4 px-4 text-right font-mono font-bold text-white text-xs">
                            {{ number_format($m->balance_after, 2) }}
                        </td>
                        <td class="py-4 px-4 text-xs text-stone-400">
                            {{ $m->user?->name ?? 'Sistem' }}
                        </td>
                        <td class="py-4 px-4 sm:px-6 text-xs text-stone-400 italic">
                            {{ $m->notes }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-stone-500 text-xs">Belum ada riwayat mutasi stok.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($mutations->hasPages())
        <div class="p-4 border-t border-stone-800">
            {{ $mutations->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
