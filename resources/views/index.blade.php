<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Data Pengembalian Buku</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>

<body class="bg-gradient-to-br from-yellow-200 to-yellow-300 min-h-screen flex">

    <!-- SIDEBAR -->
    <aside id="sidebar" class="w-20 bg-[#C34722] text-white flex flex-col items-center py-6 shadow-lg relative">
        <div id="menuWrapper" class="relative flex flex-col items-center space-y-8 flex-1">
            <div id="highlight" class="absolute left-0 w-16 h-12 bg-white/30 rounded-xl transition-all duration-300 shadow-md -z-10" style="top: 0;"></div>
            <button onclick="window.location.href='{{ route('home') }}';" class="menu-item w-12 h-12 flex items-center justify-center text-2xl opacity-80 hover:opacity-100"><i class="fa-solid fa-house"></i></button>
            <button onclick="window.location.href='{{ route('search') }}';" class="menu-item w-12 h-12 flex items-center justify-center text-2xl opacity-80 hover:opacity-100"><i class="fa-solid fa-magnifying-glass"></i></button>
            <button onclick="window.location.href='{{ route('pengembalian.index') }}';" class="menu-item w-12 h-12 flex items-center justify-center text-2xl opacity-80 hover:opacity-100"><i class="fa-solid fa-file-lines"></i></button>
            <button onclick="window.location.href='{{ route('pengaturan') }}';" class="menu-item w-12 h-12 flex items-center justify-center text-2xl opacity-80 hover:opacity-100"><i class="fa-solid fa-gear"></i></button>
            <button onclick="window.location.href='{{ url('/logout') }}'" class="menu-item w-12 h-12 flex items-center justify-center text-2xl opacity-80 hover:opacity-100 mb-4 mt-auto"><i class="fa-solid fa-right-from-bracket"></i></button>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="flex-1 p-10 relative">

        <!-- HEADER -->
        <div class="absolute right-10 top-6 flex items-center gap-4">
            <a href="{{ route('pengembalian.create') }}" class="bg-green-600 text-white px-4 py-2 rounded-xl text-sm flex items-center gap-2 shadow hover:bg-green-700 transition">Pinjam Buku <i class="fas fa-plus text-xs"></i></a>
        </div>

        <!-- TITLE -->
        <h1 class="text-3xl font-bold mt-16">Data Pengembalian Buku</h1>
        <p class="text-sm text-gray-700 mb-6">Hai {{ Auth::user()->nama ?? 'Pengguna' }}, pastikan kamu mengembalikan buku tepat waktu, ya.</p>

        <!-- STATISTIK CARDS -->
        <div class="grid grid-cols-5 gap-6 mt-6">
            <div class="bg-[#B1321B] p-6 rounded-xl text-white shadow-lg text-center">
                <div class="text-4xl font-bold">{{ $stats['total'] ?? 0 }}</div>
                <div class="mt-1">Total</div>
            </div>
            <div class="bg-[#B1321B] p-6 rounded-xl text-white shadow-lg text-center">
                <div class="text-4xl font-bold">{{ $stats['dapat_diambil'] ?? 0 }}</div>
                <div class="mt-1">Dapat Diambil</div>
            </div>
            <div class="bg-[#B1321B] p-6 rounded-xl text-white shadow-lg text-center">
                <div class="text-4xl font-bold">{{ $stats['terlambat'] ?? 0 }}</div>
                <div class="mt-1">Terlambat</div>
            </div>
            <div class="bg-[#B1321B] p-6 rounded-xl text-white shadow-lg text-center">
                <div class="text-4xl font-bold">{{ $stats['sedang_dipinjam'] ?? 0 }}</div>
                <div class="mt-1">Sedang Dipinjam</div>
            </div>
            <div class="bg-[#B1321B] p-6 rounded-xl text-white shadow-lg text-center">
                <div class="text-4xl font-bold">{{ $stats['dikembalikan'] ?? 0 }}</div>
                <div class="mt-1">Telah Dikembalikan</div>
            </div>
        </div>

        <!-- FILTER & SEARCH -->
        <form method="GET" action="{{ route('pengembalian.index') }}" class="flex items-center gap-3 mt-8">
            <input name="search" id="searchInput" type="text" value="{{ request('search') }}" class="w-72 py-2 px-3 rounded-lg border" placeholder="Search by title...">
            <select name="kategori" id="kategoriFilter" class="py-2 px-3 rounded-lg border w-40">
                <option value="">Kategori</option>
                @php
                    $kategoris = \App\Models\Pinjaman::where('pengguna_id', Auth::id())
                        ->with('buku')
                        ->get()
                        ->pluck('buku.genre')
                        ->filter()
                        ->unique()
                        ->values();
                @endphp
                @foreach($kategoris as $kat)
                    <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                @endforeach
            </select>
            <select name="status" id="statusFilter" class="py-2 px-3 rounded-lg border w-40">
                <option value="">Status</option>
                <option value="Menunggu Approval" {{ request('status') == 'Menunggu Approval' ? 'selected' : '' }}>Menunggu Approval</option>
                <option value="Dapat Diambil" {{ request('status') == 'Dapat Diambil' ? 'selected' : '' }}>Dapat Diambil</option>
                <option value="Sedang Dipinjam" {{ request('status') == 'Sedang Dipinjam' ? 'selected' : '' }}>Sedang Dipinjam</option>
                <option value="Terlambat" {{ request('status') == 'Terlambat' ? 'selected' : '' }}>Terlambat</option>
                <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
            </select>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg shadow">Search</button>
            @if(request()->hasAny(['search', 'kategori', 'status']))
                <a href="{{ route('pengembalian.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg shadow">Reset</a>
            @endif
        </form>

        <!-- TABLE -->
        <div class="mt-8 bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm" id="dataTable">
                <thead class="bg-[#B1321B] text-white">
                    <tr>
                        <th class="px-4 py-3 text-left">ID Peminjaman</th>
                        <th class="px-4 py-3 text-left">Judul Buku</th>
                        <th class="px-4 py-3 text-left">Kategori</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Denda</th>
                        <th class="px-4 py-3 text-center">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pinjaman ?? [] as $p)
                        @php
                            $tanggalJatuhTempo = $p->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($p->tanggal_jatuh_tempo) : null;
                            $hariIni = \Carbon\Carbon::now();
                            
                            if ($p->status === 'menunggu_approval') {
                                $statusLabel = 'Menunggu Approval';
                                $statusColor = 'bg-yellow-500';
                                $denda = 0;
                            } elseif ($p->status === 'dapat_diambil') {
                                $statusLabel = 'Dapat Diambil';
                                $statusColor = 'bg-blue-500';
                                $denda = 0;
                            } elseif ($p->status === 'sedang_dipinjam' && $tanggalJatuhTempo) {
                                if ($hariIni->gt($tanggalJatuhTempo)) {
                                    $statusLabel = 'Terlambat';
                                    $statusColor = 'bg-red-500';
                                    $telatHari = $hariIni->diffInDays($tanggalJatuhTempo);
                                    $denda = $telatHari * 5000;
                                } else {
                                    $statusLabel = 'Sedang Dipinjam';
                                    $statusColor = 'bg-orange-500';
                                    $denda = 0;
                                }
                            } elseif ($p->status === 'dikembalikan') {
                                $statusLabel = 'Selesai';
                                $statusColor = 'bg-green-500';
                                $denda = $p->denda ?? 0;
                            } else {
                                $statusLabel = ucfirst(str_replace('_', ' ', $p->status));
                                $statusColor = 'bg-gray-500';
                                $denda = $p->denda ?? 0;
                            }
                        @endphp
                        <tr class="odd:bg-gray-100">
                            <td class="px-4 py-2">P-{{ str_pad($p->id, 6, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-4 py-2">{{ $p->buku->judul ?? 'N/A' }}</td>
                            <td class="px-4 py-2">{{ $p->buku->genre ?? 'N/A' }}</td>
                            <td class="px-4 py-2">
                                @if($p->tanggal_pinjam)
                                    {{ \Carbon\Carbon::parse($p->tanggal_pinjam)->format('d F Y') }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td class="px-4 py-2 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $statusColor }}"></span>
                                {{ $statusLabel }}
                            </td>
                            <td class="px-4 py-2">
                                @if($denda > 0)
                                    Rp {{ number_format($denda, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-2 text-center">
                                <button onclick="openDetail('P-{{ str_pad($p->id, 6, '0', STR_PAD_LEFT) }}','{{ $p->buku->judul ?? 'N/A' }}','{{ $p->buku->genre ?? 'N/A' }}','{{ $p->tanggal_pinjam ? \Carbon\Carbon::parse($p->tanggal_pinjam)->format('d F Y') : 'N/A' }}','{{ $statusLabel }}','{{ $denda }}')" class="text-black hover:scale-110 transition"><i class="fas fa-info-circle"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada data pengembalian buku</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- DETAIL POPUP -->
    <div id="detailPopup" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white w-96 p-6 rounded-xl shadow-lg relative">
            <button onclick="closeDetail()" class="absolute right-4 top-3 text-gray-600 text-lg">✕</button>
            <h2 class="text-xl font-bold mb-4">Detail Peminjaman</h2>
            <div class="space-y-2 text-sm">
                <p><b>ID:</b> <span id="d_id"></span></p>
                <p><b>Judul:</b> <span id="d_judul"></span></p>
                <p><b>Kategori:</b> <span id="d_kategori"></span></p>
                <p><b>Tanggal:</b> <span id="d_tanggal"></span></p>
                <p><b>Status:</b> <span id="d_status"></span></p>
                <p><b>Denda:</b> <span id="d_denda"></span></p>
            </div>
        </div>
    </div>

<script>
function openDetail(id, judul, kategori, tanggal, status, denda){
    document.getElementById('d_id').innerText = id;
    document.getElementById('d_judul').innerText = judul;
    document.getElementById('d_kategori').innerText = kategori;
    document.getElementById('d_tanggal').innerText = tanggal;
    document.getElementById('d_status').innerText = status;
    document.getElementById('d_denda').innerText = denda>0 ? 'Rp '+denda.toLocaleString() : '-';
    document.getElementById('detailPopup').classList.remove('hidden');
}
function closeDetail(){
    document.getElementById('detailPopup').classList.add('hidden');
}

// Filter sudah menggunakan form GET, jadi tidak perlu JavaScript filter lagi
// Form akan otomatis submit dan reload halaman dengan filter yang dipilih
</script>
<script>
// Sidebar highlight movement
const items = document.querySelectorAll(".menu-item");
const highlight = document.getElementById("highlight");
items.forEach((btn, index) => {
    btn.addEventListener("click", () => {
        highlight.style.top = (index * 80) + "px";
    });
});
highlight.style.top = "160px";
</script>

</body>
</html>
