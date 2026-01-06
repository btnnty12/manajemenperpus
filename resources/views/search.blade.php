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
    <script src="/js/string-matching-service.js"></script>
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

        <!-- SEARCH BAR -->
        <div class="flex justify-center mb-8 relative w-2/3 mx-auto">
            <input id="searchInput"
                   class="w-full pl-12 pr-4 py-3 rounded-full shadow-lg border border-yellow-300 outline-none"
                   placeholder="Cari buku...">
            
            <img src="{{ asset('icons/search.svg') }}" 
                 class="w-5 absolute left-4 top-1/2 -translate-y-1/2 opacity-80">

            <!-- Search Results -->
            <div id="searchResults"
                 class="absolute top-full left-0 w-full mt-2 bg-white shadow-lg rounded-lg max-h-60 overflow-y-auto hidden z-50"></div>
        </div>

        <!-- ===== ROW: RIWAYAT + TOP KATEGORI ===== -->
        <div class="flex items-start gap-10 px-3">

    <!-- RIWAYAT -->
    <div class="flex-1 border-r pr-6">
                <div class="flex justify-between items-center">
                    <h4 class="font-semibold">Riwayat Pencarian</h4>
                    <button onclick="clearAllHistory()" class="text-red-600 text-sm">Hapus Semua</button>
                </div>

                <div id="historyWrapper" class="flex flex-wrap gap-3 mt-3"></div>
            </div>
    </div>

            <!-- TOP KATEGORI -->
           <div class="flex-1">
            <div class="flex-1 ml-6">
    <h4 class="font-semibold mb-2">Top Kategori</h4>

    <div id="topKategoriWrapper" class="flex flex-wrap gap-3">
        <span class="kategori-tag px-4 py-1 rounded-full bg-white shadow text-sm cursor-pointer flex items-center gap-2">
            Teknologi Informasi
        </span>
        <span class="kategori-tag px-4 py-1 rounded-full bg-white shadow text-sm cursor-pointer flex items-center gap-2">
            AI & Machine Learning
        </span>
        <span class="kategori-tag px-4 py-1 rounded-full bg-white shadow text-sm cursor-pointer flex items-center gap-2">
            Statistika
        </span>
        <span class="kategori-tag px-4 py-1 rounded-full bg-white shadow text-sm cursor-pointer flex items-center gap-2">
            Manajemen
        </span>
        <span class="kategori-tag px-4 py-1 rounded-full bg-white shadow text-sm cursor-pointer flex items-center gap-2">
            Psikologi
        </span>
    </div>
</div>

        </div>

        <!-- ===== HASIL PENCARIAN ===== -->
        <div class="mt-12 px-3 hidden" id="searchSection">
            <h3 class="font-bold text-lg mb-3">Hasil Pencarian</h3>
            <div id="hasilPencarianList" class="grid grid-cols-4 gap-6"></div>
        </div>

      <!-- ===== ROW: CARD + FILTER ===== -->
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mt-12 mx-3">

    <!-- BAGIAN KIRI (Terakhir Dipinjam + Minat Kamu) -->
    <div class="lg:col-span-3 bg-white rounded-2xl p-6 shadow-md border border-yellow-200">

       <!-- TERAKHIR DIPINJAM -->
<div class="flex justify-between items-center">
    <h3 class="font-bold text-lg">Terakhir Yang Dipinjam</h3>
    <span class="text-sm text-gray-600 cursor-pointer">Lihat Lebih Banyak ↗</span>
</div>

<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6 mt-6 pb-6 border-b">

    @foreach([
        ['Menulis Ilmiah','images/download (8).jpeg'],
        ['Deep Learning','images/images (4).jpeg'],
        ['SQL 2000','images/download (9).jpeg'],
        ['Penelitian Kualitatif','images/download (16).jpeg'],
        ['Pemrograman Web','images/images (1).jpeg'],
    ] as $buku)

    <div class="flex flex-col items-center"> <!-- DITAMBAHKAN items-center -->
        <img src="{{ asset($buku[1]) }}"
             class="w-full h-64 object-cover rounded-xl shadow-md">
        
        <p class="mt-2 text-sm font-semibold leading-tight text-center">
            {{ $buku[0] }}
        </p>
        <p class="text-xs text-gray-500 text-center">M.Pd | 2024</p>
    </div>

    @endforeach

</div>

        <!-- MINAT BACANMU -->
