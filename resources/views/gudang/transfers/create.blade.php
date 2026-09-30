@extends('layouts.app')

@section('title', 'Buat Pengeluaran Stok ke Toko - Gudang')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="transferForm()">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Form Pengeluaran Stok ke Toko</h1>
            <p class="text-stone-400 text-xs mt-1">Daftarkan bahan baku yang dikeluarkan dari gudang untuk dikirim ke kasir toko.</p>
        </div>
        <a href="{{ route('gudang.transfers.index') }}" class="px-3 py-1.5 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-300 text-xs font-semibold">
            &larr; Kembali
        </a>
    </div>

    <div class="bg-stone-900/90 border border-stone-800 rounded-3xl p-6 sm:p-8 shadow-2xl">
        <form action="{{ route('gudang.transfers.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Meta info banner -->
            <div class="p-4 rounded-2xl bg-stone-800/60 border border-stone-700/60 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-stone-500 block">Staf Pengeluar Barang (Gudang):</span>
                    <strong class="text-amber-400 text-sm">{{ auth()->user()->name }}</strong>
                </div>
                <div>
                    <span class="text-stone-500 block">Waktu Pengeluaran (Tgl Keluar):</span>
                    <strong class="text-white text-sm">{{ now()->format('d F Y, H:i') }} WIB</strong>
                </div>
            </div>

            <!-- Dynamic Items Rows -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold text-stone-300 uppercase tracking-wider">
                        Daftar Bahan Yang Dikeluarkan:
                    </h3>
                    <button type="button" @click="addItem()" class="px-3 py-1.5 rounded-lg bg-amber-500/20 hover:bg-amber-500/30 text-amber-400 text-xs font-bold transition-colors">
                        <i class="fa-solid fa-plus mr-1"></i> Tambah Baris Bahan
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="p-3.5 rounded-2xl bg-stone-800/80 border border-stone-700/80 flex flex-col sm:flex-row items-center gap-3">
                            <!-- Select Ingredient -->
                            <div class="flex-1 w-full sm:w-auto">
                                <label class="block text-[10px] text-stone-400 uppercase font-semibold mb-1">Pilih Bahan Baku</label>
                                <select :name="'items[' + index + '][ingredient_id]'" x-model="item.ingredient_id" @change="updateUnit(item)" required class="w-full px-3 py-2 bg-stone-900 border border-stone-700 rounded-xl text-white text-xs focus:outline-none focus:border-amber-500">
                                    <option value="">-- Pilih Bahan --</option>
                                    @foreach($ingredients as $ing)
                                    <option value="{{ $ing->id }}" data-unit="{{ $ing->unit }}" data-stock="{{ $ing->gudang_quantity }}">
                                        {{ $ing->name }} (Stok Gudang: {{ number_format($ing->gudang_quantity, 2) }} {{ $ing->unit }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Input Quantity -->
                            <div class="w-full sm:w-48">
                                <label class="block text-[10px] text-stone-400 uppercase font-semibold mb-1">
                                    Jumlah Keluar (<span x-text="item.unit || '-'"></span>)
                                </label>
                                <input type="number" step="0.01" min="0.01" :max="item.maxStock" :name="'items[' + index + '][quantity]'" x-model="item.quantity" required class="w-full px-3 py-2 bg-stone-900 border border-stone-700 rounded-xl text-white text-xs font-mono font-bold focus:outline-none focus:border-amber-500" placeholder="0.00">
                            </div>

                            <!-- Delete button -->
                            <div class="sm:pt-5 w-full sm:w-auto text-right">
                                <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="p-2 text-red-400 hover:text-red-300 rounded-lg hover:bg-red-950/40">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-xs font-bold text-stone-300 uppercase tracking-wider mb-2">Catatan Pengeluaran Barang</label>
                <textarea name="notes" rows="2" class="w-full p-3 bg-stone-800/80 border border-stone-700/80 rounded-xl text-white text-xs focus:outline-none focus:border-amber-500" placeholder="Contoh: Kebutuhan operasional shift pagi toko"></textarea>
            </div>

            <div class="pt-4 border-t border-stone-800 flex items-center justify-end gap-3">
                <a href="{{ route('gudang.transfers.index') }}" class="px-4 py-2 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-300 text-xs font-semibold">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-stone-950 font-bold text-xs shadow-lg shadow-amber-500/20 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-truck-fast"></i> Keluarkan & Kirim ke Toko
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function transferForm() {
    return {
        items: [
            { ingredient_id: '', quantity: '', unit: '', maxStock: 999999 }
        ],
        addItem() {
            this.items.push({ ingredient_id: '', quantity: '', unit: '', maxStock: 999999 });
        },
        removeItem(index) {
            this.items.splice(index, 1);
        },
        updateUnit(item) {
            const select = event.target;
            const opt = select.options[select.selectedIndex];
            item.unit = opt.getAttribute('data-unit') || '';
            item.maxStock = parseFloat(opt.getAttribute('data-stock')) || 999999;
        }
    }
}
</script>
@endpush
@endsection
