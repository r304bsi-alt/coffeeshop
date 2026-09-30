@extends('layouts.app')

@section('title', 'Penerimaan Stok Dari Gudang - Kasir')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 glass-panel p-6 rounded-3xl">
        <div>
            <div class="text-xs font-bold text-emerald-400 uppercase tracking-wider mb-1">
                <i class="fa-solid fa-truck-ramp-box mr-1"></i> Store Inbound Receiving
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Penerimaan Stok Barang Dari Gudang</h1>
            <p class="text-stone-400 text-xs sm:text-sm mt-1">Konfirmasi penerimaan stok yang dikirim oleh gudang untuk menambah stok toko secara otomatis.</p>
        </div>
    </div>

    <!-- Transfers List -->
    <div class="bg-stone-900/90 border border-stone-800 rounded-3xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-stone-300">
                <thead class="bg-stone-800/80 text-[11px] uppercase tracking-wider text-stone-400 font-bold border-b border-stone-700/80">
                    <tr>
                        <th class="py-3.5 px-4 sm:px-6">No. Transfer</th>
                        <th class="py-3.5 px-4">Pengirim (Gudang)</th>
                        <th class="py-3.5 px-4">Tgl Keluar Gudang</th>
                        <th class="py-3.5 px-4">Item Bahan</th>
                        <th class="py-3.5 px-4">Status & Penerima</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-800/80">
                    @forelse($transfers as $trf)
                    <tr class="hover:bg-stone-800/30 transition-colors">
                        <td class="py-4 px-4 sm:px-6 font-mono font-bold text-white text-xs">
                            {{ $trf->transfer_number }}
                        </td>
                        <td class="py-4 px-4 text-xs">
                            <span class="font-semibold text-blue-300">{{ $trf->dispatcher?->name }}</span>
                            <span class="block text-[10px] text-stone-500">Staf Gudang</span>
                        </td>
                        <td class="py-4 px-4 text-xs font-mono text-stone-400">
                            {{ $trf->dispatch_date->format('d/m/Y H:i') }} WIB
                        </td>
                        <td class="py-4 px-4 text-xs">
                            <span class="font-bold text-white">{{ $trf->items->count() }} Bahan</span>
                            <div class="text-[11px] text-stone-400 truncate max-w-xs mt-0.5">
                                {{ $trf->items->map(fn($it) => "{$it->ingredient?->name} (" . number_format($it->quantity, 2) . " {$it->ingredient?->unit})")->join(', ') }}
                            </div>
                        </td>
                        <td class="py-4 px-4 text-xs">
                            @if($trf->status === 'received')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-900/60 text-emerald-300 border border-emerald-700/50 block w-fit">
                                    <i class="fa-solid fa-circle-check mr-1"></i> TELAH DITERIMA
                                </span>
                                <span class="text-[10px] text-stone-400 block mt-1">
                                    Oleh: {{ $trf->receiver?->name }} &bull; {{ $trf->receipt_date?->format('d/m/Y H:i') }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-900/60 text-amber-300 border border-amber-700/50 block w-fit">
                                    <i class="fa-solid fa-truck mr-1"></i> SIAP DITERIMA
                                </span>
                                <span class="text-[10px] text-stone-500 block mt-1">Periksa fisik barang</span>
                            @endif
                        </td>
                        <td class="py-4 px-4 sm:px-6 text-right">
                            @if($trf->status === 'dispatched')
                            <form action="{{ route('kasir.transfers.receive', $trf) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition-all flex items-center gap-1.5 ml-auto">
                                    <i class="fa-solid fa-box-open"></i> Konfirmasi Terima
                                </button>
                            </form>
                            @else
                            <span class="text-xs text-stone-500 italic">Selesai</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-stone-500 text-xs">Belum ada pengiriman stok dari gudang.</td>
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
