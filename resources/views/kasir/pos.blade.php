@extends('layouts.app')

@section('title', 'Point of Sale (POS) - Kasir')

@section('content')
<div class="h-[calc(100vh-140px)] flex flex-col lg:flex-row gap-6" x-data="posApp()">
    
    <!-- Left Column: Menu Catalog with Categories -->
    <div class="flex-1 flex flex-col bg-stone-900/90 border border-stone-800 rounded-3xl p-5 shadow-2xl overflow-hidden">
        
        <!-- Header & Category Pills -->
        <div class="mb-4 space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-extrabold text-white flex items-center gap-2">
                        <i class="fa-solid fa-cash-register text-emerald-400"></i> Kasir POS (Pemesanan Langsung)
                    </h1>
                    <p class="text-[11px] text-stone-400">Pilih menu untuk membuat pesanan pelanggan di toko.</p>
                </div>
                <div class="relative w-48 sm:w-64">
                    <input type="text" x-model="searchQuery" placeholder="Cari nama menu..." class="w-full pl-8 pr-3 py-1.5 bg-stone-800 border border-stone-700 rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500">
                    <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2 text-stone-500 text-xs"></i>
                </div>
            </div>

            <!-- Categories Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
                <button type="button" @click="activeCategory = 'all'" 
                    :class="activeCategory === 'all' ? 'bg-amber-500 text-stone-950 font-bold' : 'bg-stone-800 text-stone-300 hover:bg-stone-700'"
                    class="px-3.5 py-1.5 rounded-xl text-xs whitespace-nowrap transition-all">
                    Semua Kategori
                </button>
                @foreach($categories as $c)
                <button type="button" @click="activeCategory = '{{ $c->id }}'" 
                    :class="activeCategory === '{{ $c->id }}' ? 'bg-amber-500 text-stone-950 font-bold' : 'bg-stone-800 text-stone-300 hover:bg-stone-700'"
                    class="px-3.5 py-1.5 rounded-xl text-xs whitespace-nowrap transition-all">
                    {{ $c->name }}
                </button>
                @endforeach
            </div>
        </div>

        <!-- Menu Items Grid -->
        <div class="flex-1 overflow-y-auto pr-1">
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3.5">
                <template x-for="menu in filteredMenus" :key="menu.id">
                    <div @click="addToCart(menu)" class="bg-stone-800/80 hover:bg-stone-800 border border-stone-700/80 hover:border-emerald-500/60 rounded-2xl p-3 flex flex-col justify-between cursor-pointer transition-all hover:scale-[1.02] shadow-md group">
                        <div>
                            <div class="relative aspect-video rounded-xl overflow-hidden bg-stone-900 mb-2.5">
                                <img :src="menu.image_url" :alt="menu.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                <span class="absolute bottom-1 right-1 px-1.5 py-0.5 rounded-md text-[9px] font-bold bg-black/70 text-amber-400 font-mono" x-text="menu.category_name"></span>
                            </div>
                            <h4 class="font-bold text-xs text-white group-hover:text-emerald-400 line-clamp-1" x-text="menu.name"></h4>
                            <p class="text-[10px] text-stone-400 line-clamp-1 mt-0.5" x-text="menu.description"></p>
                        </div>
                        <div class="flex items-center justify-between mt-3 pt-2 border-t border-stone-700/60">
                            <span class="font-mono font-bold text-xs text-white" x-text="formatRupiah(menu.price)"></span>
                            <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-400 group-hover:bg-emerald-500 group-hover:text-stone-950 flex items-center justify-center text-xs transition-colors">
                                <i class="fa-solid fa-plus"></i>
                            </span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Right Column: Cart / Checkout Panel -->
    <div class="w-full lg:w-96 bg-stone-900/90 border border-stone-800 rounded-3xl p-5 shadow-2xl flex flex-col justify-between overflow-hidden">
        <form action="{{ route('kasir.pos.order') }}" method="POST" class="h-full flex flex-col justify-between overflow-hidden" id="posOrderForm">
            @csrf

            <div class="flex flex-col flex-1 min-h-0">
                <div class="flex items-center justify-between pb-2.5 border-b border-stone-800 mb-2.5">
                    <span class="text-xs font-bold text-stone-300 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-cart-shopping text-emerald-400"></i> Keranjang Kasir
                    </span>
                    <button type="button" @click="clearCart()" x-show="cart.length > 0" class="text-[10px] text-red-400 hover:underline">
                        Kosongkan
                    </button>
                </div>

                <!-- Customer Details -->
                <div class="space-y-2 mb-2.5">
                    <div>
                        <label class="block text-[10px] uppercase font-bold text-stone-400 mb-0.5">Nama Pelanggan</label>
                        <input type="text" name="customer_name" required x-model="customerName" placeholder="Nama Pelanggan / Tamu" class="w-full px-3 py-1 bg-stone-800 border border-stone-700 rounded-xl text-white text-xs focus:outline-none focus:border-emerald-500">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] uppercase font-bold text-stone-400 mb-0.5">Tipe Pesanan</label>
                            <select name="order_type" x-model="orderType" class="w-full px-2 py-1 bg-stone-800 border border-stone-700 rounded-xl text-white text-xs focus:outline-none focus:border-emerald-500">
                                <option value="dine_in">Dine In (Meja)</option>
                                <option value="take_away">Take Away (Bungkus)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] uppercase font-bold text-stone-400 mb-0.5">Pilih Meja</label>
                            <select name="table_number" x-model="tableNumber" class="w-full px-2 py-1 bg-stone-800 border border-stone-700 rounded-xl text-white text-xs focus:outline-none focus:border-emerald-500">
                                @foreach($tables as $tbl)
                                <option value="{{ $tbl->table_number }}">{{ $tbl->table_number }}</option>
                                @endforeach
                                <option value="Kasir Bar">Kasir / Bar</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Cart Items List -->
                <div class="flex-1 min-h-[100px] max-h-44 sm:max-h-52 overflow-y-auto divide-y divide-stone-800/80 pr-1 border-t border-stone-800">
                    <template x-for="(item, idx) in cart" :key="idx">
                        <div class="py-2">
                            <input type="hidden" :name="'items[' + idx + '][menu_id]'" :value="item.id">
                            <input type="hidden" :name="'items[' + idx + '][quantity]'" :value="item.qty">
                            <input type="hidden" :name="'items[' + idx + '][notes]'" :value="item.notes">

                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-white truncate max-w-[150px]" x-text="item.name"></span>
                                <span class="font-mono text-stone-300 font-bold" x-text="formatRupiah(item.price * item.qty)"></span>
                            </div>

                            <div class="flex items-center justify-between mt-1">
                                <input type="text" x-model="item.notes" placeholder="Catatan (Less sugar, etc.)" class="text-[10px] px-2 py-0.5 bg-stone-800/60 border border-stone-700/60 rounded text-stone-300 w-32 focus:outline-none">
                                <div class="flex items-center gap-1.5">
                                    <button type="button" @click="decQty(idx)" class="w-5 h-5 rounded bg-stone-800 hover:bg-stone-700 text-stone-300 flex items-center justify-center text-xs">-</button>
                                    <span class="font-mono text-xs text-white font-bold w-4 text-center" x-text="item.qty"></span>
                                    <button type="button" @click="incQty(idx)" class="w-5 h-5 rounded bg-stone-800 hover:bg-stone-700 text-stone-300 flex items-center justify-center text-xs">+</button>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div x-show="cart.length === 0" class="py-8 text-center text-xs text-stone-500">
                        Keranjang masih kosong.<br>Klik menu di sebelah kiri.
                    </div>
                </div>
            </div>

            <!-- Footer: Summary & Payment -->
            <div class="pt-2.5 border-t border-stone-800 space-y-2.5">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-stone-400 font-medium">Total Tagihan:</span>
                    <span class="font-mono font-black text-xl text-emerald-400" x-text="formatRupiah(totalCart)"></span>
                </div>

                <div>
                    <label class="block text-[10px] uppercase font-bold text-stone-400 mb-1">Metode Pembayaran</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="p-1.5 rounded-xl border flex items-center justify-center gap-1.5 text-xs font-bold cursor-pointer transition-all"
                            :class="paymentMethod === 'cash' ? 'bg-amber-500/20 border-amber-500 text-amber-300' : 'bg-stone-800 border-stone-700 text-stone-400'">
                            <input type="radio" name="payment_method" value="cash" x-model="paymentMethod" @change="onPaymentMethodChange()" class="hidden">
                            <i class="fa-solid fa-money-bill-wave"></i> Tunai (Cash)
                        </label>
                        <label class="p-1.5 rounded-xl border flex items-center justify-center gap-1.5 text-xs font-bold cursor-pointer transition-all"
                            :class="paymentMethod === 'midtrans_qris' ? 'bg-emerald-500/20 border-emerald-500 text-emerald-300' : 'bg-stone-800 border-stone-700 text-stone-400'">
                            <input type="radio" name="payment_method" value="midtrans_qris" x-model="paymentMethod" class="hidden">
                            <i class="fa-solid fa-qrcode"></i> QRIS Midtrans
                        </label>
                    </div>
                </div>

                <!-- Input Nominal Tunai & Perhitungan Kembalian (Saat Metode Tunai) -->
                <div x-show="paymentMethod === 'cash'" x-cloak class="p-2.5 rounded-2xl bg-stone-800/80 border border-stone-700/80 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-[10px] uppercase font-bold text-amber-400 flex items-center gap-1">
                            <i class="fa-solid fa-money-bill"></i> Uang Tunai Diterima (Rp):
                        </label>
                        <button type="button" @click="setExactCash()" :disabled="totalCart === 0" class="text-[10px] px-2 py-0.5 rounded-md bg-amber-500/20 hover:bg-amber-500 text-amber-300 hover:text-stone-950 font-bold transition-all disabled:opacity-30">
                            Uang Pas
                        </button>
                    </div>

                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-stone-500 font-mono">Rp</span>
                        <input type="number" name="cash_tendered" x-model.number="cashTendered" @input="calcChange()" placeholder="0" min="0" step="1000" class="w-full pl-9 pr-3 py-1.5 bg-stone-900 border border-stone-700 rounded-xl text-white font-mono font-bold text-sm focus:outline-none focus:border-amber-500">
                    </div>

                    <!-- Quick Denomination Buttons -->
                    <div class="flex items-center gap-1 overflow-x-auto pb-0.5">
                        <button type="button" @click="setCash(20000)" class="px-2 py-0.5 bg-stone-900 hover:bg-stone-700 border border-stone-700/50 rounded-lg text-[10px] font-mono font-bold text-stone-300 whitespace-nowrap">20rb</button>
                        <button type="button" @click="setCash(50000)" class="px-2 py-0.5 bg-stone-900 hover:bg-stone-700 border border-stone-700/50 rounded-lg text-[10px] font-mono font-bold text-stone-300 whitespace-nowrap">50rb</button>
                        <button type="button" @click="setCash(100000)" class="px-2 py-0.5 bg-stone-900 hover:bg-stone-700 border border-stone-700/50 rounded-lg text-[10px] font-mono font-bold text-stone-300 whitespace-nowrap">100rb</button>
                        <button type="button" @click="addCash(10000)" class="px-2 py-0.5 bg-stone-900 hover:bg-stone-700 border border-stone-700/50 rounded-lg text-[10px] font-mono font-bold text-amber-400 whitespace-nowrap">+10rb</button>
                        <button type="button" @click="addCash(50000)" class="px-2 py-0.5 bg-stone-900 hover:bg-stone-700 border border-stone-700/50 rounded-lg text-[10px] font-mono font-bold text-amber-400 whitespace-nowrap">+50rb</button>
                    </div>

                    <!-- Kembalian Display Box -->
                    <div x-show="totalCart > 0 && cashTendered !== '' && cashTendered !== null">
                        <!-- Uang Cukup / Lebih -> Tampilkan Kembalian -->
                        <div x-show="!isUnderpaid" class="p-2 rounded-xl bg-emerald-950/60 border border-emerald-500/50 flex items-center justify-between">
                            <div>
                                <span class="text-[9px] uppercase font-bold text-emerald-300 block">Uang Kembalian:</span>
                                <span class="font-mono font-black text-base text-emerald-400" x-text="formatRupiah(changeAmount)"></span>
                            </div>
                            <span class="text-[10px] px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-bold" x-text="changeAmount === 0 ? 'Uang Pas' : 'Kembali'"></span>
                        </div>

                        <!-- Uang Kurang -> Tampilkan Peringatan -->
                        <div x-show="isUnderpaid" class="p-2 rounded-xl bg-red-950/60 border border-red-500/50 flex items-center justify-between text-red-300">
                            <div>
                                <span class="text-[9px] uppercase font-bold text-red-300 block">Uang Masih Kurang:</span>
                                <span class="font-mono font-black text-sm text-red-400" x-text="formatRupiah(shortageAmount)"></span>
                            </div>
                            <i class="fa-solid fa-triangle-exclamation text-red-400 text-sm"></i>
                        </div>
                    </div>
                </div>

                <button type="submit" 
                    :disabled="cart.length === 0 || (paymentMethod === 'cash' && isUnderpaid) || (paymentMethod === 'cash' && totalCart > 0 && (cashTendered === '' || cashTendered === null))" 
                    class="w-full py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 disabled:opacity-40 disabled:cursor-not-allowed text-stone-950 font-black text-xs uppercase tracking-wider shadow-lg shadow-emerald-600/20 transition-all flex items-center justify-center gap-2">
                    <template x-if="paymentMethod === 'cash' && isUnderpaid">
                        <span><i class="fa-solid fa-triangle-exclamation mr-1"></i> Uang Kurang <span x-text="formatRupiah(shortageAmount)"></span></span>
                    </template>
                    <template x-if="!(paymentMethod === 'cash' && isUnderpaid)">
                        <span><i class="fa-solid fa-check mr-1"></i> Proses Order & Bayar</span>
                    </template>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function posApp() {
    return {
        searchQuery: '',
        activeCategory: 'all',
        customerName: 'Pelanggan Walk-In',
        orderType: 'dine_in',
        tableNumber: 'Meja 01',
        paymentMethod: 'cash',
        cashTendered: '',
        changeAmount: 0,
        cart: [],
        menus: [
            @foreach($categories as $cat)
                @foreach($cat->menus as $m)
                {
                    id: {{ $m->id }},
                    category_id: '{{ $cat->id }}',
                    category_name: '{{ $cat->name }}',
                    name: '{{ addslashes($m->name) }}',
                    description: '{{ addslashes($m->description) }}',
                    price: {{ (float)$m->price }},
                    image_url: '{{ $m->image_url }}',
                },
                @endforeach
            @endforeach
        ],

        get filteredMenus() {
            return this.menus.filter(m => {
                const matchCat = this.activeCategory === 'all' || m.category_id === this.activeCategory;
                const matchSearch = !this.searchQuery || m.name.toLowerCase().includes(this.searchQuery.toLowerCase());
                return matchCat && matchSearch;
            });
        },

        addToCart(menu) {
            const found = this.cart.find(it => it.id === menu.id);
            if (found) {
                found.qty++;
            } else {
                this.cart.push({
                    id: menu.id,
                    name: menu.name,
                    price: menu.price,
                    qty: 1,
                    notes: ''
                });
            }
            this.calcChange();
        },

        incQty(idx) {
            this.cart[idx].qty++;
            this.calcChange();
        },

        decQty(idx) {
            if (this.cart[idx].qty > 1) {
                this.cart[idx].qty--;
            } else {
                this.cart.splice(idx, 1);
            }
            this.calcChange();
        },

        clearCart() {
            this.cart = [];
            this.cashTendered = '';
            this.changeAmount = 0;
        },

        get totalCart() {
            return this.cart.reduce((sum, it) => sum + (it.price * it.qty), 0);
        },

        setExactCash() {
            this.cashTendered = this.totalCart;
            this.calcChange();
        },

        setCash(val) {
            this.cashTendered = val;
            this.calcChange();
        },

        addCash(val) {
            const current = parseFloat(this.cashTendered) || 0;
            this.cashTendered = current + val;
            this.calcChange();
        },

        calcChange() {
            if (this.cashTendered === '' || this.cashTendered === null) {
                this.changeAmount = 0;
                return;
            }
            const tendered = parseFloat(this.cashTendered) || 0;
            const total = this.totalCart;
            this.changeAmount = Math.max(0, tendered - total);
        },

        get isUnderpaid() {
            if (this.paymentMethod !== 'cash' || this.totalCart === 0) return false;
            if (this.cashTendered === '' || this.cashTendered === null) return false;
            const tendered = parseFloat(this.cashTendered) || 0;
            return tendered < this.totalCart;
        },

        get shortageAmount() {
            const tendered = parseFloat(this.cashTendered) || 0;
            return Math.max(0, this.totalCart - tendered);
        },

        onPaymentMethodChange() {
            if (this.paymentMethod === 'cash' && (this.cashTendered === '' || this.cashTendered === null)) {
                this.cashTendered = this.totalCart > 0 ? this.totalCart : '';
                this.calcChange();
            }
        },

        formatRupiah(num) {
            return 'Rp ' + (num || 0).toLocaleString('id-ID');
        }
    }
}
</script>
@endpush
@endsection
