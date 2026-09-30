@extends('layouts.app')

@section('title', 'Status Pesanan - ' . $order->order_number)

@section('content')
<div class="max-w-md mx-auto space-y-6 py-4" x-data="orderTrackerApp({{ $order->id }}, '{{ $order->order_status }}')">
    <div class="bg-stone-900 border-2 border-stone-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6 text-center">
        
        <!-- Header -->
        <div class="border-b border-stone-800 pb-4">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-bold uppercase tracking-wider mb-2">
                <i class="fa-solid fa-receipt"></i> Status Pesanan
            </span>
            <h1 class="text-xl font-extrabold text-white">{{ $order->order_number }}</h1>
            <p class="text-xs text-stone-400 mt-1">Meja: <strong class="text-white">{{ $order->table_number }}</strong> &bull; Pemesan: <strong class="text-white">{{ $order->customer_name }}</strong></p>
        </div>

        <!-- Dynamic Status Display -->
        <div class="py-4">
            <!-- State 1: In Kitchen -->
            <div x-show="currentStatus === 'in_kitchen'" class="space-y-3">
                <div class="w-20 h-20 mx-auto rounded-3xl bg-amber-500/20 border-2 border-amber-500/40 text-amber-400 flex items-center justify-center text-3xl animate-bounce">
                    <i class="fa-solid fa-fire"></i>
                </div>
                <h3 class="text-lg font-black text-white">Sedang Disiapkan di Dapur</h3>
                <p class="text-xs text-stone-400 max-w-xs mx-auto leading-relaxed">
                    Barista kami sedang meracik pesanan Anda dengan takaran bahan baku presisi. Mohon tunggu sebentar ya!
                </p>
            </div>

            <!-- State 2: Ready / Called -->
            <div x-show="currentStatus === 'ready' || currentStatus === 'completed'" class="space-y-3" x-cloak>
                <div class="w-20 h-20 mx-auto rounded-3xl bg-emerald-500/20 border-2 border-emerald-500/40 text-emerald-400 flex items-center justify-center text-3xl">
                    <i class="fa-solid fa-bell animate-bounce"></i>
                </div>
                <h3 class="text-lg font-black text-emerald-400">Pesanan Selesai & Siap!</h3>
                <p class="text-xs text-stone-300 max-w-xs mx-auto leading-relaxed font-semibold">
                    Pesanan Anda telah selesai disiapkan oleh Dapur. Silakan ambil di kasir/bar atau tunggu staf kami mengantarkan ke <span class="text-amber-400">{{ $order->table_number }}</span>!
                </p>
            </div>
        </div>

        <!-- Progress Steps Indicator -->
        <div class="space-y-3 pt-4 border-t border-stone-800 text-left">
            <!-- Step 1: Paid -->
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-full bg-emerald-500 text-stone-950 font-bold flex items-center justify-center text-xs">
                    <i class="fa-solid fa-check"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-white block">Pembayaran Berhasil</span>
                    <span class="text-[10px] text-stone-500">Metode: {{ strtoupper($order->payment_method) }} &bull; Lunas</span>
                </div>
            </div>

            <!-- Step 2: Kitchen KDS -->
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-full font-bold flex items-center justify-center text-xs"
                    :class="currentStatus === 'in_kitchen' ? 'bg-amber-500 text-stone-950 animate-pulse' : (currentStatus === 'ready' || currentStatus === 'completed' ? 'bg-emerald-500 text-stone-950' : 'bg-stone-800 text-stone-500')">
                    <i class="fa-solid" :class="currentStatus === 'ready' || currentStatus === 'completed' ? 'fa-check' : 'fa-fire'"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-white block">Proses Seduh / Masak di Dapur</span>
                    <span class="text-[10px] text-stone-500" x-text="currentStatus === 'in_kitchen' ? 'Barista sedang meracik...' : 'Selesai disiapkan'"></span>
                </div>
            </div>

            <!-- Step 3: Ready -->
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-full font-bold flex items-center justify-center text-xs"
                    :class="currentStatus === 'ready' || currentStatus === 'completed' ? 'bg-emerald-500 text-stone-950 animate-bounce' : 'bg-stone-800 text-stone-500'">
                    <i class="fa-solid fa-bell"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-white block">Pesanan Siap & Dipanggil Kasir</span>
                    <span class="text-[10px] text-stone-500" x-text="currentStatus === 'ready' || currentStatus === 'completed' ? 'Siap dinikmati!' : 'Menunggu dapur selesai'"></span>
                </div>
            </div>
        </div>

        <!-- Items Summary -->
        <div class="text-left bg-stone-800/40 p-4 rounded-2xl border border-stone-700/40 text-xs space-y-1.5">
            <div class="font-bold text-stone-300 uppercase tracking-wider text-[10px]">Item Pesanan:</div>
            @foreach($order->items as $it)
            <div class="flex justify-between text-stone-400 text-[11px]">
                <span>{{ $it->quantity }}x {{ $it->menu_name }}</span>
                <span class="font-mono text-white">Rp {{ number_format($it->subtotal, 0, ',', '.') }}</span>
            </div>
            @endforeach
            <div class="pt-2 border-t border-stone-700/50 flex justify-between font-bold text-white text-xs">
                <span>Total:</span>
                <span class="font-mono text-emerald-400">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="pt-2">
            <a href="{{ route('customer.menu', ['table' => $order->table_number]) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-300 text-xs font-semibold transition-all">
                <i class="fa-solid fa-plus"></i> Pesan Menu Tambahan
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
function orderTrackerApp(orderId, initialStatus) {
    return {
        orderId: orderId,
        currentStatus: initialStatus,
        init() {
            // Poll order status every 3 seconds
            setInterval(() => {
                fetch(`/api/customer/order/${this.orderId}/status`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.order_status !== this.currentStatus) {
                            this.currentStatus = data.order_status;
                            if (this.currentStatus === 'ready') {
                                playChime('kitchen_done');
                            }
                        }
                    })
                    .catch(e => console.debug('Status check note:', e));
            }, 3000);
        }
    }
}
</script>
@endpush
@endsection
