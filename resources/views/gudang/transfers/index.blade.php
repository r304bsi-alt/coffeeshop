@extends('layouts.app')

@section('title', 'Daftar Barang Keluar ke Toko - Gudang')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 glass-panel p-6 rounded-3xl">
        <div>
            <div class="text-xs font-bold text-blue-400 uppercase tracking-wider mb-1">
                <i class="fa-solid fa-arrow-right-arrow-left mr-1"></i> Outbound Stock Transfers
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Pengeluaran Stok Barang ke Toko</h1>
            <p class="text-stone-400 text-xs sm:text-sm mt-1">Daftar stok yang dikeluarkan dari gudang untuk diterima oleh bagian kasir/toko.</p>
        </div>
        <a href="{{ route('gudang.transfers.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-stone-950 font-bold text-xs shadow-lg shadow-amber-500/20 transition-all">
            <i class="fa-solid fa-plus"></i> Buat Pengeluaran Stok Baru
        </a>
    </div>

    <!-- Transfers Table -->
    <div class="bg-stone-900/90 border border-stone-800 rounded-3xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-stone-300">
                <thead class="bg-stone-800/80 text-[11px] uppercase tracking-wider text-stone-400 font-bold border-b border-stone-700/80">
                    <tr>
                        <th class="py-3.5 px-4 sm:px-6">No. Transfer</th>
                        <th class="py-3.5 px-4">User Pengeluar (Gudang)</th>
                        <th class="py-3.5 px-4">User Penerima (Toko)</th>
                        <th class="py-3.5 px-4">Tgl Keluar</th>
                        <th class="py-3.5 px-4">Tgl Terima</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Rincian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-800/80">
                    @forelse($transfers as $trf)
                    <tr class="hover:bg-stone-800/30 transition-colors">
                        <td class="py-4 px-4 sm:px-6 font-mono font-bold text-white text-xs">
                            {{ $trf->transfer_number }}
                            <div class="text-[10px] text-stone-500 font-sans mt-0.5">{{ $trf->items->count() }} item bahan</div>
                        </td>
                        <td class="py-4 px-4 text-xs">
                            <span class="font-semibold text-blue-300">{{ $trf->dispatcher?->name }}</span>
                            <span class="block text-[10px] text-stone-500">Staf Gudang</span>
                        </td>
                        <td class="py-4 px-4 text-xs">
                            @if($trf->receiver)
                                <span class="font-semibold text-emerald-300">{{ $trf->receiver->name }}</span>
                                <span class="block text-[10px] text-stone-500">Kasir Toko</span>
                            @else
                                <span class="text-stone-500 italic text-[11px]">Belum dikonfirmasi kasir</span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-xs font-mono text-stone-400">
                            {{ $trf->dispatch_date->format('d/m/Y H:i') }}
                        </td>
                        <td class="py-4 px-4 text-xs font-mono text-stone-400">
                            {{ $trf->receipt_date ? $trf->receipt_date->format('d/m/Y H:i') : '-' }}
                        </td>
                        <td class="py-4 px-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                {{ $trf->status === 'received' ? 'bg-emerald-900/60 text-emerald-300 border border-emerald-700/50' : 'bg-blue-900/60 text-blue-300 border border-blue-700/50' }}
                            ">
                                {{ $trf->status === 'received' ? 'DITERIMA TOKO' : 'DIKIRIM (MENUNGGU TOKO)' }}
                            </span>
                        </td>
                        <td class="py-4 px-4 sm:px-6 text-right">
                            <a href="{{ route('gudang.transfers.show', $trf) }}" class="p-2 rounded-lg bg-stone-800 hover:bg-stone-700 text-stone-300 text-xs font-semibold inline-block">
                                <i class="fa-solid fa-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-stone-500 text-xs">Belum ada riwayat pengeluaran barang ke toko.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transfers->hasPages())
        <div class="p-4 border-t border-stone-800">
            {{ $transfers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
