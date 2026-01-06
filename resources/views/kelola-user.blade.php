<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kelola User</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#f6d47f] flex">

<style>
#indicator {
    position: absolute;
    left: 0;
    top: 522px; /* POSISI MENU USER */
    width: 75px;
    height: 38px;
    background-color: #F7DE68;
    border-radius: 0 20px 20px 0;
    box-shadow: 0 6px 10px rgba(0,0,0,0.35);
    transition: 0.3s ease-in-out;
    z-index: 0;
}

.menu-item { position: relative; z-index: 5; }
.menu-item img { width: 26px; height: 26px; }

.section-wrapper { width: 100%; padding-left: 25px; padding-right: 40px; margin-top: 20px; }
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
        <button id="messageBtn" onclick="toggleMessagePopup()" class="relative">
            <x-icon name="email" class="w-6 h-6 text-black hover:opacity-80 cursor-pointer" />
            <span id="messageBadge" class="absolute -top-1 -right-1 bg-blue-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
        </button>
        <button id="notifBtn" onclick="toggleNotifPopup()" class="relative">
            <x-icon name="notification" class="w-6 h-6 text-black hover:opacity-80 cursor-pointer" />
            <span id="notifBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
        </button>
        <div class="border-l border-white h-6"></div>

        <div class="flex items-center space-x-2">
            <div class="bg-[#717BFF] w-10 h-10 rounded-full flex items-center justify-center text-white font-bold">FA</div>
            <span class="text-black font-medium">Admin</span>
        </div>
    </div>

    <div class="w-full border-b-2 border-white mb-6"></div>

    <!-- ============================ MAIN CONTENT ============================ -->
    <div class="section-wrapper">
        <h1 class="text-4xl font-bold">Kelola User !</h1>
        <p class="text-gray-700 mt-1">Atur daftar admin, staff, maupun user mahasiswa.</p>

        <!-- STATISTIK -->
        <div class="flex justify-center gap-7 mt-8">
            <div class="bg-[#A24731] w-56 h-28 rounded-xl text-white shadow-xl flex flex-col justify-center items-center">
                <h2 class="text-4xl font-bold">52</h2>
                <p>Total User</p>
            </div>
            <div class="bg-[#A24731] w-56 h-28 rounded-xl text-white shadow-xl flex flex-col justify-center items-center">
                <h2 class="text-4xl font-bold">5</h2>
                <p>Admin</p>
            </div>
            <div class="bg-[#A24731] w-56 h-28 rounded-xl text-white shadow-xl flex flex-col justify-center items-center">
                <h2 class="text-4xl font-bold">10</h2>
                <p>Staff</p>
            </div>
            <div class="bg-[#A24731] w-56 h-28 rounded-xl text-white shadow-xl flex flex-col justify-center items-center">
                <h2 class="text-4xl font-bold">37</h2>
                <p>User (Mahasiswa)</p>
            </div>
        </div>

        <!-- FORM TAMBAH USER -->
        <div class="bg-white w-[97%] shadow-lg rounded-lg p-6 mt-10">
            <h2 class="text-xl font-bold mb-4 text-[#A63A2D]">Tambah User Baru</h2>

            <div class="grid grid-cols-3 gap-5">
                <input type="text" placeholder="Nama Lengkap" class="p-3 rounded-lg shadow border">
                <input type="text" placeholder="Username" class="p-3 rounded-lg shadow border">
                <select class="p-3 rounded-lg shadow border">
                    <option>Pilih Role</option>
                    <option>Admin</option>
                    <option>Staff</option>
                    <option>User (Mahasiswa)</option>
                </select>
                <input type="text" placeholder="Email" class="p-3 rounded-lg shadow border">
                <input type="password" placeholder="Password" class="p-3 rounded-lg shadow border">
                <button class="bg-[#2476FF] text-white rounded-lg font-bold py-2 px-4">Tambah</button>
            </div>
        </div>

        <!-- FILTER -->
        <div class="flex justify-center gap-4 mt-8">
            <input type="text" id="searchInput" placeholder="Search user..." class="w-80 p-3 rounded-lg shadow" onkeyup="filterTable()">
            <select id="roleFilter" class="p-3 w-48 rounded-lg shadow" onchange="filterTable()">
                <option value="">Filter Role</option>
                <option value="Admin">Admin</option>
                <option value="Staff">Staff</option>
                <option value="User">User (Mahasiswa)</option>
            </select>
            <button class="bg-[#2476FF] text-white px-6 py-3 rounded-lg font-bold" onclick="filterTable()">Search</button>
        </div>
    </div>

    <!-- TABEL USER -->
    <div class="mt-10 bg-white rounded-lg shadow overflow-hidden w-[97%]">
        <div id="noResults" class="hidden text-center py-8 text-gray-500">
            <p>Tidak ada user yang ditemukan.</p>
        </div>
        <table class="w-full text-left">
            <thead class="bg-[#b54a38] text-white">
                <tr>
                    <th class="px-6 py-4">ID</th>
                    <th class="px-6 py-4">Nama</th>
                    <th class="px-6 py-4">Username</th>
                    <th class="px-6 py-4">Email</th>
                    <th class="px-6 py-4">Role</th>
                    <th class="px-6 py-4">Aksi</th>
                </tr>
            </thead>
            <tbody id="userTableBody">
                <tr class="border-b user-row" data-nama="fayza azzahra" data-username="fayza12" data-email="fayza@gmail.com" data-role="Admin">
                    <td class="px-6 py-3">USR-001</td>
                    <td class="px-6 py-3">Fayza Azzahra</td>
                    <td class="px-6 py-3">fayza12</td>
                    <td class="px-6 py-3">fayza@gmail.com</td>
                    <td class="px-6 py-3 font-semibold text-blue-600">Admin</td>
                    <td class="px-6 py-3 flex gap-4">
                        <img src="{{ asset('icons/edit.png') }}" class="w-5">
                        <img src="{{ asset('icons/delete.png') }}" class="w-5">
                    </td>
                </tr>

                <tr class="border-b user-row" data-nama="nuriyanti" data-username="nuriyanti14" data-email="nuriyanti@gmail.com" data-role="Staff">
                    <td class="px-6 py-3">USR-002</td>
                    <td class="px-6 py-3">Nuriyanti</td>
                    <td class="px-6 py-3">Nuriyanti14</td>
                    <td class="px-6 py-3">Nuriyanti@gmail.com</td>
                    <td class="px-6 py-3 font-semibold text-green-600">Staff</td>
                    <td class="px-6 py-3 flex gap-4">
                        <img src="{{ asset('icons/edit.png') }}" class="w-5">
                        <img src="{{ asset('icons/delete.png') }}" class="w-5">
                    </td>
                </tr>

                <tr class="border-b user-row" data-nama="kaysa dzikrya" data-username="kaysa11" data-email="kaysa@gmail.com" data-role="User">
                    <td class="px-6 py-3">USR-003</td>
                    <td class="px-6 py-3">Kaysa dzikrya</td>
                    <td class="px-6 py-3">kaysa11</td>
                    <td class="px-6 py-3">kaysa@gmail.com</td>
                    <td class="px-6 py-3 font-semibold text-purple-600">User</td>
                    <td class="px-6 py-3 flex gap-4">
                        <img src="{{ asset('icons/edit.png') }}" class="w-5">
                        <img src="{{ asset('icons/delete.png') }}" class="w-5">
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <div id="paginationContainer" class="flex items-center justify-center space-x-4 mt-6">
        <!-- Pagination akan di-generate oleh JavaScript -->
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
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        executeSearch();
    }, 150);
}

