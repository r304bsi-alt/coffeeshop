@extends('layouts.app')

@section('title', 'Penerimaan Barang Supplier - Gudang')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 glass-panel p-6 rounded-3xl">
        <div>
            <div class="text-xs font-bold text-emerald-400 uppercase tracking-wider mb-1">
                <i class="fa-solid fa-truck-ramp-box mr-1"></i> Inbound Receiving
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Penerimaan Barang Dari Supplier</h1>
            <p class="text-stone-400 text-xs sm:text-sm mt-1">Cocokkan barang datang dengan daftar Purchase Order (PO) yang telah disetujui Owner.</p>
        </div>
    </div>

    <!-- POs List -->
    <div class="bg-stone-900/90 border border-stone-800 rounded-3xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-stone-300">
                <thead class="bg-stone-800/80 text-[11px] uppercase tracking-wider text-stone-400 font-bold border-b border-stone-700/80">
                    <tr>
                        <th class="py-3.5 px-4 sm:px-6">No. PO</th>
                        <th class="py-3.5 px-4">Supplier</th>
                        <th class="py-3.5 px-4">Tgl Disetujui</th>
                        <th class="py-3.5 px-4">Disetujui Oleh</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-800/80">
                    @forelse($procurements as $po)
                    <tr class="hover:bg-stone-800/30 transition-colors">
                        <td class="py-4 px-4 sm:px-6 font-mono font-bold text-white text-xs">
                            {{ $po->po_number }}
                            <div class="text-[11px] text-stone-500 font-sans mt-0.5">{{ $po->items->count() }} jenis bahan</div>
                        </td>
                        <td class="py-4 px-4 text-xs font-semibold text-stone-200">
                            {{ $po->supplier_name }}
                            <div class="text-[10px] text-stone-500 font-normal">{{ $po->supplier_phone ?? '-' }}</div>
                        </td>
                        <td class="py-4 px-4 text-xs text-stone-400">
                            {{ $po->approved_at?->format('d/m/Y H:i') ?? '-' }}
                        </td>
                        <td class="py-4 px-4 text-xs text-stone-400">
                            {{ $po->approver?->name ?? 'Owner' }}
                        </td>
                        <td class="py-4 px-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                {{ $po->status === 'completed' ? 'bg-emerald-900/60 text-emerald-300 border border-emerald-700/50' : 'bg-blue-900/60 text-blue-300 border border-blue-700/50' }}
                            ">
                                {{ $po->status === 'completed' ? 'SELESAI DITERIMA' : 'SIAP DIPROSES' }}
                            </span>
                        </td>
                        <td class="py-4 px-4 sm:px-6 text-right">
                            <a href="{{ route('gudang.inbound.show', $po) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition-all">
                                <i class="fa-solid fa-clipboard-check"></i> Cocokkan & Terima
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-stone-500 text-xs">Belum ada pesanan pengadaan yang disetujui untuk diterima.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
