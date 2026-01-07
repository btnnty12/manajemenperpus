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
            Hai {{ Auth::user()->nama ?? 'Pengguna' }}! Silakan isi formulir berikut untuk mengajukan peminjaman buku.<br>
            <span class="font-semibold text-orange-600">Pengajuanmu akan dikonfirmasi oleh admin/staff sebelum buku bisa diambil.</span><br>
            Setelah diajukan, kamu akan mendapat notifikasi ketika pengajuan disetujui atau ditolak.
        </p>
        
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                <div>
                    <p class="font-semibold text-blue-800 mb-1">Informasi Penting:</p>
                    <ul class="text-sm text-blue-700 list-disc list-inside space-y-1">
                        <li>Formulir ini akan dikirim untuk persetujuan admin/staff</li>
                        <li>Status peminjaman dapat dilihat di halaman "Data Pengembalian Buku"</li>
                        <li>Kamu akan mendapat notifikasi ketika pengajuan disetujui atau ditolak</li>
                        <li>Buku hanya bisa diambil setelah pengajuan disetujui oleh admin/staff</li>
                    </ul>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-200 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="bg-red-200 text-red-800 p-3 rounded mb-4">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="pinjamanForm" method="POST" action="{{ route('pengembalian.store') }}" class="grid grid-cols-2 gap-6">
            @csrf
            <input type="hidden" name="buku_id" id="buku_id">

            <!-- KIRI -->
            <div>
                <label class="font-semibold">Nama Lengkap</label>
                <input type="text" value="{{ Auth::user()->nama ?? 'Pengguna' }}"
                       class="w-full border rounded px-2 py-1 mt-1" readonly>

                <label class="font-semibold mt-4 block">ID Peminjaman</label>
                <input type="text" value="{{ $idPeminjaman ?? 'P-' . date('Ymd') . '-0001' }}"
                       class="w-full border rounded px-2 py-1 mt-1 bg-gray-100" readonly>

                <label class="font-semibold mt-4 block">ID Anggota</label>
                <input type="text" value="{{ $idAnggota ?? str_pad(Auth::id(), 8, '0', STR_PAD_LEFT) }}"
                       class="w-full border rounded px-2 py-1 mt-1 bg-gray-100" readonly>

                <label class="font-semibold mt-4 block">Tanggal Peminjaman</label>
                <input type="text" value="{{ Carbon\Carbon::now()->format('d F Y') }}"
                       class="w-full border rounded px-2 py-1 mt-1 bg-gray-100" readonly>
            </div>

            <!-- KANAN -->
            <div class="relative">
                <label class="font-semibold">Judul Buku <span class="text-red-500">*</span></label>
                <input type="text" id="judulBuku" name="judul_buku" placeholder="Ketik judul buku..."
                       value="{{ $buku->judul ?? old('judul_buku') }}"
                       class="w-full border rounded px-2 py-1 mt-1" autocomplete="off" required>
                <div id="bukuSuggestions" class="hidden absolute z-50 bg-white border rounded shadow-lg max-h-60 overflow-y-auto w-full mt-1"></div>
                <p id="bukuStatus" class="text-sm mt-1"></p>
                @if($buku ?? null)
                    <input type="hidden" id="buku_id" name="buku_id" value="{{ $buku->id }}">
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            selectBuku({
                                id: {{ $buku->id }},
                                judul: '{{ $buku->judul }}',
                                penulis: '{{ $buku->penulis ?? 'N/A' }}',
                                tahun_terbit: '{{ $buku->tahun_terbit ?? 'N/A' }}'
                            });
                        });
                    </script>
                @endif

                <label class="font-semibold mt-4 block">Penulis</label>
                <input type="text" id="penulisBuku" name="penulis"
                       class="w-full border rounded px-2 py-1 mt-1 bg-gray-100" readonly>

                <label class="font-semibold mt-4 block">Tahun Terbit</label>
                <input type="text" id="tahunTerbit" name="tahun_terbit"
                       class="w-full border rounded px-2 py-1 mt-1 bg-gray-100" readonly>

                <label class="font-semibold mt-4 block">Catatan</label>
                <input type="text" name="catatan" placeholder="Opsional"
                       class="w-full border rounded px-2 py-1 mt-1">
            </div>

            <!-- FULL ROW -->
            <div class="col-span-2">
                <label class="font-semibold mt-4 block">Tanggal Pengembalian</label>
                <input type="text" value="{{ Carbon\Carbon::now()->addDays(7)->format('d F Y') }}"
                       class="w-full border rounded px-2 py-1 mt-1 bg-gray-100" readonly>

                <p class="text-xs text-gray-500 mt-1">
                    Pastikan semua data sudah benar sebelum diajukan
                </p>
            </div>
        </form>

        <!-- TOMBOL -->
        <div class="flex justify-center gap-4 mt-10">
            <button type="submit" form="pinjamanForm" class="bg-green-600 text-white px-6 py-2 rounded-full hover:bg-green-700">
                Ajukan Peminjaman
            </button>

            <button onclick="window.location.href='{{ route('home') }}'"
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