<div class="mt-8">
    <h4 class="font-semibold text-gray-700 mb-2">Hasil Minat Bacaan Kamu</h4>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">

        @foreach([
            ['AI Dasar','images/download (1).jpeg'],
            ['Cloud Intro','images/cloud.jpg'],
            ['UI/UX Modern','images/download (11).jpeg'],
            ['Algoritma','images/download (19).jpeg'],
            ['Data Science','images/dw.jpg'],
            ['Machine Learning','images/Machine Learning.jpeg'],
        ] as $minat)

        <div class="flex flex-col items-center"> <!-- DITAMBAHKAN items-center -->
            <img src="{{ asset($minat[1]) }}"
                 class="w-full h-64 object-cover rounded-xl shadow-md">

            <p class="mt-2 font-semibold text-sm leading-tight text-center">
                {{ $minat[0] }}
            </p>
            <p class="text-xs text-gray-500 text-center">Minat Tinggi</p>
        </div>

        @endforeach

    </div>
</div>

    </div> <!-- END BOX PUTIH -->


    <!-- BAGIAN KANAN (FILTER) -->
    <aside class="w-72 p-6 bg-white rounded-2xl border border-yellow-200 h-fit">

        <h3 class="font-bold text-lg mb-4">Filter Buku</h3>

        <!-- Jenis -->
        <h4 class="font-semibold mb-2">Jenis Buku</h4>
        <div class="flex flex-wrap gap-2 mb-4">
            <span class="blue-pill px-3 py-1 rounded-full text-sm cursor-pointer filter-jenis" data-value="Fiksi">Fiksi</span>
            <span class="blue-pill px-3 py-1 rounded-full text-sm cursor-pointer filter-jenis" data-value="Non-Fiksi">Non-Fiksi</span>
            <span class="blue-pill px-3 py-1 rounded-full text-sm cursor-pointer filter-jenis" data-value="Referensi">Referensi</span>
        </div>

        <!-- Bahasa -->
        <h4 class="font-semibold mb-2">Bahasa</h4>
        <div class="flex flex-wrap gap-2 mb-4">
            <span class="blue-pill px-3 py-1 rounded-full text-sm cursor-pointer filter-bahasa" data-value="Indonesia">Indonesia</span>
            <span class="blue-pill px-3 py-1 rounded-full text-sm cursor-pointer filter-bahasa" data-value="English">English</span>
        </div>

        <!-- Tahun -->
        <h4 class="font-semibold mb-3">Tahun Terbit</h4>
        <div class="flex items-center gap-4 mb-4">
            <span id="yearMin" class="text-sm text-gray-600">2000</span>
            <input type="range" id="yearRange" min="2000" max="2025" value="2025" class="flex-1">
            <span id="yearMax" class="text-sm text-gray-600">2025</span>
        </div>

    </aside>

</div>

</main>


<!-- JAVASCRIPT RIWAYAT -->
<script>
    let searchHistory = ["Deep Learning","Android Programming","Operating System","Penulisan Ilmiah","SQL Data"];

    function renderHistory() {
        const wrapper = document.getElementById("historyWrapper");
        wrapper.innerHTML = "";

        searchHistory.forEach((item, index) => {
            wrapper.innerHTML += `
                <span class="px-4 py-1 rounded-full tag-selected text-sm flex items-center gap-1">
                    ${item}
                    <button onclick="deleteHistory(${index})" class="text-red-600 font-bold ml-1">✕</button>
                </span>
            `;
        });
    }

    function deleteHistory(i) {
        searchHistory.splice(i, 1);
        renderHistory();
    }

    function clearAllHistory() {
        searchHistory = [];
        renderHistory();
    }

    renderHistory();
</script>

