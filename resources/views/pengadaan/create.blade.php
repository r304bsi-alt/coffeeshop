@extends('layouts.app')

@section('title', 'Buat Permintaan Pengadaan (PO) - Pengadaan')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="procurementForm()">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Buat Permintaan Pengadaan Baru (PO)</h1>
            <p class="text-stone-400 text-xs mt-1">Isi formulir pengadaan bahan baku ke supplier untuk diajukan ke Owner.</p>
        </div>
        <a href="{{ route('pengadaan.index') }}" class="px-3 py-1.5 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-300 text-xs font-semibold">
            &larr; Kembali
        </a>
    </div>

    <div class="bg-stone-900/90 border border-stone-800 rounded-3xl p-6 sm:p-8 shadow-2xl">
        <form action="{{ route('pengadaan.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Supplier Information -->
            <div class="space-y-4">
                <h3 class="text-xs font-bold text-cyan-400 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-truck"></i> Informasi Supplier / Vendor
                </h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-stone-300 mb-1.5">Nama Supplier / PT</label>
                        <input type="text" name="supplier_name" value="{{ old('supplier_name') }}" required class="w-full px-3.5 py-2.5 bg-stone-800 border border-stone-700 rounded-xl text-white text-xs focus:outline-none focus:border-cyan-500" placeholder="Contoh: PT Roastery Mandiri">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-stone-300 mb-1.5">Email Supplier</label>
                        <input type="email" name="supplier_email" value="{{ old('supplier_email') }}" class="w-full px-3.5 py-2.5 bg-stone-800 border border-stone-700 rounded-xl text-white text-xs focus:outline-none focus:border-cyan-500" placeholder="order@supplier.com">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-stone-300 mb-1.5">No. Telepon / WA</label>
                        <input type="text" name="supplier_phone" value="{{ old('supplier_phone') }}" class="w-full px-3.5 py-2.5 bg-stone-800 border border-stone-700 rounded-xl text-white text-xs focus:outline-none focus:border-cyan-500" placeholder="0812xxxxxxx">
                    </div>
                </div>
            </div>

            <!-- Dynamic Ingredients List -->
            <div class="pt-4 border-t border-stone-800 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-cyan-400 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-boxes-stacked"></i> Daftar Bahan Yang Diajukan
                    </h3>
                    <button type="button" @click="addItem()" class="px-3 py-1.5 rounded-lg bg-cyan-600/20 hover:bg-cyan-600/30 text-cyan-300 text-xs font-bold transition-colors">
                        <i class="fa-solid fa-plus mr-1"></i> Tambah Item
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="p-4 rounded-2xl bg-stone-800/60 border border-stone-700/60 grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                            <!-- Select Ingredient -->
                            <div class="sm:col-span-5">
                                <label class="block text-[10px] text-stone-400 uppercase font-semibold mb-1">Bahan Baku</label>
                                <select :name="'items[' + index + '][ingredient_id]'" x-model="item.ingredient_id" @change="updateUnit(item)" required class="w-full px-3 py-2 bg-stone-900 border border-stone-700 rounded-xl text-white text-xs focus:outline-none focus:border-cyan-500">
                                    <option value="">-- Pilih Bahan --</option>
                                    @foreach($ingredients as $ing)
                                    <option value="{{ $ing->id }}" data-unit="{{ $ing->unit }}" data-cost="{{ $ing->cost_per_unit }}">
                                        {{ $ing->name }} ({{ $ing->unit }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Quantity -->
                            <div class="sm:col-span-3">
                                <label class="block text-[10px] text-stone-400 uppercase font-semibold mb-1">
                                    Jumlah (<span x-text="item.unit || '-'"></span>)
                                </label>
                                <input type="number" step="0.01" min="0.01" :name="'items[' + index + '][quantity_requested]'" x-model="item.quantity" @input="calcSubtotal(item)" required class="w-full px-3 py-2 bg-stone-900 border border-stone-700 rounded-xl text-white text-xs font-mono font-bold focus:outline-none focus:border-cyan-500" placeholder="0.00">
                            </div>

                            <!-- Unit Price -->
                            <div class="sm:col-span-3">
                                <label class="block text-[10px] text-stone-400 uppercase font-semibold mb-1">Harga Satuan (Rp)</label>
                                <input type="number" step="1" min="0" :name="'items[' + index + '][unit_price]'" x-model="item.unit_price" @input="calcSubtotal(item)" required class="w-full px-3 py-2 bg-stone-900 border border-stone-700 rounded-xl text-white text-xs font-mono font-bold focus:outline-none focus:border-cyan-500" placeholder="0">
                            </div>

                            <!-- Delete -->
                            <div class="sm:col-span-1 text-right sm:pt-4">
                                <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="p-2 text-red-400 hover:text-red-300">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Total Cost Preview -->
                <div class="p-4 rounded-2xl bg-stone-800/80 border border-stone-700/80 flex items-center justify-between">
                    <span class="text-xs font-bold text-stone-300 uppercase">Estimasi Total Biaya Pengadaan:</span>
                    <span class="text-xl font-black text-cyan-400 font-mono" x-text="formatRupiah(totalCost)"></span>
                </div>
            </div>

            <!-- Notes -->
            <div class="pt-4 border-t border-stone-800">
                <label class="block text-xs font-semibold text-stone-300 mb-1.5">Catatan Pengadaan untuk Owner</label>
                <textarea name="notes" rows="2" class="w-full p-3 bg-stone-800 border border-stone-700 rounded-xl text-white text-xs focus:outline-none focus:border-cyan-500" placeholder="Alasan kebutuhan stok, perkiraan waktu kirim, dll."></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-800">
                <a href="{{ route('pengadaan.index') }}" class="px-4 py-2 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-300 text-xs font-semibold">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs shadow-lg shadow-cyan-600/20 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i> Ajukan Permintaan ke Owner
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function procurementForm() {
    return {
        items: [
            { ingredient_id: '', quantity: 100, unit_price: 1000, unit: '', subtotal: 100000 }
        ],
        addItem() {
            this.items.push({ ingredient_id: '', quantity: 1, unit_price: 0, unit: '', subtotal: 0 });
        },
        removeItem(index) {
            this.items.splice(index, 1);
        },
        updateUnit(item) {
            const select = event.target;
            const opt = select.options[select.selectedIndex];
            item.unit = opt.getAttribute('data-unit') || '';
            const defaultCost = parseFloat(opt.getAttribute('data-cost')) || 0;
            if (defaultCost > 0 && !item.unit_price) {
                item.unit_price = defaultCost;
            }
            this.calcSubtotal(item);
        },
        calcSubtotal(item) {
            item.subtotal = (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0);
        },
        get totalCost() {
            return this.items.reduce((acc, it) => acc + (parseFloat(it.subtotal) || 0), 0);
        },
        formatRupiah(num) {
            return 'Rp ' + (num || 0).toLocaleString('id-ID');
        }
    }
}
</script>
@endpush
@endsection
