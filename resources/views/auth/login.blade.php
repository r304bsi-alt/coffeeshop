@extends('layouts.app')

@section('title', 'Masuk ke Sistem - Kopi Senja')

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:py-12">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
        
        <!-- Left: Brand Showcase & Role Simulator Cards -->
        <div class="lg:col-span-6 space-y-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs font-semibold uppercase tracking-wider mb-3">
                    <i class="fa-solid fa-mug-saucer"></i> Sistem Coffee Shop Terpadu
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight">
                    Kelola Kedai Kopi Anda dengan Presisi & Cepat
                </h1>
                <p class="text-stone-400 text-sm mt-3 leading-relaxed">
                    Sistem all-in-one yang menghubungkan Meja Pelanggan, Kasir Toko, Dapur Barista, Gudang Stok, Pengadaan, dan Laporan Pemilik dalam satu platform terintegrasi.
                </p>
            </div>

            <!-- Fast Role Login Buttons for Development Testing -->
            <div class="bg-stone-900/90 border border-stone-800 rounded-2xl p-5 shadow-xl">
                <div class="flex items-center justify-between mb-3 border-b border-stone-800 pb-2">
                    <span class="text-xs font-bold text-amber-400 uppercase tracking-wider">
                        <i class="fa-solid fa-bolt mr-1"></i> Quick Login (Akses Langsung)
                    </span>
                    <span class="text-[10px] text-stone-500">Pilih role untuk uji coba:</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                    <a href="{{ route('quick.login', 'owner') }}" class="p-2.5 rounded-xl bg-purple-950/40 hover:bg-purple-900/60 border border-purple-800/40 text-left transition-all hover:scale-[1.02] group">
                        <div class="text-xs font-bold text-purple-300 group-hover:text-purple-200"><i class="fa-solid fa-crown mr-1"></i> Owner</div>
                        <div class="text-[10px] text-stone-400 mt-0.5">Laporan & User</div>
                    </a>

                    <a href="{{ route('quick.login', 'kasir') }}" class="p-2.5 rounded-xl bg-emerald-950/40 hover:bg-emerald-900/60 border border-emerald-800/40 text-left transition-all hover:scale-[1.02] group">
                        <div class="text-xs font-bold text-emerald-300 group-hover:text-emerald-200"><i class="fa-solid fa-cash-register mr-1"></i> Kasir</div>
                        <div class="text-[10px] text-stone-400 mt-0.5">POS & Menu BOM</div>
                    </a>

                    <a href="{{ route('quick.login', 'dapur') }}" class="p-2.5 rounded-xl bg-amber-950/40 hover:bg-amber-900/60 border border-amber-800/40 text-left transition-all hover:scale-[1.02] group">
                        <div class="text-xs font-bold text-amber-300 group-hover:text-amber-200"><i class="fa-solid fa-fire mr-1"></i> Dapur</div>
                        <div class="text-[10px] text-stone-400 mt-0.5">KDS Antrean FIFO</div>
                    </a>

                    <a href="{{ route('quick.login', 'gudang') }}" class="p-2.5 rounded-xl bg-blue-950/40 hover:bg-blue-900/60 border border-blue-800/40 text-left transition-all hover:scale-[1.02] group">
                        <div class="text-xs font-bold text-blue-300 group-hover:text-blue-200"><i class="fa-solid fa-warehouse mr-1"></i> Gudang</div>
                        <div class="text-[10px] text-stone-400 mt-0.5">Masuk & Keluar Stok</div>
                    </a>

                    <a href="{{ route('quick.login', 'pengadaan') }}" class="p-2.5 rounded-xl bg-cyan-950/40 hover:bg-cyan-900/60 border border-cyan-800/40 text-left transition-all hover:scale-[1.02] group">
                        <div class="text-xs font-bold text-cyan-300 group-hover:text-cyan-200"><i class="fa-solid fa-file-invoice-dollar mr-1"></i> Pengadaan</div>
                        <div class="text-[10px] text-stone-400 mt-0.5">Cek Stok & PO</div>
                    </a>

                    <a href="{{ route('customer.menu') }}" class="p-2.5 rounded-xl bg-stone-800 hover:bg-stone-700 border border-stone-700 text-left transition-all hover:scale-[1.02] group">
                        <div class="text-xs font-bold text-amber-400 group-hover:text-amber-300"><i class="fa-solid fa-qrcode mr-1"></i> Customer</div>
                        <div class="text-[10px] text-stone-400 mt-0.5">Order Scan Meja</div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Right: Login Form & Google Sign-In -->
        <div class="lg:col-span-6">
            <div class="bg-stone-900/90 border border-stone-800 rounded-3xl p-6 sm:p-8 shadow-2xl relative overflow-hidden">
                <div class="absolute -top-12 -right-12 w-40 h-40 bg-amber-500/10 rounded-full blur-2xl"></div>

                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-white tracking-tight">Masuk Akun</h2>
                    <p class="text-xs text-stone-400 mt-1">Gunakan akun Anda atau login dengan Google untuk pelanggan.</p>
                </div>

                <!-- Google Sign-In Button for Customer Requirement -->
                <form action="{{ route('auth.google') }}" method="POST" class="mb-6">
                    @csrf
                    <input type="hidden" name="name" value="Customer Google">
                    <input type="hidden" name="email" value="customer.google@coffeeshop.test">
                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-white hover:bg-stone-100 text-stone-800 font-semibold text-xs sm:text-sm flex items-center justify-center gap-3 shadow-md hover:shadow-lg transition-all border border-stone-200">
                        <svg class="w-4 h-4" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                        </svg>
                        <span>Masuk Menggunakan Google Sign-In</span>
                    </button>
                </form>

                <div class="relative flex items-center justify-center my-6">
                    <div class="border-t border-stone-800 w-full"></div>
                    <span class="bg-stone-900 px-3 text-[11px] text-stone-500 uppercase tracking-wider absolute">atau gunakan email & password</span>
                </div>

                <!-- Email/Password Form -->
                <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-stone-300 mb-1.5">Alamat Email</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-stone-500 text-sm">
                                <i class="fa-regular fa-envelope"></i>
                            </span>
                            <input type="email" name="email" value="{{ old('email', 'owner@coffeeshop.test') }}" required class="w-full pl-10 pr-4 py-2.5 bg-stone-800/80 border border-stone-700/80 rounded-xl text-white text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all" placeholder="nama@email.com">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-300 mb-1.5">Kata Sandi</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-stone-500 text-sm">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input type="password" name="password" value="password123" required class="w-full pl-10 pr-4 py-2.5 bg-stone-800/80 border border-stone-700/80 rounded-xl text-white text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all" placeholder="••••••••">
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs text-stone-400">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded bg-stone-800 border-stone-700 text-amber-500 focus:ring-amber-500">
                            <span>Ingat saya di perangkat ini</span>
                        </label>
                        <span class="text-[11px] text-stone-500">Demo Password: <code class="text-amber-400">password123</code></span>
                    </div>

                    <button type="submit" class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-stone-950 font-bold text-sm shadow-lg shadow-amber-600/20 hover:shadow-amber-600/40 transition-all">
                        Masuk ke Dashboard
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
