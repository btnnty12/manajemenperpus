<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pengaturan Akun</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-b from-yellow-200 to-yellow-300 min-h-screen flex">

<aside class="w-20 bg-[#C34722] text-white flex flex-col items-center py-6 shadow-lg relative">
    <div id="highlight" class="absolute left-0 w-16 h-12 bg-white/30 rounded-xl transition-all duration-300 shadow-md -z-10"></div>
    <button onclick="window.location.href='/home';" class="menu-item w-12 h-12 flex items-center justify-center text-2xl opacity-80 hover:opacity-100"><i class="fa-solid fa-house"></i></button>
    <button onclick="window.location.href='/search';" class="menu-item w-12 h-12 flex items-center justify-center text-2xl opacity-80 hover:opacity-100"><i class="fa-solid fa-magnifying-glass"></i></button>
    <button onclick="window.location.href='/pengembalian-buku';" class="menu-item w-12 h-12 flex items-center justify-center text-2xl opacity-80 hover:opacity-100"><i class="fa-solid fa-file-lines"></i></button>
    <button onclick="window.location.href='/pengaturan';" class="menu-item w-12 h-12 flex items-center justify-center text-2xl opacity-80 hover:opacity-100"><i class="fa-solid fa-gear"></i></button>
    <button onclick="window.location.href='/logout';" class="menu-item w-12 h-12 flex items-center justify-center text-2xl opacity-80 hover:opacity-100 mb-4 mt-auto"><i class="fa-solid fa-right-from-bracket"></i></button>
</aside>

