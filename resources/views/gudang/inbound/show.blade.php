@extends('layouts.app')

@section('title', 'Pemeriksaan Barang Masuk - Gudang')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Cocokkan Barang Masuk dari Supplier</h1>
            <p class="text-stone-400 text-xs mt-1">Periksa fisik barang supplier sesuai Purchase Order #{{ $procurement->po_number }}.</p>
        </div>
        <a href="{{ route('gudang.inbound.index') }}" class="px-3 py-1.5 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-300 text-xs font-semibold">
            &larr; Kembali
        </a>
    </div>

    <!-- PO Details Banner -->
    <div class="bg-stone-900/90 border border-stone-800 rounded-3xl p-6 shadow-xl space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 border-b border-stone-800 pb-4 text-xs">
            <div>
                <span class="text-stone-500 block">Nomor PO:</span>
                <strong class="font-mono text-amber-400 text-sm">{{ $procurement->po_number }}</strong>
            </div>
            <div>
                <span class="text-stone-500 block">Nama Supplier:</span>
                <strong class="text-white text-sm">{{ $procurement->supplier_name }}</strong>
            </div>
            <div>
                <span class="text-stone-500 block">Disetujui Oleh:</span>
                <strong class="text-emerald-400 text-sm">{{ $procurement->approver?->name ?? 'Owner' }}</strong>
            </div>
        </div>

        <form action="{{ route('gudang.inbound.receive', $procurement) }}" method="POST">
            @csrf

            <h3 class="text-sm font-bold text-white mb-3 flex items-center gap-2">
                <i class="fa-solid fa-list-check text-emerald-400"></i> Cocokkan Kuantitas Diterima:
            </h3>

            <div class="overflow-x-auto rounded-2xl border border-stone-800 mb-5">
                <table class="w-full text-left text-xs text-stone-300">
                    <thead class="bg-stone-800/80 text-[10px] uppercase font-bold text-stone-400">
                        <tr>
                            <th class="py-3 px-4">Bahan Baku</th>
                            <th class="py-3 px-4 text-center">Satuan</th>
                            <th class="py-3 px-4 text-right">Dipesan di PO</th>
                            <th class="py-3 px-4 text-right">Stok Gudang Saat Ini</th>
                            <th class="py-3 px-4 text-right w-44">Jumlah Diterima Nyata</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-800 bg-stone-900/50">
                        @foreach($procurement->items as $item)
                        <tr>
                            <td class="py-3 px-4 font-bold text-white">
                                {{ $item->ingredient?->name }}
                                <span class="block font-mono text-[10px] text-stone-500 font-normal">{{ $item->ingredient?->code }}</span>
                            </td>
                            <td class="py-3 px-4 text-center font-semibold text-stone-400">
                                {{ $item->ingredient?->unit }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-amber-400">
                                {{ number_format($item->quantity_requested, 2) }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-stone-400">
                                {{ number_format($item->ingredient?->gudang_quantity, 2) }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                <input type="number" step="0.01" name="received[{{ $item->id }}]" 
                                    value="{{ old("received.{$item->id}", $item->quantity_requested) }}" 
                                    required 
                                    class="w-full text-right px-3 py-1.5 bg-stone-800 border border-stone-700 focus:border-emerald-500 rounded-lg text-white font-mono font-bold text-xs">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mb-5">
                <label class="block text-xs font-bold text-stone-400 uppercase tracking-wider mb-2">Catatan Tambahan Penerimaan Gudang (Opsional):</label>
                <textarea name="notes" rows="2" class="w-full p-3 bg-stone-800 border border-stone-700 rounded-xl text-white text-xs focus:outline-none focus:border-emerald-500" placeholder="Kondisi kemasan utuh, surat jalan No. SJ-xxxx"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-800">
                <a href="{{ route('gudang.inbound.index') }}" class="px-4 py-2 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-300 text-xs font-semibold">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg shadow-emerald-600/30 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-boxes-packing"></i> Konfirmasi Terima & Tambah Stok Gudang
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
