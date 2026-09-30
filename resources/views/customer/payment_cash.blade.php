@extends('layouts.app')

@section('title', 'Voucher Bayar Tunai - ' . $order->order_number)

@section('content')
<div class="max-w-md mx-auto space-y-6 py-4" x-data="cashVoucherApp({{ $order->id }})">
    <div class="bg-stone-900 border-2 border-stone-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6 text-center">
        
        <!-- Header -->
        <div class="border-b border-stone-800 pb-4">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 text-[10px] font-bold uppercase tracking-wider mb-2">
                <i class="fa-solid fa-money-bill-wave"></i> Metode Pembayaran Tunai
            </span>
            <h1 class="text-xl font-extrabold text-white">Voucher Bayar Kasir</h1>
            <p class="text-xs text-stone-400 mt-1">Tunjukkan QR Code ini kepada kasir di meja pembayaran.</p>
        </div>

        <!-- Total Amount -->
        <div>
            <span class="text-xs text-stone-400">Total Yang Harus Dibayar:</span>
            <div class="text-3xl font-black text-amber-400 font-mono mt-1">
                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
            </div>
            <div class="text-xs text-stone-300 font-semibold mt-1">
                {{ $order->order_number }} &bull; {{ $order->table_number }}
            </div>
        </div>

        <!-- Big QR Code Display -->
        <div class="bg-white p-6 rounded-3xl shadow-inner max-w-xs mx-auto border-4 border-stone-800 relative">
            <div class="aspect-square flex items-center justify-center">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode($order->payment_token) }}" 
                     alt="QR Bayar Kasir" 
                     class="w-full h-full object-contain">
            </div>
            <div class="mt-2 font-mono font-black text-sm text-stone-900 tracking-wider">
                {{ $order->payment_token }}
            </div>
        </div>

        <div class="p-3 bg-stone-800/80 rounded-2xl border border-stone-700/80 text-xs text-stone-300 flex items-center justify-center gap-2">
            <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
            <span>Menunggu kasir memindai QR Code...</span>
        </div>

        <div class="text-left bg-stone-800/40 p-4 rounded-2xl border border-stone-700/40 text-xs space-y-1.5">
            <div class="font-bold text-stone-300 uppercase tracking-wider text-[10px]">Rincian Pesanan:</div>
            @foreach($order->items as $it)
            <div class="flex justify-between text-stone-400 text-[11px]">
                <span>{{ $it->quantity }}x {{ $it->menu_name }}</span>
                <span class="font-mono text-white">Rp {{ number_format($it->subtotal, 0, ',', '.') }}</span>
            </div>
            @endforeach
        </div>

        <!-- Shortcut for Testing Kasir Scan directly -->
        <div class="pt-2 border-t border-stone-800 text-[11px] text-stone-500">
            Ingin menguji sebagai kasir?
            <a href="{{ route('kasir.payment.scan') }}" target="_blank" class="text-amber-400 hover:underline font-bold">
                Buka Layar Scan Kasir &rarr;
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
function cashVoucherApp(orderId) {
    return {
        orderId: orderId,
        init() {
            // Live poll to check if cashier has confirmed the cash payment
            setInterval(() => {
                fetch(`/api/customer/order/${this.orderId}/status`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.payment_status === 'paid') {
                            window.location.href = `/customer/order/${this.orderId}/status`;
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
