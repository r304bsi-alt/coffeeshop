<!DOCTYPE html>
<html lang="id" class="h-full bg-stone-900 text-stone-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kopi Senja') - Coffee Shop POS & Management System</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Tailwind CSS with custom theme config -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        coffee: {
                            50: '#fbf8f5',
                            100: '#f5efe9',
                            200: '#ebdec5',
                            300: '#dbc4a1',
                            400: '#c6a279',
                            500: '#b08456',
                            600: '#946644',
                            700: '#774f38',
                            800: '#5c3d2e',
                            900: '#3c271f',
                            950: '#221510',
                        },
                        amber: {
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #1c1917; }
        ::-webkit-scrollbar-thumb { background: #44403c; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #78716c; }
        .glass-panel {
            background: rgba(41, 37, 36, 0.75);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(214, 180, 142, 0.15);
        }
    </style>
    @stack('styles')
</head>
<body class="h-full flex flex-col font-sans antialiased bg-[#131110] text-stone-200" x-data="notificationSystem('{{ auth()->check() ? auth()->user()->role : 'guest' }}')">

    <!-- Top Navigation Bar -->
    <nav class="sticky top-0 z-40 bg-[#1c1917]/90 backdrop-blur-md border-b border-stone-800/80 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand Logo & Title -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-600 to-coffee-400 flex items-center justify-center shadow-lg shadow-amber-600/20 group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-mug-hot text-stone-900 text-lg"></i>
                        </div>
                        <div>
                            <span class="font-extrabold text-lg tracking-tight text-white group-hover:text-amber-400 transition-colors">KOPI SENJA</span>
                            <span class="block text-[10px] uppercase tracking-wider text-amber-400/80 font-semibold">Management & POS</span>
                        </div>
                    </a>

                    <!-- Current Role Badge -->
                    @auth
                    <div class="hidden md:flex items-center ml-4 px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wider 
                        {{ auth()->user()->role === 'owner' ? 'bg-purple-900/60 text-purple-300 border border-purple-700/50' : '' }}
                        {{ auth()->user()->role === 'kasir' ? 'bg-emerald-900/60 text-emerald-300 border border-emerald-700/50' : '' }}
                        {{ auth()->user()->role === 'dapur' ? 'bg-amber-900/60 text-amber-300 border border-amber-700/50' : '' }}
                        {{ auth()->user()->role === 'gudang' ? 'bg-blue-900/60 text-blue-300 border border-blue-700/50' : '' }}
                        {{ auth()->user()->role === 'pengadaan' ? 'bg-cyan-900/60 text-cyan-300 border border-cyan-700/50' : '' }}
                        {{ auth()->user()->role === 'customer' ? 'bg-stone-800 text-stone-300 border border-stone-700' : '' }}
                    ">
                        <i class="fa-solid fa-id-badge mr-1.5 opacity-80"></i>
                        Role: {{ auth()->user()->role }}
                    </div>
                    @endauth
                </div>

                <!-- Center Navigation Links based on Role -->
                @auth
                <div class="hidden lg:flex items-center space-x-1">
                    @if(auth()->user()->isOwner())
                        <a href="{{ route('owner.dashboard') }}" class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('owner.dashboard') ? 'bg-amber-500/20 text-amber-400' : 'text-stone-300 hover:text-white hover:bg-stone-800' }}">Dashboard</a>
                        <a href="{{ route('owner.users.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('owner.users*') ? 'bg-amber-500/20 text-amber-400' : 'text-stone-300 hover:text-white hover:bg-stone-800' }}">Kelola User</a>
                        <a href="{{ route('owner.procurements.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('owner.procurements*') ? 'bg-amber-500/20 text-amber-400' : 'text-stone-300 hover:text-white hover:bg-stone-800' }}">Approval PO</a>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="px-3 py-2 rounded-lg text-sm font-medium text-stone-300 hover:text-white hover:bg-stone-800 flex items-center gap-1">
                                Laporan <i class="fa-solid fa-chevron-down text-xs ml-1"></i>
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak class="absolute left-0 mt-2 w-48 rounded-xl bg-stone-900 border border-stone-800 shadow-2xl py-1 z-50">
                                <a href="{{ route('owner.reports.sales') }}" class="block px-4 py-2 text-sm text-stone-300 hover:bg-stone-800 hover:text-amber-400"><i class="fa-solid fa-chart-line mr-2"></i> Laporan Penjualan</a>
                                <a href="{{ route('owner.reports.stock') }}" class="block px-4 py-2 text-sm text-stone-300 hover:bg-stone-800 hover:text-amber-400"><i class="fa-solid fa-boxes-stacked mr-2"></i> Laporan Stok Barang</a>
                                <a href="{{ route('owner.reports.procurement') }}" class="block px-4 py-2 text-sm text-stone-300 hover:bg-stone-800 hover:text-amber-400"><i class="fa-solid fa-truck-ramp-box mr-2"></i> Laporan Pengadaan</a>
                                <a href="{{ route('owner.reports.profit_loss') }}" class="block px-4 py-2 text-sm text-stone-300 hover:bg-stone-800 hover:text-amber-400"><i class="fa-solid fa-scale-balanced mr-2"></i> Laporan Laba Rugi</a>
                            </div>
                        </div>
                    @elseif(auth()->user()->isGudang())
                        <a href="{{ route('gudang.dashboard') }}" class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('gudang.dashboard') ? 'bg-amber-500/20 text-amber-400' : 'text-stone-300 hover:text-white hover:bg-stone-800' }}">Dashboard</a>
                        <a href="{{ route('gudang.inbound.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('gudang.inbound*') ? 'bg-amber-500/20 text-amber-400' : 'text-stone-300 hover:text-white hover:bg-stone-800' }}">Barang Masuk (Supplier)</a>
                        <a href="{{ route('gudang.transfers.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('gudang.transfers*') ? 'bg-amber-500/20 text-amber-400' : 'text-stone-300 hover:text-white hover:bg-stone-800' }}">Barang Keluar (Toko)</a>
                        <a href="{{ route('gudang.stocks.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('gudang.stocks*') ? 'bg-amber-500/20 text-amber-400' : 'text-stone-300 hover:text-white hover:bg-stone-800' }}">Stok Gudang</a>
                    @elseif(auth()->user()->isPengadaan())
                        <a href="{{ route('pengadaan.dashboard') }}" class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('pengadaan.dashboard') ? 'bg-amber-500/20 text-amber-400' : 'text-stone-300 hover:text-white hover:bg-stone-800' }}">Dashboard</a>
                        <a href="{{ route('pengadaan.stock.check') }}" class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('pengadaan.stock.check') ? 'bg-amber-500/20 text-amber-400' : 'text-stone-300 hover:text-white hover:bg-stone-800' }}">Cek Stok</a>
                        <a href="{{ route('pengadaan.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('pengadaan.index*') ? 'bg-amber-500/20 text-amber-400' : 'text-stone-300 hover:text-white hover:bg-stone-800' }}">Daftar Pengadaan (PO)</a>
                        <a href="{{ route('pengadaan.create') }}" class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('pengadaan.create') ? 'bg-amber-500/20 text-amber-400' : 'text-stone-300 hover:text-white hover:bg-stone-800' }}">Buat Pengadaan</a>
                    @elseif(auth()->user()->isKasir())
                        <a href="{{ route('kasir.dashboard') }}" class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('kasir.dashboard') ? 'bg-amber-500/20 text-amber-400' : 'text-stone-300 hover:text-white hover:bg-stone-800' }}">Dashboard</a>
                        <a href="{{ route('kasir.pos') }}" class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('kasir.pos*') ? 'bg-emerald-500/20 text-emerald-400' : 'text-stone-300 hover:text-white hover:bg-stone-800' }}"><i class="fa-solid fa-cash-register mr-1"></i> POS Kasir</a>
                        <a href="{{ route('kasir.payment.scan') }}" class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('kasir.payment.scan*') ? 'bg-amber-500/20 text-amber-400' : 'text-stone-300 hover:text-white hover:bg-stone-800' }}"><i class="fa-solid fa-qrcode mr-1"></i> Scan Bayar Tunai</a>
                        <a href="{{ route('kasir.menus.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('kasir.menus*') ? 'bg-amber-500/20 text-amber-400' : 'text-stone-300 hover:text-white hover:bg-stone-800' }}">Menu & Resep (BOM)</a>
                        <a href="{{ route('kasir.transfers.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('kasir.transfers*') ? 'bg-amber-500/20 text-amber-400' : 'text-stone-300 hover:text-white hover:bg-stone-800' }}">Terima Stok Gudang</a>
                    @elseif(auth()->user()->isDapur())
                        <a href="{{ route('dapur.dashboard') }}" class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('dapur.dashboard') ? 'bg-amber-500/20 text-amber-400' : 'text-stone-300 hover:text-white hover:bg-stone-800' }} flex items-center gap-1.5">
                            <i class="fa-solid fa-fire text-amber-500"></i> KDS Antrean Dapur (FIFO)
                        </a>
                    @endif
                </div>
                @endauth

                <!-- Right Actions: Quick Role Switcher, Notifications & User Profile -->
                <div class="flex items-center gap-3">
                    
                    <!-- Quick Role Switcher Menu (Essential for Testing Pair Programming) -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" title="Ganti Role Cepat untuk Testing" class="px-2.5 py-1.5 rounded-lg bg-stone-800/90 hover:bg-stone-700 text-stone-300 text-xs font-medium border border-stone-700/80 flex items-center gap-1.5 transition-colors">
                            <i class="fa-solid fa-users-gear text-amber-400"></i>
                            <span class="hidden sm:inline">Ganti Role</span>
                            <i class="fa-solid fa-caret-down text-[10px] opacity-60"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-56 rounded-xl bg-stone-900 border border-stone-800 shadow-2xl p-2 z-50">
                            <div class="text-[11px] font-semibold text-stone-400 uppercase tracking-wider px-2 py-1 border-b border-stone-800 mb-1">
                                Simulasi Login Role
                            </div>
                            <a href="{{ route('quick.login', 'owner') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs hover:bg-purple-950/50 text-purple-300">
                                <i class="fa-solid fa-crown w-4"></i> 1. Owner (Pemilik)
                            </a>
                            <a href="{{ route('quick.login', 'kasir') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs hover:bg-emerald-950/50 text-emerald-300">
                                <i class="fa-solid fa-cash-register w-4"></i> 2. Kasir (Toko)
                            </a>
                            <a href="{{ route('quick.login', 'dapur') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs hover:bg-amber-950/50 text-amber-300">
                                <i class="fa-solid fa-fire w-4"></i> 3. Dapur (Barista)
                            </a>
                            <a href="{{ route('quick.login', 'gudang') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs hover:bg-blue-950/50 text-blue-300">
                                <i class="fa-solid fa-warehouse w-4"></i> 4. Bagian Gudang
                            </a>
                            <a href="{{ route('quick.login', 'pengadaan') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs hover:bg-cyan-950/50 text-cyan-300">
                                <i class="fa-solid fa-file-invoice-dollar w-4"></i> 5. Bagian Pengadaan
                            </a>
                            <a href="{{ route('customer.menu') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs hover:bg-stone-800 text-stone-300">
                                <i class="fa-solid fa-qrcode w-4"></i> 6. Customer (Scan QR)
                            </a>
                        </div>
                    </div>

                    <!-- Customer QR Menu Shortcut -->
                    <a href="{{ route('customer.menu') }}" target="_blank" title="Buka Halaman Customer (Scan Meja)" class="p-2 rounded-lg bg-stone-800/80 hover:bg-stone-700 text-amber-400 text-sm border border-stone-700/80 transition-colors">
                        <i class="fa-solid fa-mobile-screen"></i>
                    </a>

                    <!-- Realtime Notification Bell (WebSocket / Live Polling) -->
                    @auth
                    <div class="relative" x-data="{ notifOpen: false }">
                        <button @click="notifOpen = !notifOpen; markAllRead()" class="relative p-2 rounded-lg bg-stone-800/80 hover:bg-stone-700 text-stone-300 hover:text-white transition-colors">
                            <i class="fa-solid fa-bell"></i>
                            <span x-show="unreadCount > 0" x-text="unreadCount" class="absolute -top-1 -right-1 px-1.5 py-0.2 rounded-full text-[10px] font-extrabold bg-red-600 text-white animate-pulse"></span>
                        </button>

                        <!-- Notification Dropdown -->
                        <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak class="absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl bg-stone-900 border border-stone-800 shadow-2xl z-50 overflow-hidden">
                            <div class="p-3.5 bg-stone-800/80 border-b border-stone-700/60 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-bell text-amber-400 text-sm"></i>
                                    <span class="text-sm font-bold text-white">Notifikasi Real-time</span>
                                </div>
                                <span class="text-[11px] text-stone-400" x-text="notifications.length + ' pesan'"></span>
                            </div>
                            <div class="max-h-80 overflow-y-auto divide-y divide-stone-800/80">
                                <template x-for="n in notifications" :key="n.id">
                                    <div class="p-3 hover:bg-stone-800/50 transition-colors">
                                        <div class="flex items-start gap-2.5">
                                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                                                :class="n.target_role === 'dapur' ? 'bg-amber-500/20 text-amber-400' : 'bg-emerald-500/20 text-emerald-400'">
                                                <i class="fa-solid" :class="n.target_role === 'dapur' ? 'fa-fire' : 'fa-bullhorn'"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-xs font-bold text-white truncate" x-text="n.title"></div>
                                                <div class="text-[11px] text-stone-300 mt-0.5 leading-snug" x-text="n.message"></div>
                                                <div class="text-[9px] text-stone-500 mt-1" x-text="new Date(n.created_at).toLocaleTimeString('id-ID')"></div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <div x-show="notifications.length === 0" class="p-6 text-center text-xs text-stone-500">
                                    Belum ada notifikasi baru.
                                </div>
                            </div>
                        </div>
                    </div>
                    @endauth

                    <!-- User Profile & Logout -->
                    @auth
                    <div class="flex items-center gap-2 pl-2 border-l border-stone-800">
                        <div class="hidden sm:block text-right">
                            <div class="text-xs font-semibold text-white truncate max-w-[120px]">{{ auth()->user()->name }}</div>
                            <div class="text-[10px] text-amber-400 uppercase font-medium">{{ auth()->user()->role }}</div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" title="Keluar / Logout" class="p-2 rounded-lg bg-stone-800/80 hover:bg-red-950/60 text-stone-400 hover:text-red-400 transition-colors">
                                <i class="fa-solid fa-power-off text-sm"></i>
                            </button>
                        </form>
                    </div>
                    @else
                    <a href="{{ route('login') }}" class="px-3.5 py-1.5 rounded-lg bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-stone-950 font-bold text-xs tracking-wide shadow-md transition-all">
                        Masuk
                    </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Global Toast Alert for Real-time Notifications -->
    <div x-show="toast.show" x-transition x-cloak class="fixed bottom-6 right-6 z-50 max-w-md w-full bg-stone-900 border-2 border-amber-500/80 rounded-2xl shadow-2xl p-4 flex items-start gap-3 text-stone-100">
        <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center shrink-0 text-lg">
            <i class="fa-solid fa-bell animate-bounce"></i>
        </div>
        <div class="flex-1">
            <h4 class="font-bold text-sm text-white" x-text="toast.title"></h4>
            <p class="text-xs text-stone-300 mt-0.5 leading-relaxed" x-text="toast.message"></p>
        </div>
        <button @click="toast.show = false" class="text-stone-500 hover:text-white p-1">
            <i class="fa-solid fa-xmark text-sm"></i>
        </button>
    </div>

    <!-- Flash Messages -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if(session('success'))
        <div class="rounded-xl bg-emerald-950/80 border border-emerald-600/40 p-3.5 mb-4 flex items-center justify-between text-emerald-200 text-sm shadow-lg">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-circle-check text-emerald-400 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
        </div>
        @endif

        @if(session('error'))
        <div class="rounded-xl bg-red-950/80 border border-red-600/40 p-3.5 mb-4 flex items-center justify-between text-red-200 text-sm shadow-lg">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-circle-exclamation text-red-400 text-base"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
        </div>
        @endif

        @if(session('info'))
        <div class="rounded-xl bg-blue-950/80 border border-blue-600/40 p-3.5 mb-4 flex items-center justify-between text-blue-200 text-sm shadow-lg">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-circle-info text-blue-400 text-base"></i>
                <span>{{ session('info') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-blue-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
        </div>
        @endif

        @if($errors->any())
        <div class="rounded-xl bg-red-950/80 border border-red-600/40 p-3.5 mb-4 text-red-200 text-sm shadow-lg">
            <div class="font-bold flex items-center gap-2 mb-1">
                <i class="fa-solid fa-triangle-exclamation text-red-400"></i>
                Terdapat kesalahan pengisian data:
            </div>
            <ul class="list-disc list-inside text-xs space-y-0.5 ml-2">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    <!-- Main Page Content -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-[#171514] border-t border-stone-800/80 py-6 text-center text-xs text-stone-500">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                <span>Sistem Operasional Kopi Senja &copy; {{ date('Y') }}</span>
            </div>
            <div class="text-[11px] text-stone-400">
                Arsitektur: PHP Laravel &bull; Database: SQLite/MySQL &bull; Real-time WebSocket Ready
            </div>
        </div>
    </footer>

    <!-- Audio Chime Synthesizer & Realtime Notification Client -->
    <script>
        // Web Audio API pure JS chime synthesizer (no external mp3 file required)
        function playChime(type = 'order') {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const ctx = new AudioCtx();
                
                const now = ctx.currentTime;
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();

                osc.connect(gain);
                gain.connect(ctx.destination);

                if (type === 'kitchen_done') {
                    // Two-tone bell for cashier (high cheerful chime)
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(587.33, now); // D5
                    osc.frequency.setValueAtTime(880.00, now + 0.15); // A5
                    gain.gain.setValueAtTime(0.3, now);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.8);
                    osc.start(now);
                    osc.stop(now + 0.8);
                } else {
                    // Warm chime for new kitchen order
                    osc.type = 'triangle';
                    osc.frequency.setValueAtTime(440, now); // A4
                    osc.frequency.setValueAtTime(659.25, now + 0.12); // E5
                    gain.gain.setValueAtTime(0.3, now);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.7);
                    osc.start(now);
                    osc.stop(now + 0.7);
                }
            } catch (e) {
                console.warn('Audio synthesis note:', e);
            }
        }

        // Alpine Notification Management
        function notificationSystem(role) {
            return {
                role: role,
                notifications: [],
                unreadCount: 0,
                lastSeenId: 0,
                toast: { show: false, title: '', message: '' },

                init() {
                    if (this.role !== 'guest') {
                        this.fetchNotifications();
                        // Real-time polling every 3 seconds
                        setInterval(() => this.fetchNotifications(), 3000);
                    }
                },

                fetchNotifications() {
                    const url = `/api/notifications/poll?role=${this.role}&since_id=${this.lastSeenId}`;
                    fetch(url)
                        .then(res => res.json())
                        .then(data => {
                            if (data.notifications && data.notifications.length > 0) {
                                // New notification arrived!
                                const newest = data.notifications[data.notifications.length - 1];
                                
                                // Play chime
                                if (this.lastSeenId > 0) {
                                    playChime(this.role === 'kasir' ? 'kitchen_done' : 'order');
                                    this.showToast(newest.title, newest.message);

                                    // If on Dapur page, reload active orders list dynamically
                                    if (window.refreshKitchenKDS) {
                                        window.refreshKitchenKDS();
                                    }
                                }

                                this.notifications.unshift(...data.notifications);
                                this.unreadCount += data.notifications.length;
                                this.lastSeenId = Math.max(this.lastSeenId, data.latest_id);
                            }
                        })
                        .catch(err => console.debug('Polling note:', err));
                },

                showToast(title, message) {
                    this.toast.title = title;
                    this.toast.message = message;
                    this.toast.show = true;
                    setTimeout(() => { this.toast.show = false; }, 6000);
                },

                markAllRead() {
                    this.unreadCount = 0;
                    fetch('/api/notifications/read-all', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ role: this.role })
                    });
                }
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
