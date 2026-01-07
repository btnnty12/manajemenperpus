<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Staff' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="{{ asset('js/string-matching-service.js') }}"></script>
    
    <style>
        .menu-item {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            padding: 10px 0;
            transition: all 0.2s ease;
        }

        .menu-item:hover {
            opacity: 0.8;
        }

        .menu-item.active .icon-wrapper {
            background-color: #F7DE68;
            border-radius: 12px;
            padding: 8px;
        }

        .icon-wrapper {
            transition: all 0.2s ease;
            padding: 8px;
            border-radius: 12px;
        }
    </style>
</head>
<body class="min-h-screen text-gray-800" style="background: linear-gradient(180deg,#f7d77a 0%, #f6d99f 30%, #fff7ec 100%);">
<div class="flex">
    <!-- SIDEBAR -->
    <div class="w-20 bg-[#a63a2d] min-h-screen flex flex-col items-center py-6 relative">
        <!-- MENU ATAS -->
        <div class="flex flex-col items-center space-y-8 pt-8 w-full">
            <a href="{{ route('staff.dashboard') }}" class="menu-item" aria-label="Dashboard" data-route="staff.dashboard">
                <div class="icon-wrapper">
                    <x-icon name="home" class="w-7 h-7 text-white" />
                </div>
            </a>
            <a href="{{ route('staff.kelola-buku') }}" class="menu-item" aria-label="Kelola Buku" data-route="staff.kelola-buku">
                <div class="icon-wrapper">
                    <x-icon name="buku" class="w-7 h-7 text-white" />
                </div>
            </a>
            <a href="{{ route('staff.laporan-peminjaman') }}" class="menu-item" aria-label="Laporan Peminjaman" data-route="staff.laporan-peminjaman">
                <div class="icon-wrapper">
                    <x-icon name="grafik" class="w-7 h-7 text-white" />
                </div>
            </a>
            <a href="{{ route('staff.data-anggota') }}" class="menu-item" aria-label="Data Anggota" data-route="staff.data-anggota">
                <div class="icon-wrapper">
                    <x-icon name="anggota" class="w-7 h-7 text-white" />
                </div>
            </a>
            <a href="{{ route('staff.pengaturan') }}" class="menu-item" aria-label="Pengaturan" data-route="staff.pengaturan">
                <div class="icon-wrapper">
                    <x-icon name="setting" class="w-7 h-7 text-white" />
                </div>
            </a>
        </div>

        <!-- LOGOUT PALING BAWAH -->
        <a href="{{ url('/logout') }}" class="menu-item mt-auto mb-4" aria-label="Logout">
            <div class="icon-wrapper">
                <x-icon name="logout" class="w-7 h-7 text-white" />
            </div>
        </a>
    </div>

    <!-- MAIN -->
    <div class="flex-1 py-6 px-8">
        <!-- TOPBAR -->
        <div class="flex justify-end items-center w-full py-4 px-6 space-x-6 relative">
            <button id="notifBtn" onclick="toggleNotifPopup()" class="relative">
                <x-icon name="notification" class="w-6 h-6 text-black hover:opacity-80 cursor-pointer" />
                <span id="notifBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
            </button>
            <div id="staffProfileBtn" class="flex items-center space-x-2 cursor-pointer">
                <div class="bg-[#717BFF] w-10 h-10 rounded-full flex items-center justify-center text-white font-bold overflow-hidden">
                    @if(Auth::check() && Auth::user()->foto)
                        <img src="{{ asset('storage/' . Auth::user()->foto) }}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.parentElement.innerHTML='{{ strtoupper(substr(Auth::user()->nama ?? 'ST', 0, 2)) }}'">
                    @else
                        {{ strtoupper(substr(Auth::user()->nama ?? 'ST', 0, 2)) }}
                    @endif
                </div>
                <div class="text-left">
                    <div class="text-black font-medium leading-tight">{{ Auth::user()->nama ?? 'Staff' }}</div>
                    <div class="text-xs text-gray-600">Staff</div>
                </div>
            </div>
            <div id="staffProfileDropdown" class="hidden absolute top-14 right-6 w-40 bg-white shadow-xl rounded-xl py-2 z-50">
                <a href="{{ route('staff.pengaturan') }}" class="block px-4 py-2 text-sm hover:bg-gray-100">Pengaturan</a>
                <a href="/logout" class="block px-4 py-2 text-sm hover:bg-gray-100 text-red-600 font-semibold">Logout</a>
            </div>
        </div>

        <div class="w-full border-b-2 border-white mb-6"></div>

        <main class="pb-10">
            {{ $slot }}
        </main>
    </div>
</div>

<script>
    // Set active state pada menu berdasarkan route saat ini
    const currentPath = window.location.pathname;
    const menuItems = document.querySelectorAll('.menu-item[data-route]');

    // Set menu aktif berdasarkan path
    menuItems.forEach((item) => {
        const route = item.getAttribute('data-route');
        
        // Cek apakah route ini sesuai dengan path saat ini
        let isActive = false;
        if (currentPath === '/staff' && route === 'staff.dashboard') {
            isActive = true;
        } else if (currentPath.includes('/staff/kelola-buku') && route === 'staff.kelola-buku') {
            isActive = true;
        } else if (currentPath.includes('/staff/laporan-peminjaman') && route === 'staff.laporan-peminjaman') {
            isActive = true;
        } else if (currentPath.includes('/staff/data-anggota') && route === 'staff.data-anggota') {
            isActive = true;
        } else if (currentPath.includes('/staff/pengaturan') && route === 'staff.pengaturan') {
            isActive = true;
        }

        if (isActive) {
            item.classList.add('active');
        }
    });
</script>
<script>
    const staffProfileBtn = document.getElementById('staffProfileBtn');
    const staffProfileDropdown = document.getElementById('staffProfileDropdown');
    if (staffProfileBtn && staffProfileDropdown) {
        staffProfileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            staffProfileDropdown.classList.toggle('hidden');
        });
        document.addEventListener('click', () => {
            staffProfileDropdown.classList.add('hidden');
        });
    }
</script>

<!-- POPUP NOTIFIKASI STAFF -->
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

<script>
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
        if (data.unread_count > 0) {
            badge.textContent = data.unread_count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
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
    .catch(() => {});
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
document.addEventListener('DOMContentLoaded', () => {
    loadNotifikasi();
    notifInterval = setInterval(loadNotifikasi, 30000);
});
document.getElementById('notifPopup').addEventListener('click', function(e) {
    if (e.target === this) {
        toggleNotifPopup();
    }
});
</script>
</body>
</html>
