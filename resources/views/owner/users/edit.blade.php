@extends('layouts.app')

@section('title', 'Edit Pengguna - Owner')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Edit Data Pengguna</h1>
            <p class="text-stone-400 text-xs mt-1">Ubah identitas, role akses, status banned, atau reset kata sandi pengguna.</p>
        </div>
        <a href="{{ route('owner.users.index') }}" class="px-3 py-1.5 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-300 text-xs font-semibold">
            &larr; Kembali
        </a>
    </div>

    <div class="bg-stone-900/90 border border-stone-800 rounded-3xl p-6 sm:p-8 shadow-2xl">
        <form action="{{ route('owner.users.update', $user) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold text-stone-300 uppercase tracking-wider mb-2">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-4 py-2.5 bg-stone-800/80 border border-stone-700/80 rounded-xl text-white text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-stone-300 uppercase tracking-wider mb-2">Alamat Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-4 py-2.5 bg-stone-800/80 border border-stone-700/80 rounded-xl text-white text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-300 uppercase tracking-wider mb-2">Nomor Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full px-4 py-2.5 bg-stone-800/80 border border-stone-700/80 rounded-xl text-white text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-stone-300 uppercase tracking-wider mb-2">Role Hak Akses</label>
                    <select name="role" required class="w-full px-4 py-2.5 bg-stone-800/80 border border-stone-700/80 rounded-xl text-white text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                        <option value="owner" {{ old('role', $user->role) === 'owner' ? 'selected' : '' }}>Owner (Pemilik Toko)</option>
                        <option value="kasir" {{ old('role', $user->role) === 'kasir' ? 'selected' : '' }}>Bagian Kasir (Toko)</option>
                        <option value="dapur" {{ old('role', $user->role) === 'dapur' ? 'selected' : '' }}>Bagian Dapur (Barista / Kitchen)</option>
                        <option value="gudang" {{ old('role', $user->role) === 'gudang' ? 'selected' : '' }}>Bagian Gudang (Warehouse)</option>
                        <option value="pengadaan" {{ old('role', $user->role) === 'pengadaan' ? 'selected' : '' }}>Bagian Pengadaan (Procurement)</option>
                        <option value="customer" {{ old('role', $user->role) === 'customer' ? 'selected' : '' }}>Customer (Pelanggan)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-300 uppercase tracking-wider mb-2">Kata Sandi Baru (Opsional)</label>
                    <input type="password" name="password" class="w-full px-4 py-2.5 bg-stone-800/80 border border-stone-700/80 rounded-xl text-white text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500" placeholder="Kosongkan jika tidak diubah">
                </div>
            </div>

            <div class="p-4 rounded-xl bg-stone-800/60 border border-stone-700/60">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_banned" value="1" {{ old('is_banned', $user->is_banned) ? 'checked' : '' }} class="w-4 h-4 rounded bg-stone-900 border-stone-700 text-red-600 focus:ring-red-500">
                    <div>
                        <span class="text-xs font-bold text-white block">Status Banned Akun</span>
                        <span class="text-[11px] text-stone-400">Jika dicentang, pengguna tidak akan bisa login ke dalam sistem.</span>
                    </div>
                </label>
            </div>

            <div class="pt-4 border-t border-stone-800 flex items-center justify-end gap-3">
                <a href="{{ route('owner.users.index') }}" class="px-4 py-2 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-300 text-xs font-semibold">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-stone-950 font-bold text-xs shadow-lg shadow-amber-500/20 transition-all">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