<script>
    const notifBtn = document.getElementById("notifBtn");
    const msgBtn = document.getElementById("msgBtn");

    const notifPopup = document.getElementById("notifPopup");
    const msgPopup = document.getElementById("msgPopup");

    notifBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        notifPopup.classList.toggle("hidden");
        msgPopup.classList.add("hidden");
    });

    msgBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        msgPopup.classList.toggle("hidden");
        notifPopup.classList.add("hidden");
    });

    // Klik Luar → Tutup
    document.addEventListener("click", () => {
        notifPopup.classList.add("hidden");
        msgPopup.classList.add("hidden");
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
// ============ DATA DUMMY ============
const allBooks = [
    { title: "Deep Learning", jenis: "Non-Fiksi", bahasa: "English", tahun: 2018, img: "images/book1.jpg" },
    { title: "AI Dasar", jenis: "Non-Fiksi", bahasa: "Indonesia", tahun: 2021, img: "images/book1.jpg" },
    { title: "Cloud Intro", jenis: "Non-Fiksi", bahasa: "English", tahun: 2019, img: "images/book1.jpg" },
    { title: "UI/UX Modern", jenis: "Referensi", bahasa: "Indonesia", tahun: 2020, img: "images/book2.jpg" },
    { title: "Algoritma", jenis: "Referensi", bahasa: "Indonesia", tahun: 2016, img: "images/book3.jpg" },
    { title: "Novel Fiksi A", jenis: "Fiksi", bahasa: "Indonesia", tahun: 2015, img: "images/book4.jpg" },
    { title: "Novel Fantasi", jenis: "Fiksi", bahasa: "English", tahun: 2013, img: "images/book5.jpg" },
];

// ============ ELEMENT ============
// Target hasil pencarian utama
const hasilPencarianSection = document.getElementById("searchSection");
const hasilPencarianList   = document.getElementById("hasilPencarianList");

// Filter
const jenisBtns  = document.querySelectorAll(".filter-jenis");
const bahasaBtns = document.querySelectorAll(".filter-bahasa");
const yearSlider = document.getElementById("yearRange");

// Search
const searchInput   = document.getElementById("searchInput");
const searchResults = document.getElementById("searchResults");

let selectedJenis  = null;
let selectedBahasa = null;
let selectedTahun  = 2025;


// =========================================
// RENDER BUKU KE GRID HASIL PENCARIAN
// =========================================
function renderBooks(list) {
    hasilPencarianSection.classList.remove("hidden");

    if (!list.length) {
        hasilPencarianList.innerHTML =
            `<p class="text-red-600 col-span-4">Buku tidak ditemukan.</p>`;
        return;
    }

    hasilPencarianList.innerHTML = list.map(b => `
        <div class="w-full">
            <img src="/${b.img}" class="w-full rounded-lg shadow-md">
            <p class="mt-2 text-sm font-semibold">${b.title}</p>
            <p class="text-xs text-gray-500">${b.jenis} • ${b.bahasa}</p>
            <p class="text-xs text-gray-500">Tahun: ${b.tahun}</p>
        </div>
    `).join("");
}


// =========================================
// FILTER FUNGSI
// =========================================
function filterBooks() {
    let f = allBooks;

    if (selectedJenis)  f = f.filter(b => b.jenis === selectedJenis);
    if (selectedBahasa) f = f.filter(b => b.bahasa === selectedBahasa);

    f = f.filter(b => b.tahun <= selectedTahun);

    renderBooks(f);
}


// =========================================
// EVENT FILTER
// =========================================

// Jenis
jenisBtns.forEach(btn => {
    btn.addEventListener("click", () => {
        if (selectedJenis === btn.dataset.value) {
            selectedJenis = null;
            btn.classList.remove("bg-yellow-300");
        } else {
            selectedJenis = btn.dataset.value;
            jenisBtns.forEach(b => b.classList.remove("bg-yellow-300"));
            btn.classList.add("bg-yellow-300");
        }
        filterBooks();
    });
});

// Bahasa
bahasaBtns.forEach(btn => {
    btn.addEventListener("click", () => {
        if (selectedBahasa === btn.dataset.value) {
            selectedBahasa = null;
            btn.classList.remove("bg-yellow-300");
        } else {
            selectedBahasa = btn.dataset.value;
            bahasaBtns.forEach(b => b.classList.remove("bg-yellow-300"));
            btn.classList.add("bg-yellow-300");
        }
        filterBooks();
    });
});

// Tahun
yearSlider.addEventListener("input", () => {
    selectedTahun = parseInt(yearSlider.value);
    document.getElementById("yearMax").textContent = selectedTahun;
    filterBooks();
});


// =========================================
// SEARCH BAR FUNGSI
// =========================================
searchInput.addEventListener("input", () => {
    const q = searchInput.value.toLowerCase();

    if (q === "") {
        searchResults.classList.add("hidden");
        hasilPencarianSection.classList.add("hidden");
        return;
    }

    const matches = allBooks.filter(b =>
        b.title.toLowerCase().includes(q)
    );

    searchResults.innerHTML = "";
    if (!matches.length) {
        searchResults.innerHTML = `<p class="p-3 text-gray-500">Tidak ditemukan</p>`;
    } else {
        matches.forEach(book => {
            let d = document.createElement("div");
            d.className = "p-3 cursor-pointer hover:bg-yellow-100 rounded-lg";
            d.textContent = book.title;

            d.addEventListener("click", () => {
                searchInput.value = book.title;
                searchResults.classList.add("hidden");
                renderBooks([book]);
            });

            searchResults.appendChild(d);
        });
    }

    searchResults.classList.remove("hidden");

    renderBooks(matches);
});


// Klik di luar → tutup dropdown search
document.addEventListener("click", (e) => {
    if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.classList.add("hidden");
    }
});
</script>

<script>
const books = allBooks; // ambil data buku dari array utama

const searchInput = document.getElementById("searchInput");
const searchResults = document.getElementById("searchResults");
const bookGrid = document.getElementById("rekomendasiList");

function renderBooks(list) {
    if (!list.length) {
        bookGrid.innerHTML = `<p class='text-red-600'>Buku tidak ditemukan.</p>`;
        return;
    }

    bookGrid.innerHTML = list.map(b => `
        <div class="w-32">
            <img src="/${b.img}" class="w-full rounded-lg shadow-md">
            <p class="mt-2 text-sm font-semibold">${b.title}</p>
            <p class="text-xs text-gray-500">${b.jenis} • ${b.bahasa}</p>
            <p class="text-xs text-gray-500">Tahun: ${b.tahun}</p>
        </div>
    `).join("");
}

// EVENT SEARCH
searchInput.addEventListener("input", () => {
    const q = searchInput.value.toLowerCase();

    if (q === "") {
        searchResults.classList.add("hidden");
        renderBooks(allBooks); // kembali tampilkan semua
        return;
    }

    const matches = books.filter(b =>
        b.title.toLowerCase().includes(q)
    );

    // tampilkan dropdown teks
    searchResults.innerHTML = "";
    if (!matches.length) {
        searchResults.innerHTML = `<p class="p-3 text-gray-500">Tidak ditemukan</p>`;
    } else {
        matches.forEach(m => {
            let div = document.createElement("div");
            div.textContent = m.title;
            div.className = "p-3 cursor-pointer hover:bg-yellow-100 rounded-lg";

            // KLIK → tampilkan buku lengkap di rekomendasi
            div.addEventListener("click", () => {
                searchInput.value = m.title;
                searchResults.classList.add("hidden");
                renderBooks([m]);
            });

            searchResults.appendChild(div);
        });
    }

    searchResults.classList.remove("hidden");

    // auto render saat mengetik
    renderBooks(matches);
});

// klik luar → tutup dropdown
document.addEventListener("click", (e) => {
    if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.classList.add("hidden");
    }
});
</script>

