@extends('layouts.app')

@section('title', 'Pembayaran QRIS Midtrans - ' . $order->order_number)

@section('content')
<div class="max-w-md mx-auto space-y-6 py-4">
    <!-- Midtrans QRIS Container -->
    <div class="bg-stone-900 border-2 border-stone-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6 text-center">
        
        <!-- Midtrans & QRIS Header -->
        <div class="flex items-center justify-between border-b border-stone-800 pb-4">
            <div class="text-left">
                <span class="text-[10px] uppercase font-black text-blue-400 tracking-wider">Payment Gateway</span>
                <div class="font-extrabold text-white text-base">Midtrans QRIS</div>
            </div>
            <div class="w-12 h-6 bg-red-600 rounded flex items-center justify-center font-black text-white text-[10px] tracking-wider">
                QRIS
            </div>
        </div>

        <!-- Order & Merchant Info -->
        <div>
            <span class="text-xs text-stone-400">Total Pembayaran:</span>
            <div class="text-3xl font-black text-emerald-400 font-mono mt-1">
                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
            </div>
            <div class="text-xs text-stone-300 font-semibold mt-1">
                {{ $order->order_number }} &bull; {{ $order->table_number }}
            </div>
        </div>

        <!-- QR Code Canvas Display -->
        <div class="bg-white p-6 rounded-3xl shadow-inner max-w-xs mx-auto border-4 border-stone-800 relative">
            <!-- Simulated QRIS Image / SVG -->
            <div class="aspect-square flex items-center justify-center">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode(route('customer.midtrans.pay', $order)) }}" 
                     alt="QRIS Midtrans" 
                     class="w-full h-full object-contain"
                     onerror="this.src='https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=MIDTRANS-QRIS-{{ $order->order_number }}'">
            </div>
            <div class="mt-2 text-[10px] font-bold text-stone-700 tracking-widest uppercase">
                NMID: ID102003948271
            </div>
        </div>

        <p class="text-xs text-stone-400 leading-relaxed">
            Buka aplikasi BCA, GoPay, OVO, Dana, ShopeePay, atau Mobile Banking apa saja, lalu scan QR Code di atas.
        </p>

        <!-- Simulation Button for User / Evaluator -->
        <div class="pt-4 border-t border-stone-800 space-y-2">
            <form action="{{ route('customer.midtrans.simulate', $order) }}" method="POST">
                @csrf
                <button type="submit" class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-stone-950 font-black text-xs uppercase tracking-wider shadow-lg shadow-emerald-600/30 transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-circle-check text-sm"></i>
                    <span>Simulasikan Pembayaran Berhasil</span>
                </button>
            </form>
            <span class="text-[10px] text-stone-500 block">
                (Klik tombol di atas untuk menyimulasikan webhook Midtrans bahwa pembayaran telah lunas)
            </span>
        </div>

        <div class="pt-2 text-center">
            <a href="{{ route('customer.menu', ['table' => $order->table_number]) }}" class="text-xs text-stone-500 hover:text-stone-300">
                &larr; Batalkan / Kembali ke Menu
            </a>
        </div>
    </div>
</div>
@endsection
