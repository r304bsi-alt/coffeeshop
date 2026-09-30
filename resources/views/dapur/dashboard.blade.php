@extends('layouts.app')

@section('title', 'KDS Antrean Dapur Barista (FIFO) - Kopi Senja')

@section('content')
<div class="space-y-6" x-data="kitchenKDS()">
    <!-- Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 glass-panel p-6 rounded-3xl">
        <div>
            <div class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                <i class="fa-solid fa-fire text-amber-500"></i> Kitchen Display System (KDS)
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
                <span>Antrean Masak & Seduh (FIFO)</span>
                <span class="px-3 py-1 rounded-full text-xs font-black bg-amber-500 text-stone-950 font-mono">
                    {{ $activeOrders->count() }} Antrean Aktif
                </span>
            </h1>
            <p class="text-stone-400 text-xs sm:text-sm mt-1">
                Orderan ditampilkan otomatis urut dari waktu yang paling awal masuk (First In First Out).
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-stone-800/80 border border-stone-700 text-xs text-stone-400">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                <span>WebSocket / Live Auto-Sync</span>
            </div>
            <button type="button" @click="fetchActiveOrders()" class="p-2.5 rounded-xl bg-stone-800 hover:bg-stone-700 text-amber-400 border border-stone-700 text-xs transition-colors" title="Muat Ulang Antrean">
                <i class="fa-solid fa-rotate"></i>
            </button>
        </div>
    </div>

    <!-- Active Orders Grid (FIFO) -->
    @if($activeOrders->isEmpty())
        <div class="bg-stone-900/90 border border-stone-800 rounded-3xl p-12 text-center shadow-xl">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-3xl mb-3">
                <i class="fa-solid fa-mug-hot"></i>
            </div>
            <h3 class="text-lg font-bold text-white">Semua Pesanan Selesai!</h3>
            <p class="text-xs text-stone-400 mt-1">Saat ini belum ada orderan baru yang masuk. Halaman akan memperbarui otomatis saat pesanan tiba.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($activeOrders as $index => $ord)
            @php
                $minutesElapsed = $ord->paid_at ? (int) $ord->paid_at->diffInMinutes(now()) : 0;
                $isUrgent = $minutesElapsed >= 10;
            @endphp
            <div class="rounded-3xl border-2 {{ $isUrgent ? 'border-red-500 bg-red-950/20' : ($index === 0 ? 'border-amber-500 bg-stone-900/95' : 'border-stone-800 bg-stone-900/90') }} p-5 shadow-2xl flex flex-col justify-between transition-all hover:scale-[1.01]">
                <div>
                    <!-- Order Card Header -->
                    <div class="flex items-center justify-between border-b border-stone-800 pb-3 mb-3">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-xl text-xs font-black {{ $index === 0 ? 'bg-amber-500 text-stone-950' : 'bg-stone-800 text-stone-300' }}">
                                Antrean #{{ $index + 1 }}
                            </span>
                            <span class="font-mono font-bold text-sm text-white">{{ $ord->order_number }}</span>
                        </div>
                        <span class="px-2.5 py-1 rounded-xl text-xs font-black {{ $isUrgent ? 'bg-red-500 text-white animate-pulse' : 'bg-stone-800 text-amber-400' }}">
                            <i class="fa-regular fa-clock mr-1"></i> {{ $minutesElapsed }} Menit Lalu
                        </span>
                    </div>

                    <!-- Customer & Table Target -->
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <span class="text-[10px] text-stone-500 uppercase font-bold block">Pelanggan:</span>
                            <span class="text-sm font-extrabold text-white">{{ $ord->customer_name }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-stone-500 uppercase font-bold block">Lokasi:</span>
                            <span class="px-3 py-1 rounded-xl text-xs font-black bg-emerald-500/20 border border-emerald-500/40 text-emerald-300">
                                {{ $ord->table_number ?? 'Take Away' }}
                            </span>
                        </div>
                    </div>

                    @if($ord->notes)
                    <div class="mb-3 p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-xs text-amber-300">
                        <i class="fa-solid fa-note-sticky mr-1"></i> <strong>Catatan:</strong> {{ $ord->notes }}
                    </div>
                    @endif

                    <!-- Items & Recipe List -->
                    <div class="space-y-3 mb-4">
                        <div class="text-[10px] uppercase font-bold text-stone-400 tracking-wider">Item Menu Yang Disiapkan:</div>
                        @foreach($ord->items as $item)
                        <div class="p-3 rounded-2xl bg-stone-800/80 border border-stone-700/80">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-sm text-white">
                                    <span class="text-amber-400 text-base font-black mr-1">{{ $item->quantity }}x</span>
                                    {{ $item->menu_name }}
                                </span>
                            </div>
                            @if($item->notes)
                            <div class="text-xs text-amber-300 italic mt-0.5 ml-5">
                                "{{ $item->notes }}"
                            </div>
                            @endif

                            <!-- Recipe / Ingredient guidance for Barista -->
                            @if($item->menu && $item->menu->recipes->isNotEmpty())
                            <div class="mt-2 pt-2 border-t border-stone-700/50 flex flex-wrap gap-1.5 ml-5">
                                @foreach($item->menu->recipes as $r)
                                <span class="px-2 py-0.5 rounded-md bg-stone-900 text-[10px] text-stone-400 font-mono">
                                    {{ $r->ingredient?->name }}: <strong class="text-amber-300">{{ number_format($r->amount * $item->quantity, 2) }} {{ $r->ingredient?->unit }}</strong>
                                </span>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Done Button -->
                <div class="pt-3 border-t border-stone-800">
                    <form action="{{ route('dapur.orders.done', $ord) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-3 px-4 rounded-2xl bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-stone-950 font-black text-sm uppercase tracking-wider shadow-lg shadow-emerald-600/30 transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-circle-check text-base"></i> Selesai (Notifikasi ke Kasir)
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

@push('scripts')
<script>
function kitchenKDS() {
    return {
        init() {
            // Register global hook for auto-refresh when notification arrives
            window.refreshKitchenKDS = () => {
                window.location.reload();
            };

            // Auto-refresh fallback every 10 seconds
            setInterval(() => {
                window.location.reload();
            }, 10000);
        },

        fetchActiveOrders() {
            window.location.reload();
        }
    }
}
</script>
@endpush
@endsection
