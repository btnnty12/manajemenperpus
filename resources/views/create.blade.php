<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Formulir Peminjaman</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>

<body class="bg-gradient-to-b from-yellow-200 to-yellow-300 min-h-screen flex">

<!-- ======================== -->
<!--     SIDEBAR NAVBAR       -->
<!-- ======================== -->
<aside id="sidebar"
       class="w-20 bg-[#C34722] text-white flex flex-col items-center py-6 shadow-lg relative">

    <div id="menuWrapper" class="relative flex flex-col items-center space-y-8 flex-1">

        <!-- Highlight PUTIH -->
        <div id="highlight"
             class="absolute left-0 w-16 h-12 bg-white/30 rounded-xl transition-all duration-300 shadow-md -z-10"
             style="top: 0;">
        </div>

        <!-- Icons -->
        <button onclick="window.location.href='{{ route('home') }}'"
            class="menu-item w-12 h-12 flex items-center justify-center text-2xl opacity-80 hover:opacity-100">
            <i class="fa-solid fa-house"></i>
        </button>

        <button onclick="window.location.href='{{ route('search') }}'"
            class="menu-item w-12 h-12 flex items-center justify-center text-2xl opacity-80 hover:opacity-100">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>

        <button onclick="window.location.href='{{ route('pengembalian.index') }}'"
            class="menu-item w-12 h-12 flex items-center justify-center text-2xl opacity-80 hover:opacity-100">
            <i class="fa-solid fa-file-lines"></i>
        </button>

        <button onclick="window.location.href='{{ route('pengaturan') }}'"
            class="menu-item w-12 h-12 flex items-center justify-center text-2xl opacity-80 hover:opacity-100">
            <i class="fa-solid fa-gear"></i>
        </button>
    </div>

    <!-- LOGOUT -->
    <button onclick="window.location.href='{{ route('logout') }}'"
            class="menu-item w-12 h-12 flex items-center justify-center text-2xl opacity-80 hover:opacity-100 mb-4 mt-auto">
        <i class="fa-solid fa-right-from-bracket"></i>
    </button>

</aside>

        <!-- CONTENT -->
<div class="flex-1 py-10 px-10">
    <div class="bg-white w-full rounded-xl shadow-lg p-10">

        <h1 class="text-center text-2xl font-bold">Formulir Peminjaman Buku</h1>
        <p class="text-center text-sm text-gray-500 -mt-1 mb-8">
            Hai {{ $user['nama'] ?? 'Pengguna' }}! Silakan isi formulir berikut untuk mengajukan peminjaman buku.<br>
            Pengajuanmu akan dikonfirmasi oleh admin sebelum buku bisa diambil, ya.
        </p>

        <form class="grid grid-cols-2 gap-6">

            <!-- KIRI -->
            <div>
                <label class="font-semibold">Nama Lengkap</label>
                <input type="text" value="{{ $user['nama'] ?? 'Pengguna' }}"
                       class="w-full border rounded px-2 py-1 mt-1" readonly>

                <label class="font-semibold mt-4 block">ID Peminjaman</label>
                <input type="text"
                       class="w-full border rounded px-2 py-1 mt-1">

                <label class="font-semibold mt-4 block">ID Anggota</label>
                <input type="text" value="20250112"
                       class="w-full border rounded px-2 py-1 mt-1">

                <label class="font-semibold mt-4 block">Tanggal Peminjaman</label>
                <input type="text" value="18 Oktober 2025"
                       class="w-full border rounded px-2 py-1 mt-1">
            </div>

            <!-- KANAN -->
            <div>
                <label class="font-semibold">Judul Buku</label>
                <input type="text" value="Cyber Security And Network Security"
                       class="w-full border rounded px-2 py-1 mt-1">

                <label class="font-semibold mt-4 block">Penulis</label>
                <input type="text" value="Iwan Sofana & Rifkie Primartha"
                       class="w-full border rounded px-2 py-1 mt-1">

                <label class="font-semibold mt-4 block">Tahun Terbit</label>
                <input type="text" value="2019"
                       class="w-full border rounded px-2 py-1 mt-1">

                <label class="font-semibold mt-4 block">Catatan</label>
                <input type="text"
                       class="w-full border rounded px-2 py-1 mt-1">
            </div>

            <!-- FULL ROW -->
            <div>
                <label class="font-semibold mt-4 block">Tanggal Pengembalian</label>
                <input type="text" value="25 Oktober 2025"
                       class="w-full border rounded px-2 py-1 mt-1">

                <p class="text-xs text-gray-500 mt-1">
                    Pastikan semua data sudah benar sebelum diajukan
                </p>
            </div>

            <div>
                <p class="font-semibold mt-4">Status:</p>
                <p class="text-gray-400 italic text-sm">-</p>
            </div>
        </form>

        <!-- TOMBOL -->
        <div class="flex justify-center gap-4 mt-10">
            <button class="bg-green-600 text-white px-6 py-2 rounded-full hover:bg-green-700">
                Ajukan Peminjaman
            </button>

            <button onclick="window.location.href='{{ url('/home') }}'"
        class="bg-red-600 text-white px-6 py-2 rounded-full hover:bg-red-700">
    Batal
</button>
        </div>

    </div>
</div>

<!-- Highlight Script -->
<script>
    const items = document.querySelectorAll(".menu-item");
    const highlight = document.getElementById("highlight");

    items.forEach((btn, index) => {
        btn.addEventListener("click", () => {
            highlight.style.top = (index * 80) + "px";
        });
    });

    highlight.style.top = "160px"; // Set untuk posisi pengembalian-buku
</script>

</body>
</html>