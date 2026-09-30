@extends('layouts.app')

@section('title', 'Tambah Menu & Resep Bahan (BOM) - Kasir')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="recipeForm()">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Tambah Menu Baru & Resep Presisi</h1>
            <p class="text-stone-400 text-xs mt-1">Daftarkan menu kedai beserta komposisi bahan baku (BOM) dalam satuan gram atau mililiter.</p>
        </div>
        <a href="{{ route('kasir.menus.index') }}" class="px-3 py-1.5 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-300 text-xs font-semibold">
            &larr; Kembali
        </a>
    </div>

    <div class="bg-stone-900/90 border border-stone-800 rounded-3xl p-6 sm:p-8 shadow-2xl">
        <form action="{{ route('kasir.menus.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Section 1: Menu Info -->
            <div class="space-y-4">
                <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-mug-hot"></i> Informasi Dasar Menu
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-stone-300 mb-1.5">Nama Menu</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-3.5 py-2.5 bg-stone-800 border border-stone-700 rounded-xl text-white text-xs focus:outline-none focus:border-amber-500" placeholder="Contoh: Es Kopi Susu Pandan">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-300 mb-1.5">Kategori Menu</label>
                        <select name="category_id" required class="w-full px-3.5 py-2.5 bg-stone-800 border border-stone-700 rounded-xl text-white text-xs focus:outline-none focus:border-amber-500">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-stone-300 mb-1.5">Harga Jual (Rp)</label>
                        <input type="number" name="price" value="{{ old('price') }}" required min="0" step="100" class="w-full px-3.5 py-2.5 bg-stone-800 border border-stone-700 rounded-xl text-white text-xs font-mono font-bold focus:outline-none focus:border-amber-500" placeholder="25000">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-300 mb-1.5">Unggah Foto Menu (Opsional)</label>
                        <input type="file" name="image" accept="image/*" class="w-full text-xs text-stone-400 file:mr-4 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-stone-800 file:text-amber-400 hover:file:bg-stone-700 cursor-pointer">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-300 mb-1.5">Deskripsi Singkat</label>
                    <textarea name="description" rows="2" class="w-full p-3 bg-stone-800 border border-stone-700 rounded-xl text-white text-xs focus:outline-none focus:border-amber-500" placeholder="Jelaskan cita rasa menu, bahan utama, atau keunikan rasa...">{{ old('description') }}</textarea>
                </div>

                <div class="p-3 bg-stone-800/40 rounded-xl border border-stone-700/60">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="is_available" value="1" checked class="w-4 h-4 rounded bg-stone-900 border-stone-700 text-amber-500 focus:ring-amber-500">
                        <span class="text-xs font-bold text-white">Menu Tersedia untuk Dipesan (Aktif)</span>
                    </label>
                </div>
            </div>

            <!-- Section 2: Bill of Materials (BOM) / Precise Ingredients Composition -->
            <div class="pt-6 border-t border-stone-800 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-flask-vial"></i> Komposisi Bahan Baku (Bill of Materials / BOM)
                        </h3>
                        <p class="text-[11px] text-stone-400 mt-0.5">
                            Setiap gram atau mililiter yang dimasukkan di sini akan dipotong secara otomatis dari stok toko setiap kali pesanan dibayar lunas.
                        </p>
                    </div>
                    <button type="button" @click="addRecipe()" class="px-3 py-1.5 rounded-lg bg-amber-500/20 hover:bg-amber-500/30 text-amber-400 text-xs font-bold transition-colors">
                        <i class="fa-solid fa-plus mr-1"></i> Tambah Bahan
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(recipe, index) in recipes" :key="index">
                        <div class="p-3.5 rounded-2xl bg-stone-800/80 border border-stone-700/80 flex flex-col sm:flex-row items-center gap-3">
                            <div class="flex-1 w-full sm:w-auto">
                                <label class="block text-[10px] text-stone-400 uppercase font-semibold mb-1">Pilih Bahan Baku</label>
                                <select :name="'recipes[' + index + '][ingredient_id]'" x-model="recipe.ingredient_id" @change="updateUnit(recipe)" required class="w-full px-3 py-2 bg-stone-900 border border-stone-700 rounded-xl text-white text-xs focus:outline-none focus:border-amber-500">
                                    <option value="">-- Pilih Bahan Baku --</option>
                                    @foreach($ingredients as $ing)
                                    <option value="{{ $ing->id }}" data-unit="{{ $ing->unit }}">
                                        {{ $ing->name }} (Satuan: {{ $ing->unit }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="w-full sm:w-56">
                                <label class="block text-[10px] text-stone-400 uppercase font-semibold mb-1">
                                    Takaran Presisi Per Porsi (<span x-text="recipe.unit || '-'"></span>)
                                </label>
                                <input type="number" step="0.01" min="0.01" :name="'recipes[' + index + '][amount]'" x-model="recipe.amount" required class="w-full px-3 py-2 bg-stone-900 border border-stone-700 rounded-xl text-white text-xs font-mono font-bold focus:outline-none focus:border-amber-500" placeholder="Contoh: 18.00">
                            </div>

                            <div class="sm:pt-5 w-full sm:w-auto text-right">
                                <button type="button" @click="removeRecipe(index)" x-show="recipes.length > 1" class="p-2 text-red-400 hover:text-red-300">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="pt-4 border-t border-stone-800 flex items-center justify-end gap-3">
                <a href="{{ route('kasir.menus.index') }}" class="px-4 py-2 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-300 text-xs font-semibold">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-stone-950 font-bold text-xs shadow-lg shadow-amber-500/20 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-check"></i> Simpan Menu & Resep
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function recipeForm() {
    return {
        recipes: [
            { ingredient_id: '', amount: '', unit: '' }
        ],
        addRecipe() {
            this.recipes.push({ ingredient_id: '', amount: '', unit: '' });
        },
        removeRecipe(index) {
            this.recipes.splice(index, 1);
        },
        updateUnit(recipe) {
            const select = event.target;
            const opt = select.options[select.selectedIndex];
            recipe.unit = opt.getAttribute('data-unit') || '';
        }
    }
}
</script>
@endpush
@endsection
