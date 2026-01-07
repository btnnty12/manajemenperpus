<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Beranda - Perpustakaan</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/home.css">

</head>

<body class="bg-gradient-to-b from-yellow-200 to-yellow-300 min-h-screen flex">

<aside id="sidebar"
       class="w-20 bg-[#C34722] text-white flex flex-col items-center py-6 shadow-lg relative">

    <div id="menuWrapper" class="relative flex flex-col items-center space-y-8 flex-1">

        <!-- Highlight PUTIH -->
        <div id="highlight"
             class="absolute left-0 w-16 h-12 bg-white/30 rounded-xl transition-all duration-300 shadow-md -z-10"
             style="top: 0;">
        </div>

        <!-- Icons -->
        <button onclick="window.location.href='/home';"
    class="menu-item w-12 h-12 flex items-center justify-center text-2xl opacity-80 hover:opacity-100">
    <i class="fa-solid fa-house"></i>
</button>

        <button 
    onclick="window.location.href='/search';"
    class="menu-item w-12 h-12 flex items-center justify-center text-2xl opacity-80 hover:opacity-100">
    <i class="fa-solid fa-magnifying-glass"></i>
</button>

<button onclick="window.location.href='/pengembalian-buku';"
    class="menu-item w-12 h-12 flex items-center justify-center text-2xl opacity-80 hover:opacity-100">
    <i class="fa-solid fa-file-lines"></i>
</button>

      <button onclick="window.location.href='/pengaturan';" 
        class="menu-item w-12 h-12 flex items-center justify-center text-2xl opacity-80 hover:opacity-100">
    <i class="fa-solid fa-gear"></i>
</button>

    </div>

    <!-- LOGOUT PALING BAWAH -->
    <button 
        onclick="window.location.href='/logout';"
        class="menu-item w-12 h-12 flex items-center justify-center text-2xl opacity-80 hover:opacity-100 mb-4 mt-auto">
        <i class="fa-solid fa-right-from-bracket"></i>
    </button>

</aside>

<!-- MENU HIGHLIGHT SCRIPT -->
<script>
    const items = document.querySelectorAll(".menu-item");
    const highlight = document.getElementById("highlight");

    items.forEach((btn, index) => {
        btn.addEventListener("click", () => {
            highlight.style.top = (index * 80) + "px";
        });
    });

    highlight.style.top = "0px";
</script>

<!-- Font Awesome -->
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

<!-- MAIN CONTENT -->
<main class="flex-1 p-8">

    <!-- TOP BAR -->
<div class="flex justify-end items-center space-x-6 mb-6 relative">


    <!-- NOTIFICATION BUTTON -->
        <button id="notifBtn" onclick="toggleNotifPopup()" class="text-2xl hover:opacity-80 relative">
            🔔
            <span id="notifBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
        </button>

        <div id="profileBtn"
     class="bg-blue-500 w-10 h-10 rounded-full text-white flex items-center justify-center font-bold cursor-pointer overflow-hidden">
    @if(Auth::check() && Auth::user()->foto)
        <img src="{{ asset('storage/' . Auth::user()->foto) }}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.parentElement.innerHTML='{{ strtoupper(substr(Auth::user()->nama ?? 'PU', 0, 2)) }}'">
    @else
        {{ strtoupper(substr(Auth::user()->nama ?? 'PU', 0, 2)) }}
    @endif
</div>

<span id="profileBtn2" class="font-semibold text-lg cursor-pointer">
    {{ Auth::user()->nama ?? 'Pengguna' }}
</span>
    </div>

    <!-- PROFILE DROPDOWN -->
<div id="profileDropdown"
     class="hidden absolute top-14 right-0 w-40 bg-white shadow-xl rounded-xl py-2 z-50">

    <a href="/settings"
       class="block px-4 py-2 text-sm hover:bg-gray-100">
        Pengaturan
    </a>

    <a href="/logout"
       class="block px-4 py-2 text-sm hover:bg-gray-100 text-red-600 font-semibold">
        Logout
    </a>

