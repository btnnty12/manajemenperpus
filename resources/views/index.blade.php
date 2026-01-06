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
        <div class="grid grid-cols-4 gap-6 mt-6">
            @php
                $stats = [
                    ['20','Total'],
                    ['2','Terlambat'],
                    ['3','Sedang Dipinjam'],
                    ['15','Telah Dikembalikan']
                ];
            @endphp
            @foreach($stats as $c)
                <div class="bg-[#B1321B] p-6 rounded-xl text-white shadow-lg text-center">
                    <div class="text-4xl font-bold">{{ $c[0] }}</div>
                    <div class="mt-1">{{ $c[1] }}</div>
                </div>
            @endforeach
        </div>

        <!-- FILTER & SEARCH -->
        <div class="flex items-center gap-3 mt-8">
            <input id="searchInput" type="text" class="w-72 py-2 px-3 rounded-lg border" placeholder="Search by title...">
            <select id="kategoriFilter" class="py-2 px-3 rounded-lg border w-40">
                <option value="">Kategori</option>
                <option value="Teknologi">Teknologi</option>
                <option value="Informatika">Informatika</option>
                <option value="Psikologi">Psikologi</option>
                <option value="Ekonomi">Ekonomi</option>
                <option value="Manajemen">Manajemen</option>
            </select>
            <select id="statusFilter" class="py-2 px-3 rounded-lg border w-40">
                <option value="">Status</option>
                <option value="Selesai">Selesai</option>
                <option value="Sedang Dipinjam">Sedang Dipinjam</option>
                <option value="Terlambat">Terlambat</option>
            </select>
            <button onclick="applyFilter()" class="bg-blue-600 text-white px-6 py-2 rounded-lg shadow">Search</button>
        </div>

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
                    @php
                        $peminjaman = [
                            ['P-023456','Pemrograman Web dengan ...','Teknologi','15 April 2025','Selesai'],
                            ['P-032467','Algoritma dan Struktur Data','Informatika','19 Oktober 2025','Sedang Dipinjam'],
                            ['P-098754','Psikologi Remaja Modern','Psikologi','3 Mei 2025','Selesai'],
                            ['P-086532','Dasar-Dasar Akuntansi','Ekonomi','17 Mei 2025','Selesai'],
                            ['P-076542','Manajemen Proyek TI','Manajemen','18 Juni 2025','Terlambat'],
                        ];
                        $today = strtotime('8 December 2025');
                    @endphp

                    @foreach($peminjaman as $row)
                        @php
                            $tanggal = strtotime($row[3]);
                            $status = $row[4];
                            $denda = 0;
                            if($status=='Terlambat'){
                                $daysLate = ceil(($today - $tanggal)/86400);
                                $denda = $daysLate*5000;
                            }
                        @endphp
                        <tr class="odd:bg-gray-100">
                            <td class="px-4 py-2">{{ $row[0] }}</td>
                            <td class="px-4 py-2">{{ $row[1] }}</td>
                            <td class="px-4 py-2">{{ $row[2] }}</td>
                            <td class="px-4 py-2">{{ $row[3] }}</td>
                            <td class="px-4 py-2 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $status=='Terlambat'?'bg-red-500':($status=='Sedang Dipinjam'?'bg-orange-500':'bg-green-500') }}"></span>
                                {{ $status }}
                            </td>
                            <td class="px-4 py-2">@if($denda>0) Rp {{ number_format($denda,0,',','.') }} @else - @endif</td>
                            <td class="px-4 py-2 text-center">
                                <button onclick="openDetail('{{ $row[0] }}','{{ $row[1] }}','{{ $row[2] }}','{{ $row[3] }}','{{ $row[4] }}','{{ $denda }}')" class="text-black hover:scale-110 transition"><i class="fas fa-info-circle"></i></button>
                            </td>
                        </tr>
                    @endforeach
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

// FILTER & SEARCH
function applyFilter(){
    const search = document.getElementById('searchInput').value.toLowerCase();
    const kategori = document.getElementById('kategoriFilter').value;
    const status = document.getElementById('statusFilter').value;
    const table = document.getElementById('dataTable');
    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(row=>{
        const title = row.cells[1].innerText.toLowerCase();
        const cat = row.cells[2].innerText;
        const stat = row.cells[4].innerText;
        let show = true;
        if(search && !title.includes(search)) show=false;
        if(kategori && cat!=kategori) show=false;
        if(status && stat!=status) show=false;
        row.style.display = show?'':'none';
    });
}
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
