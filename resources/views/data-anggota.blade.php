<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Data Anggota</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#f6d47f]">

    <div class="flex">
<style>
#indicator {
    position: absolute;
    left: 0;
    top: 204px;
    width: 75px;
    height: 38px;
    background-color: #F7DE68;
    border-radius: 0 20px 20px 0;
    box-shadow: 0 6px 10px rgba(0,0,0,0.35);
    transition: 0.3s ease-in-out;
    z-index: 0; /* indikator di belakang */
}

/* wrapper menu */
.menu-item {
    position: relative;
    z-index: 5; /* menu di atas indikator */
}

/* gambar icon */
.menu-item img {
    width: 26px;
    height: 26px;
}
</style>

<!-- SIDEBAR -->
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

<!-- MAIN CONTENT -->
<div class="flex-1 py-6 px-10 min-h-screen">

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

    <!-- GARIS PEMBATAS PANJANG -->
    <div class="w-full border-b-2 border-white mb-6"></div>

    <!-- TITLE -->
    <h1 class="text-3xl font-bold text-[#7c1d0f] mb-1">Data Anggota</h1>
    <p class="text-sm text-gray-700 mb-6">Pantau dan atur data anggota perpustakaan untuk memastikan informasi terbaru.</p>

    <!-- STATISTIC CARDS -->
    <div class="grid grid-cols-4 gap-6 mb-8">
        <div class="bg-[#b94a36] text-white rounded-lg p-6 text-center">
            <h2 class="text-4xl font-bold">{{ $totalAnggota ?? 0 }}</h2>
            <p>Total Anggota</p>
        </div>

        <div class="bg-[#b94a36] text-white rounded-lg p-6 text-center">
            <h2 class="text-4xl font-bold">{{ $aktif ?? 0 }}</h2>
            <p>Anggota Aktif</p>
        </div>

        <div class="bg-[#b94a36] text-white rounded-lg p-6 text-center">
            <h2 class="text-4xl font-bold">{{ $nonaktif ?? 0 }}</h2>
            <p>Anggota Nonaktif</p>
        </div>

        <div class="bg-[#b94a36] text-white rounded-lg p-6 text-center">
            <h2 class="text-4xl font-bold">{{ $anggotaBaru ?? 0 }}</h2>
            <p>Anggota Baru Bulan Ini</p>
        </div>
    </div>

    <!-- SEARCH + FILTER -->
    <div class="grid grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg px-4 py-2 flex items-center">
            <x-icon name="search" class="w-5 h-5 mr-3 text-gray-500" />
            <input type="text" id="searchInputAnggota" placeholder="Cari nama atau email..." class="w-full outline-none">
        </div>

        <div class="bg-white rounded-lg px-4 py-2 flex items-center">
            <span class="text-gray-500">Status</span>
        </div>

        <div class="bg-white rounded-lg px-4 py-2 flex items-center">
            <span class="text-gray-500">19/10/2025</span>
        </div>
    </div>

    <!-- TABEL -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-left">
                    <thead class="bg-[#b94a36] text-white">
                        <tr>
                            <th class="px-4 py-3">ID Anggota</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Denda</th>
                            <th class="px-4 py-3">Opsi</th>
                        </tr>
                    </thead>

                    <tbody id="anggotaTableBody">
                        @forelse($anggota ?? [] as $item)
                        @php
                            // Hitung total denda dari pinjaman yang belum dikembalikan atau telat
                            $totalDenda = \App\Models\Pinjaman::where('pengguna_id', $item->id)
                                ->where(function($q) {
                                    $q->where('status', 'sedang_dipinjam')
                                      ->orWhere('status', 'dikembalikan');
                                })
                                ->sum('denda');
                            
                            // Cek apakah ada pinjaman aktif
                            $hasActiveLoan = \App\Models\Pinjaman::where('pengguna_id', $item->id)
                                ->where('status', 'sedang_dipinjam')
                                ->exists();
                            
                            // Status: Aktif jika ada pinjaman aktif, atau jika pernah pinjam buku
                            $hasLoanHistory = \App\Models\Pinjaman::where('pengguna_id', $item->id)->exists();
                            $status = $hasActiveLoan ? 'Aktif' : ($hasLoanHistory ? 'Aktif' : 'Aktif');
                        @endphp
                        <tr class="border-b anggota-row hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold">AG-{{ str_pad($item->id, 3, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-4 py-3">{{ $item->created_at->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">{{ $item->nama }}</td>
                            <td class="px-4 py-3">{{ $item->email }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $hasActiveLoan ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-semibold {{ $totalDenda > 0 ? 'text-red-600' : 'text-gray-600' }}">
                                Rp {{ number_format($totalDenda, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <button onclick="editAnggota({{ $item->id }})" class="p-1 hover:bg-gray-200 rounded cursor-pointer" title="Edit">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button onclick="deleteAnggota({{ $item->id }})" class="p-1 hover:bg-gray-200 rounded cursor-pointer" title="Hapus">
                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">Tidak ada data anggota</td>
                        </tr>
                        @endforelse
                    </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <div class="flex items-center justify-center space-x-4 mt-6">
        @if(isset($anggota) && method_exists($anggota, 'links'))
            {{ $anggota->links() }}
        @endif
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
// FUNGSI PAGINATION
// ======================================================
let currentPageAnggota = 1;
const itemsPerPageAnggota = 5;

function updatePaginationAnggota() {
    const rows = Array.from(document.querySelectorAll('.anggota-row:not([style*="display: none"])'));
    const totalItems = rows.length;
    const totalPages = Math.ceil(totalItems / itemsPerPageAnggota);
    
    const paginationContainer = document.getElementById('paginationContainer');
    if (!paginationContainer) return;
    
    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        // Tampilkan semua rows jika hanya 1 halaman
        rows.forEach(row => row.style.display = '');
        return;
    }
    
    let paginationHTML = '';
    
    // Tombol Prev
    paginationHTML += `
        <button onclick="goToPageAnggota(${currentPageAnggota - 1})" 
                class="w-8 h-8 flex items-center justify-center bg-gray-300 rounded-full text-gray-700 hover:bg-gray-400 ${currentPageAnggota === 1 ? 'opacity-50 cursor-not-allowed' : ''}"
                ${currentPageAnggota === 1 ? 'disabled' : ''}>
            ‹
        </button>
    `;
    
    // Halaman
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPageAnggota - 1 && i <= currentPageAnggota + 1)) {
            if (i === currentPageAnggota) {
                paginationHTML += `<div class="w-7 h-7 flex items-center justify-center bg-[#A63A2D] text-white rounded-full">${i}</div>`;
            } else {
                paginationHTML += `<button onclick="goToPageAnggota(${i})" class="w-7 h-7 flex items-center justify-center text-gray-800 hover:bg-gray-200 rounded-full">${i}</button>`;
            }
        } else if (i === currentPageAnggota - 2 || i === currentPageAnggota + 2) {
            paginationHTML += `<span class="text-gray-800 text-lg">...</span>`;
        }
    }
    
    // Tombol Next
    paginationHTML += `
        <button onclick="goToPageAnggota(${currentPageAnggota + 1})" 
                class="w-8 h-8 flex items-center justify-center bg-gray-300 rounded-full text-gray-700 hover:bg-gray-400 ${currentPageAnggota === totalPages ? 'opacity-50 cursor-not-allowed' : ''}"
                ${currentPageAnggota === totalPages ? 'disabled' : ''}>
            ›
        </button>
    `;
    
    paginationContainer.innerHTML = paginationHTML;
    
    // Tampilkan/sembunyikan rows berdasarkan halaman
    rows.forEach((row, index) => {
        const startIndex = (currentPageAnggota - 1) * itemsPerPageAnggota;
        const endIndex = startIndex + itemsPerPageAnggota;
        
        if (index >= startIndex && index < endIndex) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function goToPageAnggota(page) {
    const rows = Array.from(document.querySelectorAll('.anggota-row:not([style*="display: none"])'));
    const totalPages = Math.ceil(rows.length / itemsPerPageAnggota);
    
    if (page < 1 || page > totalPages) return;
    
    currentPageAnggota = page;
    updatePaginationAnggota();
}

// Inisialisasi pagination saat halaman dimuat
document.addEventListener('DOMContentLoaded', () => {
    updatePaginationAnggota();
});
</script>


<script>
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

// --- SEARCH, STATUS, TANGGAL FILTER ---
const searchInput = document.getElementById('searchInputAnggota') || document.querySelector('input[placeholder*="Cari"]');
const statusFilter = document.querySelectorAll('.grid-cols-3 div:nth-child(2)');
const dateFilter = document.querySelectorAll('.grid-cols-3 div:nth-child(3)');
const tableRows = document.querySelectorAll('tbody tr.anggota-row');

// Buat dropdown status dan input tanggal
statusFilter[0].innerHTML = `
    <select id="statusSelect" class="w-full outline-none text-gray-500">
        <option value="">Semua Status</option>
        <option value="Aktif">Aktif</option>
        <option value="Nonaktif">Nonaktif</option>
        <option value="Menunggu Konfirmasi">Menunggu Konfirmasi</option>
    </select>
`;

dateFilter[0].innerHTML = `
    <input type="date" id="dateSelect" class="w-full outline-none text-gray-500">
`;

// Fungsi filter tabel dengan algoritma string matching
let searchTimeout;
function filterTable() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        executeSearch();
    }, 150);
}

function executeSearch() {
    const searchValue = searchInput.value.trim().toLowerCase();
    const statusValue = document.getElementById('statusSelect').value;
    const dateValue = document.getElementById('dateSelect').value; // format yyyy-mm-dd

    if (!searchValue && !statusValue && !dateValue) {
        tableRows.forEach(row => {
            row.style.display = '';
        });
        return;
    }

    // Pilih algoritma terbaik berdasarkan panjang pattern
    const algorithm = selectBestAlgorithm(searchValue);

    tableRows.forEach(row => {
        const name = row.cells[2].textContent.toLowerCase(); // kolom Nama
        const email = row.cells[3].textContent.toLowerCase(); // kolom Email
        const status = row.cells[4].textContent.trim(); // kolom Status
        const tanggalCell = row.cells[1].textContent; // kolom Tanggal
        const tanggal = new Date(tanggalCell.split('/').reverse().join('-')).toISOString().split('T')[0]; // convert ke yyyy-mm-dd

        let matchesSearch = true;
        if (searchValue) {
            const nameMatches = searchWithAlgorithm(name, searchValue, algorithm);
            const emailMatches = searchWithAlgorithm(email, searchValue, algorithm);
            matchesSearch = nameMatches.length > 0 || emailMatches.length > 0;
        }

        const matchesStatus = statusValue === "" || status.includes(statusValue);
        const matchesDate = dateValue === "" || tanggal === dateValue;

        row.style.display = (matchesSearch && matchesStatus && matchesDate) ? "" : "none";
    });
    
    // Update pagination setelah filter
    currentPageAnggota = 1; // Reset ke halaman 1 setelah filter
    updatePaginationAnggota();
}

// Event listener
searchInput.addEventListener('input', filterTable);
document.getElementById('statusSelect').addEventListener('change', filterTable);
document.getElementById('dateSelect').addEventListener('change', filterTable);
</script>

<!-- Update kolom Opsi di tbody dengan icon -->
<script>
// Tambahkan icon "Detail" dan "Hapus" dengan event listener
document.querySelectorAll('tbody tr').forEach((row) => {
    const opsiCell = row.querySelector('td:last-child');
    opsiCell.innerHTML = `
        <img src="{{ asset('images/icon-edit-opsi.png') }}" class="w-5 cursor-pointer mr-2" title="Detail">
        <img src="{{ asset('images/icon-delete-opsi.png') }}" class="w-5 cursor-pointer" title="Hapus">
    `;

    // EVENT DETAIL
    opsiCell.querySelector('img:nth-child(1)').addEventListener('click', () => {
        const data = {
            id: row.cells[0].textContent.trim(),
            tanggal: row.cells[1].textContent.trim(),
            nama: row.cells[2].textContent.trim(),
            email: row.cells[3].textContent.trim(),
            status: row.cells[4].textContent.trim(),
            denda: row.cells[5].textContent.trim()
        };
        alert(`Detail Anggota:\n\nID: ${data.id}\nTanggal Daftar: ${data.tanggal}\nNama: ${data.nama}\nEmail: ${data.email}\nStatus: ${data.status}\nTotal Denda: ${data.denda}`);
    });

    // EVENT DELETE
    opsiCell.querySelector('img:nth-child(2)').addEventListener('click', () => {
        const confirmDelete = confirm(`Apakah Anda yakin ingin menghapus anggota "${row.cells[2].textContent}"?`);
        if(confirmDelete) {
            row.remove();
            alert('Data berhasil dihapus.');
        }
    });
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

</div>
<!-- End flex container -->

</body>
</html>
