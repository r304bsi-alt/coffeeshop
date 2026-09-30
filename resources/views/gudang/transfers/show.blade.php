@extends('layouts.app')

@section('title', 'Detail Pengeluaran Stok - Gudang')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Detail Pengeluaran Barang #{{ $transfer->transfer_number }}</h1>
            <p class="text-stone-400 text-xs mt-1">Dokumen mutasi pengeluaran stok gudang menuju kasir toko.</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-3 py-1.5 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-300 text-xs font-semibold border border-stone-700">
                <i class="fa-solid fa-print mr-1"></i> Cetak
            </button>
            <a href="{{ route('gudang.transfers.index') }}" class="px-3 py-1.5 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-300 text-xs font-semibold">
                &larr; Kembali
            </a>
        </div>
    </div>

    <div class="bg-stone-900/90 border border-stone-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
        <!-- Status & Meta Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-2xl bg-stone-800/50 border border-stone-700/60 text-xs">
            <div>
                <span class="text-stone-500 block">Staf Pengeluar (Gudang):</span>
                <strong class="text-blue-300 text-sm">{{ $transfer->dispatcher?->name }}</strong>
                <span class="text-stone-400 block mt-1">Tgl Keluar: {{ $transfer->dispatch_date->format('d/m/Y H:i') }} WIB</span>
            </div>
            <div>
                <span class="text-stone-500 block">Staf Penerima (Toko/Kasir):</span>
                @if($transfer->receiver)
                    <strong class="text-emerald-300 text-sm">{{ $transfer->receiver->name }}</strong>
                    <span class="text-stone-400 block mt-1">Tgl Terima: {{ $transfer->receipt_date?->format('d/m/Y H:i') }} WIB</span>
                @else
                    <span class="text-amber-400 italic font-semibold">Menunggu konfirmasi penerimaan kasir</span>
                @endif
            </div>
        </div>

        @if($transfer->notes)
        <div class="p-3 bg-stone-800/40 rounded-xl border border-stone-700/40 text-xs text-stone-300">
            <strong>Catatan:</strong> {{ $transfer->notes }}
        </div>
        @endif

        <!-- Items Table -->
        <div>
            <h3 class="text-xs font-bold text-stone-400 uppercase tracking-wider mb-3">Daftar Bahan Yang Dikeluarkan:</h3>
            <div class="overflow-x-auto rounded-2xl border border-stone-800">
                <table class="w-full text-left text-xs text-stone-300">
                    <thead class="bg-stone-800 text-[10px] uppercase font-bold text-stone-400">
                        <tr>
                            <th class="py-3 px-4">Kode</th>
                            <th class="py-3 px-4">Nama Bahan Baku</th>
                            <th class="py-3 px-4 text-center">Satuan</th>
                            <th class="py-3 px-4 text-right">Jumlah Keluar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-800 bg-stone-900/50">
                        @foreach($transfer->items as $it)
                        <tr>
                            <td class="py-3 px-4 font-mono text-stone-400">{{ $it->ingredient?->code }}</td>
                            <td class="py-3 px-4 font-bold text-white">{{ $it->ingredient?->name }}</td>
                            <td class="py-3 px-4 text-center text-stone-400">{{ $it->ingredient?->unit }}</td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-emerald-400 text-sm">
                                {{ number_format($it->quantity, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