function executeSearch() {
    const searchInput = document.getElementById('searchInput');
    const roleFilter = document.getElementById('roleFilter');
    const rows = document.querySelectorAll('.user-row');
    const noResults = document.getElementById('noResults');
    
    if (!searchInput || !rows.length) return;
    
    const searchValue = searchInput.value.trim().toLowerCase();
    const roleValue = roleFilter ? roleFilter.value : '';
    let visibleCount = 0;

    // Jika tidak ada input search, tampilkan semua berdasarkan filter
    if (!searchValue && !roleValue) {
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
        const username = row.getAttribute('data-username') || '';
        const email = row.getAttribute('data-email') || '';
        const role = row.getAttribute('data-role') || '';
        
        let matchesSearch = true;
        if (searchValue) {
            const namaMatches = searchWithAlgorithm(nama, searchValue, algorithm);
            const usernameMatches = searchWithAlgorithm(username, searchValue, algorithm);
            const emailMatches = searchWithAlgorithm(email, searchValue, algorithm);
            matchesSearch = namaMatches.length > 0 || usernameMatches.length > 0 || emailMatches.length > 0;
        }

        const matchesRole = !roleValue || role === roleValue;
        
        if (matchesSearch && matchesRole) {
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
    updatePaginationUser();
}

// ======================================================
// FUNGSI PAGINATION
// ======================================================
let currentPageUser = 1;
const itemsPerPageUser = 5;

function updatePaginationUser() {
    const rows = Array.from(document.querySelectorAll('.user-row:not([style*="display: none"])'));
    const totalItems = rows.length;
    const totalPages = Math.ceil(totalItems / itemsPerPageUser);
    
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
        <button onclick="goToPageUser(${currentPageUser - 1})" 
                class="w-8 h-8 flex items-center justify-center bg-gray-300 rounded-full text-gray-700 hover:bg-gray-400 ${currentPageUser === 1 ? 'opacity-50 cursor-not-allowed' : ''}"
                ${currentPageUser === 1 ? 'disabled' : ''}>
            ‹
        </button>
    `;
    
    // Halaman
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPageUser - 1 && i <= currentPageUser + 1)) {
            if (i === currentPageUser) {
                paginationHTML += `<div class="w-7 h-7 flex items-center justify-center bg-[#A63A2D] text-white rounded-full">${i}</div>`;
            } else {
                paginationHTML += `<button onclick="goToPageUser(${i})" class="w-7 h-7 flex items-center justify-center text-gray-800 hover:bg-gray-200 rounded-full">${i}</button>`;
            }
        } else if (i === currentPageUser - 2 || i === currentPageUser + 2) {
            paginationHTML += `<span class="text-gray-800 text-lg">...</span>`;
        }
    }
    
    // Tombol Next
    paginationHTML += `
        <button onclick="goToPageUser(${currentPageUser + 1})" 
                class="w-8 h-8 flex items-center justify-center bg-gray-300 rounded-full text-gray-700 hover:bg-gray-400 ${currentPageUser === totalPages ? 'opacity-50 cursor-not-allowed' : ''}"
                ${currentPageUser === totalPages ? 'disabled' : ''}>
            ›
        </button>
    `;
    
    paginationContainer.innerHTML = paginationHTML;
    
    // Tampilkan/sembunyikan rows berdasarkan halaman
    rows.forEach((row, index) => {
        const startIndex = (currentPageUser - 1) * itemsPerPageUser;
        const endIndex = startIndex + itemsPerPageUser;
        
        if (index >= startIndex && index < endIndex) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function goToPageUser(page) {
    const rows = Array.from(document.querySelectorAll('.user-row:not([style*="display: none"])'));
    const totalPages = Math.ceil(rows.length / itemsPerPageUser);
    
    if (page < 1 || page > totalPages) return;
    
    currentPageUser = page;
    updatePaginationUser();
}

// Inisialisasi pagination saat halaman dimuat
document.addEventListener('DOMContentLoaded', () => {
    updatePaginationUser();
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

<!-- POPUP PESAN -->
<div id="messagePopup" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-[32rem] max-h-[80vh] overflow-y-auto relative">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-[#A63A2D]">Pesan</h3>
            <div class="flex gap-2">
                <button onclick="toggleMessagePopup()" class="text-gray-500 hover:text-gray-900 text-2xl">&times;</button>
            </div>
        </div>
        <div class="mb-4 bg-gray-50 rounded-xl p-3">
            <p class="text-sm font-semibold text-[#A63A2D] mb-2">Kirim Pesan ke Pengguna</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                <select id="recipientSelect" class="border rounded p-2 text-sm md:col-span-1"></select>
                <input id="composeIsi" type="text" class="border rounded p-2 text-sm md:col-span-2" placeholder="Tulis pesan singkat..." />
            </div>
            <div class="flex justify-end mt-2">
                <button class="px-3 py-1 bg-[#A63A2D] text-white rounded text-xs" onclick="sendMessage()">Kirim</button>
            </div>
        </div>
        <ul id="messageList" class="space-y-3">
            <li class="p-3 text-center text-gray-500">Memuat pesan...</li>
        </ul>
    </div>
</div>

<script>
function toggleMessagePopup() {
    const popup = document.getElementById('messagePopup');
    popup.classList.toggle('hidden');
    popup.classList.toggle('flex');
    if (!popup.classList.contains('hidden')) {
        loadRecipients();
        loadMessages();
    }
}
function loadMessages() {
    fetch('/api/pesan?only_inbox=1', {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        const list = document.getElementById('messageList');
        const badge = document.getElementById('messageBadge');
        if (badge) {
            const count = data.unread_count || 0;
            badge.textContent = count;
            badge.classList.toggle('hidden', count === 0);
        }
        if (Array.isArray(data.data) && data.data.length > 0) {
            list.innerHTML = data.data.map(m => {
                const confirmed = m.status === 'confirmed';
                const status = confirmed ? '<span class="text-green-700 text-xs ml-2">Dikonfirmasi</span>' : '';
                return `
                    <li class="p-3 bg-gray-100 rounded-xl shadow hover:shadow-md">
                        <p class="text-sm font-semibold">Pesan ${status}</p>
                        <p class="text-sm text-gray-700 mt-1">${m.isi}</p>
                        <div class="flex justify-end mt-2 text-xs gap-4">
                            ${!confirmed ? `<button class="text-green-700" onclick="confirmMessage(${m.id})">Konfirmasi</button>` : ''}
                            <button class="text-blue-700" onclick="replyMessage(${m.id})">Balas</button>
                        </div>
                    </li>
                `;
            }).join('');
        } else {
            list.innerHTML = '<li class="p-3 text-center text-gray-500">Tidak ada pesan</li>';
        }
    })
    .catch(() => {
        const list = document.getElementById('messageList');
        list.innerHTML = '<li class="p-3 text-center text-red-600">Gagal memuat pesan</li>';
    });
}
function confirmMessage(id) {
    fetch(`/api/pesan/${id}/confirm`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json'
        }
    }).then(() => loadMessages());
}
function replyMessage(id) {
    const isi = prompt('Tulis balasan:');
    if (!isi) return;
    fetch(`/api/pesan/${id}/reply`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ isi })
    }).then(() => loadMessages());
}
function loadRecipients() {
    fetch('/api/pengguna', {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(users => {
        const sel = document.getElementById('recipientSelect');
        if (!sel) return;
        const onlyPengguna = Array.isArray(users) ? users.filter(u => u.peran === 'pengguna') : [];
        sel.innerHTML = onlyPengguna.map(u => `<option value="${u.id}">${u.nama} (${u.email})</option>`).join('');
    });
}
function sendMessage() {
    const sel = document.getElementById('recipientSelect');
    const isi = document.getElementById('composeIsi');
    if (!sel || !isi) return;
    const penerima_id = sel.value;
    const text = isi.value.trim();
    if (!penerima_id || !text) return alert('Pilih penerima dan isi pesan');
    fetch('/api/pesan', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ penerima_id, isi: text })
    })
    .then(res => {
        if (!res.ok) throw new Error();
        isi.value = '';
        loadMessages();
    })
    .catch(() => alert('Gagal mengirim pesan'));
}
document.addEventListener('DOMContentLoaded', () => {
    loadMessages();
});
document.getElementById('messagePopup')?.addEventListener('click', function(e) {
    if (e.target === this) toggleMessagePopup();
});
</script>

</body>
</html>
