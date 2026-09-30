@extends('layouts.app')

@section('title', 'Detail PO #' . $procurement->po_number . ' - Pengadaan')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-extrabold text-white tracking-tight">Purchase Order #{{ $procurement->po_number }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider
                    {{ $procurement->status === 'completed' ? 'bg-emerald-900/60 text-emerald-300 border border-emerald-700/50' : '' }}
                    {{ $procurement->status === 'approved' ? 'bg-blue-900/60 text-blue-300 border border-blue-700/50' : '' }}
                    {{ $procurement->status === 'ordered' ? 'bg-cyan-900/60 text-cyan-300 border border-cyan-700/50' : '' }}
                    {{ $procurement->status === 'pending' ? 'bg-amber-900/60 text-amber-300 border border-amber-700/50' : '' }}
                    {{ $procurement->status === 'rejected' ? 'bg-red-900/60 text-red-300 border border-red-700/50' : '' }}
                ">
                    {{ $procurement->status }}
                </span>
            </div>
            <p class="text-stone-400 text-xs mt-1">Diajukan oleh: {{ $procurement->creator?->name }} &bull; {{ $procurement->created_at->format('d M Y, H:i') }}</p>
        </div>

        <div class="flex items-center gap-2">
            @if($procurement->isApproved())
                <a href="{{ route('pengadaan.print', $procurement) }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-200 text-xs font-semibold border border-stone-700 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-print"></i> Cetak PO
                </a>

                @if($procurement->supplier_email)
                <form action="{{ route('pengadaan.send_email', $procurement) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs shadow-md transition-all flex items-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i> Kirim Email ke Supplier
                    </button>
                </form>
                @endif
            @else
                <span class="text-xs text-amber-400 bg-amber-500/10 border border-amber-500/20 px-3 py-1.5 rounded-xl font-medium">
                    <i class="fa-solid fa-lock mr-1"></i> Cetak/Email terkunci hingga disetujui Owner
                </span>
            @endif
            <a href="{{ route('pengadaan.index') }}" class="px-3 py-2 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-300 text-xs font-semibold">
                &larr; Kembali
            </a>
        </div>
    </div>

    <!-- PO Details Card -->
    <div class="bg-stone-900/90 border border-stone-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 p-4 rounded-2xl bg-stone-800/40 border border-stone-700/60 text-xs">
            <div>
                <h4 class="font-bold text-stone-400 uppercase tracking-wider mb-2">Tujuan Pengadaan (Supplier):</h4>
                <div class="text-white text-base font-bold">{{ $procurement->supplier_name }}</div>
                <div class="text-stone-300 mt-1">Email: {{ $procurement->supplier_email ?? '-' }}</div>
                <div class="text-stone-300">Telepon: {{ $procurement->supplier_phone ?? '-' }}</div>
            </div>
            <div>
                <h4 class="font-bold text-stone-400 uppercase tracking-wider mb-2">Status Persetujuan Owner:</h4>
                @if($procurement->approved_at)
                    <div class="text-emerald-400 font-bold text-sm flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-check"></i> Telah Disetujui Owner
                    </div>
                    <div class="text-stone-400 mt-1">Oleh: {{ $procurement->approver?->name }} ({{ $procurement->approved_at->format('d/m/Y H:i') }})</div>
                @elseif($procurement->status === 'rejected')
                    <div class="text-red-400 font-bold text-sm flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-xmark"></i> Ditolak Owner
                    </div>
                    <div class="text-stone-300 mt-1 italic">Alasan: "{{ $procurement->rejection_reason }}"</div>
                @else
                    <div class="text-amber-400 font-bold text-sm flex items-center gap-1.5">
                        <i class="fa-solid fa-clock"></i> Masih Menunggu Tinjauan Owner
                    </div>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <div>
            <h3 class="text-xs font-bold text-stone-300 uppercase tracking-wider mb-3">Rincian Barang & Estimasi Biaya:</h3>
            <div class="overflow-x-auto rounded-2xl border border-stone-800">
                <table class="w-full text-left text-xs text-stone-300">
                    <thead class="bg-stone-800 text-[10px] uppercase font-bold text-stone-400">
                        <tr>
                            <th class="py-3 px-4">Nama Bahan</th>
                            <th class="py-3 px-4 text-center">Satuan</th>
                            <th class="py-3 px-4 text-right">Qty Diminta</th>
                            <th class="py-3 px-4 text-right">Qty Diterima Gudang</th>
                            <th class="py-3 px-4 text-right">Harga Satuan</th>
                            <th class="py-3 px-4 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-800 bg-stone-900/50">
                        @foreach($procurement->items as $it)
                        <tr>
                            <td class="py-3.5 px-4 font-bold text-white">
                                {{ $it->ingredient?->name }}
                            </td>
                            <td class="py-3.5 px-4 text-center text-stone-400">
                                {{ $it->ingredient?->unit }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-amber-400">
                                {{ number_format($it->quantity_requested, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold {{ $it->quantity_received >= $it->quantity_requested ? 'text-emerald-400' : 'text-stone-400' }}">
                                {{ number_format($it->quantity_received, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-stone-400">
                                Rp {{ number_format($it->unit_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-white">
                                Rp {{ number_format($it->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-stone-800/80 font-bold border-t border-stone-700">
                        <tr>
                            <td colspan="5" class="py-3 px-4 text-right text-stone-300">TOTAL ESTIMASI BIAYA:</td>
                            <td class="py-3 px-4 text-right font-mono text-base text-cyan-400">
                                Rp {{ number_format($procurement->total_cost, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
