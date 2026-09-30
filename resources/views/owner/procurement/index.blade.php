@extends('layouts.app')

@section('title', 'Persetujuan Pengadaan (PO) - Owner')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 glass-panel p-6 rounded-3xl">
        <div>
            <div class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-1">
                <i class="fa-solid fa-stamp mr-1"></i> Owner Approval
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Persetujuan Permintaan Pengadaan</h1>
            <p class="text-stone-400 text-xs sm:text-sm mt-1">Tinjau dan setujui usulan pembelian barang/stok dari Bagian Pengadaan.</p>
        </div>
    </div>

    <!-- Pending POs -->
    <div class="space-y-4">
        @forelse($procurements as $po)
        <div class="bg-stone-900/90 border border-stone-800 rounded-3xl p-6 shadow-xl" x-data="{ rejectModal: false }">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-stone-800">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="font-mono font-black text-lg text-white">{{ $po->po_number }}</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-500/20 text-amber-400 border border-amber-500/40">
                            MENUNGGU PERSETUJUAN
                        </span>
                    </div>
                    <div class="text-xs text-stone-400 mt-1">
                        Supplier: <strong class="text-white">{{ $po->supplier_name }}</strong> ({{ $po->supplier_email ?? 'Email -' }}, {{ $po->supplier_phone ?? 'Telp -' }})
                    </div>
                    <div class="text-[11px] text-stone-500 mt-0.5">
                        Diajukan oleh: <span class="text-amber-400">{{ $po->creator?->name }}</span> &bull; Diajukan pada: {{ $po->created_at->format('d M Y, H:i') }}
                    </div>
                    @if($po->notes)
                    <div class="mt-2 text-xs text-stone-300 italic bg-stone-800/60 p-2.5 rounded-xl border border-stone-700/40">
                        "{{ $po->notes }}"
                    </div>
                    @endif
                </div>

                <div class="flex flex-col sm:flex-row lg:flex-col sm:items-end justify-between gap-3">
                    <div class="text-right">
                        <span class="text-xs text-stone-400 block">Total Estimasi Biaya</span>
                        <span class="text-2xl font-black text-emerald-400 font-mono">Rp {{ number_format($po->total_cost, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <!-- Reject Button -->
                        <button type="button" @click="rejectModal = true" class="px-4 py-2 rounded-xl bg-red-950/60 hover:bg-red-900 text-red-300 font-bold text-xs border border-red-800/50 transition-colors">
                            <i class="fa-solid fa-xmark mr-1"></i> Tolak PO
                        </button>

                        <!-- Approve Button -->
                        <form action="{{ route('owner.procurements.approve', $po) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg shadow-emerald-600/30 transition-all">
                                <i class="fa-solid fa-check mr-1"></i> Setujui Pengadaan
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Items Table in PO -->
            <div class="mt-4">
                <h4 class="text-xs font-bold text-stone-400 uppercase tracking-wider mb-2">Daftar Bahan Yang Diminta:</h4>
                <div class="overflow-x-auto rounded-xl border border-stone-800">
                    <table class="w-full text-left text-xs text-stone-300">
                        <thead class="bg-stone-800 text-[10px] uppercase font-bold text-stone-400">
                            <tr>
                                <th class="py-2.5 px-3">Bahan Baku</th>
                                <th class="py-2.5 px-3 text-right">Jumlah Diminta</th>
                                <th class="py-2.5 px-3 text-right">Harga Satuan</th>
                                <th class="py-2.5 px-3 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-800 bg-stone-900/50">
                            @foreach($po->items as $item)
                            <tr>
                                <td class="py-2.5 px-3 font-semibold text-white">
                                    {{ $item->ingredient?->name }} ({{ $item->ingredient?->code }})
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono font-bold text-amber-300">
                                    {{ number_format($item->quantity_requested, 2, ',', '.') }} {{ $item->ingredient?->unit }}
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono text-stone-400">
                                    Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono font-bold text-white">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Rejection Modal -->
            <div x-show="rejectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
                <div class="bg-stone-900 border border-stone-800 rounded-3xl p-6 max-w-md w-full shadow-2xl space-y-4" @click.away="rejectModal = false">
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-red-500"></i> Alasan Penolakan PO #{{ $po->po_number }}
                    </h3>
                    <form action="{{ route('owner.procurements.reject', $po) }}" method="POST">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-stone-300 mb-1.5">Tuliskan alasan penolakan untuk Bagian Pengadaan:</label>
                            <textarea name="reason" rows="3" required class="w-full p-3 bg-stone-800 border border-stone-700 rounded-xl text-white text-xs focus:outline-none focus:border-red-500" placeholder="Contoh: Anggaran belum mencukupi atau stok di gudang masih memadai."></textarea>
                        </div>
                        <div class="flex items-center justify-end gap-2 mt-4">
                            <button type="button" @click="rejectModal = false" class="px-4 py-2 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-300 text-xs">Batal</button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs">Konfirmasi Tolak</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-stone-900/90 border border-stone-800 rounded-3xl p-12 text-center shadow-xl">
            <i class="fa-solid fa-clipboard-check text-5xl text-stone-600 mb-3"></i>
            <h3 class="text-base font-bold text-white">Tidak Ada Permintaan Pengadaan Tertunda</h3>
            <p class="text-xs text-stone-500 mt-1">Semua Purchase Order yang diajukan telah disetujui atau diproses.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
