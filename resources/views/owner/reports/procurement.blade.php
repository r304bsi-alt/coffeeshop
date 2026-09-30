@extends('layouts.app')

@section('title', 'Laporan Pengadaan - Owner')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 glass-panel p-6 rounded-3xl">
        <div>
            <div class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-1">
                <i class="fa-solid fa-truck-ramp-box mr-1"></i> Procurement Report
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Laporan Pengadaan Barang</h1>
            <p class="text-stone-400 text-xs sm:text-sm mt-1">Rekapitulasi riwayat pembelian stok, supplier, biaya, dan status pengadaan.</p>
        </div>

        <form method="GET" action="{{ route('owner.reports.procurement') }}" class="flex items-center gap-2">
            <select name="status" onchange="this.form.submit()" class="px-3 py-2 bg-stone-800 border border-stone-700 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500">
                <option value="">Semua Status PO</option>
                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending Approval</option>
                <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Disetujui (Approved)</option>
                <option value="ordered" {{ $status === 'ordered' ? 'selected' : '' }}>Dipesan ke Supplier</option>
                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Selesai Diterima Gudang</option>
                <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Ditolak (Rejected)</option>
            </select>
        </form>
    </div>

    <!-- Summary Total Card -->
    <div class="bg-stone-900/90 border border-stone-800 rounded-2xl p-5 shadow-lg max-w-sm">
        <span class="text-xs font-semibold text-stone-400">Total Pengeluaran Pengadaan (Disetujui/Selesai)</span>
        <div class="text-2xl font-black text-amber-400 mt-2">
            Rp {{ number_format($totalSpend, 0, ',', '.') }}
        </div>
    </div>

    <!-- Procurements Table Card -->
    <div class="bg-stone-900/90 border border-stone-800 rounded-3xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-stone-300">
                <thead class="bg-stone-800/80 text-[11px] uppercase tracking-wider text-stone-400 font-bold border-b border-stone-700/80">
                    <tr>
                        <th class="py-3.5 px-4 sm:px-6">No. PO</th>
                        <th class="py-3.5 px-4">Supplier</th>
                        <th class="py-3.5 px-4">Dibuat Oleh</th>
                        <th class="py-3.5 px-4">Disetujui Oleh</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Total Biaya</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-800/80">
                    @forelse($procurements as $po)
                    <tr class="hover:bg-stone-800/30 transition-colors">
                        <td class="py-4 px-4 sm:px-6 font-mono font-bold text-white text-xs">
                            {{ $po->po_number }}
                            <div class="text-[10px] text-stone-500 font-sans mt-0.5">{{ $po->created_at->format('d M Y') }}</div>
                        </td>
                        <td class="py-4 px-4 text-xs font-semibold text-stone-200">
                            {{ $po->supplier_name }}
                            <div class="text-[10px] text-stone-500 font-normal">{{ $po->supplier_email ?? '-' }}</div>
                        </td>
                        <td class="py-4 px-4 text-xs text-stone-400">
                            {{ $po->creator?->name }}
                        </td>
                        <td class="py-4 px-4 text-xs text-stone-400">
                            {{ $po->approver?->name ?? '-' }}
                        </td>
                        <td class="py-4 px-4 text-center">
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
                        <td class="py-4 px-4 sm:px-6 text-right font-mono font-bold text-white text-sm">
                            Rp {{ number_format($po->total_cost, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-stone-500 text-xs">Tidak ada data pengadaan ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($procurements->hasPages())
        <div class="p-4 border-t border-stone-800">
            {{ $procurements->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
