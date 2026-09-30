@extends('layouts.app')

@section('title', 'Gudang Dashboard - Kopi Senja')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 glass-panel p-6 rounded-3xl">
        <div>
            <div class="text-xs font-bold text-blue-400 uppercase tracking-wider mb-1">
                <i class="fa-solid fa-warehouse mr-1"></i> Warehouse Operations
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Operasional Gudang Stok</h1>
            <p class="text-stone-400 text-xs sm:text-sm mt-1">Penerimaan barang dari supplier & pengeluaran stok menuju toko kasir.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('gudang.inbound.index') }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition-all">
                <i class="fa-solid fa-arrow-down-to-bracket mr-1.5"></i> Terima Barang Masuk
            </a>
            <a href="{{ route('gudang.transfers.create') }}" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-stone-950 font-bold text-xs shadow-md transition-all">
                <i class="fa-solid fa-arrow-up-from-bracket mr-1.5"></i> Catat Barang Keluar (Toko)
            </a>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-stone-900/90 border border-stone-800 rounded-2xl p-5 shadow-lg">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-stone-400">PO Siap Diterima (Inbound)</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-truck"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-white mt-3">{{ $inboundPendingCount }} PO</div>
            <div class="text-[11px] text-emerald-400 mt-1">
                <a href="{{ route('gudang.inbound.index') }}" class="hover:underline">Cocokkan surat jalan &rarr;</a>
            </div>
        </div>

        <div class="bg-stone-900/90 border border-stone-800 rounded-2xl p-5 shadow-lg">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-stone-400">Pengiriman Ke Toko (Outbound)</span>
                <div class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-dolly"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-white mt-3">{{ $dispatchedTransfersCount }} Pengiriman</div>
            <div class="text-[11px] text-blue-400 mt-1">Menunggu konfirmasi terima kasir</div>
        </div>

        <div class="bg-stone-900/90 border border-stone-800 rounded-2xl p-5 shadow-lg">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-stone-400">Total Jenis Bahan di Gudang</span>
                <div class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-white mt-3">{{ $gudangStocks->count() }} Bahan</div>
            <div class="text-[11px] text-stone-400 mt-1">
                <a href="{{ route('gudang.stocks.index') }}" class="hover:underline">Lihat semua stok &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Dual Lists: Recent Inbound vs Outbound Transfers -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Recent Inbound -->
        <div class="bg-stone-900/90 border border-stone-800 rounded-3xl p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4 border-b border-stone-800 pb-3">
                <h3 class="font-bold text-white text-base flex items-center gap-2">
                    <i class="fa-solid fa-truck-ramp-box text-emerald-400"></i> Riwayat Masuk (Supplier)
                </h3>
                <a href="{{ route('gudang.stocks.mutations') }}" class="text-xs text-amber-400 hover:underline">Semua Mutasi</a>
            </div>
            <div class="divide-y divide-stone-800/80">
                @forelse($recentInbound as $mut)
                <div class="py-3 flex items-center justify-between text-xs">
                    <div>
                        <div class="font-bold text-white">{{ $mut->ingredient?->name }}</div>
                        <div class="text-stone-400 text-[11px] mt-0.5">{{ $mut->notes }}</div>
                        <div class="text-stone-500 text-[10px] mt-0.5">{{ $mut->created_at->diffForHumans() }} &bull; Oleh: {{ $mut->user?->name }}</div>
                    </div>
                    <div class="text-right">
                        <span class="font-bold text-emerald-400 font-mono text-sm">+{{ number_format($mut->quantity_change, 2) }} {{ $mut->ingredient?->unit }}</span>
                        <div class="text-[10px] text-stone-500">Saldo: {{ number_format($mut->balance_after, 2) }}</div>
                    </div>
                </div>
                @empty
                <div class="py-6 text-center text-xs text-stone-500">Belum ada barang masuk tercatat.</div>
                @endforelse
            </div>
        </div>

        <!-- Recent Outbound to Store -->
        <div class="bg-stone-900/90 border border-stone-800 rounded-3xl p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4 border-b border-stone-800 pb-3">
                <h3 class="font-bold text-white text-base flex items-center gap-2">
                    <i class="fa-solid fa-arrow-right-arrow-left text-blue-400"></i> Pengiriman ke Toko Kasir
                </h3>
                <a href="{{ route('gudang.transfers.index') }}" class="text-xs text-amber-400 hover:underline">Lihat Semua</a>
            </div>
            <div class="divide-y divide-stone-800/80">
                @forelse($recentTransfers as $trf)
                <div class="py-3 flex items-center justify-between text-xs">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-bold text-white">{{ $trf->transfer_number }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                                {{ $trf->status === 'received' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-blue-500/20 text-blue-400' }}">
                                {{ strtoupper($trf->status) }}
                            </span>
                        </div>
                        <div class="text-stone-400 text-[11px] mt-1">
                            Diserahkan: {{ $trf->dispatcher?->name }} &bull; Diterima: {{ $trf->receiver?->name ?? 'Belum diterima' }}
                        </div>
                        <div class="text-stone-500 text-[10px]">
                            Tgl Keluar: {{ $trf->dispatch_date->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-bold text-white">{{ $trf->items->count() }} Bahan</span>
                        <div class="text-[10px] text-stone-500">
                            <a href="{{ route('gudang.transfers.show', $trf) }}" class="text-blue-400 hover:underline">Lihat Rincian</a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="py-6 text-center text-xs text-stone-500">Belum ada pengiriman ke toko.</div>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection
