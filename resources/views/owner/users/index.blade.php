@extends('layouts.app')

@section('title', 'Manajemen Pengguna - Owner')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 glass-panel p-6 rounded-3xl">
        <div>
            <div class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-1">
                <i class="fa-solid fa-users-gear mr-1"></i> User Management
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">
                Kelola Pengguna Sistem
            </h1>
            <p class="text-stone-400 text-xs sm:text-sm mt-1">
                Tambah pengguna baru, ubah hak akses (role), lakukan banned akun, atau hapus akun pengguna.
            </p>
        </div>
        <a href="{{ route('owner.users.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-stone-950 font-bold text-xs shadow-lg shadow-amber-500/20 transition-all">
            <i class="fa-solid fa-user-plus"></i> Tambah Pengguna Baru
        </a>
    </div>

    <!-- Users Table Card -->
    <div class="bg-stone-900/90 border border-stone-800 rounded-3xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-stone-300">
                <thead class="bg-stone-800/80 text-[11px] uppercase tracking-wider text-stone-400 font-bold border-b border-stone-700/80">
                    <tr>
                        <th class="py-3.5 px-4 sm:px-6">Nama Pengguna</th>
                        <th class="py-3.5 px-4">Email</th>
                        <th class="py-3.5 px-4">Role Akses</th>
                        <th class="py-3.5 px-4">No. Telepon</th>
                        <th class="py-3.5 px-4">Status Akun</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-800/80">
                    @forelse($users as $u)
                    <tr class="hover:bg-stone-800/40 transition-colors {{ $u->is_banned ? 'opacity-60 bg-red-950/10' : '' }}">
                        <td class="py-4 px-4 sm:px-6">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-stone-800 border border-stone-700 flex items-center justify-center font-bold text-amber-400 text-sm">
                                    {{ substr($u->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-bold text-white text-sm">{{ $u->name }}</div>
                                    <div class="text-[11px] text-stone-500">Terdaftar {{ $u->created_at->format('d M Y') }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-4 font-mono text-xs text-stone-300">
                            {{ $u->email }}
                        </td>
                        <td class="py-4 px-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                                {{ $u->role === 'owner' ? 'bg-purple-900/60 text-purple-300 border border-purple-700/50' : '' }}
                                {{ $u->role === 'kasir' ? 'bg-emerald-900/60 text-emerald-300 border border-emerald-700/50' : '' }}
                                {{ $u->role === 'dapur' ? 'bg-amber-900/60 text-amber-300 border border-amber-700/50' : '' }}
                                {{ $u->role === 'gudang' ? 'bg-blue-900/60 text-blue-300 border border-blue-700/50' : '' }}
                                {{ $u->role === 'pengadaan' ? 'bg-cyan-900/60 text-cyan-300 border border-cyan-700/50' : '' }}
                                {{ $u->role === 'customer' ? 'bg-stone-800 text-stone-300 border border-stone-700' : '' }}
                            ">
                                {{ $u->role }}
                            </span>
                        </td>
                        <td class="py-4 px-4 text-xs text-stone-400">
                            {{ $u->phone ?? '-' }}
                        </td>
                        <td class="py-4 px-4">
                            @if($u->is_banned)
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-red-900/60 text-red-300 border border-red-700/50">
                                    <i class="fa-solid fa-ban mr-1"></i> BANNED
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-900/60 text-emerald-300 border border-emerald-700/50">
                                    <i class="fa-solid fa-check mr-1"></i> AKTIF
                                </span>
                            @endif
                        </td>
                        <td class="py-4 px-4 sm:px-6 text-right space-x-2">
                            <!-- Toggle Banned -->
                            @if($u->id !== auth()->id())
                            <form action="{{ route('owner.users.toggle_ban', $u) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="p-2 rounded-lg {{ $u->is_banned ? 'bg-emerald-900/40 text-emerald-400 hover:bg-emerald-800/60' : 'bg-amber-900/40 text-amber-400 hover:bg-amber-800/60' }} text-xs font-semibold" title="{{ $u->is_banned ? 'Aktifkan Akun' : 'Ban Akun' }}">
                                    <i class="fa-solid {{ $u->is_banned ? 'fa-unlock' : 'fa-ban' }}"></i>
                                </button>
                            </form>
                            @endif

                            <!-- Edit -->
                            <a href="{{ route('owner.users.edit', $u) }}" class="p-2 rounded-lg bg-stone-800 hover:bg-stone-700 text-stone-300 hover:text-white text-xs font-semibold inline-block" title="Edit Pengguna">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>

                            <!-- Delete -->
                            @if($u->id !== auth()->id())
                            <form action="{{ route('owner.users.destroy', $u) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini? Tindakan tidak dapat dibatalkan.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 rounded-lg bg-red-950/50 hover:bg-red-900 text-red-400 text-xs font-semibold" title="Hapus Pengguna">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-stone-500 text-xs">Belum ada data pengguna.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="p-4 border-t border-stone-800">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