<script>
const kategoriTags = document.querySelectorAll('.kategori-tag');
const StringMatching = window.StringMatchingService;

kategoriTags.forEach(tag => {
    tag.addEventListener('click', () => {

        const active = tag.classList.contains("bg-yellow-300");

        // Jika aktif → matikan
        if (active) {
            tag.classList.remove("bg-yellow-300");
            const closeBtn = tag.querySelector(".x-btn");
            if (closeBtn) closeBtn.remove();
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
            hasilPencarianSection.classList.add("hidden");
        });

        const kategori = tag.innerText.replace("×","").trim().toLowerCase();
        const algo = StringMatching.selectBestAlgorithm(kategori);
        Promise.all(allBooks.map(b => StringMatching.searchWithAlgorithm(b.title.toLowerCase(), kategori, algo).then(pos => (pos.length > 0 ? b : null)))).then(results => {
            const filtered = results.filter(Boolean);
            hasilPencarianSection.classList.remove("hidden");
            hasilPencarianList.innerHTML = filtered.length
                ? filtered.map(b => `
                    <div>
                        <img src="/${b.img}" class="w-full rounded-lg shadow-md">
                        <p class="mt-2 text-sm font-semibold">${b.title}</p>
                        <p class="text-xs text-gray-500">${b.jenis} • ${b.bahasa}</p>
                        <p class="text-xs text-gray-500">Tahun: ${b.tahun}</p>
                    </div>`
                ).join("")
                : `<p class="text-red-600">Tidak ada buku ditemukan.</p>`;
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

</body>
</html>
