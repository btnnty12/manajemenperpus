<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Search Buku - Perpustakaan</title>

    <!-- TAILWIND -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

    <style>
        .sidebar-btn:hover {
            background: #ffffff40;
        }
        .tag-selected {
            background: #fff;
            border: 1px solid #ffbc4c;
            box-shadow: 0 3px 5px rgba(0,0,0,0.08);
        }
        .blue-pill {
            background: #d7edff;
        }

        .kategori-tag {
    position: relative;
}

.kategori-tag .x-btn {
    background: #ff5757;
    color: white;
    font-size: 10px;
    width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    position: absolute;
    top: -6px;
    right: -6px;
}
    </style>

</head>

<body class="bg-gradient-to-b from-[#F8E79D] to-[#F4C86E] min-h-screen">

<div class="flex">

    <!-- SIDEBAR -->
    <aside id="sidebar"
       class="w-20 bg-[#C34722] text-white flex flex-col items-center py-6 shadow-lg relative">

    <div id="menuWrapper" class="relative flex flex-col items-center space-y-8 flex-1">
        <div id="highlight"
             class="absolute left-0 w-16 h-12 bg-white/30 rounded-xl transition-all duration-300 shadow-md -z-10"
             style="top: 80px;"></div>


        <!-- HOME -->
        <button onclick="window.location.href='{{ url('/home') }}';"
            class="menu-item w-12 h-12 flex items-center justify-center text-2xl opacity-80 hover:opacity-100">
            <i class="fa-solid fa-house"></i>
        </button>

        <!-- SEARCH -->
        <button onclick="window.location.href='{{ url('/search') }}';"
            class="menu-item w-12 h-12 flex items-center justify-center text-2xl opacity-80 hover:opacity-100">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>

        <!-- TRANSAKSI -->
        <button onclick="window.location.href='{{ url('/pengembalian-buku') }}';"
            class="menu-item w-12 h-12 flex items-center justify-center text-2xl opacity-80 hover:opacity-100">
            <i class="fa-solid fa-file-lines"></i>
        </button>

        <!-- SETTINGS -->
        <button onclick="window.location.href='/pengaturan';" 
        class="menu-item w-12 h-12 flex items-center justify-center text-2xl opacity-80 hover:opacity-100">
    <i class="fa-solid fa-gear"></i>
</button>

    </div>

    <!-- LOGOUT PALING BAWAH -->
    <button onclick="window.location.href='{{ url('/logout') }}';"
        class="menu-item w-12 h-12 flex items-center justify-center text-2xl opacity-80 hover:opacity-100 mb-4 mt-auto">
        <i class="fa-solid fa-right-from-bracket"></i>
    </button>

