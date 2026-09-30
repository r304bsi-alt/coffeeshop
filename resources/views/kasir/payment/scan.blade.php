@extends('layouts.app')

@section('title', 'Scan Pembayaran Tunai - Kasir')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="cashScanner()">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2.5">
                <i class="fa-solid fa-qrcode text-amber-400"></i> Scan & Pembayaran Tunai (Cash)
            </h1>
            <p class="text-stone-400 text-xs mt-1">Scan QR Code Bayar atau ketikkan kode pemesanan pelanggan untuk memproses pembayaran kasir.</p>
        </div>
        <a href="{{ route('kasir.dashboard') }}" class="px-3 py-1.5 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-300 text-xs font-semibold">
            &larr; Kembali
        </a>
    </div>

    <!-- Scan Input Card -->
    <div class="bg-stone-900/90 border border-stone-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
        <div>
            <label class="block text-xs font-bold text-stone-300 uppercase tracking-wider mb-2">
                Scan QR Code Pembayaran / Masukkan Kode Pesanan:
            </label>
            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-stone-500 text-sm">
                        <i class="fa-solid fa-barcode"></i>
                    </span>
                    <input type="text" x-model="orderCode" @keyup.enter="lookupOrder()" placeholder="Contoh: CASH-XXXXXXXX atau ORD-20260930-XXXX" autofocus class="w-full pl-10 pr-4 py-3 bg-stone-800 border-2 border-stone-700 focus:border-amber-500 rounded-2xl text-white font-mono font-bold text-sm focus:outline-none transition-all">
                </div>
                <button type="button" @click="lookupOrder()" class="px-6 py-3 rounded-2xl bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs tracking-wider uppercase transition-all shadow-lg shadow-amber-500/20">
                    Cek Pesanan
                </button>
            </div>
            <p class="text-[11px] text-stone-500 mt-2">
                <i class="fa-solid fa-circle-info mr-1"></i> Mendukung pemindai barcode / QR scanner USB/Bluetooth otomatis.
            </p>
        </div>

        <!-- Error state -->
        <div x-show="errorMessage" x-cloak class="p-3.5 rounded-xl bg-red-950/60 border border-red-700/50 text-red-300 text-xs flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span x-text="errorMessage"></span>
        </div>

        <!-- Order Result Summary Box -->
        <div x-show="order" x-cloak class="p-6 rounded-2xl bg-stone-800/60 border-2 border-emerald-500/50 space-y-5 animate-fadeIn">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-stone-700/60 pb-4">
                <div>
                    <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider">Nomor Pesanan:</span>
                    <div class="font-mono font-black text-lg text-white" x-text="order.order_number"></div>
                    <div class="text-xs text-stone-300 mt-0.5">
                        Pemesan: <strong class="text-amber-400" x-text="order.customer_name"></strong> &bull; Meja: <strong class="text-emerald-400" x-text="order.table_number"></strong> (<span x-text="order.order_type"></span>)
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">Total Tagihan</span>
                    <span class="font-mono font-black text-2xl text-emerald-400" x-text="order.formatted_total"></span>
                </div>
            </div>

            <!-- Items list in order -->
            <div>
                <h4 class="text-xs font-bold text-stone-400 uppercase tracking-wider mb-2">Item Yang Dipesan:</h4>
                <div class="divide-y divide-stone-700/50 rounded-xl bg-stone-900/60 border border-stone-700/50 p-3">
                    <template x-for="item in order.items" :key="item.name">
                        <div class="py-2 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-bold text-white" x-text="item.qty + 'x ' + item.name"></span>
                                <span x-show="item.notes" class="block text-[10px] text-stone-400 italic" x-text="'Catatan: ' + item.notes"></span>
                            </div>
                            <span class="font-mono text-stone-300" x-text="item.subtotal"></span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Payment Process Form -->
            <form action="{{ route('kasir.payment.process') }}" method="POST" class="pt-2 space-y-4">
                @csrf
                <input type="hidden" name="order_identifier" :value="order.order_number">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-stone-300 uppercase tracking-wider mb-1.5">Uang Tunai Diterima (Rp)</label>
                        <input type="number" name="cash_tendered" x-model="cashTendered" @input="calcChange()" min="0" step="1000" class="w-full px-3.5 py-2.5 bg-stone-900 border border-stone-700 rounded-xl text-white font-mono font-bold text-sm focus:outline-none focus:border-emerald-500" placeholder="0">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-300 uppercase tracking-wider mb-1.5">Uang Kembalian</label>
                        <div class="px-3.5 py-2.5 bg-stone-900/60 border border-stone-700/50 rounded-xl text-emerald-400 font-mono font-black text-sm">
                            <span x-text="formatRupiah(changeAmount)"></span>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-stone-700/60 flex items-center justify-between">
                    <div class="text-[11px] text-stone-400">
                        <i class="fa-solid fa-bell text-amber-400 mr-1"></i> Setelah lunas, dapur otomatis menerima orderan & stok toko berkurang.
                    </div>
                    <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-stone-950 font-black text-xs uppercase tracking-wider shadow-lg shadow-emerald-600/30 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-check"></i> Konfirmasi Lunas Tunai
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function cashScanner() {
    return {
        orderCode: '',
        order: null,
        errorMessage: '',
        cashTendered: 0,
        changeAmount: 0,

        lookupOrder() {
            if (!this.orderCode.trim()) return;
            this.errorMessage = '';
            this.order = null;

            fetch(`/api/kasir/lookup-order?code=${encodeURIComponent(this.orderCode.trim())}`)
                .then(res => {
                    if (!res.ok) throw new Error('Pesanan dengan kode tersebut tidak ditemukan atau sudah dibayar.');
                    return res.json();
                })
                .then(data => {
                    if (data.success && data.order) {
                        this.order = data.order;
                        this.cashTendered = this.order.total_amount;
                        this.calcChange();
                    } else {
                        this.errorMessage = 'Pesanan tidak ditemukan.';
                    }
                })
                .catch(err => {
                    this.errorMessage = err.message;
                });
        },

        calcChange() {
            if (!this.order) return;
            const tendered = parseFloat(this.cashTendered) || 0;
            const total = parseFloat(this.order.total_amount) || 0;
            this.changeAmount = Math.max(0, tendered - total);
        },

        formatRupiah(num) {
            return 'Rp ' + (num || 0).toLocaleString('id-ID');
        }
    }
}
</script>
@endpush
@endsection