<main class="flex-1 p-8">
    <div class="flex justify-end items-center space-x-6 mb-6 relative">
        <button id="notifBtn" onclick="toggleNotifPopup()" class="text-2xl hover:opacity-80 relative">🔔
            <span id="notifBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
        </button>
        <div class="bg-blue-500 w-10 h-10 rounded-full text-white flex items-center justify-center font-bold cursor-pointer overflow-hidden">
            @if(Auth::check() && Auth::user()->foto)
                <img src="{{ asset('storage/' . Auth::user()->foto) }}" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='{{ strtoupper(substr(Auth::user()->nama ?? 'PU', 0, 2)) }}'">
            @else
                {{ strtoupper(substr(Auth::user()->nama ?? 'PU', 0, 2)) }}
            @endif
        </div>
        <span class="font-semibold text-lg">{{ Auth::user()->nama ?? 'Pengguna' }}</span>
    </div>

    <!-- POPUP NOTIFIKASI (Sama seperti di halaman utama) -->
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

    <div class="w-full border-b-2 border-white mb-6"></div>

    <section class="max-w-4xl">
        <h1 class="text-3xl font-bold text-[#7c1d0f]">Pengaturan Akun</h1>
        <p class="text-sm text-gray-700 mt-1">Perbarui informasi profil dan kredensial Anda.</p>

        <div class="bg-white rounded-2xl shadow-xl p-6 mt-6">
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

            <form action="{{ Auth::check() ? route('profile.update') : '#' }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @if(Auth::check()) @method('PUT') @endif

                <div class="flex flex-col items-center">
                    @if(Auth::check() && Auth::user()->foto)
                        <img src="{{ asset('storage/' . Auth::user()->foto) }}" id="profilePreview" class="w-24 h-24 rounded-full shadow mb-3 object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div id="profilePreviewFallback" class="w-24 h-24 rounded-full shadow mb-3 bg-gray-300 flex items-center justify-center text-gray-600 font-bold text-2xl" style="display: none;">
                            {{ strtoupper(substr(Auth::user()->nama ?? 'PU', 0, 2)) }}
                        </div>
                    @else
                        <div id="profilePreview" class="w-24 h-24 rounded-full shadow mb-3 bg-gray-300 flex items-center justify-center text-gray-600 font-bold text-2xl">
                            {{ strtoupper(substr(Auth::user()->nama ?? 'PU', 0, 2)) }}
                        </div>
                    @endif
                    @if(Auth::check())
                    <label class="cursor-pointer bg-[#A63A2D] hover:bg-[#923223] text-white px-4 py-2 rounded text-sm">
                        Ubah Foto
                        <input type="file" name="profile_photo" id="profilePhotoInput" class="hidden" accept="image/*">
                    </label>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="font-semibold text-sm">Nama Lengkap</label>
                        <input type="text" name="nama" value="{{ Auth::check() ? old('nama', Auth::user()->nama) : '' }}" class="mt-1 w-full p-3 rounded border shadow-sm" {{ Auth::check() ? '' : 'disabled' }}>
                    </div>
                    <div>
                        <label class="font-semibold text-sm">Email</label>
                        <input type="email" name="email" value="{{ Auth::check() ? old('email', Auth::user()->email) : '' }}" class="mt-1 w-full p-3 rounded border shadow-sm" {{ Auth::check() ? '' : 'disabled' }}>
                    </div>
                    <div>
                        <label class="font-semibold text-sm">Kata Sandi</label>
                        <input type="password" name="kata_sandi" placeholder="Isi jika ingin ganti" class="mt-1 w-full p-3 rounded border shadow-sm" {{ Auth::check() ? '' : 'disabled' }}>
                    </div>
                    <div>
                        <label class="font-semibold text-sm">Konfirmasi Kata Sandi</label>
                        <input type="password" name="kata_sandi_confirmation" placeholder="Ulangi kata sandi" class="mt-1 w-full p-3 rounded border shadow-sm" {{ Auth::check() ? '' : 'disabled' }}>
                    </div>
                    <div>
                        <label class="font-semibold text-sm">No. Telepon</label>
                        <input type="text" name="phone" value="{{ Auth::check() ? old('phone', Auth::user()->phone) : '' }}" class="mt-1 w-full p-3 rounded border shadow-sm" {{ Auth::check() ? '' : 'disabled' }}>
                    </div>
                    <div>
                        <label class="font-semibold text-sm">Tanggal Bergabung</label>
                        <input type="text" value="{{ Auth::check() ? Auth::user()->created_at->format('d F Y') : '' }}" class="mt-1 w-full p-3 rounded border shadow-sm" disabled>
                    </div>
                </div>

                @if(Auth::check())
                <div class="flex gap-3">
                    <button type="submit" class="bg-[#A63A2D] hover:bg-[#923223] text-white px-6 py-2 rounded-lg text-sm font-semibold">Simpan Perubahan</button>
                    <a href="{{ route('home') }}" class="text-sm text-blue-600 font-semibold">Kembali ke Beranda</a>
                </div>
                @endif
            </form>
        </div>
    </section>
</main>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<script>
    const items = document.querySelectorAll(".menu-item");
    const highlight = document.getElementById("highlight");
    items.forEach((btn, index) => { btn.addEventListener("click", () => { highlight.style.top = (index * 80) + "px"; }); });
    highlight.style.top = (3 * 80) + "px";
</script>
<script>
    const input = document.getElementById('profilePhotoInput');
    const preview = document.getElementById('profilePreview');
    if(input){
        input.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) { preview.src = e.target.result; }
                reader.readAsDataURL(file);
            }
        });
    }
</script>
<script>
    function toggleNotifPopup() {
        const popup = document.getElementById('notifPopup');
        if (!popup) return;
        popup.classList.toggle('hidden');
        popup.classList.toggle('flex');
        if (!popup.classList.contains('hidden')) loadNotifikasi();
    }

    function loadNotifikasi() {
        fetch('/api/notifikasi', {
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(d => {
            const badge = document.getElementById('notifBadge');
            const notifList = document.getElementById('notifList');
            if (badge) {
                const c = d.unread_count || 0;
                badge.textContent = c;
                badge.classList.toggle('hidden', c === 0);
            }

            if (!notifList) return;

            if (d.notifikasi && d.notifikasi.length > 0) {
                notifList.innerHTML = d.notifikasi.map(notif => {
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

    // Load notifikasi saat halaman dimuat
    document.addEventListener('DOMContentLoaded', () => {
        loadNotifikasi();
    });
</script>
</body>
</html>
