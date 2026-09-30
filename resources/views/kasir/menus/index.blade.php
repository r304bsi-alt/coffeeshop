@extends('layouts.app')

@section('title', 'Kelola Menu & Resep (BOM) - Kasir')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 glass-panel p-6 rounded-3xl">
        <div>
            <div class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-1">
                <i class="fa-solid fa-utensils mr-1"></i> Menu & Bill of Materials
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Manajemen Menu & Komposisi Resep</h1>
            <p class="text-stone-400 text-xs sm:text-sm mt-1">Atur ketersediaan menu, unggah foto, dan tentukan takaran bahan baku (gram/ml) untuk pemotongan stok otomatis.</p>
        </div>
        <a href="{{ route('kasir.menus.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-stone-950 font-bold text-xs shadow-lg shadow-amber-500/20 transition-all">
            <i class="fa-solid fa-plus"></i> Tambah Menu Baru
        </a>
    </div>

    <!-- Menus Grid / Table -->
    <div class="bg-stone-900/90 border border-stone-800 rounded-3xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-stone-300">
                <thead class="bg-stone-800/80 text-[11px] uppercase tracking-wider text-stone-400 font-bold border-b border-stone-700/80">
                    <tr>
                        <th class="py-3.5 px-4 sm:px-6">Foto & Nama Menu</th>
                        <th class="py-3.5 px-4">Kategori</th>
                        <th class="py-3.5 px-4">Harga Jual</th>
                        <th class="py-3.5 px-4">Komposisi Bahan (BOM)</th>
                        <th class="py-3.5 px-4 text-center">Ketersediaan</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-800/80">
                    @forelse($menus as $menu)
                    <tr class="hover:bg-stone-800/30 transition-colors {{ !$menu->is_available ? 'opacity-60 bg-stone-950/40' : '' }}">
                        <td class="py-4 px-4 sm:px-6">
                            <div class="flex items-center gap-3">
                                <img src="{{ $menu->image_url }}" alt="{{ $menu->name }}" class="w-12 h-12 rounded-xl object-cover border border-stone-700 shrink-0">
                                <div>
                                    <div class="font-bold text-white text-sm">{{ $menu->name }}</div>
                                    <div class="text-[11px] text-stone-400 line-clamp-1 max-w-xs">{{ $menu->description }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-4 text-xs font-semibold text-stone-300">
                            {{ $menu->category?->name }}
                        </td>
                        <td class="py-4 px-4 font-mono font-bold text-white text-sm">
                            Rp {{ number_format($menu->price, 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-4 text-xs">
                            <div class="space-y-1">
                                @forelse($menu->recipes as $rec)
                                <div class="text-[11px] text-stone-300 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                    <span>{{ $rec->ingredient?->name }}:</span>
                                    <strong class="font-mono text-amber-300">{{ number_format($rec->amount, 2) }} {{ $rec->ingredient?->unit }}</strong>
                                </div>
                                @empty
                                <span class="text-[11px] text-red-400 italic">Belum ada resep bahan</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <form action="{{ route('kasir.menus.toggle', $menu) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider transition-all 
                                    {{ $menu->is_available ? 'bg-emerald-900/60 text-emerald-300 border border-emerald-700/50 hover:bg-emerald-800' : 'bg-red-900/60 text-red-300 border border-red-700/50 hover:bg-red-800' }}">
                                    <i class="fa-solid {{ $menu->is_available ? 'fa-check' : 'fa-xmark' }} mr-1"></i>
                                    {{ $menu->is_available ? 'TERSEDIA' : 'HABIS' }}
                                </button>
                            </form>
                        </td>
                        <td class="py-4 px-4 sm:px-6 text-right space-x-1.5">
                            <a href="{{ route('kasir.menus.edit', $menu) }}" class="p-2 rounded-lg bg-stone-800 hover:bg-stone-700 text-stone-300 hover:text-white text-xs inline-block" title="Edit Menu & Resep">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form action="{{ route('kasir.menus.destroy', $menu) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus menu ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 rounded-lg bg-red-950/40 hover:bg-red-900 text-red-400 text-xs inline-block" title="Hapus Menu">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-stone-500 text-xs">Belum ada menu yang dibuat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($menus->hasPages())
        <div class="p-4 border-t border-stone-800">
            {{ $menus->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