<!-- Auto-detect Buku Script -->
<script>
    const judulBukuInput = document.getElementById('judulBuku');
    const bukuSuggestions = document.getElementById('bukuSuggestions');
    const bukuStatus = document.getElementById('bukuStatus');
    const penulisBuku = document.getElementById('penulisBuku');
    const tahunTerbit = document.getElementById('tahunTerbit');
    const bukuIdInput = document.getElementById('buku_id');
    let searchTimeout;

    judulBukuInput.addEventListener('input', function() {
        const keyword = this.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (keyword.length < 2) {
            bukuSuggestions.classList.add('hidden');
            bukuStatus.textContent = '';
            clearBukuFields();
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`/api/search-buku?q=${encodeURIComponent(keyword)}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.data && data.data.length > 0) {
                    bukuSuggestions.innerHTML = '';
                    bukuSuggestions.classList.remove('hidden');
                    
                    data.data.forEach(buku => {
                        const item = document.createElement('div');
                        item.className = 'p-3 hover:bg-gray-100 cursor-pointer border-b';
                        item.textContent = `${buku.judul} - ${buku.penulis} (${buku.tahun_terbit})`;
                        item.addEventListener('click', () => {
                            selectBuku(buku);
                        });
                        bukuSuggestions.appendChild(item);
                    });
                    
                    bukuStatus.textContent = '';
                    bukuStatus.className = 'text-sm mt-1';
                } else {
                    bukuSuggestions.classList.add('hidden');
                    bukuStatus.textContent = 'Buku tidak tersedia';
                    bukuStatus.className = 'text-sm mt-1 text-red-600';
                    clearBukuFields();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                bukuSuggestions.classList.add('hidden');
            });
        }, 300);
    });

    function selectBuku(buku) {
        judulBukuInput.value = buku.judul;
        penulisBuku.value = buku.penulis || 'N/A';
        tahunTerbit.value = buku.tahun_terbit || 'N/A';
        bukuIdInput.value = buku.id;
        bukuSuggestions.classList.add('hidden');
        bukuStatus.textContent = 'Buku ditemukan ✓';
        bukuStatus.className = 'text-sm mt-1 text-green-600';
    }

    function clearBukuFields() {
        penulisBuku.value = '';
        tahunTerbit.value = '';
        bukuIdInput.value = '';
    }

    // Tutup suggestions saat klik di luar
    document.addEventListener('click', function(e) {
        if (!judulBukuInput.contains(e.target) && !bukuSuggestions.contains(e.target)) {
            bukuSuggestions.classList.add('hidden');
        }
    });

    // Validasi form sebelum submit
    document.getElementById('pinjamanForm').addEventListener('submit', function(e) {
        if (!bukuIdInput.value) {
            e.preventDefault();
            alert('Silakan pilih buku dari daftar yang muncul saat mengetik judul buku.');
            judulBukuInput.focus();
        }
    });
</script>

</body>
</html>