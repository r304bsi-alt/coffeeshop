@extends('layouts.app')

@section('title', 'Daftar Pengadaan (PO) - Pengadaan')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 glass-panel p-6 rounded-3xl">
        <div>
            <div class="text-xs font-bold text-cyan-400 uppercase tracking-wider mb-1">
                <i class="fa-solid fa-file-invoice mr-1"></i> Purchase Orders
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Daftar Permintaan Pengadaan</h1>
            <p class="text-stone-400 text-xs sm:text-sm mt-1">Kelola permohonan pengadaan barang dari status pengajuan hingga penerimaan di gudang.</p>
        </div>
        <a href="{{ route('pengadaan.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs shadow-lg shadow-cyan-600/20 transition-all">
            <i class="fa-solid fa-plus"></i> Buat PO Baru
        </a>
    </div>

    <!-- PO Table -->
    <div class="bg-stone-900/90 border border-stone-800 rounded-3xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-stone-300">
                <thead class="bg-stone-800/80 text-[11px] uppercase tracking-wider text-stone-400 font-bold border-b border-stone-700/80">
                    <tr>
                        <th class="py-3.5 px-4 sm:px-6">No. PO & Tanggal</th>
                        <th class="py-3.5 px-4">Supplier</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Total Biaya</th>
                        <th class="py-3.5 px-4">Approval Owner</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-800/80">
                    @forelse($procurements as $po)
                    <tr class="hover:bg-stone-800/30 transition-colors">
                        <td class="py-4 px-4 sm:px-6 font-mono font-bold text-white text-xs">
                            {{ $po->po_number }}
                            <div class="text-[10px] text-stone-500 font-sans mt-0.5">{{ $po->created_at->format('d M Y, H:i') }}</div>
                        </td>
                        <td class="py-4 px-4 text-xs font-semibold text-stone-200">
                            {{ $po->supplier_name }}
                            <div class="text-[10px] text-stone-500 font-normal">{{ $po->supplier_email ?? '-' }}</div>
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
                        <td class="py-4 px-4 text-right font-mono font-bold text-white text-xs">
                            Rp {{ number_format($po->total_cost, 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-4 text-xs text-stone-400">
                            @if($po->approved_at)
                                <span class="text-emerald-400 font-medium"><i class="fa-solid fa-circle-check mr-1"></i> Disetujui</span>
                                <div class="text-[10px] text-stone-500">{{ $po->approver?->name }}</div>
                            @elseif($po->status === 'rejected')
                                <span class="text-red-400 font-medium"><i class="fa-solid fa-circle-xmark mr-1"></i> Ditolak</span>
                            @else
                                <span class="text-amber-400 font-medium"><i class="fa-solid fa-clock mr-1"></i> Menunggu Owner</span>
                            @endif
                        </td>
                        <td class="py-4 px-4 sm:px-6 text-right space-x-2">
                            <a href="{{ route('pengadaan.show', $po) }}" class="p-2 rounded-lg bg-stone-800 hover:bg-stone-700 text-stone-300 text-xs font-semibold inline-block" title="Detail PO">
                                <i class="fa-solid fa-eye"></i> Detail
                            </a>
                            @if($po->isApproved())
                            <a href="{{ route('pengadaan.print', $po) }}" target="_blank" class="p-2 rounded-lg bg-cyan-950/60 hover:bg-cyan-900 text-cyan-300 text-xs font-semibold inline-block" title="Cetak PO">
                                <i class="fa-solid fa-print"></i> Cetak
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-stone-500 text-xs">Belum ada pengadaan barang.</td>
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