</aside>

    <main class="flex">

    <!-- ======== MAIN CONTENT (LEFT) ======== -->
    <div class="flex-1 p-8">

        <!-- HEADER -->
        <div class="flex justify-end items-center space-x-6 mb-6">
            <button id="notifBtn" onclick="toggleNotifPopup()" class="text-2xl hover:opacity-80 relative">🔔
                <span id="notifBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
            </button>
            @if(Auth::check())
            <div class="bg-blue-500 w-10 h-10 rounded-full text-white flex items-center justify-center font-bold cursor-pointer overflow-hidden">
                @if(Auth::user()->foto)
                    <img src="{{ asset('storage/' . Auth::user()->foto) }}" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='{{ strtoupper(substr(Auth::user()->nama ?? 'PU', 0, 2)) }}'">
                @else
                    {{ strtoupper(substr(Auth::user()->nama ?? 'PU', 0, 2)) }}
                @endif
            </div>
            <span class="font-semibold text-lg">{{ Auth::user()->nama ?? 'Pengguna' }}</span>
            @endif
        </div>

        <!-- POPUP NOTIFIKASI -->
        <div id="notifPopup" class="hidden fixed inset-0 bg-black/50 items-center justify-center z-50">
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

        <!-- SEARCH BAR -->
        <div class="flex justify-center mb-8 relative w-2/3 mx-auto">
            <input id="searchInput"
                   class="w-full pl-12 pr-4 py-3 rounded-full shadow-lg border border-yellow-300 outline-none"
                   placeholder="Cari buku...">
            
            <img src="{{ asset('icons/search.svg') }}" 
                 class="w-5 absolute left-4 top-1/2 -translate-y-1/2 opacity-80">

            <!-- Spinner + info -->
            <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-2">
                <div id="searchSpinner" class="hidden w-6 h-6 border-2 border-t-transparent rounded-full animate-spin border-yellow-400"></div>
            </div>

            <!-- Search Results -->
            <div id="searchResults"
                 class="absolute top-full left-0 w-full mt-2 bg-white shadow-lg rounded-lg max-h-60 overflow-y-auto hidden z-50"></div>

            <!-- Small info below input -->
            <div id="searchInfo" class="absolute top-full left-0 w-full mt-1 text-xs text-gray-500 hidden px-2"></div>
        </div>

        <!-- ===== ROW: RIWAYAT ===== -->
        <div class="flex items-start gap-10 px-3">
            <!-- RIWAYAT -->
            <div class="flex-1">
                <div class="flex justify-between items-center">
                    <h4 class="font-semibold">Riwayat Pencarian</h4>
                    <button onclick="clearAllHistory()" class="text-red-600 text-sm">Hapus Semua</button>
                </div>
                <div id="historyWrapper" class="flex flex-wrap gap-3 mt-3"></div>
            </div>
        </div>

        <!-- ===== HASIL PENCARIAN ===== -->
        <div class="mt-12 px-3 hidden" id="searchSection">
            <h3 class="font-bold text-lg mb-3">Hasil Pencarian</h3>
            <div id="hasilPencarianList" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"></div>
        </div>

        <!-- ===== BUKU YANG TERSEDIA ===== -->
        <div class="mt-12 px-3" id="bukuTersediaSection">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-xl">Buku yang Tersedia</h3>
                <span class="text-sm text-gray-600">Jelajahi koleksi buku kami</span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @forelse($beberapaBuku ?? [] as $buku)
                @php
                    $dummyData = \App\Models\Buku::dummyData();
                    $slug = str_replace(' ', '-', strtolower($buku->judul));
                    $imgPath = $dummyData[$buku->judul]['img'] ?? 'images/book.png';
                @endphp
                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition">
                    <div class="cursor-pointer" onclick="window.location.href='{{ route('detail', $slug) }}'">
                        <img src="{{ asset($imgPath) }}" class="w-full h-64 object-cover" onerror="this.src='{{ asset('images/book.png') }}'">
                        <div class="p-4">
                            <p class="font-semibold text-sm mb-1">{{ Str::limit($buku->judul, 30) }}</p>
                            <p class="text-xs text-gray-500">{{ $buku->penulis ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $buku->genre ?? 'N/A' }} • {{ $buku->tahun_terbit ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="px-4 pb-4">
                        <button onclick="pinjamBuku({{ $buku->id }}, '{{ addslashes($buku->judul) }}')" 
                                class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition text-sm font-semibold">
                            <i class="fas fa-book-reader mr-1"></i> Pinjam Buku
                        </button>
                    </div>
                </div>
                @empty
                <div class="col-span-4 text-center text-gray-500 py-8">
                    <p>Belum ada buku populer</p>
                </div>
                @endforelse
            </div>
        </div>

        <script>
            // Expose basic allBooks array for client-side demo searches (if available)
            window.allBooks = @json($allBooks ?? []);
        </script>

        <!-- ===== BUKU TERBARU ===== -->
        @if(isset($bukuTerbaru) && $bukuTerbaru->count() > 0)
        <div class="mt-12 px-3" id="bukuTerbaruSection">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-xl">Buku Terbaru</h3>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach($bukuTerbaru as $buku)
                @php
                    $dummyData = \App\Models\Buku::dummyData();
                    $slug = str_replace(' ', '-', strtolower($buku->judul));
                    $imgPath = $dummyData[$buku->judul]['img'] ?? 'images/book.png';
                @endphp
                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition">
                    <div class="cursor-pointer" onclick="window.location.href='{{ route('detail', $slug) }}'">
                        <img src="{{ asset($imgPath) }}" class="w-full h-48 object-cover" onerror="this.src='{{ asset('images/book.png') }}'">
                        <div class="p-3">
                            <p class="font-semibold text-xs mb-1">{{ Str::limit($buku->judul, 25) }}</p>
                            <p class="text-xs text-gray-500">{{ $buku->tahun_terbit ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="px-3 pb-3">
                        <button onclick="pinjamBuku({{ $buku->id }}, '{{ addslashes($buku->judul) }}')" 
                                class="w-full bg-green-600 text-white px-2 py-1 rounded text-xs hover:bg-green-700 transition font-semibold">
                            <i class="fas fa-book-reader mr-1"></i> Pinjam
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

      <!-- ===== ROW: FILTER ===== -->
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mt-12 mx-3">

    <!-- BAGIAN KANAN (FILTER) -->
    <aside class="w-72 p-6 bg-white rounded-2xl border border-yellow-200 h-fit">

        <h3 class="font-bold text-lg mb-4">Filter Buku</h3>

        <!-- Genre -->
        <h4 class="font-semibold mb-2">Genre</h4>
        <div class="flex flex-wrap gap-2 mb-4">
            @if(isset($genres) && count($genres) > 0)
                @foreach($genres as $genre)
                <span class="blue-pill px-3 py-1 rounded-full text-sm cursor-pointer filter-genre" data-value="{{ $genre }}">{{ $genre }}</span>
                @endforeach
            @else
                <span class="text-sm text-gray-500">Belum ada kategori</span>
            @endif
        </div>

        <!-- Tahun -->
        <h4 class="font-semibold mb-3">Tahun Terbit</h4>
        <div class="flex items-center gap-4 mb-4">
            <span id="yearMin" class="text-sm text-gray-600">{{ $minYear ?? 2000 }}</span>
            <input type="range" id="yearRange" min="{{ $minYear ?? 2000 }}" max="{{ $maxYear ?? date('Y') }}" value="{{ $maxYear ?? date('Y') }}" class="flex-1">
            <span id="yearMax" class="text-sm text-gray-600">{{ $maxYear ?? date('Y') }}</span>
        </div>

    </aside>

</div>

</main>


<!-- JAVASCRIPT RIWAYAT -->
<script>
    let searchHistory = [];

    function loadHistory() {
        fetch('/api/riwayat-pencarian', {
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            searchHistory = data.data || [];
            renderHistory();
        })
        .catch(error => {
            console.error('Error loading history:', error);
        });
    }

    function renderHistory() {
        const wrapper = document.getElementById("historyWrapper");
        wrapper.innerHTML = "";

        if (searchHistory.length === 0) {
            wrapper.innerHTML = '<p class="text-gray-500 text-sm">Belum ada riwayat pencarian</p>';
            return;
        }

        searchHistory.forEach((item) => {
            wrapper.innerHTML += `
                <span class="px-4 py-1 rounded-full tag-selected text-sm flex items-center gap-1">
                    ${item.keyword}
                    <button onclick="deleteHistory('${item.keyword}')" class="text-red-600 font-bold ml-1">✕</button>
                </span>
            `;
        });
    }

    function deleteHistory(keyword) {
        // Hapus dari database
        fetch(`/api/riwayat-pencarian/${keyword}`, {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to delete');
            return response.json();
        })
        .then(() => {
            // Hapus dari array lokal dan render ulang
            searchHistory = searchHistory.filter(item => item.keyword !== keyword);
            renderHistory();
        })
        .catch(error => {
            console.error('Error deleting history:', error);
            // Fallback: hapus dari array lokal saja
            searchHistory = searchHistory.filter(item => item.keyword !== keyword);
            renderHistory();
        });
    }

    function clearAllHistory() {
        if (confirm('Yakin ingin menghapus semua riwayat pencarian?')) {
            fetch('/api/riwayat-pencarian', {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Failed to clear');
                return response.json();
            })
            .then(() => {
                searchHistory = [];
                renderHistory();
            })
            .catch(error => {
                console.error('Error clearing history:', error);
            });
        }
    }

    // Load history saat halaman dimuat
    document.addEventListener('DOMContentLoaded', loadHistory);
</script>

<script>
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
                notifList.innerHTML = '<li class="p-3 text-center text-gray-500 py-8">Tidak ada notifikasi</li>';
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

    // Load notifikasi saat halaman dimuat
    document.addEventListener('DOMContentLoaded', () => {
        loadNotifikasi();
    });
</script>

<script>
    // Delete pesan
    document.querySelectorAll(".delete-btn").forEach(b => {
        b.addEventListener("click", function () {
            this.closest(".message-item").remove();
        });
    });

    // Toggle balas
    document.querySelectorAll(".reply-btn").forEach(b => {
        b.addEventListener("click", function () {
            const box = this.closest(".message-item").querySelector(".reply-box");
            box.classList.toggle("hidden");
        });
    });

    // Kirim
    document.querySelectorAll(".send-reply").forEach(b => {
        b.addEventListener("click", function () {
            const input = this.previousElementSibling;
            if (input.value.trim() !== "") {
                alert("Balasan terkirim:\n" + input.value);
                input.value = "";
            }
        });
    });
</script>

<script>
    const yearRange = document.getElementById("yearRange");
const yearMin = document.getElementById("yearMin");
const yearMax = document.getElementById("yearMax");

// Update label saat slider digeser
yearRange.addEventListener("input", () => {
    yearMax.textContent = yearRange.value;
});
</script>

<script>
// ============ ELEMENT ============
const hasilPencarianSection = document.getElementById("searchSection");
const hasilPencarianList   = document.getElementById("hasilPencarianList");
const searchInput   = document.getElementById("searchInput");
const searchResults = document.getElementById("searchResults");
const yearSlider = document.getElementById("yearRange");
let selectedGenre = null;
let selectedTahun = {{ $maxYear ?? date('Y') }};

// =========================================
// SEARCH BAR FUNGSI (Menggunakan API)
// =========================================
let searchTimeout;
searchInput.addEventListener("input", () => {
    const q = searchInput.value.trim();
    const spinner = document.getElementById('searchSpinner');
    const info = document.getElementById('searchInfo');

    clearTimeout(searchTimeout);
    
    if (q === "") {
        searchResults.classList.add("hidden");
        hasilPencarianSection.classList.add("hidden");
        info.classList.add('hidden');
        return;
    }

    searchTimeout = setTimeout(() => {
        spinner.classList.remove('hidden');
        info.classList.add('hidden');

        const genreParam = selectedGenre ? `&genre=${encodeURIComponent(selectedGenre)}` : '';
        const tahunParam = selectedTahun ? `&tahun=${selectedTahun}` : '';
        
        fetch(`/api/search?q=${encodeURIComponent(q)}${genreParam}${tahunParam}`, {            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            spinner.classList.add('hidden');
            if (data.success && data.data && data.data.results) {
                const results = data.data.results;

                // Show info (count, engine, time)
                const engine = (data.data.engine || 'db').toUpperCase();
                info.textContent = `${data.data.pagination.total} hasil • ${engine} • ${Math.round(data.data.process_time_ms)} ms`;
                info.classList.remove('hidden');
                
                // Update dropdown suggestions
                searchResults.innerHTML = "";
                if (results.length === 0) {
                    searchResults.innerHTML = `<p class="p-3 text-gray-500">Tidak ditemukan</p>`;
                } else {
                    results.slice(0, 5).forEach(book => {
                        let d = document.createElement("div");
                        d.className = "p-3 cursor-pointer hover:bg-yellow-100 rounded-lg";
                        d.innerHTML = `<div class='font-semibold'>${escapeHtml(book.judul)}</div><div class='text-xs text-gray-500 mt-1'>${book.matches && book.matches.judul ? book.matches.judul.snippet : ''}</div>`;
                        d.addEventListener("click", () => {
                            searchInput.value = book.judul;
                            searchResults.classList.add("hidden");
                            performSearch(book.judul);
                        });

                        // Tambahkan badge singkat dengan info algoritma jika service tersedia (hanya di laman publik)
                        if (window.StringMatchingService && q) {
                            try {
                                const algo = window.StringMatchingService.selectBestAlgorithm(q, (book.judul || '').length);
                                const t0 = performance.now();
                                window.StringMatchingService.searchWithAlgorithm(book.judul || '', q, algo)
                                    .then(positions => {
                                        const t1 = performance.now();
                                        const ms = Math.max(0, Math.round(t1 - t0));
                                        const badge = document.createElement('div');
                                        badge.className = 'text-xs text-gray-400 mt-1';
                                        badge.textContent = `${algo.toUpperCase()} • ${ms} ms`;
                                        d.appendChild(badge);
                                    })
                                    .catch(() => {
                                        // ignore service failures
                                    });
                            } catch (err) {
                                // ignore
                            }
                        }

                        searchResults.appendChild(d);
                    });
                }
                searchResults.classList.remove("hidden");
                
                // Render hasil pencarian
                renderSearchResults(results);

                // Update riwayat setelah pencarian berhasil
                loadHistory();
            } else {
                searchResults.innerHTML = `<p class="p-3 text-gray-500">Tidak ditemukan</p>`;
                searchResults.classList.remove("hidden");
                hasilPencarianSection.classList.add("hidden");
            }
        })
        .catch(error => {
            spinner.classList.add('hidden');
            console.error('Search error:', error);
        });
    }, 300);
});

// Utility to escape HTML for safety where needed
function escapeHtml(unsafe) {
    return (unsafe || '').replace(/[&<"'`=\/]/g, function (s) {
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;','/':'\/','`':'&#96;','=':'&#61;'}[s];
    });
}

function performSearch(query) {
    const genreParam = selectedGenre ? `&genre=${encodeURIComponent(selectedGenre)}` : '';
    const tahunParam = selectedTahun ? `&tahun=${selectedTahun}` : '';
    const spinner = document.getElementById('searchSpinner');
    const info = document.getElementById('searchInfo');

    spinner.classList.remove('hidden');
    info.classList.add('hidden');

    fetch(`/api/search?q=${encodeURIComponent(query)}${genreParam}${tahunParam}`, {
        credentials: 'same-origin',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        spinner.classList.add('hidden');
        if (data.success && data.data && data.data.results) {
            const engine = (data.data.engine || 'db').toUpperCase();
            info.textContent = `${data.data.pagination.total} hasil • ${engine} • ${Math.round(data.data.process_time_ms)} ms`;
            info.classList.remove('hidden');
            renderSearchResults(data.data.results);

            // Update riwayat setelah pencarian berhasil
            loadHistory();
        } else {
            hasilPencarianSection.classList.remove("hidden");
            hasilPencarianList.innerHTML = '<p class="text-red-600 col-span-4">Buku tidak ditemukan.</p>';
        }
    })
    .catch(error => {
        spinner.classList.add('hidden');
        console.error('Search error:', error);
        hasilPencarianSection.classList.remove("hidden");
        hasilPencarianList.innerHTML = '<p class="text-red-600 col-span-4">Terjadi kesalahan saat mencari.</p>';
    });
}

function renderSearchResults(results) {
    hasilPencarianSection.classList.remove("hidden");
    
    if (!results || results.length === 0) {
        hasilPencarianList.innerHTML = '<p class="text-red-600 col-span-4">Buku tidak ditemukan.</p>';
        return;
    }

    hasilPencarianList.innerHTML = results.map(b => {
        const snippet = (b.matches && b.matches.judul && b.matches.judul.snippet) ? b.matches.judul.snippet : '';
        const cover = b.cover_url ? `<img src="${b.cover_url}" class="w-full h-40 object-cover rounded-lg mb-2" onerror="this.style.display='none'">` : `<div class="w-full h-40 bg-gray-200 rounded-lg shadow-md flex items-center justify-center mb-2"><i class="fas fa-book text-4xl text-gray-400"></i></div>`;
        const avail = b.available ? `<span class="text-xs text-green-700 font-semibold">Tersedia (${b.stok})</span>` : `<span class="text-xs text-red-600 font-semibold">Habis</span>`;
        return `
            <div class="w-full">
                ${cover}
                <p class="mt-2 text-sm font-semibold">${escapeHtml(b.judul || 'N/A')}</p>
                <div class="flex items-center justify-between mb-2">
                    ${snippet ? `<p class="text-xs text-gray-700">${snippet}</p>` : `<p class="text-xs text-gray-500">${b.genre || 'N/A'} • ${b.tahun_terbit || 'N/A'}</p>`}
                    ${avail}
                </div>
                <button onclick="pinjamBuku(${b.id}, '${(b.judul || '').replace(/'/g, "\\'")}')" 
                        class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition text-sm font-semibold">
                    <i class="fas fa-book-reader mr-1"></i> Pinjam Buku
                </button>
            </div>
        `;
    }).join("");
}

function pinjamBuku(bukuId, judulBuku) {
    if (confirm(`Apakah Anda yakin ingin meminjam buku "${judulBuku}"?`)) {
        window.location.href = `/pengembalian/create?buku_id=${bukuId}`;
    }
}

// =========================================
// FILTER GENRE
// =========================================
document.querySelectorAll(".filter-genre").forEach(btn => {
    btn.addEventListener("click", () => {
        if (selectedGenre === btn.dataset.value) {
            selectedGenre = null;
            btn.classList.remove("bg-yellow-300");
        } else {
            selectedGenre = btn.dataset.value;
            document.querySelectorAll(".filter-genre").forEach(b => b.classList.remove("bg-yellow-300"));
            btn.classList.add("bg-yellow-300");
        }
        
        // Jika ada keyword di search input, lakukan pencarian ulang
        if (searchInput.value.trim()) {
            performSearch(searchInput.value.trim());
        }
    });
});

// =========================================
// FILTER TAHUN
// =========================================
if (yearSlider) {
    yearSlider.addEventListener("input", () => {
        selectedTahun = parseInt(yearSlider.value);
        document.getElementById("yearMax").textContent = selectedTahun;
        
        // Jika ada keyword di search input, lakukan pencarian ulang
        if (searchInput.value.trim()) {
            performSearch(searchInput.value.trim());
        }
    });
}


// Klik di luar → tutup dropdown search
document.addEventListener("click", (e) => {
    if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.classList.add("hidden");
    }
});
</script>



<script>
const kategoriTags = document.querySelectorAll('.kategori-tag');

kategoriTags.forEach(tag => {
    tag.addEventListener('click', () => {

        const active = tag.classList.contains("bg-yellow-300");

        // Jika aktif → matikan
        if (active) {
            tag.classList.remove("bg-yellow-300");
            const closeBtn = tag.querySelector(".x-btn");
            if (closeBtn) closeBtn.remove();
            selectedGenre = null;
            hasilPencarianSection.classList.add("hidden");
            return;
        }

        // Reset semua tag lain
        kategoriTags.forEach(t => {
            t.classList.remove("bg-yellow-300");
            const x = t.querySelector(".x-btn");
            if (x) x.remove();
        });

        // Aktifkan tag
        tag.classList.add("bg-yellow-300");

        // Tambahkan tombol X
        const closeButton = document.createElement("span");
        closeButton.textContent = "×";
        closeButton.classList.add("x-btn");
        tag.appendChild(closeButton);

        closeButton.addEventListener("click", (e) => {
            e.stopPropagation();
            tag.classList.remove("bg-yellow-300");
            closeButton.remove();
            selectedGenre = null;
            hasilPencarianSection.classList.add("hidden");
        });

        const kategori = tag.innerText.replace("×"," ").trim();
        selectedGenre = kategori;
        // Use server-side search: set search input to category and perform search
        searchInput.value = kategori;
        performSearch(kategori);

        });
    });
});
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
highlight.style.top = "80px";
</script>

<script>
// Prevent infinite image onerror loops: set placeholder only once, hide if placeholder fails
(function(){
    // Use an existing placeholder image (book.png) to avoid 404s
    const placeholder = "{{ asset('images/book.png') }}";
    window.addEventListener('error', function(e){
        const t = e.target;
        if (!t || t.tagName !== 'IMG') return;
        try {
            if (t.dataset.errored) return; // already handled
            t.dataset.errored = '1';
            if (t.src && (t.src.indexOf('book.png') !== -1)) {
                console.warn('Backup image failed to load — hiding element to avoid repeated requests', t.src);
                t.style.display = 'none';
            } else {
                t.src = placeholder;
            }
        } catch (err) {
            console.error('Image error handler failed', err);
        }
    }, true);
})();

// Detect frequent full-page reloads in a session
(function(){
    try {
        const key = 'pageReloads';
        const c = Number(sessionStorage.getItem(key) || 0) + 1;
        sessionStorage.setItem(key, c);
        console.info('page load count (session):', c);
        if (c > 5) console.warn('High reload count detected in this session:', c);
        // Reset counter after 60 seconds inactivity
        setTimeout(() => sessionStorage.setItem(key, 0), 60000);
    } catch (err) { /* ignore */ }
})();
</script>

<!-- String matching service (used only on public search page for demo/highlight) -->
<script src="{{ asset('js/string-matching-service.js') }}"></script>

</body>
</html>
