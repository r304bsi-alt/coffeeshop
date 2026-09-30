@extends('layouts.app')

@section('title', 'Tambah Pengguna Baru - Owner')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Tambah Pengguna Baru</h1>
            <p class="text-stone-400 text-xs mt-1">Buat akun untuk staf atau pelanggan dengan hak akses tertentu.</p>
        </div>
        <a href="{{ route('owner.users.index') }}" class="px-3 py-1.5 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-300 text-xs font-semibold">
            &larr; Kembali
        </a>
    </div>

    <div class="bg-stone-900/90 border border-stone-800 rounded-3xl p-6 sm:p-8 shadow-2xl">
        <form action="{{ route('owner.users.store') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-bold text-stone-300 uppercase tracking-wider mb-2">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-4 py-2.5 bg-stone-800/80 border border-stone-700/80 rounded-xl text-white text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500" placeholder="Contoh: Siti Barista">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-stone-300 uppercase tracking-wider mb-2">Alamat Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-4 py-2.5 bg-stone-800/80 border border-stone-700/80 rounded-xl text-white text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500" placeholder="nama@coffeeshop.test">
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-300 uppercase tracking-wider mb-2">Nomor Telepon / WA</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="w-full px-4 py-2.5 bg-stone-800/80 border border-stone-700/80 rounded-xl text-white text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500" placeholder="0812xxxxxxx">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-stone-300 uppercase tracking-wider mb-2">Role Hak Akses</label>
                    <select name="role" required class="w-full px-4 py-2.5 bg-stone-800/80 border border-stone-700/80 rounded-xl text-white text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                        <option value="">-- Pilih Role --</option>
                        <option value="owner" {{ old('role') === 'owner' ? 'selected' : '' }}>Owner (Pemilik Toko)</option>
                        <option value="kasir" {{ old('role') === 'kasir' ? 'selected' : '' }}>Bagian Kasir (Toko)</option>
                        <option value="dapur" {{ old('role') === 'dapur' ? 'selected' : '' }}>Bagian Dapur (Barista / Kitchen)</option>
                        <option value="gudang" {{ old('role') === 'gudang' ? 'selected' : '' }}>Bagian Gudang (Warehouse)</option>
                        <option value="pengadaan" {{ old('role') === 'pengadaan' ? 'selected' : '' }}>Bagian Pengadaan (Procurement)</option>
                        <option value="customer" {{ old('role') === 'customer' ? 'selected' : '' }}>Customer (Pelanggan)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-300 uppercase tracking-wider mb-2">Kata Sandi (Password)</label>
                    <input type="password" name="password" required class="w-full px-4 py-2.5 bg-stone-800/80 border border-stone-700/80 rounded-xl text-white text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500" placeholder="Minimal 6 karakter">
                </div>
            </div>

            <div class="pt-4 border-t border-stone-800 flex items-center justify-end gap-3">
                <a href="{{ route('owner.users.index') }}" class="px-4 py-2 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-300 text-xs font-semibold">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-stone-950 font-bold text-xs shadow-lg shadow-amber-500/20 transition-all">
                    Simpan Pengguna
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
