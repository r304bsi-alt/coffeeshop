@extends('layouts.app')

@section('title', 'Dashboard Pengadaan - Kopi Senja')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 glass-panel p-6 rounded-3xl">
        <div>
            <div class="text-xs font-bold text-cyan-400 uppercase tracking-wider mb-1">
                <i class="fa-solid fa-file-invoice-dollar mr-1"></i> Procurement Management
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Divisi Pengadaan Barang</h1>
            <p class="text-stone-400 text-xs sm:text-sm mt-1">Cek persediaan stok bahan baku dan buat Purchase Order ke supplier.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('pengadaan.stock.check') }}" class="px-4 py-2.5 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-200 font-semibold text-xs border border-stone-700 transition-all">
                <i class="fa-solid fa-boxes-stacked mr-1.5 text-cyan-400"></i> Cek Stok Bahan
            </a>
            <a href="{{ route('pengadaan.create') }}" class="px-4 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs shadow-md transition-all">
                <i class="fa-solid fa-plus mr-1.5"></i> Buat Permintaan PO Baru
            </a>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-stone-900/90 border border-stone-800 rounded-2xl p-5 shadow-lg">
            <span class="text-xs font-semibold text-stone-400">Bahan Baku Menipis / Kritis</span>
            <div class="text-2xl font-black text-red-400 mt-2">{{ $lowStocks->count() }} Bahan</div>
            <div class="text-[11px] text-red-400 mt-1">
                <a href="{{ route('pengadaan.stock.check') }}" class="hover:underline">Perlu dibuatkan PO segera &rarr;</a>
            </div>
        </div>

        <div class="bg-stone-900/90 border border-stone-800 rounded-2xl p-5 shadow-lg">
            <span class="text-xs font-semibold text-stone-400">Menunggu Approval Owner</span>
            <div class="text-2xl font-black text-amber-400 mt-2">{{ $pendingApprovals }} PO</div>
            <div class="text-[11px] text-stone-400 mt-1">Sedang ditinjau oleh Owner</div>
        </div>

        <div class="bg-stone-900/90 border border-stone-800 rounded-2xl p-5 shadow-lg">
            <span class="text-xs font-semibold text-stone-400">PO Disetujui (Siap Dipesan)</span>
            <div class="text-2xl font-black text-emerald-400 mt-2">{{ $approvedCount }} PO</div>
            <div class="text-[11px] text-emerald-400 mt-1">Siap dikirim email ke supplier</div>
        </div>
    </div>

    <!-- Recent POs Table -->
    <div class="bg-stone-900/90 border border-stone-800 rounded-3xl p-6 shadow-xl">
        <div class="flex items-center justify-between mb-4 border-b border-stone-800 pb-3">
            <h3 class="font-bold text-white text-base flex items-center gap-2">
                <i class="fa-solid fa-file-lines text-cyan-400"></i> Permintaan Pengadaan Terakhir
            </h3>
            <a href="{{ route('pengadaan.index') }}" class="text-xs text-amber-400 hover:underline">Lihat Semua PO</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-stone-300">
                <thead class="bg-stone-800/80 text-[11px] uppercase tracking-wider text-stone-400 font-bold border-b border-stone-700/80">
                    <tr>
                        <th class="py-3 px-4">No. PO</th>
                        <th class="py-3 px-4">Supplier</th>
                        <th class="py-3 px-4">Status Approval</th>
                        <th class="py-3 px-4 text-right">Estimasi Biaya</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-800/80">
                    @forelse($recentProcurements as $po)
                    <tr class="hover:bg-stone-800/30 transition-colors">
                        <td class="py-3.5 px-4 font-mono font-bold text-white text-xs">
                            {{ $po->po_number }}
                            <div class="text-[10px] text-stone-500 font-sans mt-0.5">{{ $po->created_at->format('d/m/Y') }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-xs font-semibold text-stone-200">
                            {{ $po->supplier_name }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                {{ $po->status === 'completed' ? 'bg-emerald-900/60 text-emerald-300 border border-emerald-700/50' : '' }}
                                {{ $po->status === 'approved' ? 'bg-blue-900/60 text-blue-300 border border-blue-700/50' : '' }}
                                {{ $po->status === 'ordered' ? 'bg-cyan-900/60 text-cyan-300 border border-cyan-700/50' : '' }}
                                {{ $po->status === 'pending' ? 'bg-amber-900/60 text-amber-300 border border-amber-700/50' : '' }}
                                {{ $po->status === 'rejected' ? 'bg-red-900/60 text-red-300 border border-red-700/50' : '' }}
                            ">
                                {{ $po->status }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono font-bold text-white text-xs">
                            Rp {{ number_format($po->total_cost, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ route('pengadaan.show', $po) }}" class="p-1.5 rounded-lg bg-stone-800 hover:bg-stone-700 text-stone-300 text-xs font-semibold">
                                Detail & Cetak
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-xs text-stone-500">Belum ada pengadaan dibuat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