</div>


    <!-- POPUP NOTIFIKASI -->
    <div id="notifPopup"
         class="hidden fixed inset-0 bg-black/50 items-center justify-center z-50">
        <div class="bg-white shadow-xl rounded-2xl p-6 w-96 max-h-[80vh] overflow-y-auto relative">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-xl text-[#A63A2D]">Notifikasi</h3>
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



    <!-- BANNER -->
    <div class="w-full bg-[#C4431E] rounded-3xl text-white p-10 flex justify-between shadow-xl relative overflow-hidden">
        <div class="w-2/3">
            <h1 class="text-4xl font-bold">Hai, {{ $user->nama ?? 'Pengguna' }}</h1>
            <p class="text-xl mt-2 mb-6">ada koleksi buku baru yang bisa kamu jelajahi hari ini!</p>

           <a href="{{ route('search') }}" 
   class="px-8 py-3 bg-white text-black font-bold rounded-full shadow hover:bg-gray-100 transition">
    Jelajahi Sekarang
</a>
        </div>

        <img src="images/book.png" 
     class="w-48 absolute right-12 top-1/2 -translate-y-[55%]">
    </div>

    <!-- GRID 3 KOLOM (REVISI LAYOUT) -->
    <div class="grid grid-cols-3 gap-8 mt-10 items-stretch">

    
        <!-- REKOMENDASI (kiri full) -->
<div class="col-span-2">
    <div class="bg-white p-6 rounded-2xl shadow-lg h-full flex flex-col">

                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold">Rekomendasi Untuk Kamu</h2>
                </div>

                <div class="flex-1 overflow-y-auto pr-2 mt-4 relative" id="scrollBox">
                    <div id="fadeBottom"
     class="pointer-events-none absolute bottom-0 left-0 right-0 h-12 
            bg-gradient-to-t from-white to-transparent">
</div>
                    <div class="grid grid-cols-5 gap-x-6 gap-y-10">
                        @forelse($rekomendasiBuku ?? [] as $buku)
                        <div class="flex flex-col items-center">
                            @php
                                $dummyData = \App\Models\Buku::dummyData();
                                $slug = str_replace(' ', '-', strtolower($buku->judul));
                                $imgPath = $dummyData[$buku->judul]['img'] ?? 'images/book-placeholder.jpg';
                            @endphp
                            <img src="{{ asset($imgPath) }}" class="w-36 h-48 object-cover rounded-xl shadow" onerror="this.src='{{ asset('images/book-placeholder.jpg') }}'">
                            <p class="font-semibold text-center mt-2">{{ Str::limit($buku->judul, 20) }}</p>
                            <p class="text-xs text-gray-500 text-center">{{ $buku->penulis ?? 'N/A' }}</p>
                            <a href="{{ route('detail', $slug) }}" class="mt-1 text-xl font-bold">＋</a>
                        </div>
                        @empty
                        <div class="col-span-5 text-center text-gray-500 py-8">
                            <p>Belum ada rekomendasi. Mulai pinjam buku untuk mendapatkan rekomendasi!</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        
<!-- KOLOM KANAN -->
<div class="col-span-1 flex flex-col gap-8">

    <!-- AKTIVITAS -->
    <div class="bg-[#B54C2B] text-white p-8 rounded-3xl shadow-xl w-full">
        <h2 class="text-2xl font-bold text-center mb-8">Aktivitas Pengguna</h2>

        <div class="grid grid-cols-2 gap-10 place-items-center">
            <div class="flex flex-col items-center space-y-3">
                <div class="w-24 h-24 border-2 border-white rounded-2xl flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5 4v16a1 1 0 001 1h12a1 1 0 001-1V4m-7 9l3-3m0 0l-3-3m3 3H9"/>
                    </svg>
                </div>
                <p class="text-lg font-medium text-center">{{ $sedangDipinjam ?? 0 }} Buku sedang dipinjam</p>
            </div>

            <div class="flex flex-col items-center space-y-3">
                <div class="w-24 h-24 border-2 border-white rounded-2xl flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-lg font-medium text-center">
                    @if($hariTersisa !== null)
                        {{ $hariTersisa }} Hari lagi pengembalian
                    @else
                        Tidak ada pinjaman aktif
                    @endif
                </p>
            </div>
        </div>

        <div class="bg-white text-black text-center mt-8 py-3 px-5 rounded-full font-semibold shadow-sm text-[16px] leading-tight">
            {{ $bukuBulanIni ?? 0 }} Buku yang telah dibaca bulan ini <br>
            Genre buku favoritmu <span class="font-bold">{{ $genreFavorit ?? 'Belum ada' }}</span>
        </div>
    </div>

    <!-- RIWAYAT -->
    <div class="bg-white p-7 rounded-3xl shadow-xl w-full">
       <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
            <span class="px-5 py-2 bg-[#DDA08A] rounded-full font-semibold text-[15px] shadow-sm">
                Riwayat Peminjaman
            </span>

            <div class="flex items-center gap-3">
                <input id="searchInput" type="text" placeholder="Cari buku..."
       class="border rounded-full px-3 py-1.5 text-sm w-32 focus:ring-2 focus:ring-[#DDA08A]">

<select id="filterSelect"
        class="border rounded-full px-3 py-1.5 text-sm w-32 focus:ring-2 focus:ring-[#DDA08A]">
                    <option value="all">Semua</option>
                    <option value="returned">Dikembalikan</option>
                    <option value="not-returned">Belum Dikembalikan</option>
                </select>
            </div>
        </div>

        <div id="loanList" class="space-y-6 text-[17px] leading-relaxed font-medium pl-3"></div>
    </div>

</div>

    <!-- SCRIPT RIWAYAT -->
    <script>
        const scrollBox = document.getElementById("scrollBox");
const fadeBottom = document.getElementById("fadeBottom");

if(scrollBox && fadeBottom) {
    scrollBox.addEventListener("scroll", () => {
        const atBottom = scrollBox.scrollHeight - scrollBox.scrollTop <= scrollBox.clientHeight + 2;
        fadeBottom.style.opacity = atBottom ? "0" : "1";
    });
}

        const loans = @json($riwayatPeminjaman ?? []);
        const loansData = loans.map(loan => ({
            title: loan.buku ? `${loan.buku.judul}, ${loan.buku.penulis}` : 'Buku tidak ditemukan',
            status: loan.status === 'dikembalikan' ? 'returned' : 'not-returned',
            date: loan.tanggal_kembali ? new Date(loan.tanggal_kembali).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : null
        }));

        const list = document.getElementById("loanList");
        const searchInput = document.getElementById("searchInput");
        const filterSelect = document.getElementById("filterSelect");

        function renderList() {
            if (!list) return;
            
            const keyword = searchInput ? searchInput.value.toLowerCase() : '';
            const filter = filterSelect ? filterSelect.value : 'all';

            list.innerHTML = "";

            if (loansData.length === 0) {
                list.innerHTML = '<div class="p-3 text-center text-gray-500">Belum ada riwayat peminjaman</div>';
                return;
            }

            loansData
                .filter(item =>
                    item.title.toLowerCase().includes(keyword) &&
                    (filter === "all" ||
                        (filter === "returned" && item.status === "returned") ||
                        (filter === "not-returned" && item.status === "not-returned"))
                )
                .forEach((item, index) => {
                    const div = document.createElement("div");
                    div.innerHTML = `
                        <div>
                            <span class="font-bold">${index + 1}. ${item.title}</span><br>
                            ${item.status === "returned"
                        ? `<span>Dikembalikan: ${item.date}</span>`
                        : `<span class="text-red-600 font-semibold">Belum dikembalikan</span>`}
                        </div>
                    `;
                    list.appendChild(div);
                });
        }

        if (searchInput) searchInput.addEventListener("input", renderList);
        if (filterSelect) filterSelect.addEventListener("change", renderList);

        renderList();
    </script>

<script>
    const notifBtn = document.getElementById("notifBtn");
    const notifPopup = document.getElementById("notifPopup");
    let notifInterval;

function toggleNotifPopup() {
    const popup = document.getElementById("notifPopup");
    popup.classList.toggle("hidden");
    popup.classList.toggle("flex");
    
    if (!popup.classList.contains("hidden")) {
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
                const link = notif.link ? `onclick="window.location.href='${notif.link}'"` : '';
                
                return `
                    <li class="p-3 ${color} rounded-xl border-l-4 ${unreadClass} cursor-pointer hover:shadow-md" ${link} onclick="markAsRead(${notif.id})">
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

    notifBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        toggleNotifPopup();
    });
    
    // Load notifikasi saat halaman dimuat dan update setiap 30 detik
    document.addEventListener('DOMContentLoaded', () => {
        loadNotifikasi();
        notifInterval = setInterval(loadNotifikasi, 30000); // Update setiap 30 detik
    });

    // Klik luar menutup semua popup
    document.getElementById("notifPopup").addEventListener("click", function(e) {
        if (e.target === this) {
            toggleNotifPopup();
        }
    });
</script>

<script>
    const profileBtn = document.getElementById("profileBtn");
    const profileBtn2 = document.getElementById("profileBtn2");
    const profileDropdown = document.getElementById("profileDropdown");

    function toggleProfileMenu(e) {
        e.stopPropagation();
        profileDropdown.classList.toggle("hidden");
    }

    profileBtn.addEventListener("click", toggleProfileMenu);
    profileBtn2.addEventListener("click", toggleProfileMenu);

    document.addEventListener("click", () => {
        profileDropdown.classList.add("hidden");
    });
</script>

<script>
    // Ambil semua tombol + di grid
    const addButtons = document.querySelectorAll(".grid button");

    addButtons.forEach((btn) => {
        btn.addEventListener("click", function () {
            const parent = this.parentElement;
            const title = parent.querySelector("p").innerText;

            // Ubah judul jadi URL friendly
            const encoded = encodeURIComponent(title);

            // Redirect ke detail buku
            window.location.href = `/detail?buku=${encoded}`;
        });
    });
</script>

</main>

</body>
</html>
