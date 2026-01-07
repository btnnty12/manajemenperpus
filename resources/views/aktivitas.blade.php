<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Aktivitas Saya</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body class="bg-gradient-to-br from-yellow-200 to-yellow-300 min-h-screen flex">

<aside class="w-20 bg-[#C34722] text-white flex flex-col items-center py-6 shadow-lg relative">
    <div id="highlight" class="absolute left-0 w-16 h-12 bg-white/30 rounded-xl transition-all duration-300 shadow-md -z-10"></div>
    <button onclick="window.location.href='/home';" class="menu-item w-12 h-12 flex items-center justify-center text-2xl opacity-80 hover:opacity-100"><i class="fa-solid fa-house"></i></button>
    <button onclick="window.location.href='/search';" class="menu-item w-12 h-12 flex items-center justify-center text-2xl opacity-80 hover:opacity-100"><i class="fa-solid fa-magnifying-glass"></i></button>
    <button onclick="window.location.href='/pengembalian-buku';" class="menu-item w-12 h-12 flex items-center justify-center text-2xl opacity-80 hover:opacity-100"><i class="fa-solid fa-file-lines"></i></button>
    <button onclick="window.location.href='/pinjaman';" class="menu-item w-12 h-12 flex items-center justify-center text-2xl opacity-80 hover:opacity-100"><i class="fa-solid fa-book"></i></button>
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

    <div class="w-full border-b-2 border-white mb-6"></div>

    <section class="max-w-4xl">
        <h1 class="text-3xl font-bold text-[#7c1d0f]">Aktivitas Saya</h1>
        <p class="text-sm text-gray-700 mt-1">Riwayat aktivitas Anda di sistem perpustakaan.</p>

        <div class="bg-white rounded-2xl shadow-xl p-6 mt-6">
            @forelse($activities ?? [] as $activity)
            <div class="border-b border-gray-200 py-4 last:border-b-0">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                        @if($activity->type === 'login')
                            <i class="fas fa-sign-in-alt text-blue-600"></i>
                        @elseif($activity->type === 'pinjam_buku')
                            <i class="fas fa-book text-green-600"></i>
                        @elseif($activity->type === 'kembalikan_buku')
                            <i class="fas fa-book-reader text-orange-600"></i>
                        @elseif($activity->type === 'cari_buku')
                            <i class="fas fa-search text-purple-600"></i>
                        @else
                            <i class="fas fa-circle text-gray-600"></i>
                        @endif
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-800">{{ $activity->description ?? ucfirst(str_replace('_', ' ', $activity->type)) }}</p>
                        @if($activity->meta && is_array($activity->meta))
                            @if(isset($activity->meta['buku_judul']))
                                <p class="text-sm text-gray-600 mt-1">Buku: {{ $activity->meta['buku_judul'] }}</p>
                            @endif
                            @if(isset($activity->meta['keyword']))
                                <p class="text-sm text-gray-600 mt-1">Kata kunci: {{ $activity->meta['keyword'] }}</p>
                            @endif
                        @endif
                        <p class="text-xs text-gray-400 mt-1">{{ $activity->created_at->format('d M Y H:i') }}</p>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-history text-4xl mb-4"></i>
                <p>Belum ada aktivitas</p>
            </div>
            @endforelse
        </div>
    </section>
</main>

<script>
    const items = document.querySelectorAll(".menu-item");
    const highlight = document.getElementById("highlight");
    items.forEach((btn, index) => { 
        btn.addEventListener("click", () => { 
            highlight.style.top = (index * 80) + "px"; 
        }); 
    });
    highlight.style.top = (3 * 80) + "px";
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

