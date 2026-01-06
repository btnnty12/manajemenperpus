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
        <button id="msgBtn" class="text-2xl hover:opacity-80 relative">✉️
            <span id="msgBadge" class="absolute -top-1 -right-1 bg-blue-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
        </button>
        <div class="bg-blue-500 w-10 h-10 rounded-full text-white flex items-center justify-center font-bold cursor-pointer overflow-hidden">
            @if(Auth::check() && Auth::user()->profile_photo)
                <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" class="w-full h-full object-cover">
            @else
                {{ strtoupper(substr(Auth::user()->nama ?? 'PU', 0, 2)) }}
            @endif
        </div>
        <span class="font-semibold text-lg">{{ Auth::user()->nama ?? 'Pengguna' }}</span>
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
                    <img src="{{ (Auth::check() && Auth::user()->profile_photo) ? asset('storage/' . Auth::user()->profile_photo) : asset('icons/profile.png') }}" id="profilePreview" class="w-24 h-24 rounded-full shadow mb-3">
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
            if (badge) {
                const c = d.unread_count || 0;
                badge.textContent = c;
                badge.classList.toggle('hidden', c === 0);
            }
        });
    }
    document.addEventListener('DOMContentLoaded', loadNotifikasi);
</script>
</body>
</html>
