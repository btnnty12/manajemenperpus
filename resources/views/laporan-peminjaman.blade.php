<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laporan Peminjaman</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#f6d47f] flex">

<style>
#indicator {
    position: absolute;
    left: 0;
    top: 415px;
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

.section-wrapper {
    width: 100%;
    padding-left: 25px;
    padding-right: 40px;
    margin-top: 20px;
}

.stats-row {
    display: flex;
    justify-content: center;
    gap: 25px;
    margin-top: 25px;
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
        <div class="border-l border-white h-6"></div>
        <button id="notifBtn" onclick="toggleNotifPopup()" class="relative">
            <x-icon name="notification" class="w-6 h-6 text-black hover:opacity-80 cursor-pointer" />
            <span id="notifBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
        </button>
        <div class="border-l border-white h-6"></div>
        <div class="flex items-center space-x-2">
            <div class="bg-[#717BFF] w-10 h-10 rounded-full flex items-center justify-center text-white font-bold overflow-hidden">
                @if(Auth::check() && Auth::user()->foto)
                    <img src="{{ asset('storage/' . Auth::user()->foto) }}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.parentElement.innerHTML='{{ strtoupper(substr(Auth::user()->nama ?? 'AD', 0, 2)) }}'">
                @else
                    {{ strtoupper(substr(Auth::user()->nama ?? 'AD', 0, 2)) }}
                @endif
            </div>
            <span class="text-black font-medium">{{ Auth::user()->nama ?? 'Admin' }}</span>
        </div>
    </div>

    <div class="w-full border-b-2 border-white mb-6"></div>

    <div class="section-wrapper">
        <h1 class="text-4xl font-bold">Laporan Peminjaman !</h1>
        <p class="text-gray-700 mt-1">Cek siapa saja yang sedang meminjam, mengembalikan, atau terlambat mengembalikan buku.</p>

        <!-- STATISTIK -->
        <div class="stats-row">
            <div class="stat-card">
                <h2 class="text-4xl font-bold">{{ $totalDipinjam ?? 0 }}</h2>
                <p>Total Peminjaman</p>
            </div>
            <div class="stat-card">
                <h2 class="text-4xl font-bold">{{ $sedangDipinjam ?? 0 }}</h2>
                <p>Sedang Dipinjam</p>
            </div>
            <div class="stat-card">
                <h2 class="text-4xl font-bold">{{ $terlambat ?? 0 }}</h2>
                <p>Terlambat</p>
            </div>
            <div class="stat-card">
                <h2 class="text-4xl font-bold">Rp {{ number_format($totalDenda ?? 0, 0, ',', '.') }}</h2>
                <p>Total Denda Terkumpul</p>
            </div>
        </div>

        <!-- FILTER BAR -->
        <div class="filter-row">
            <input type="text" id="searchInput" placeholder="Search" class="filter-search shadow p-3 rounded-lg" onkeyup="filterTable()">
            <select id="statusFilter" class="filter-select shadow" onchange="filterTable()">
                <option value="">Semua Status</option>
                <option value="menunggu_approval">Menunggu Approval</option>
                <option value="dapat_diambil">Dapat Diambil</option>
                <option value="sedang_dipinjam">Sedang Dipinjam</option>
                <option value="terlambat">Terlambat</option>
                <option value="dikembalikan">Dikembalikan</option>
            </select>
            <input type="date" id="dateFilter" class="filter-select shadow" onchange="filterTable()">
            <button class="btn-search" onclick="filterTable()">Search</button>
        </div>
    </div>

    <!-- ============================ TABLE ============================ -->
    <div class="mt-8 bg-white rounded-lg shadow overflow-hidden w-[97%]">
        <table class="w-full text-left">
            <thead class="bg-[#b54a38] text-white">
                <tr>
                    <th class="px-6 py-4">ID Transaksi</th>
                    <th class="px-6 py-4">Nama Anggota</th>
                    <th class="px-6 py-4">Judul Buku</th>
                    <th class="px-6 py-4">Tanggal Pinjam</th>
                    <th class="px-6 py-4">Tanggal Kembali</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Denda</th>
                    <th class="px-10 py-5">Aksi</th>
                </tr>
            </thead>
            <tbody id="pinjamanTableBody">
                @forelse($pinjaman ?? [] as $p)
                    @php
                        $tanggalJatuhTempo = $p->tanggal_jatuh_tempo ? Carbon\Carbon::parse($p->tanggal_jatuh_tempo) : null;
                        $hariIni = Carbon\Carbon::now();
                        $isTerlambat = false;
                        
                        if ($p->status === 'menunggu_approval') {
                            $statusDisplay = 'Menunggu Approval';
                            $statusClass = 'text-yellow-600';
                        } elseif ($p->status === 'dapat_diambil') {
                            $statusDisplay = 'Dapat Diambil';
                            $statusClass = 'text-blue-600';
                        } elseif ($p->status === 'sedang_dipinjam' && $tanggalJatuhTempo) {
                            if ($hariIni->gt($tanggalJatuhTempo)) {
                                $statusDisplay = 'Terlambat';
                                $statusClass = 'text-red-600';
                                $isTerlambat = true;
                            } else {
                                $statusDisplay = 'Sedang Dipinjam';
                                $statusClass = 'text-amber-600';
                            }
                        } elseif ($p->status === 'dikembalikan') {
                            $statusDisplay = 'Dikembalikan';
                            $statusClass = 'text-green-600';
                        } else {
                            $statusDisplay = ucfirst(str_replace('_', ' ', $p->status));
                            $statusClass = 'text-gray-600';
                        }
                        
                        $dataStatus = $isTerlambat ? 'terlambat' : $p->status;
                    @endphp
                    <tr class="border-b pinjaman-row" 
                        data-nama="{{ strtolower($p->pengguna->nama ?? '') }}" 
                        data-judul="{{ strtolower($p->buku->judul ?? '') }}"
                        data-status="{{ $dataStatus }}"
                        data-tanggal="{{ $p->tanggal_pinjam ? Carbon\Carbon::parse($p->tanggal_pinjam)->format('Y-m-d') : '' }}">
                        <td class="px-6 py-3">P-{{ str_pad($p->id, 6, '0', STR_PAD_LEFT) }}</td>
                        <td class="px-6 py-3">{{ $p->pengguna->nama ?? '-' }}</td>
                        <td class="px-6 py-3">{{ $p->buku->judul ?? '-' }}</td>
                        <td class="px-6 py-3">{{ $p->tanggal_pinjam ? Carbon\Carbon::parse($p->tanggal_pinjam)->format('d/m/Y') : '-' }}</td>
                        <td class="px-6 py-3">{{ $p->tanggal_kembali ? Carbon\Carbon::parse($p->tanggal_kembali)->format('d/m/Y') : '-' }}</td>
                        <td class="px-6 py-3 {{ $statusClass }} font-semibold">{{ $statusDisplay }}</td>
                        <td class="px-6 py-3">
                            @if($p->denda && $p->denda > 0)
                                Rp {{ number_format($p->denda, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex gap-2 items-center">
                                @if($p->status === 'menunggu_approval')
                                    <button onclick="approvePinjaman({{ $p->id }})" 
                                            class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700 transition font-semibold"
                                            title="Setujui peminjaman">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button onclick="rejectPinjaman({{ $p->id }})" 
                                            class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700 transition font-semibold"
                                            title="Tolak peminjaman">
                                        <i class="fas fa-times"></i> Tolak
                                    </button>
                                @elseif($p->status === 'dapat_diambil')
                                    <button onclick="confirmAmbil({{ $p->id }})" 
                                            class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700 transition font-semibold"
                                            title="Konfirmasi buku sudah diambil">
                                        <i class="fas fa-check-circle"></i> Konfirmasi Ambil
                                    </button>
                                    <button onclick="showDetail({{ $p->id }}, '{{ addslashes($p->pengguna->nama ?? '') }}', '{{ addslashes($p->buku->judul ?? '') }}', '{{ $p->tanggal_pinjam ? Carbon\Carbon::parse($p->tanggal_pinjam)->format('d/m/Y') : '-' }}', '{{ $p->tanggal_jatuh_tempo ? Carbon\Carbon::parse($p->tanggal_jatuh_tempo)->format('d/m/Y') : '-' }}', '{{ $statusDisplay }}', '{{ $p->denda ?? 0 }}')" 
                                            class="bg-gray-600 text-white px-3 py-1 rounded text-xs hover:bg-gray-700 transition"
                                            title="Lihat detail">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                @else
                                    <button onclick="showDetail({{ $p->id }}, '{{ addslashes($p->pengguna->nama ?? '') }}', '{{ addslashes($p->buku->judul ?? '') }}', '{{ $p->tanggal_pinjam ? Carbon\Carbon::parse($p->tanggal_pinjam)->format('d/m/Y') : '-' }}', '{{ $p->tanggal_jatuh_tempo ? Carbon\Carbon::parse($p->tanggal_jatuh_tempo)->format('d/m/Y') : '-' }}', '{{ $statusDisplay }}', '{{ $p->denda ?? 0 }}')" 
                                            class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700 transition"
                                            title="Lihat detail">
                                        <i class="fas fa-info-circle"></i> Detail
                                    </button>
                                    @if($p->status === 'sedang_dipinjam' && !$isTerlambat)
                                        <button onclick="returnBook({{ $p->id }})" 
                                                class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700 transition"
                                                title="Konfirmasi pengembalian">
                                            <i class="fas fa-undo"></i> Kembalikan
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            <p class="text-lg">Tidak ada data peminjaman</p>
                            <p class="text-sm mt-2">Belum ada peminjaman yang tercatat dalam sistem.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div id="noResults" class="hidden text-center py-8 text-gray-500">
            <p>Tidak ada data peminjaman yang ditemukan.</p>
        </div>
    </div>

    <!-- PAGINATION -->
    <div id="paginationContainer" class="flex items-center justify-center space-x-4 mt-6">
        <!-- Pagination akan di-generate oleh JavaScript -->
    </div>
</div>

<!-- MODAL DETAIL -->
<div id="detailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg p-6 w-96 max-w-[90vw] relative">
        <button onclick="closeDetail()" class="absolute right-4 top-3 text-gray-500 hover:text-gray-900 text-2xl">&times;</button>
        <h2 class="text-xl font-bold mb-4 text-[#b54a38]">Detail Peminjaman</h2>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between">
                <span class="font-semibold text-gray-700">ID Transaksi:</span>
                <span id="detailId" class="text-gray-900">-</span>
            </div>
            <div class="flex justify-between">
                <span class="font-semibold text-gray-700">Nama Anggota:</span>
                <span id="detailNama" class="text-gray-900">-</span>
            </div>
            <div class="flex justify-between">
                <span class="font-semibold text-gray-700">Judul Buku:</span>
                <span id="detailJudul" class="text-gray-900 text-right max-w-[200px]">-</span>
            </div>
            <div class="flex justify-between">
                <span class="font-semibold text-gray-700">Tanggal Pinjam:</span>
                <span id="detailTglPinjam" class="text-gray-900">-</span>
            </div>
            <div class="flex justify-between">
                <span class="font-semibold text-gray-700">Tanggal Jatuh Tempo:</span>
                <span id="detailTglJatuhTempo" class="text-gray-900">-</span>
            </div>
            <div class="flex justify-between">
                <span class="font-semibold text-gray-700">Tanggal Kembali:</span>
                <span id="detailTglKembali" class="text-gray-900">-</span>
            </div>
            <div class="flex justify-between">
                <span class="font-semibold text-gray-700">Status:</span>
                <span id="detailStatus" class="text-gray-900 font-semibold">-</span>
            </div>
            <div class="flex justify-between border-t pt-2">
                <span class="font-semibold text-gray-700">Denda:</span>
                <span id="detailDenda" class="text-gray-900">-</span>
            </div>
        </div>
        <div class="mt-4 flex justify-end">
            <button onclick="closeDetail()" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.menu-item').forEach((item, index) => {
    item.addEventListener('click', function () {

        // PINDAHKAN INDIKATOR
        document.getElementById('indicator').style.top = (204 + index * 95) + 'px';

        // ARAHKAN KE HALAMAN SESUAI MENU
        if (index === 0) window.location.href = "/admin";              // Dashboard
        if (index === 1) window.location.href = "/data-anggota";       // Data Anggota
        if (index === 2) window.location.href = "/kelola-buku";        // Kelola Buku
        if (index === 3) window.location.href = "/laporan-peminjaman"; // Laporan Grafik/Peminjaman
        if (index === 4) window.location.href = "/kelola-user";        // Kelola User
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
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        executeSearch();
    }, 150);
}

function executeSearch() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const dateFilter = document.getElementById('dateFilter');
    const rows = document.querySelectorAll('.pinjaman-row');
    const noResults = document.getElementById('noResults');
    
    if (!searchInput || !rows.length) return;
    
    const searchValue = searchInput.value.trim().toLowerCase();
    const statusValue = statusFilter ? statusFilter.value : '';
    const dateValue = dateFilter ? dateFilter.value : '';
    let visibleCount = 0;

    // Jika tidak ada input search, tampilkan semua berdasarkan filter
    if (!searchValue && !statusValue && !dateValue) {
        rows.forEach(row => {
            row.style.display = '';
            visibleCount++;
        });
        if (noResults) noResults.classList.add('hidden');
        return;
    }

    // Pilih algoritma terbaik berdasarkan panjang pattern
    const algorithm = selectBestAlgorithm(searchValue);

    // Lakukan pencarian dengan algoritma string matching
    rows.forEach(row => {
        const nama = row.getAttribute('data-nama') || '';
        const judul = row.getAttribute('data-judul') || '';
        const status = row.getAttribute('data-status') || '';
        const tanggal = row.getAttribute('data-tanggal') || '';
        
        let matchesSearch = true;
        if (searchValue) {
            const namaMatches = searchWithAlgorithm(nama, searchValue, algorithm);
            const judulMatches = searchWithAlgorithm(judul, searchValue, algorithm);
            matchesSearch = namaMatches.length > 0 || judulMatches.length > 0;
        }

        const matchesStatus = !statusValue || status === statusValue;
        const matchesDate = !dateValue || tanggal === dateValue;
        
        if (matchesSearch && matchesStatus && matchesDate) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Tampilkan pesan jika tidak ada hasil
    const tableBody = document.getElementById('pinjamanTableBody');
    if (visibleCount === 0) {
        if (noResults) {
            noResults.classList.remove('hidden');
            noResults.innerHTML = '<p class="text-lg font-semibold">Tidak ada data peminjaman yang ditemukan</p><p class="text-sm mt-2 text-gray-400">Coba ubah filter atau kata kunci pencarian Anda.</p>';
        }
        // Sembunyikan semua row
        rows.forEach(row => row.style.display = 'none');
    } else {
        if (noResults) {
            noResults.classList.add('hidden');
        }
    }
    
    // Update pagination setelah filter
    updatePaginationPeminjaman();
}

// ======================================================
// FUNGSI APPROVE & REJECT PINJAMAN
// ======================================================
function approvePinjaman(id) {
    if (!confirm('Apakah Anda yakin ingin menyetujui peminjaman ini?')) {
        return;
    }
    
    fetch(`/api/pinjaman/${id}/approve`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Peminjaman berhasil disetujui!');
            location.reload();
        } else {
            alert('Gagal menyetujui peminjaman: ' + (data.message || 'Terjadi kesalahan'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyetujui peminjaman');
    });
}

function rejectPinjaman(id) {
    if (!confirm('Apakah Anda yakin ingin menolak peminjaman ini? Peminjaman akan dihapus dari sistem.')) {
        return;
    }
    
    fetch(`/api/pinjaman/${id}/reject`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Peminjaman berhasil ditolak!');
            location.reload();
        } else {
            alert('Gagal menolak peminjaman: ' + (data.message || 'Terjadi kesalahan'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menolak peminjaman');
    });
}

function showDetail(id, nama, judul, tglPinjam, tglJatuhTempo, status, denda) {
    document.getElementById('detailId').textContent = 'P-' + String(id).padStart(6, '0');
    document.getElementById('detailNama').textContent = nama || '-';
    document.getElementById('detailJudul').textContent = judul || '-';
    document.getElementById('detailTglPinjam').textContent = tglPinjam || '-';
    document.getElementById('detailTglJatuhTempo').textContent = tglJatuhTempo || '-';
    document.getElementById('detailTglKembali').textContent = '-';
    document.getElementById('detailStatus').textContent = status || '-';
    document.getElementById('detailDenda').textContent = denda > 0 ? 'Rp ' + parseInt(denda).toLocaleString('id-ID') : '-';
    
    document.getElementById('detailModal').classList.remove('hidden');
    document.getElementById('detailModal').classList.add('flex');
}

function closeDetail() {
    document.getElementById('detailModal').classList.add('hidden');
    document.getElementById('detailModal').classList.remove('flex');
}

function confirmAmbil(id) {
    if (!confirm('Apakah Anda yakin buku sudah diambil oleh peminjam?')) {
        return;
    }
    
    fetch(`/api/pinjaman/${id}/confirm-taken`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Status berhasil diupdate! Buku sedang dipinjam.');
            location.reload();
        } else {
            alert('Gagal mengupdate status: ' + (data.message || 'Terjadi kesalahan'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengupdate status');
    });
}

function returnBook(id) {
    if (!confirm('Apakah Anda yakin buku sudah dikembalikan?')) {
        return;
    }
    
    fetch(`/api/pinjaman/${id}/return`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success || data.message) {
            alert('Buku berhasil dikembalikan!');
            location.reload();
        } else {
            alert('Gagal mengembalikan buku: ' + (data.message || 'Terjadi kesalahan'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengembalikan buku');
    });
}

// ======================================================
// FUNGSI PAGINATION
// ======================================================
let currentPagePeminjaman = 1;
const itemsPerPagePeminjaman = 5;

function updatePaginationPeminjaman() {
    const rows = Array.from(document.querySelectorAll('.pinjaman-row:not([style*="display: none"])'));
    const totalItems = rows.length;
    const totalPages = Math.ceil(totalItems / itemsPerPagePeminjaman);
    
    const paginationContainer = document.getElementById('paginationContainer');
    if (!paginationContainer) return;
    
    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        rows.forEach(row => row.style.display = '');
        return;
    }
    
    let paginationHTML = '';
    
    // Tombol Prev
    paginationHTML += `
        <button onclick="goToPagePeminjaman(${currentPagePeminjaman - 1})" 
                class="w-8 h-8 flex items-center justify-center bg-gray-300 rounded-full text-gray-700 hover:bg-gray-400 ${currentPagePeminjaman === 1 ? 'opacity-50 cursor-not-allowed' : ''}"
                ${currentPagePeminjaman === 1 ? 'disabled' : ''}>
            ‹
        </button>
    `;
    
    // Halaman
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPagePeminjaman - 1 && i <= currentPagePeminjaman + 1)) {
            if (i === currentPagePeminjaman) {
                paginationHTML += `<div class="w-7 h-7 flex items-center justify-center bg-[#A63A2D] text-white rounded-full">${i}</div>`;
            } else {
                paginationHTML += `<button onclick="goToPagePeminjaman(${i})" class="w-7 h-7 flex items-center justify-center text-gray-800 hover:bg-gray-200 rounded-full">${i}</button>`;
            }
        } else if (i === currentPagePeminjaman - 2 || i === currentPagePeminjaman + 2) {
            paginationHTML += `<span class="text-gray-800 text-lg">...</span>`;
        }
    }
    
    // Tombol Next
    paginationHTML += `
        <button onclick="goToPagePeminjaman(${currentPagePeminjaman + 1})" 
                class="w-8 h-8 flex items-center justify-center bg-gray-300 rounded-full text-gray-700 hover:bg-gray-400 ${currentPagePeminjaman === totalPages ? 'opacity-50 cursor-not-allowed' : ''}"
                ${currentPagePeminjaman === totalPages ? 'disabled' : ''}>
            ›
        </button>
    `;
    
    paginationContainer.innerHTML = paginationHTML;
    
    // Tampilkan/sembunyikan rows berdasarkan halaman
    rows.forEach((row, index) => {
        const startIndex = (currentPagePeminjaman - 1) * itemsPerPagePeminjaman;
        const endIndex = startIndex + itemsPerPagePeminjaman;
        
        if (index >= startIndex && index < endIndex) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function goToPagePeminjaman(page) {
    const rows = Array.from(document.querySelectorAll('.pinjaman-row:not([style*="display: none"])'));
    const totalPages = Math.ceil(rows.length / itemsPerPagePeminjaman);
    
    if (page < 1 || page > totalPages) return;
    
    currentPagePeminjaman = page;
    updatePaginationPeminjaman();
}

// Inisialisasi pagination saat halaman dimuat
document.addEventListener('DOMContentLoaded', () => {
    updatePaginationPeminjaman();
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
