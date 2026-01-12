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

    <!-- ============================ MAIN CONTENT ============================ -->
    @php
        use App\Models\Pengguna;

        $perPage = request('per_page', 10);
        $q = trim(request('q', ''));
        $roleFilter = request('role', '');

        $userQuery = Pengguna::query();
        if ($q !== '') {
            $userQuery->where(function($s) use ($q) {
                $s->where('nama', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('username', 'like', "%{$q}%");
            });
        }
        if ($roleFilter !== '') {
            // map display roles to stored values if needed
            $map = ['Admin' => 'admin', 'Staff' => 'staff', 'User' => 'pengguna'];
            $value = $map[$roleFilter] ?? $roleFilter;
            $userQuery->where('peran', $value);
        }
        $users = $userQuery->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
    @endphp

    <div class="section-wrapper">
        <h1 class="text-4xl font-bold">Kelola User !</h1>
        <p class="text-gray-700 mt-1">Atur daftar admin, staff, maupun pengguna.</p>

        <!-- STATISTIK -->
        <div class="flex justify-center gap-7 mt-8">
            <div class="bg-[#A24731] w-56 h-28 rounded-xl text-white shadow-xl flex flex-col justify-center items-center">
                <h2 class="text-4xl font-bold" id="statTotal">{{ \App\Models\Pengguna::count() }}</h2>
                <p>Total User</p>
            </div>
            <div class="bg-[#A24731] w-56 h-28 rounded-xl text-white shadow-xl flex flex-col justify-center items-center">
                <h2 class="text-4xl font-bold" id="statAdmin">{{ \App\Models\Pengguna::where('peran', 'admin')->count() }}</h2>
                <p>Admin</p>
            </div>
            <div class="bg-[#A24731] w-56 h-28 rounded-xl text-white shadow-xl flex flex-col justify-center items-center">
                <h2 class="text-4xl font-bold" id="statStaff">{{ \App\Models\Pengguna::where('peran', 'staff')->count() }}</h2>
                <p>Staff</p>
            </div>
            <div class="bg-[#A24731] w-56 h-28 rounded-xl text-white shadow-xl flex flex-col justify-center items-center">
                <h2 class="text-4xl font-bold" id="statPengguna">{{ \App\Models\Pengguna::where('peran', 'pengguna')->count() }}</h2>
                <p>Pengguna</p>
            </div>
        </div>

        <!-- FORM TAMBAH USER -->
        <div class="bg-white w-[97%] shadow-lg rounded-lg p-6 mt-10">
            <h2 class="text-xl font-bold mb-4 text-[#A63A2D]">Tambah User Baru</h2>
            
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

            <form id="formTambahUser" action="{{ route('kelola-user.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <input type="text" name="nama" placeholder="Nama Lengkap" class="p-3 rounded-lg shadow border" required>
                    <select name="peran" class="p-3 rounded-lg shadow border" required>
                        <option value="">Pilih Role</option>
                        <option value="admin">Admin</option>
                        <option value="staff">Staff</option>
                        <option value="pengguna">Pengguna</option>
                    </select>
                    <input type="email" name="email" placeholder="Email" class="p-3 rounded-lg shadow border" required>
                    <input type="password" name="password" placeholder="Password" class="p-3 rounded-lg shadow border md:col-span-2" required>
                </div>
                <div class="mt-4">
                    <button type="submit" class="bg-[#2476FF] text-white rounded-lg font-bold py-2 px-4">Tambah</button>
                </div>
            </form>
        </div>

        <!-- FILTER -->
        <form id="filterForm" method="GET" class="flex justify-center gap-4 mt-8">
            <input type="text" name="q" id="searchInput" placeholder="Search user..." value="{{ request('q') }}" class="w-80 p-3 rounded-lg shadow" oninput="debouncedSubmit()">
            <select name="role" id="roleFilter" class="p-3 w-48 rounded-lg shadow" onchange="debouncedSubmit()">
                <option value="" {{ request('role') == '' ? 'selected' : '' }}>Filter Role</option>
                <option value="Admin" {{ request('role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                <option value="Staff" {{ request('role') == 'Staff' ? 'selected' : '' }}>Staff</option>
                <option value="User" {{ request('role') == 'User' ? 'selected' : '' }}>Pengguna</option>
            </select>

            <select name="per_page" id="perPageSelect" class="p-3 w-48 rounded-lg shadow" onchange="debouncedSubmit()">
                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 / halaman</option>
                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 / halaman</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 / halaman</option>
            </select>
        </form>
    </div>

    <!-- TABEL USER -->
    <div class="mt-10 bg-white rounded-lg shadow overflow-hidden w-[97%]">
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
                @forelse($users as $user)
                <tr class="user-row odd:bg-gray-50" data-nama="{{ strtolower($user->nama) }}" data-username="{{ strtolower($user->username ?? '') }}" data-email="{{ strtolower($user->email) }}" data-role="{{ ucfirst($user->peran) }}">
                    <td class="px-6 py-4">U-{{ str_pad($user->id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-6 py-4">{{ $user->nama }}</td>
                    <td class="px-6 py-4">{{ $user->username ?? '-' }}</td>
                    <td class="px-6 py-4">{{ $user->email }}</td>
                    <td class="px-6 py-4">{{ ucfirst($user->peran) }}</td>
                    <td class="px-6 py-4">
                        <div class="flex gap-2">
                            <button onclick="editUser({{ $user->id }})" class="bg-blue-600 text-white px-3 py-1 rounded text-xs">Edit</button>
                            <button onclick="deleteUser({{ $user->id }}, '{{ addslashes($user->nama) }}')" class="bg-red-600 text-white px-3 py-1 rounded text-xs">Hapus</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">Tidak ada user yang ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <div id="paginationContainer" class="flex items-center justify-center space-x-4 mt-6">
        {{ $users->appends(request()->query())->links() }}

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
    loadStats();
    loadUsers();
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

// Integrasi API Pengguna: stats, load, edit, delete, tambah
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

async function loadStats() {
    try {
        const res = await fetch('/api/pengguna/stats', {
            headers: { 'Accept': 'application/json' }
        });
        if (!res.ok) return;
        const s = await res.json();
        const elTotal = document.getElementById('statTotal');
        const elAdmin = document.getElementById('statAdmin');
        const elStaff = document.getElementById('statStaff');
        const elPengguna = document.getElementById('statPengguna');
        if (elTotal) elTotal.textContent = s.total ?? 0;
        if (elAdmin) elAdmin.textContent = s.admin ?? 0;
        if (elStaff) elStaff.textContent = s.staff ?? 0;
        if (elPengguna) elPengguna.textContent = s.pengguna ?? 0;
    } catch (e) {
        console.error('Gagal memuat statistik', e);
    }
}

function roleLabel(peran) {
    if (peran === 'admin') return { label: 'Admin', cls: 'text-blue-600' };
    if (peran === 'staff') return { label: 'Staff', cls: 'text-green-600' };
    return { label: 'User', cls: 'text-purple-600' };
}

function padId(id) {
    const s = String(id);
    return 'USR-' + s.padStart(3, '0');
}

function emailLocal(email) {
    if (!email) return '';
    return String(email).split('@')[0];
}

function renderUsers(users) {
    const tbody = document.getElementById('userTableBody');
    if (!tbody) return;
    tbody.innerHTML = '';
    users.forEach(u => {
        const rl = roleLabel(u.peran);
        const tr = document.createElement('tr');
        tr.className = 'border-b user-row';
        tr.setAttribute('data-nama', u.nama || '');
        tr.setAttribute('data-username', emailLocal(u.email));
        tr.setAttribute('data-email', u.email || '');
        tr.setAttribute('data-role', rl.label);
        tr.innerHTML = `
            <td class="px-6 py-3">${padId(u.id)}</td>
            <td class="px-6 py-3">${u.nama || ''}</td>
            <td class="px-6 py-3">${emailLocal(u.email)}</td>
            <td class="px-6 py-3">${u.email || ''}</td>
            <td class="px-6 py-3 font-semibold ${rl.cls}">${rl.label}</td>
            <td class="px-6 py-3 flex gap-4">
                <button class="btn-edit" data-id="${u.id}" title="Edit">
                    <x-icon name="edit" class="w-5 h-5 text-gray-700" />
                </button>
                <button class="btn-delete" data-id="${u.id}" title="Hapus">
                    <x-icon name="delete" class="w-5 h-5 text-gray-700" />
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
    attachActionHandlers();
    updatePaginationUser();
    const noResults = document.getElementById('noResults');
    if (noResults) {
        if (users.length === 0) noResults.classList.remove('hidden');
        else noResults.classList.add('hidden');
    }
}

async function loadUsers() {
    try {
        const res = await fetch('/api/pengguna?per_page=200', { headers: { 'Accept': 'application/json' }});
        if (!res.ok) return;
        const json = await res.json();
        const data = Array.isArray(json.data) ? json.data : (Array.isArray(json) ? json : []);
        renderUsers(data);
    } catch (e) {
        console.error('Gagal memuat pengguna', e);
    }
}

function attachActionHandlers() {
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.getAttribute('data-id');
            if (!confirm('Hapus pengguna ini?')) return;
            try {
                const res = await fetch(`/api/pengguna/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                    }
                });
                if (res.ok) {
                    await loadUsers();
                    await loadStats();
                }
            } catch (e) {
                console.error('Gagal menghapus pengguna', e);
            }
        });
    });

    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.getAttribute('data-id');
            const namaBaru = prompt('Nama baru (kosongkan untuk skip):');
            const emailBaru = prompt('Email baru (kosongkan untuk skip):');
            const peranBaru = prompt('Peran (admin/staff/pengguna), kosongkan untuk skip:');
            const payload = {};
            if (namaBaru) payload.nama = namaBaru;
            if (emailBaru) payload.email = emailBaru;
            if (peranBaru) payload.peran = peranBaru;
            if (Object.keys(payload).length === 0) return;
            try {
                const res = await fetch(`/api/pengguna/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                    },
                    body: JSON.stringify(payload),
                });
                if (res.ok) {
                    await loadUsers();
                    await loadStats();
                }
            } catch (e) {
                console.error('Gagal memperbarui pengguna', e);
            }
        });
    });
}

const formTambah = document.getElementById('formTambahUser');
if (formTambah) {
    formTambah.addEventListener('submit', async (ev) => {
        ev.preventDefault();
        const fd = new FormData(formTambah);
        const payload = {
            nama: fd.get('nama'),
            email: fd.get('email'),
            password: fd.get('password'),
            peran: fd.get('peran'),
        };
        try {
            const res = await fetch(formTambah.getAttribute('action'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify(payload),
            });
            if (res.ok) {
                formTambah.reset();
                await loadUsers();
                await loadStats();
                alert('Pengguna berhasil ditambahkan');
            } else {
                const err = await res.json().catch(() => ({}));
                alert('Gagal menambah pengguna: ' + (err.message || 'Error'));
            }
        } catch (e) {
            console.error('Gagal menambah pengguna', e);
            alert('Terjadi kesalahan jaringan');
        }
    });
}
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
