<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kelola Buku</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#f6d47f] flex">

<style>
#indicator {
    position: absolute;
    left: 0;
    top: 310px;
    width: 75px;
    height: 38px;
    background-color: #F7DE68;
    border-radius: 0 20px 20px 0;
    box-shadow: 0 6px 10px rgba(0,0,0,0.35);
    transition: 0.3s ease-in-out;
    z-index: 0;
}

.menu-item {
    position: relative;
    z-index: 5;
}

.menu-item img {
    width: 26px;
    height: 26px;
}

/* --- WRAPPER SETELAH JUDUL --- */
.section-wrapper {
    width: 100%;
    padding-left: 25px;   /* AGAR POSISINYA MENDUPLIKASI DATA ANGGOTA */
    padding-right: 40px;
    margin-top: 20px;
}

/* --- CARDS STATISTIK --- */
.stats-row {
    display: flex;
    justify-content: flex-start;   /* RATA KIRI */
    gap: 25px;
    margin-top: 25px;
    margin-left: 0; /* LURUS DENGAN JUDUL */
}

.stat-card {
    width: 240px;
    height: 120px;
    background: #A24731;
    border-radius: 12px;
    box-shadow: 0 6px 12px rgba(0,0,0,0.2);
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

/* --- TOMBOL TAMBAH BUKU & IMPORT EXCEL --- */
.top-buttons {
    position: absolute;
    right: 40px;
    top: 221px;    /* lebih naik sedikit dari sebelumnya */
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.btn-green {
    background: #24A645;
    padding: 12px 24px;
    color: white;
    font-weight: 600;
    border-radius: 10px;
}

.btn-white {
    background: white;
    padding: 12px 24px;
    color: #333;
    font-weight: 600;
    border-radius: 10px;
    box-shadow: 0 3px 6px rgba(0,0,0,0.15);
}

/* --- FILTER BAR (SEARCH + KATEGORI + STATUS) --- */
.filter-row {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 35px;
}

.filter-search {
    width: 320px;
}

.filter-select {
    width: 180px;
    height: 45px;
    border-radius: 8px;
}

.btn-search {
    background: #2476FF;
    padding: 10px 28px;
    border-radius: 10px;
    color: white;
    font-weight: bold;
    height: 45px;
}

/* --- TABEL --- */
.table-container {
    margin-top: 25px;
    width: 100%;
    padding: 0 40px;
}

</style>

<!-- ============================ SIDEBAR ============================ -->
<div class="w-20 bg-[#a63a2d] min-h-screen flex flex-col items-center py-6 relative">

    <!-- INDIKATOR AKTIF -->
    <div id="indicator"></div>

    <!-- MENU ATAS -->
    <div class="flex flex-col items-center space-y-20 pt-20 w-full">
        <a href="{{ route('admin') }}" class="menu-item" aria-label="Dashboard"><x-icon name="home" class="w-7 h-7 text-white" /></a>
        <a href="{{ route('data.anggota') }}" class="menu-item" aria-label="Data Anggota"><x-icon name="anggota" class="w-7 h-7 text-white" /></a>
        <a href="{{ route('kelola.buku') }}" class="menu-item" aria-label="Kelola Buku"><x-icon name="buku" class="w-7 h-7 text-white" /></a>
        <a href="{{ route('laporan-peminjaman') }}" class="menu-item" aria-label="Laporan Peminjaman"><x-icon name="grafik" class="w-7 h-7 text-white" /></a>
        <a href="{{ route('kelola-user') }}" class="menu-item" aria-label="Kelola User"><x-icon name="user" class="w-7 h-7 text-white" /></a>
    </div>

    <!-- LOGOUT PALING BAWAH -->
    <a href="{{ url('/logout') }}" class="menu-item mt-auto mb-4" aria-label="Logout">
        <x-icon name="logout" class="w-7 h-7 text-white" />
    </a>
</div>

<!-- ============================ CONTENT ============================ -->
 <div class="flex-1 py-6 px-10">

<!-- TOPBAR -->
<div class="flex justify-end items-center w-full py-4 px-10 text-white space-x-6">

    <!-- Divider kiri -->
    <div class="border-l border-white h-6"></div>

    <!-- Icon notif -->
        <button id="notifBtn" onclick="toggleNotifPopup()" class="relative">
            <x-icon name="notification" class="w-6 h-6 text-black hover:opacity-80 cursor-pointer" />
            <span id="notifBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
        </button>

    <!-- Divider kanan -->
    <div class="border-l border-white h-6"></div>

    <!-- Profile -->
    <div class="flex items-center space-x-2">
        <div class="bg-[#717BFF] w-10 h-10 rounded-full flex items-center justify-center text-white font-bold">
            A
        </div>
        <span class="text-black font-medium">Admin</span>
    </div>

</div>

<!-- GARIS PEMBATAS PANJANG -->
<div class="w-full border-b-2 border-white mb-6"></div>

<div class="section-wrapper">

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
            <ul class="list-disc pl-5 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if(session('import_errors') && count(session('import_errors'))>0)
        <div class="bg-amber-50 text-amber-900 p-3 rounded mb-4">
            <div class="font-semibold mb-1 text-sm">Detail kesalahan:</div>
            <ul class="list-disc pl-5 text-xs">
                @foreach(session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h1 class="text-4xl font-bold">Kelola Buku !</h1>
    <p class="text-gray-700 mt-1">Kelola dan perbarui data koleksi buku perpustakaan.</p>

<!-- TOMBOL TAMBAH BUKU & IMPORT EXCEL -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<div class="top-buttons">
    <button class="btn-green" onclick="openTambahBukuModal()">
        <i class="fa-solid fa-plus"></i> Tambah Buku
    </button>

    <button class="btn-white" onclick="openImportExcelModal()">
        <i class="fa-solid fa-file-import"></i> Import Excel
    </button>
</div>

    <!-- 4 KOTAK STATISTIK -->
    <div class="stats-row">
        <div class="stat-card">
            <h2 class="text-4xl font-bold">255</h2>
            <p>Total Buku</p>
        </div>

        <div class="stat-card">
            <h2 class="text-4xl font-bold">224</h2>
            <p>Buku Tersedia</p>
        </div>

        <div class="stat-card">
            <h2 class="text-4xl font-bold">150</h2>
            <p>Sedang Dipinjam</p>
        </div>

        <div class="stat-card">
            <h2 class="text-4xl font-bold">10</h2>
            <p>Buku Baru Bulan Ini</p>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="filter-row">

        <input type="text" id="searchInput" placeholder="Search"
               class="filter-search shadow p-3 rounded-lg" onkeyup="filterTable()">

        <select id="kategoriFilter" class="filter-select shadow" onchange="filterTable()">
            <option value="">Semua Kategori</option>
            <option value="Bahasa">Bahasa</option>
            <option value="Pemrograman">Pemrograman</option>
            <option value="Psikologi">Psikologi</option>
            <option value="Akuntansi">Akuntansi</option>
            <option value="Manajemen">Manajemen</option>
            <option value="Statistik">Statistik</option>
            <option value="AI">AI</option>
            <option value="Budaya">Budaya</option>
        </select>

        <select id="statusFilter" class="filter-select shadow" onchange="filterTable()">
            <option value="">Semua Status</option>
            <option value="Tersedia">Tersedia</option>
            <option value="Tidak Tersedia">Tidak Tersedia</option>
        </select>

        <button class="btn-search" onclick="filterTable()">Search</button>
    </div>

</div>

<!-- ============================ TABLE ============================ -->
<div class="mt-8 bg-white rounded-lg shadow overflow-hidden w-[97%]">

    <table class="w-full text-left">
        <thead class="bg-[#b54a38] text-white">
            <tr>
                <th class="px-6 py-4">ID Buku</th>
                <th class="px-6 py-4">Rak Buku</th>
                <th class="px-6 py-4">Judul Buku</th>
                <th class="px-6 py-4">Kategori</th>
                <th class="px-6 py-4">Stok</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-10 py-5">Opsi</th>
            </tr>
        </thead>

        <tbody id="bukuTableBody">

            <!-- 1 -->
            <tr class="border-b buku-row" data-judul="bahasa inggris untuk akademik" data-kategori="Bahasa" data-status="Tersedia">
                <td class="px-6 py-3">BK-001</td>
                <td class="px-6 py-3">Rak-001</td>
                <td class="px-6 py-3">Bahasa Inggris untuk Akademik</td>
                <td class="px-6 py-3">Bahasa</td>
                <td class="px-6 py-3">8</td>
                <td class="px-6 py-3 text-green-600 font-semibold">Tersedia</td>
                <td class="px-6 py-3 flex gap-2">
                    <button class="p-2 rounded hover:bg-gray-100" title="Detail">
                        <x-icon name="eye" class="w-5 h-5 text-gray-700" />
                    </button>
                    <button class="p-2 rounded hover:bg-gray-100" title="Edit">
                        <x-icon name="edit" class="w-5 h-5 text-gray-700" />
                    </button>
                    <button class="p-2 rounded hover:bg-gray-100" title="Hapus">
                        <x-icon name="delete" class="w-5 h-5 text-gray-700" />
                    </button>
                </td>
            </tr>

            <!-- 2 -->
            <tr class="border-b buku-row" data-judul="algoritma dan struktur data" data-kategori="Pemrograman" data-status="Tidak Tersedia">
                <td class="px-6 py-3">BK-002</td>
                <td class="px-6 py-3">Rak-002</td>
                <td class="px-6 py-3">Algoritma dan Struktur Data</td>
                <td class="px-6 py-3">Pemrograman</td>
                <td class="px-6 py-3">3</td>
                <td class="px-6 py-3 text-red-600 font-semibold">Tidak Tersedia</td>
                <td class="px-6 py-3 flex gap-2">
                    <button class="p-2 rounded hover:bg-gray-100" title="Detail">
                        <x-icon name="eye" class="w-5 h-5 text-gray-700" />
                    </button>
                    <button class="p-2 rounded hover:bg-gray-100" title="Edit">
                        <x-icon name="edit" class="w-5 h-5 text-gray-700" />
                    </button>
                    <button class="p-2 rounded hover:bg-gray-100" title="Hapus">
                        <x-icon name="delete" class="w-5 h-5 text-gray-700" />
                    </button>
                </td>
            </tr>

            <!-- 3 -->
            <tr class="border-b buku-row" data-judul="psikologi remaja modern" data-kategori="Psikologi" data-status="Tersedia">
                <td class="px-6 py-3">BK-003</td>
                <td class="px-6 py-3">Rak-003</td>
                <td class="px-6 py-3">Psikologi Remaja Modern</td>
                <td class="px-6 py-3">Psikologi</td>
                <td class="px-6 py-3">6</td>
                <td class="px-6 py-3 text-green-600 font-semibold">Tersedia</td>
                <td class="px-6 py-3 flex gap-2">
                    <button class="p-2 rounded hover:bg-gray-100" title="Detail">
                        <x-icon name="eye" class="w-5 h-5 text-gray-700" />
                    </button>
                    <button class="p-2 rounded hover:bg-gray-100" title="Edit">
                        <x-icon name="edit" class="w-5 h-5 text-gray-700" />
                    </button>
                    <button class="p-2 rounded hover:bg-gray-100" title="Hapus">
                        <x-icon name="delete" class="w-5 h-5 text-gray-700" />
                    </button>
                </td>
            </tr>

            <!-- 4 -->
            <tr class="border-b buku-row" data-judul="dasar-dasar akuntansi" data-kategori="Akuntansi" data-status="Tersedia">
                <td class="px-6 py-3">BK-004</td>
                <td class="px-6 py-3">Rak-004</td>
                <td class="px-6 py-3">Dasar-Dasar Akuntansi</td>
                <td class="px-6 py-3">Akuntansi</td>
                <td class="px-6 py-3">10</td>
                <td class="px-6 py-3 text-green-600 font-semibold">Tersedia</td>
                <td class="px-6 py-3 flex gap-2">
                    <button class="p-2 rounded hover:bg-gray-100" title="Detail">
                        <x-icon name="eye" class="w-5 h-5 text-gray-700" />
                    </button>
                    <button class="p-2 rounded hover:bg-gray-100" title="Edit">
                        <x-icon name="edit" class="w-5 h-5 text-gray-700" />
                    </button>
                    <button class="p-2 rounded hover:bg-gray-100" title="Hapus">
                        <x-icon name="delete" class="w-5 h-5 text-gray-700" />
                    </button>
                </td>
            </tr>

            <!-- 5 -->
            <tr class="border-b buku-row" data-judul="manajemen proyek ti" data-kategori="Manajemen" data-status="Tidak Tersedia">
                <td class="px-6 py-3">BK-005</td>
                <td class="px-6 py-3">Rak-005</td>
                <td class="px-6 py-3">Manajemen Proyek TI</td>
                <td class="px-6 py-3">Manajemen</td>
                <td class="px-6 py-3">-</td>
                <td class="px-6 py-3 text-red-600 font-semibold">Tidak Tersedia</td>
                <td class="px-6 py-3 flex gap-2">
                    <button class="p-2 rounded hover:bg-gray-100" title="Detail">
                        <x-icon name="eye" class="w-5 h-5 text-gray-700" />
                    </button>
                    <button class="p-2 rounded hover:bg-gray-100" title="Edit">
                        <x-icon name="edit" class="w-5 h-5 text-gray-700" />
                    </button>
                    <button class="p-2 rounded hover:bg-gray-100" title="Hapus">
                        <x-icon name="delete" class="w-5 h-5 text-gray-700" />
                    </button>
                </td>
            </tr>

            <!-- 6 -->
            <tr class="border-b buku-row" data-judul="statistika untuk penelitian" data-kategori="Statistik" data-status="Tersedia">
                <td class="px-6 py-3">BK-006</td>
                <td class="px-6 py-3">Rak-006</td>
                <td class="px-6 py-3">Statistika untuk Penelitian</td>
                <td class="px-6 py-3">Statistik</td>
                <td class="px-6 py-3">14</td>
                <td class="px-6 py-3 text-green-600 font-semibold">Tersedia</td>
                <td class="px-6 py-3 flex gap-2">
                    <button class="p-2 rounded hover:bg-gray-100" title="Detail">
                        <x-icon name="eye" class="w-5 h-5 text-gray-700" />
                    </button>
                    <button class="p-2 rounded hover:bg-gray-100" title="Edit">
                        <x-icon name="edit" class="w-5 h-5 text-gray-700" />
                    </button>
                    <button class="p-2 rounded hover:bg-gray-100" title="Hapus">
                        <x-icon name="delete" class="w-5 h-5 text-gray-700" />
                    </button>
                </td>
            </tr>

            <!-- 7 -->
            <tr class="border-b buku-row" data-judul="pengantar kecerdasan buatan" data-kategori="AI" data-status="Tersedia">
                <td class="px-6 py-3">BK-007</td>
                <td class="px-6 py-3">Rak-007</td>
                <td class="px-6 py-3">Pengantar Kecerdasan Buatan</td>
                <td class="px-6 py-3">AI</td>
                <td class="px-6 py-3">23</td>
                <td class="px-6 py-3 text-green-600 font-semibold">Tersedia</td>
                <td class="px-6 py-3 flex gap-3">
                    <img src="{{ asset('icons/info.png') }}" class="w-5">
                    <img src="{{ asset('icons/edit.png') }}" class="w-5">
                    <img src="{{ asset('icons/delete.png') }}" class="w-5">
                </td>
            </tr>

            <!-- 8 -->
            <tr class="buku-row" data-judul="sejarah nusantara kuno" data-kategori="Budaya" data-status="Tidak Tersedia">
                <td class="px-6 py-3">BK-008</td>
                <td class="px-6 py-3">Rak-008</td>
                <td class="px-6 py-3">Sejarah Nusantara Kuno</td>
                <td class="px-6 py-3">Budaya</td>
                <td class="px-6 py-3">8</td>
                <td class="px-6 py-3 text-red-600 font-semibold">Tidak Tersedia</td>
                <td class="px-6 py-3 flex gap-3">
                    <img src="{{ asset('icons/info.png') }}" class="w-5">
                    <img src="{{ asset('icons/edit.png') }}" class="w-5">
                    <img src="{{ asset('icons/delete.png') }}" class="w-5">
                </td>
            </tr>

        </tbody>
    </table>
    <div id="noResults" class="hidden text-center py-8 text-gray-500">
        <p>Tidak ada buku yang ditemukan.</p>
    </div>
</div>

    <!-- ============================ PAGINATION ============================ -->
    <div id="paginationContainer" class="flex items-center justify-center space-x-4 mt-6">
        <!-- Pagination akan di-generate oleh JavaScript -->
    </div>
    
</div>

<!-- MODAL TAMBAH BUKU -->
<div id="tambahBukuModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-8 w-96 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-bold text-[#A63A2D]">Tambah Buku Baru</h3>
            <button onclick="closeTambahBukuModal()" class="text-gray-500 hover:text-gray-900 text-2xl">&times;</button>
        </div>

        <form id="tambahBukuForm" onsubmit="handleTambahBuku(event)" enctype="multipart/form-data">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-2">ID Buku</label>
                    <input type="text" name="id_buku" class="w-full p-3 rounded-lg border shadow" placeholder="BK-001" required>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold mb-2">Rak Buku</label>
                    <input type="text" name="rak" class="w-full p-3 rounded-lg border shadow" placeholder="Rak-001" required>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold mb-2">Judul Buku</label>
                    <input type="text" name="judul" class="w-full p-3 rounded-lg border shadow" placeholder="Judul Buku" required>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold mb-2">Kategori</label>
                    <select name="kategori" class="w-full p-3 rounded-lg border shadow" required>
                        <option value="">Pilih Kategori</option>
                        <option value="Bahasa">Bahasa</option>
                        <option value="Pemrograman">Pemrograman</option>
                        <option value="Psikologi">Psikologi</option>
                        <option value="Akuntansi">Akuntansi</option>
                        <option value="Manajemen">Manajemen</option>
                        <option value="Statistik">Statistik</option>
                        <option value="AI">AI</option>
                        <option value="Budaya">Budaya</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold mb-2">Cover Buku</label>
                    <input type="file" name="cover" id="coverInput" accept="image/*" class="w-full p-3 rounded-lg border shadow" onchange="previewCover(event)">
                    <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, atau GIF (Max: 2MB)</p>
                    <div id="coverPreview" class="mt-3 hidden">
                        <img id="coverPreviewImg" src="" alt="Preview Cover" class="w-full h-48 object-cover rounded-lg border shadow">
                        <button type="button" onclick="removeCoverPreview()" class="mt-2 text-sm text-red-600 hover:underline">
                            <i class="fa-solid fa-trash"></i> Hapus Preview
        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">Stok</label>
                    <input type="number" name="stok" class="w-full p-3 rounded-lg border shadow" placeholder="10" min="0" required>
        </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">Status</label>
                    <select name="status" class="w-full p-3 rounded-lg border shadow" required>
                        <option value="Tersedia">Tersedia</option>
                        <option value="Tidak Tersedia">Tidak Tersedia</option>
                    </select>
                </div>
            </div>
            
            <div class="flex gap-4 mt-6">
                <button type="button" onclick="closeTambahBukuModal()" class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-lg font-bold hover:bg-gray-400">
                    Batal
        </button>
                <button type="submit" class="flex-1 bg-[#A63A2D] text-white py-3 rounded-lg font-bold hover:bg-[#923223]">
                    Simpan
        </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL IMPORT EXCEL -->
<div id="importExcelModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-8 w-96">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-bold text-[#A63A2D]">Import Excel</h3>
            <button onclick="closeImportExcelModal()" class="text-gray-500 hover:text-gray-900 text-2xl">&times;</button>
    </div>
    
        <form id="importExcelForm" action="{{ route('kelola-buku.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-2">Pilih File Excel</label>
                    <input type="file" name="file" accept=".csv,text/csv" class="w-full p-3 rounded-lg border shadow" required>
                    <p class="text-xs text-gray-500 mt-2">Gunakan CSV dari Excel (semicolon). Header: Judul; Penulis; Kategori; Tahun Terbit; Stok; Deskripsi.</p>
                </div>
                
                <div class="bg-yellow-50 p-3 rounded-lg text-sm text-yellow-800">
                    <p class="font-semibold mb-1">Format CSV:</p>
                    <p>Judul; Penulis; Kategori; Tahun Terbit; Stok; Deskripsi</p>
                </div>
            </div>
            
            <div class="flex gap-4 mt-6">
                <button type="button" onclick="closeImportExcelModal()" class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-lg font-bold hover:bg-gray-400">
                    Batal
                </button>
                <button type="submit" class="flex-1 bg-[#A63A2D] text-white py-3 rounded-lg font-bold hover:bg-[#923223]">
                    Import
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL DETAIL BUKU -->
<div id="detailBukuModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-8 w-[420px] max-h-[85vh] overflow-y-auto relative">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-2xl font-bold text-[#A63A2D]">Detail Buku</h3>
            <button onclick="closeDetailBukuModal()" class="text-gray-500 hover:text-gray-900 text-2xl">&times;</button>
        </div>
        <div class="space-y-3 text-sm">
            <p><span class="font-semibold">ID Buku:</span> <span id="detailId"></span></p>
            <p><span class="font-semibold">Rak:</span> <span id="detailRak"></span></p>
            <p><span class="font-semibold">Judul:</span> <span id="detailJudul"></span></p>
            <p><span class="font-semibold">Kategori:</span> <span id="detailKategori"></span></p>
            <p><span class="font-semibold">Stok:</span> <span id="detailStok"></span></p>
            <p><span class="font-semibold">Status:</span> <span id="detailStatus"></span></p>
        </div>
    </div>
</div>

<!-- MODAL EDIT BUKU -->
<div id="editBukuModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-8 w-96 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-bold text-[#A63A2D]">Edit Buku</h3>
            <button onclick="closeEditBukuModal()" class="text-gray-500 hover:text-gray-900 text-2xl">&times;</button>
        </div>

        <form id="editBukuForm" onsubmit="handleEditBuku(event)">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-2">ID Buku</label>
                    <input type="text" name="id_buku" id="editIdBuku" class="w-full p-3 rounded-lg border shadow" required>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold mb-2">Rak Buku</label>
                    <input type="text" name="rak" id="editRak" class="w-full p-3 rounded-lg border shadow" required>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold mb-2">Judul Buku</label>
                    <input type="text" name="judul" id="editJudul" class="w-full p-3 rounded-lg border shadow" required>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold mb-2">Kategori</label>
                    <select name="kategori" id="editKategori" class="w-full p-3 rounded-lg border shadow" required>
                        <option value="">Pilih Kategori</option>
                        <option value="Bahasa">Bahasa</option>
                        <option value="Pemrograman">Pemrograman</option>
                        <option value="Psikologi">Psikologi</option>
                        <option value="Akuntansi">Akuntansi</option>
                        <option value="Manajemen">Manajemen</option>
                        <option value="Statistik">Statistik</option>
                        <option value="AI">AI</option>
                        <option value="Budaya">Budaya</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold mb-2">Stok</label>
                    <input type="number" name="stok" id="editStok" class="w-full p-3 rounded-lg border shadow" min="0" required>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold mb-2">Status</label>
                    <select name="status" id="editStatus" class="w-full p-3 rounded-lg border shadow" required>
                        <option value="Tersedia">Tersedia</option>
                        <option value="Tidak Tersedia">Tidak Tersedia</option>
                    </select>
                </div>
            </div>
            
            <div class="flex gap-4 mt-6">
                <button type="button" onclick="closeEditBukuModal()" class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-lg font-bold hover:bg-gray-400">
                    Batal
                </button>
                <button type="submit" class="flex-1 bg-[#A63A2D] text-white py-3 rounded-lg font-bold hover:bg-[#923223]">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.menu-item').forEach((item, index) => {
    item.addEventListener('click', function () {

        document.getElementById('indicator').style.top = (310 + index * 95) + 'px';

        // ROUTING UNTUK ADMIN
        if (index === 0) window.location.href = "/admin";           // dashboard
        if (index === 1) window.location.href = "/data-anggota";     // kelola buku
        if (index === 2) window.location.href = "/kelola-buku";    // data anggota
        if (index === 3) window.location.href = "/laporan-peminjaman"; // laporan
        if (index === 4) window.location.href = "/kelola-user";     // kelola user
    });
});

// ======================================================
// ALGORITMA STRING MATCHING
// ======================================================

// 1. Brute Force Algorithm
function bruteForce(text, pattern) {
    if (!text || !pattern) return [];
    
    const n = text.length;
    const m = pattern.length;
    const results = [];
    
    if (m === 0 || m > n) return results;
    
    for (let i = 0; i <= n - m; i++) {
        let j = 0;
        while (j < m && text[i + j] === pattern[j]) {
            j++;
        }
        if (j === m) {
            results.push(i);
        }
    }
    return results;
}

// 2. Knuth-Morris-Pratt (KMP) Algorithm
function kmpLps(pattern) {
    const m = pattern.length;
    const lps = new Array(m).fill(0);
    let len = 0;
    let i = 1;
    
    while (i < m) {
        if (pattern[i] === pattern[len]) {
            len++;
            lps[i] = len;
            i++;
        } else {
            if (len !== 0) {
                len = lps[len - 1];
            } else {
                lps[i] = 0;
                i++;
            }
        }
    }
    return lps;
}

function kmp(text, pattern) {
    if (!text || !pattern) return [];
    
    const n = text.length;
    const m = pattern.length;
    const results = [];
    
    if (m === 0 || m > n) return results;
    
    const lps = kmpLps(pattern);
    let i = 0;
    let j = 0;
    
    while (i < n) {
        if (text[i] === pattern[j]) {
            i++;
            j++;
            if (j === m) {
                results.push(i - j);
                j = lps[j - 1];
            }
        } else {
            if (j !== 0) {
                j = lps[j - 1];
            } else {
                i++;
            }
        }
    }
    return results;
}

// 3. Boyer-Moore Algorithm (Simplified)
function boyerMoore(text, pattern) {
    if (!text || !pattern) return [];
    
    const n = text.length;
    const m = pattern.length;
    const results = [];
    
    if (m === 0 || m > n) return results;
    
    // Build bad character table
    const bad = new Array(256).fill(-1);
    for (let i = 0; i < m; i++) {
        bad[pattern.charCodeAt(i)] = i;
    }
    
    let shift = 0;
    while (shift <= n - m) {
        let j = m - 1;
        
        while (j >= 0 && pattern[j] === text[shift + j]) {
            j--;
        }
        
        if (j < 0) {
            results.push(shift);
            shift += (shift + m < n) ? m - (bad[text.charCodeAt(shift + m)] !== undefined ? bad[text.charCodeAt(shift + m)] : -1) : 1;
        } else {
            const bc = bad[text.charCodeAt(shift + j)];
            shift += Math.max(1, j - bc);
        }
    }
    return results;
}

// Fungsi untuk memilih algoritma terbaik berdasarkan panjang pattern
function selectBestAlgorithm(pattern) {
    if (!pattern) return 'bf';
    const len = pattern.length;
    if (len <= 3) return 'bf'; // Brute Force untuk pattern pendek
    if (len <= 10) return 'kmp'; // KMP untuk pattern sedang
    return 'bm'; // Boyer-Moore untuk pattern panjang
}

// Fungsi pencarian dengan algoritma
function searchWithAlgorithm(text, pattern, algorithm) {
    if (!text || !pattern) return [];
    
    switch(algorithm) {
        case 'bf':
            return bruteForce(text, pattern);
        case 'kmp':
            return kmp(text, pattern);
        case 'bm':
            return boyerMoore(text, pattern);
        default:
            return bruteForce(text, pattern);
    }
}

// ======================================================
// FUNGSI FILTER TABLE DENGAN ALGORITMA
// ======================================================
let searchTimeout;

function filterTable() {
    // Debounce untuk menghindari terlalu banyak eksekusi
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        executeSearch();
    }, 150); // Delay 150ms
}

function executeSearch() {
    const searchInput = document.getElementById('searchInput');
    const kategoriFilter = document.getElementById('kategoriFilter');
    const statusFilter = document.getElementById('statusFilter');
    const rows = document.querySelectorAll('.buku-row');
    const noResults = document.getElementById('noResults');
    
    if (!searchInput || !rows.length) return;
    
    const searchValue = searchInput.value.trim().toLowerCase();
    const kategoriValue = kategoriFilter ? kategoriFilter.value : '';
    const statusValue = statusFilter ? statusFilter.value : '';
    let visibleCount = 0;

    // Jika tidak ada input search, tampilkan semua berdasarkan filter
    if (!searchValue && !kategoriValue && !statusValue) {
        rows.forEach(row => {
            row.style.display = '';
            visibleCount++;
        });
        if (noResults) noResults.classList.add('hidden');
        return;
    }

    // Pilih algoritma terbaik berdasarkan panjang pattern
    const algorithm = selectBestAlgorithm(searchValue);
    
    // Mulai timer untuk mengukur performa
    const startTime = performance.now();

    // Lakukan pencarian dengan algoritma string matching
    rows.forEach(row => {
        const judul = row.getAttribute('data-judul') || '';
        const kategori = row.getAttribute('data-kategori') || '';
        const status = row.getAttribute('data-status') || '';
        
        let matchesSearch = true;
        if (searchValue) {
            const judulMatches = searchWithAlgorithm(judul, searchValue, algorithm);
            matchesSearch = judulMatches.length > 0;
        }
        
        const matchesKategori = !kategoriValue || kategori === kategoriValue;
        const matchesStatus = !statusValue || status === statusValue;
        
        if (matchesSearch && matchesKategori && matchesStatus) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Tampilkan pesan jika tidak ada hasil
    if (noResults) {
        if (visibleCount === 0) {
            noResults.classList.remove('hidden');
        } else {
            noResults.classList.add('hidden');
        }
    }
    
    // Update pagination setelah filter
    updatePagination();
}

// ======================================================
// FUNGSI MODAL TAMBAH BUKU
// ======================================================
function openTambahBukuModal() {
    document.getElementById('tambahBukuModal').classList.remove('hidden');
    document.getElementById('tambahBukuModal').classList.add('flex');
}

function closeTambahBukuModal() {
    document.getElementById('tambahBukuModal').classList.add('hidden');
    document.getElementById('tambahBukuModal').classList.remove('flex');
    document.getElementById('tambahBukuForm').reset();
    removeCoverPreview();
}

// ======================================================
// FUNGSI MODAL DETAIL & EDIT BUKU
// ======================================================
function openDetailBukuModal(data) {
    document.getElementById('detailId').textContent = data.id;
    document.getElementById('detailRak').textContent = data.rak;
    document.getElementById('detailJudul').textContent = data.judul;
    document.getElementById('detailKategori').textContent = data.kategori;
    document.getElementById('detailStok').textContent = data.stok;
    document.getElementById('detailStatus').textContent = data.status;
    document.getElementById('detailBukuModal').classList.remove('hidden');
}

function closeDetailBukuModal() {
    document.getElementById('detailBukuModal').classList.add('hidden');
}

let editTargetRow = null;
function openEditBukuModal(data, row) {
    editTargetRow = row;
    document.getElementById('editIdBuku').value = data.id;
    document.getElementById('editRak').value = data.rak;
    document.getElementById('editJudul').value = data.judul;
    document.getElementById('editKategori').value = data.kategori;
    document.getElementById('editStok').value = data.stok;
    document.getElementById('editStatus').value = data.status;
    document.getElementById('editBukuModal').classList.remove('hidden');
    document.getElementById('editBukuModal').classList.add('flex');
}

function closeEditBukuModal() {
    document.getElementById('editBukuModal').classList.add('hidden');
    document.getElementById('editBukuModal').classList.remove('flex');
    editTargetRow = null;
}

function handleEditBuku(event) {
    event.preventDefault();
    if (!editTargetRow) return;

    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData);

    const cells = editTargetRow.querySelectorAll('td');
    cells[0].textContent = data.id_buku;
    cells[1].textContent = data.rak;
    cells[2].textContent = data.judul;
    cells[3].textContent = data.kategori;
    cells[4].textContent = data.stok;

    const statusCell = cells[5];
    statusCell.textContent = data.status;
    statusCell.className = `px-6 py-3 font-semibold ${data.status === 'Tersedia' ? 'text-green-600' : 'text-red-600'}`;

    // Simpan ke dataset untuk filtering
    editTargetRow.setAttribute('data-judul', data.judul.toLowerCase());
    editTargetRow.setAttribute('data-kategori', data.kategori);
    editTargetRow.setAttribute('data-status', data.status);
    editTargetRow.setAttribute('data-id', data.id_buku);
    editTargetRow.setAttribute('data-rak', data.rak);
    editTargetRow.setAttribute('data-stok', data.stok);

    closeEditBukuModal();
    updatePagination();
}

function handleDeleteBuku(row, data) {
    const confirmDel = confirm(`Hapus buku \"${data.judul}\"?`);
    if (confirmDel) {
        row.remove();
        updatePagination();
    }
}

function attachRowActions(row) {
    const icons = row.querySelectorAll('img');
    if (icons.length < 3) return;

    const getData = () => {
        const cells = row.querySelectorAll('td');
        return {
            id: cells[0].textContent.trim(),
            rak: cells[1].textContent.trim(),
            judul: cells[2].textContent.trim(),
            kategori: cells[3].textContent.trim(),
            stok: cells[4].textContent.trim(),
            status: cells[5].textContent.trim(),
        };
    };

    icons[0].onclick = () => openDetailBukuModal(getData());
    icons[1].onclick = () => openEditBukuModal(getData(), row);
    icons[2].onclick = () => handleDeleteBuku(row, getData());
}

function initRowActions() {
    document.querySelectorAll('#bukuTableBody tr').forEach(attachRowActions);
}

function previewCover(event) {
    const file = event.target.files[0];
    const previewDiv = document.getElementById('coverPreview');
    const previewImg = document.getElementById('coverPreviewImg');
    
    if (file) {
        // Validasi ukuran file (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file terlalu besar! Maksimal 2MB.');
            event.target.value = '';
            return;
        }
        
        // Validasi tipe file
        if (!file.type.match('image.*')) {
            alert('File harus berupa gambar!');
            event.target.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewDiv.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    } else {
        previewDiv.classList.add('hidden');
    }
}

function removeCoverPreview() {
    const previewDiv = document.getElementById('coverPreview');
    const coverInput = document.getElementById('coverInput');
    previewDiv.classList.add('hidden');
    if (coverInput) {
        coverInput.value = '';
    }
}

function handleTambahBuku(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData);
    
    // Simulasi menambahkan buku ke tabel
    const tbody = document.getElementById('bukuTableBody');
    const newRow = document.createElement('tr');
    newRow.className = 'border-b buku-row';
    newRow.setAttribute('data-judul', data.judul.toLowerCase());
    newRow.setAttribute('data-kategori', data.kategori);
    newRow.setAttribute('data-status', data.status);
    newRow.setAttribute('data-id', data.id_buku);
    newRow.setAttribute('data-rak', data.rak);
    newRow.setAttribute('data-stok', data.stok);
    
    const statusClass = data.status === 'Tersedia' ? 'text-green-600' : 'text-red-600';
    
    newRow.innerHTML = `
        <td class="px-6 py-3">${data.id_buku}</td>
        <td class="px-6 py-3">${data.rak}</td>
        <td class="px-6 py-3">${data.judul}</td>
        <td class="px-6 py-3">${data.kategori}</td>
        <td class="px-6 py-3">${data.stok}</td>
        <td class="px-6 py-3 font-semibold ${statusClass}">${data.status}</td>
        <td class="px-6 py-3 flex gap-2">
            <button class="p-2 rounded hover:bg-gray-100" title="Detail">
                <x-icon name="eye" class="w-5 h-5 text-gray-700" />
            </button>
            <button class="p-2 rounded hover:bg-gray-100" title="Edit">
                <x-icon name="edit" class="w-5 h-5 text-gray-700" />
            </button>
            <button class="p-2 rounded hover:bg-gray-100" title="Hapus">
                <x-icon name="delete" class="w-5 h-5 text-gray-700" />
            </button>
        </td>
    `;
    
    tbody.appendChild(newRow);
    attachRowActions(newRow);
    closeTambahBukuModal();
    alert('Buku berhasil ditambahkan!');
    updatePagination();
}

// ======================================================
// FUNGSI MODAL IMPORT EXCEL
// ======================================================
function openImportExcelModal() {
    document.getElementById('importExcelModal').classList.remove('hidden');
    document.getElementById('importExcelModal').classList.add('flex');
}

function closeImportExcelModal() {
    document.getElementById('importExcelModal').classList.add('hidden');
    document.getElementById('importExcelModal').classList.remove('flex');
    document.getElementById('importExcelForm').reset();
}

function handleImportExcel(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const file = formData.get('excel_file');
    
    if (!file) {
        alert('Silakan pilih file Excel!');
        return;
    }
    
    // Simulasi import (dalam implementasi nyata, kirim ke server)
    alert('File Excel berhasil diimport! (Fitur ini memerlukan implementasi backend)');
    closeImportExcelModal();
}

// ======================================================
// FUNGSI PAGINATION
// ======================================================
let currentPage = 1;
const itemsPerPage = 5;

function updatePagination() {
    const rows = Array.from(document.querySelectorAll('.buku-row:not([style*="display: none"])'));
    const totalItems = rows.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    
    const paginationContainer = document.getElementById('paginationContainer');
    if (!paginationContainer) return;
    
    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }
    
    let paginationHTML = '';
    
    // Tombol Prev
    paginationHTML += `
        <button onclick="goToPage(${currentPage - 1})" 
                class="w-8 h-8 flex items-center justify-center bg-gray-300 rounded-full text-gray-700 hover:bg-gray-400 ${currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''}"
                ${currentPage === 1 ? 'disabled' : ''}>
            ‹
        </button>
    `;
    
    // Halaman
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
            if (i === currentPage) {
                paginationHTML += `<div class="w-7 h-7 flex items-center justify-center bg-[#A63A2D] text-white rounded-full">${i}</div>`;
            } else {
                paginationHTML += `<button onclick="goToPage(${i})" class="w-7 h-7 flex items-center justify-center text-gray-800 hover:bg-gray-200 rounded-full">${i}</button>`;
            }
        } else if (i === currentPage - 2 || i === currentPage + 2) {
            paginationHTML += `<span class="text-gray-800 text-lg">...</span>`;
        }
    }
    
    // Tombol Next
    paginationHTML += `
        <button onclick="goToPage(${currentPage + 1})" 
                class="w-8 h-8 flex items-center justify-center bg-gray-300 rounded-full text-gray-700 hover:bg-gray-400 ${currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : ''}"
                ${currentPage === totalPages ? 'disabled' : ''}>
            ›
        </button>
    `;
    
    paginationContainer.innerHTML = paginationHTML;
    
    // Tampilkan/sembunyikan rows berdasarkan halaman
    rows.forEach((row, index) => {
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        
        if (index >= startIndex && index < endIndex) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function goToPage(page) {
    const rows = Array.from(document.querySelectorAll('.buku-row:not([style*="display: none"])'));
    const totalPages = Math.ceil(rows.length / itemsPerPage);
    
    if (page < 1 || page > totalPages) return;
    
    currentPage = page;
    updatePagination();
}

// Inisialisasi pagination saat halaman dimuat
document.addEventListener('DOMContentLoaded', () => {
    updatePagination();
    initRowActions();
});

// ======================================================
// FUNGSI NOTIFIKASI
// ======================================================
let notifInterval;

function toggleNotifPopup() {
    const popup = document.getElementById('notifPopup');
    popup.classList.toggle('hidden');
    popup.classList.toggle('flex');
    
    if (!popup.classList.contains('hidden')) {
        loadNotifikasi();
    }
}

function loadNotifikasi() {
    fetch('/api/notifikasi', {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const notifList = document.getElementById('notifList');
        const badge = document.getElementById('notifBadge');
        
        // Update badge
        if (data.unread_count > 0) {
            badge.textContent = data.unread_count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
        
        // Render notifikasi
        if (data.notifikasi && data.notifikasi.length > 0) {
            notifList.innerHTML = data.notifikasi.map(notif => {
                const tipeColors = {
                    'info': 'bg-blue-50 border-blue-500',
                    'warning': 'bg-yellow-50 border-yellow-500',
                    'success': 'bg-green-50 border-green-500',
                    'error': 'bg-red-50 border-red-500'
                };
                const color = tipeColors[notif.tipe] || tipeColors['info'];
                const waktu = formatTime(notif.created_at);
                const unreadClass = !notif.dibaca ? 'font-semibold' : '';
                
                return `
                    <li class="p-3 ${color} rounded-xl border-l-4 ${unreadClass} cursor-pointer hover:shadow-md" onclick="markAsRead(${notif.id})">
                        <p class="text-sm font-semibold text-gray-800">${notif.judul}</p>
                        <p class="text-xs text-gray-600 mt-1">${notif.pesan}</p>
                        <p class="text-xs text-gray-400 mt-1">${waktu}</p>
                    </li>
                `;
            }).join('');
        } else {
            notifList.innerHTML = '<li class="p-3 text-center text-gray-500">Tidak ada notifikasi</li>';
        }
    })
    .catch(error => {
        console.error('Error loading notifications:', error);
    });
}

function markAsRead(id) {
    fetch(`/api/notifikasi/${id}/read`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(() => {
        loadNotifikasi();
    });
}

function markAllAsRead() {
    fetch('/api/notifikasi/read-all', {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(() => {
        loadNotifikasi();
    });
}

function formatTime(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 1) return 'Baru saja';
    if (diffMins < 60) return `${diffMins} menit lalu`;
    if (diffHours < 24) return `${diffHours} jam lalu`;
    if (diffDays < 7) return `${diffDays} hari lalu`;
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

// Load notifikasi saat halaman dimuat dan update setiap 30 detik
document.addEventListener('DOMContentLoaded', () => {
    loadNotifikasi();
    notifInterval = setInterval(loadNotifikasi, 30000); // Update setiap 30 detik
});

// Tutup popup saat klik di luar
document.getElementById('notifPopup').addEventListener('click', function(e) {
    if (e.target === this) {
        toggleNotifPopup();
    }
});
</script>

<!-- POPUP NOTIFIKASI -->
<div id="notifPopup" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-96 max-h-[80vh] overflow-y-auto relative">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-[#A63A2D]">Notifikasi</h3>
            <div class="flex gap-2">
                <button onclick="markAllAsRead()" class="text-xs text-blue-600 hover:underline">Tandai semua dibaca</button>
                <button onclick="toggleNotifPopup()" class="text-gray-500 hover:text-gray-900 text-2xl">&times;</button>
            </div>
        </div>
        <ul id="notifList" class="space-y-3">
            <li class="p-3 text-center text-gray-500">Memuat notifikasi...</li>
        </ul>
    </div>
</div>


</body>
</html>
