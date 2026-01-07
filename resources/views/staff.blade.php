@php($title = 'Dashboard Staff')
<x-staff-layout :title="$title">
    <!-- HERO -->
    <section class="px-2 sm:px-4 pt-2 pb-4">
        <div class="rounded-2xl bg-[#9b3d29] text-white p-6 sm:p-8 flex flex-col md:flex-row items-center gap-6 shadow-lg">
            <div class="flex-1">
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold leading-tight">Halo, {{ Auth::user()->nama ?? 'Staff' }}</h1>
                <p class="mt-3 text-white/90">Pantau peminjaman, pengembalian, dan notifikasi pengguna.</p>
                <div class="mt-6 flex gap-3 flex-wrap">
                    <a href="{{ route('staff.laporan-peminjaman') }}" class="bg-white text-amber-700 font-semibold px-5 py-2 rounded-full shadow hover:brightness-95">Lihat Laporan</a>
                    <a href="{{ route('staff.kelola-buku') }}" class="bg-white/20 border border-white text-white font-semibold px-5 py-2 rounded-full hover:bg-white/30">Kelola Buku</a>
                </div>
            </div>
            <div class="flex-1 flex justify-end">
                <x-icon name="book-open" class="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 text-white" />
            </div>
        </div>
    </section>

    <!-- GRID -->
    <div class="px-2 sm:px-4 pb-10 space-y-6">
        <!-- STAT CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
            <div class="p-5 rounded-xl shadow-lg bg-white flex items-center gap-4">
                <div class="w-14 h-14 rounded-lg flex items-center justify-center bg-amber-200">
                    <x-icon name="buku" class="w-8 h-8 text-amber-800" />
                </div>
                <div>
                    <div class="text-sm text-gray-600">Sedang Dipinjam</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $sedangDipinjam ?? 0 }}</div>
                </div>
            </div>
            <div class="p-5 rounded-xl shadow-lg bg-white flex items-center gap-4">
                <div class="w-14 h-14 rounded-lg flex items-center justify-center bg-blue-200">
                    <x-icon name="notification" class="w-8 h-8 text-blue-800" />
                </div>
                <div>
                    <div class="text-sm text-gray-600">Notifikasi Hari Ini</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $notifikasiHariIni ?? 0 }}</div>
                </div>
            </div>
            <div class="p-5 rounded-xl shadow-lg bg-white flex items-center gap-4">
                <div class="w-14 h-14 rounded-lg flex items-center justify-center bg-green-200">
                    <x-icon name="grafik" class="w-8 h-8 text-green-800" />
                </div>
                <div>
                    <div class="text-sm text-gray-600">Pengembalian Minggu Ini</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $pengembalianMingguIni ?? 0 }}</div>
                </div>
            </div>
        </div>

        <!-- TABLE & PROFILE -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">Peminjaman Aktif</h3>
                    <a href="{{ route('staff.laporan-peminjaman') }}" class="text-sm text-blue-600 font-semibold">Lihat semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="px-3 py-2 text-left">User</th>
                                <th class="px-3 py-2 text-left">Judul</th>
                                <th class="px-3 py-2 text-left">Jatuh Tempo</th>
                                <th class="px-3 py-2 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($peminjamanAktif ?? [] as $row)
                            <tr>
                                <td class="px-3 py-2">{{ $row['user'] }}</td>
                                <td class="px-3 py-2">{{ $row['judul'] }}</td>
                                <td class="px-3 py-2">{{ $row['due'] }}</td>
                                <td class="px-3 py-2">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                                        {{ $row['isTerlambat'] ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-3 py-4 text-center text-gray-500">Tidak ada peminjaman aktif</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 space-y-4">
                <h3 class="text-xl font-bold text-gray-800">Profil Staff</h3>
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold text-xl overflow-hidden">
                        @if(Auth::check() && Auth::user()->foto)
                            <img src="{{ asset('storage/' . Auth::user()->foto) }}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.parentElement.innerHTML='{{ strtoupper(substr(Auth::user()->nama ?? 'ST', 0, 2)) }}'">
                        @else
                            {{ strtoupper(substr(Auth::user()->nama ?? 'ST', 0, 2)) }}
                        @endif
                    </div>
                    <div>
                        <div class="text-lg font-semibold text-gray-800">{{ Auth::user()->nama ?? 'Staff' }}</div>
                        <div class="text-sm text-gray-600">{{ Auth::user()->email ?? 'staff@example.com' }}</div>
                        <div class="text-xs text-gray-500 mt-1">Peran: Staff</div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 text-sm text-gray-700">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="text-xs text-gray-500">Peminjaman aktif</div>
                        <div class="text-lg font-bold text-gray-800">{{ $peminjamanAktifStaff ?? 0 }}</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="text-xs text-gray-500">Pengembalian hari ini</div>
                        <div class="text-lg font-bold text-gray-800">{{ $pengembalianHariIni ?? 0 }}</div>
                    </div>
                </div>
                <a href="{{ route('staff.pengaturan') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#a63a2d] text-white rounded-lg text-sm font-semibold hover:brightness-95">
                    <x-icon name="setting" class="w-4 h-4 text-white" /> Edit Profil
                </a>
            </div>
        </div>

        <!-- ACTIVITIES -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-800">Aktivitas Terbaru</h3>
                <a href="{{ route('staff.laporan-peminjaman') }}" class="text-sm text-blue-600 font-semibold">Lihat laporan</a>
            </div>
            <div class="space-y-3">
                @forelse($aktivitasTerbaru ?? [] as $act)
                <div class="flex items-center gap-3 bg-gray-50 rounded-lg p-3">
                    <div class="w-10 h-10 rounded-full bg-amber-200 flex items-center justify-center font-bold text-amber-800">
                        {{ strtoupper(substr($act['user'], 0, 2)) }}
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-semibold text-gray-800">{{ $act['user'] }}</div>
                        <div class="text-sm text-gray-600">{{ $act['aksi'] }} @if($act['judul']) buku <span class="font-semibold">{{ $act['judul'] }}</span>@endif</div>
                    </div>
                    <div class="text-xs text-gray-500">{{ $act['waktu'] }}</div>
                </div>
                @empty
                <div class="text-center text-gray-500 py-4">Tidak ada aktivitas terbaru</div>
                @endforelse
            </div>
        </div>
    </div>
</x-staff-layout>

