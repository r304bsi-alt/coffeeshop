@extends('layouts.app')

@section('title', 'Menu Kopi Senja - Pesan Dari Meja')

@section('content')
<div class="space-y-6 pb-24" x-data="customerMenuApp()">
    
    <!-- Hero / Table Identifier Header -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-coffee-950 via-stone-900 to-amber-950/60 border border-stone-800 p-6 sm:p-8 shadow-2xl">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 border border-amber-500/40 text-amber-300 text-xs font-bold uppercase tracking-wider mb-2">
                    <i class="fa-solid fa-qrcode"></i> Order Meja QR Code
                </div>
                <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
                    Pesan Langsung dari Meja Anda
                </h1>
                <p class="text-stone-300 text-xs sm:text-sm mt-1 max-w-xl">
                    Pilih menu favorit, tentukan metode pembayaran (QRIS Midtrans atau Tunai ke Kasir), dan barista kami akan langsung menyiapkannya.
                </p>
            </div>

            <!-- Table Picker / Current Table Badge -->
            <div class="bg-stone-900/90 border border-stone-700/80 rounded-2xl p-4 shrink-0 shadow-lg text-right">
                <span class="text-[10px] text-stone-400 uppercase font-bold block">Nomor Meja Anda:</span>
                <div class="flex items-center justify-end gap-2 mt-1">
                    <i class="fa-solid fa-chair text-amber-400 text-base"></i>
                    <select x-model="selectedTable" class="bg-stone-800 border border-stone-700 rounded-xl px-3 py-1 text-sm font-black text-amber-300 font-mono focus:outline-none focus:border-amber-500">
                        @foreach($tables as $tbl)
                        <option value="{{ $tbl->table_number }}" {{ $tableParam === $tbl->table_number ? 'selected' : '' }}>
                            {{ $tbl->table_number }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Google Sign-In Banner for Customer Requirement -->
        <div class="mt-5 pt-4 border-t border-stone-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
            @auth
            <div class="flex items-center gap-2 text-stone-300">
                <div class="w-6 h-6 rounded-full bg-amber-500 text-stone-950 font-bold flex items-center justify-center text-[10px]">
                    <i class="fa-solid fa-user"></i>
                </div>
                <span>Masuk sebagai: <strong class="text-white">{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }})</span>
            </div>
            @else
            <div class="flex items-center gap-2 text-stone-300">
                <i class="fa-brands fa-google text-amber-400 text-base"></i>
                <span>Ingin riwayat pesanan tersimpan?</span>
            </div>
            <form action="{{ route('auth.google') }}" method="POST">
                @csrf
                <input type="hidden" name="table" :value="selectedTable">
                <button type="submit" class="px-3 py-1.5 rounded-xl bg-white hover:bg-stone-100 text-stone-900 font-bold text-xs flex items-center gap-2 transition-all shadow-sm">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                    </svg>
                    <span>Login dengan Google Sign-In</span>
                </button>
            </form>
            @endauth
        </div>
    </div>

    <!-- Category Filter Pills -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
        <a href="{{ route('customer.menu', ['table' => $tableParam]) }}" 
           class="px-4 py-2 rounded-2xl text-xs font-bold whitespace-nowrap transition-all {{ empty($selectedCategory) ? 'bg-amber-500 text-stone-950 shadow-lg shadow-amber-500/20' : 'bg-stone-900 border border-stone-800 text-stone-300 hover:bg-stone-800' }}">
            Semua Menu
        </a>
        @foreach($categories as $cat)
        <a href="{{ route('customer.menu', ['table' => $tableParam, 'category' => $cat->id]) }}" 
           class="px-4 py-2 rounded-2xl text-xs font-bold whitespace-nowrap transition-all {{ $selectedCategory == $cat->id ? 'bg-amber-500 text-stone-950 shadow-lg shadow-amber-500/20' : 'bg-stone-900 border border-stone-800 text-stone-300 hover:bg-stone-800' }}">
            <i class="fa-solid {{ $cat->icon ?? 'fa-mug-hot' }} mr-1.5"></i>
            {{ $cat->name }}
        </a>
        @endforeach
    </div>

    <!-- Menu Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @forelse($menus as $m)
        <div class="bg-stone-900/90 border border-stone-800 rounded-3xl p-4 shadow-xl flex flex-col justify-between hover:border-amber-500/40 transition-all hover:scale-[1.01] group">
            <div>
                <!-- Image & Tag -->
                <div class="relative aspect-video rounded-2xl overflow-hidden bg-stone-950 mb-3">
                    <img src="{{ $m->image_url }}" alt="{{ $m->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <span class="absolute top-2 left-2 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-black/70 backdrop-blur-md text-amber-400">
                        {{ $m->category?->name }}
                    </span>
                </div>

                <!-- Title & Description -->
                <h3 class="font-bold text-white text-base group-hover:text-amber-400 transition-colors">
                    {{ $m->name }}
                </h3>
                <p class="text-stone-400 text-xs mt-1 line-clamp-2 leading-relaxed">
                    {{ $m->description }}
                </p>
            </div>

            <!-- Price & Add Button -->
            <div class="mt-4 pt-3 border-t border-stone-800 flex items-center justify-between">
                <div>
                    <span class="text-[10px] text-stone-500 uppercase block font-semibold">Harga</span>
                    <span class="font-mono font-black text-amber-400 text-base">
                        Rp {{ number_format($m->price, 0, ',', '.') }}
                    </span>
                </div>
                <button type="button" @click="addToCart({{ $m->id }}, '{{ addslashes($m->name) }}', {{ $m->price }})" class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-stone-950 font-bold text-xs shadow-md transition-all flex items-center gap-1.5">
                    <i class="fa-solid fa-plus"></i> Tambah
                </button>
            </div>
        </div>
        @empty
        <div class="col-span-full py-12 text-center text-stone-500">
            <i class="fa-solid fa-mug-hot text-4xl mb-2 block"></i>
            Tidak ada menu dalam kategori ini.
        </div>
        @endforelse
    </div>

    <!-- Floating Cart Bar (Sticky at Bottom) -->
    <div x-show="cart.length > 0" x-transition x-cloak class="fixed bottom-4 inset-x-4 max-w-xl mx-auto z-40 bg-stone-900/95 backdrop-blur-md border-2 border-amber-500/80 rounded-2xl p-3.5 shadow-2xl flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-stone-950 font-black flex items-center justify-center text-sm shadow-md">
                <span x-text="totalItems"></span>
            </div>
            <div>
                <span class="text-xs text-stone-300 font-semibold block">Total Pesanan Meja:</span>
                <span class="font-mono font-black text-lg text-emerald-400" x-text="formatRupiah(totalCart)"></span>
            </div>
        </div>
        <button type="button" @click="cartDrawerOpen = true" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-stone-950 font-black text-xs uppercase tracking-wider shadow-lg shadow-amber-500/30 transition-all flex items-center gap-2">
            <span>Lihat Keranjang & Bayar</span>
            <i class="fa-solid fa-arrow-right"></i>
        </button>
    </div>

    <!-- Cart & Checkout Modal Drawer -->
    <div x-show="cartDrawerOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div class="bg-stone-900 border border-stone-800 rounded-3xl max-w-lg w-full max-h-[90vh] flex flex-col justify-between shadow-2xl overflow-hidden animate-scaleIn" @click.away="cartDrawerOpen = false">
            
            <!-- Drawer Header -->
            <div class="p-5 bg-stone-800/80 border-b border-stone-700/80 flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-white text-base flex items-center gap-2">
                        <i class="fa-solid fa-bag-shopping text-amber-400"></i> Keranjang Pesanan
                    </h3>
                    <span class="text-[11px] text-stone-400" x-text="'Meja: ' + selectedTable"></span>
                </div>
                <button type="button" @click="cartDrawerOpen = false" class="text-stone-400 hover:text-white p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Drawer Body -->
            <form action="{{ route('customer.order.place') }}" method="POST" class="p-5 flex-1 overflow-y-auto space-y-4" id="customerOrderForm">
                @csrf
                <input type="hidden" name="table_number" :value="selectedTable">

                <!-- Customer Details -->
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-stone-300 mb-1">Nama Pemesan</label>
                        <input type="text" name="customer_name" required value="{{ auth()->check() ? auth()->user()->name : 'Tamu ' . $tableParam }}" class="w-full px-3 py-2 bg-stone-800 border border-stone-700 rounded-xl text-white text-xs focus:outline-none focus:border-amber-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-stone-300 mb-1">Jenis Santap</label>
                            <select name="order_type" class="w-full px-3 py-2 bg-stone-800 border border-stone-700 rounded-xl text-white text-xs focus:outline-none focus:border-amber-500">
                                <option value="dine_in">Dine In (Makan di Tempat)</option>
                                <option value="take_away">Take Away (Bungkus)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-stone-300 mb-1">Catatan Tambahan</label>
                            <input type="text" name="notes" placeholder="Catatan ke barista..." class="w-full px-3 py-2 bg-stone-800 border border-stone-700 rounded-xl text-white text-xs focus:outline-none focus:border-amber-500">
                        </div>
                    </div>
                </div>

                <!-- Items in cart -->
                <div class="pt-3 border-t border-stone-800">
                    <div class="text-xs font-bold text-stone-400 uppercase tracking-wider mb-2">Item Terpilih:</div>
                    <div class="space-y-2.5 max-h-48 overflow-y-auto pr-1">
                        <template x-for="(item, idx) in cart" :key="idx">
                            <div class="p-2.5 rounded-xl bg-stone-800/60 border border-stone-700/60 flex items-center justify-between text-xs">
                                <input type="hidden" :name="'items[' + idx + '][menu_id]'" :value="item.id">
                                <input type="hidden" :name="'items[' + idx + '][quantity]'" :value="item.qty">
                                <input type="hidden" :name="'items[' + idx + '][notes]'" :value="item.notes">

                                <div class="flex-1 mr-2">
                                    <span class="font-bold text-white block" x-text="item.name"></span>
                                    <span class="font-mono text-stone-400 text-[10px]" x-text="formatRupiah(item.price * item.qty)"></span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" @click="decQty(idx)" class="w-6 h-6 rounded bg-stone-700 text-stone-200 flex items-center justify-center font-bold text-xs">-</button>
                                    <span class="font-mono font-bold text-white w-5 text-center text-xs" x-text="item.qty"></span>
                                    <button type="button" @click="incQty(idx)" class="w-6 h-6 rounded bg-stone-700 text-stone-200 flex items-center justify-center font-bold text-xs">+</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Payment Method Selection Requirement -->
                <div class="pt-3 border-t border-stone-800">
                    <label class="block text-xs font-bold text-amber-400 uppercase tracking-wider mb-2">Pilih Cara Pembayaran:</label>
                    <div class="grid grid-cols-2 gap-2.5">
                        <label class="p-3 rounded-2xl border-2 flex flex-col items-center text-center cursor-pointer transition-all"
                            :class="paymentMethod === 'midtrans_qris' ? 'bg-amber-500/20 border-amber-500 text-white' : 'bg-stone-800/80 border-stone-700 text-stone-400'">
                            <input type="radio" name="payment_method" value="midtrans_qris" x-model="paymentMethod" class="hidden">
                            <i class="fa-solid fa-qrcode text-xl text-amber-400 mb-1"></i>
                            <span class="font-bold text-xs">QRIS (Midtrans)</span>
                            <span class="text-[10px] text-stone-400">Bayar online instan</span>
                        </label>

                        <label class="p-3 rounded-2xl border-2 flex flex-col items-center text-center cursor-pointer transition-all"
                            :class="paymentMethod === 'cash' ? 'bg-amber-500/20 border-amber-500 text-white' : 'bg-stone-800/80 border-stone-700 text-stone-400'">
                            <input type="radio" name="payment_method" value="cash" x-model="paymentMethod" class="hidden">
                            <i class="fa-solid fa-money-bill-wave text-xl text-emerald-400 mb-1"></i>
                            <span class="font-bold text-xs">Tunai (Di Kasir)</span>
                            <span class="text-[10px] text-stone-400">Tunjukkan QR ke kasir</span>
                        </label>
                    </div>
                </div>

                <!-- Total & Submit -->
                <div class="pt-4 border-t border-stone-800 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] text-stone-400 uppercase block font-semibold">Total Pembayaran:</span>
                        <span class="font-mono font-black text-xl text-emerald-400" x-text="formatRupiah(totalCart)"></span>
                    </div>
                    <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-stone-950 font-black text-xs uppercase tracking-wider shadow-lg shadow-amber-500/30 transition-all flex items-center gap-2">
                        <span>Konfirmasi Order</span>
                        <i class="fa-solid fa-check"></i>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

@push('scripts')
<script>
function customerMenuApp() {
    return {
        selectedTable: '{{ $tableParam }}',
        cart: [],
        cartDrawerOpen: false,
        paymentMethod: 'midtrans_qris',

        addToCart(id, name, price) {
            const found = this.cart.find(it => it.id === id);
            if (found) {
                found.qty++;
            } else {
                this.cart.push({ id: id, name: name, price: price, qty: 1, notes: '' });
            }
        },

        incQty(idx) {
            this.cart[idx].qty++;
        },

        decQty(idx) {
            if (this.cart[idx].qty > 1) {
                this.cart[idx].qty--;
            } else {
                this.cart.splice(idx, 1);
            }
        },

        get totalItems() {
            return this.cart.reduce((sum, it) => sum + it.qty, 0);
        },

        get totalCart() {
            return this.cart.reduce((sum, it) => sum + (it.price * it.qty), 0);
        },

        formatRupiah(num) {
            return 'Rp ' + (num || 0).toLocaleString('id-ID');
        }
    }
}
</script>
@endpush
@endsection
