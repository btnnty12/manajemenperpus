<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pengaturan Akun</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="bg-[#f6d47f] flex">

<style>
#indicator {
    position: absolute;
    left: 0;
    top: 628px;
    width: 75px;
    height: 38px;
    background-color: #F7DE68;
    border-radius: 0 20px 20px 0;
    box-shadow: 0 6px 10px rgba(0,0,0,0.35);
    transition: 0.3s ease-in-out;
    z-index: 0;
}
.menu-item { position: relative; z-index: 5; cursor: pointer; }
.menu-item img, .menu-item i { width: 26px; height: 26px; }
.section-wrapper { width: 100%; padding-left: 25px; padding-right: 40px; margin-top: 20px; }
</style>

<!-- ============================ SIDEBAR ============================ -->
<div class="w-20 bg-[#a63a2d] min-h-screen flex flex-col items-center py-6">
    <div id="indicator"></div>

    <div class="flex flex-col items-center space-y-20 pt-20">
        <a href="{{ route('admin') }}" class="menu-item" data-index="0"><x-icon name="home" class="w-7 h-7 text-white" /></a>
        <a href="{{ route('data.anggota') }}" class="menu-item" data-index="1"><x-icon name="anggota" class="w-7 h-7 text-white" /></a>
        <a href="{{ route('kelola.buku') }}" class="menu-item" data-index="2"><x-icon name="buku" class="w-7 h-7 text-white" /></a>
        <a href="{{ route('laporan-peminjaman') }}" class="menu-item" data-index="3"><x-icon name="grafik" class="w-7 h-7 text-white" /></a>
        <a href="{{ route('kelola-user') }}" class="menu-item" data-index="4"><x-icon name="user" class="w-7 h-7 text-white" /></a>
        <a href="{{ route('pengaturan') }}" class="menu-item" data-index="5"><x-icon name="setting" class="w-7 h-7 text-white" /></a>
    </div>

    <a href="{{ url('/logout') }}" class="menu-item mt-auto mb-4" aria-label="Logout">
        <x-icon name="logout" class="w-7 h-7 text-white" />
    </a>
</div>

<!-- ============================ CONTENT ============================ -->
<div class="flex-1 py-6 px-10">

 <!-- TOPBAR -->
    <div class="flex justify-end items-center w-full py-4 px-10 text-white space-x-6">
        <div class="border-l border-white h-6"></div>
        <button id="messageBtn" onclick="toggleMessagePopup()" class="relative">
            <x-icon name="email" class="w-6 h-6 text-black hover:opacity-80 cursor-pointer" />
            <span id="messageBadge" class="absolute -top-1 -right-1 bg-blue-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
        </button>
        <a href="{{ route('notifikasi') }}" class="relative">
            <x-icon name="notification" class="w-6 h-6 text-black hover:opacity-80 cursor-pointer" />
            <span id="notifBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
        </a>
        <div class="border-l border-white h-6"></div>

        <div class="flex items-center space-x-2">
            <div class="bg-[#717BFF] w-10 h-10 rounded-full flex items-center justify-center text-white font-bold overflow-hidden">
                @if(Auth::check() && Auth::user()->profile_photo)
                    <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr(Auth::user()->nama ?? 'AD', 0, 2)) }}
                @endif
            </div>
            <span class="text-black font-medium">{{ Auth::user()->nama ?? 'Admin' }}</span>
        </div>
    </div>

    <div class="w-full border-b-2 border-white mb-6"></div>

    <!-- ============================ MAIN CONTENT ============================ -->
    <div class="section-wrapper">
        <h1 class="text-4xl font-bold text-[#A63A2D]">Pengaturan Akun</h1>
        <p class="text-gray-700 mt-1">Kelola informasi akun dan data profil Anda.</p>

        <div class="bg-white w-[97%] shadow-xl rounded-xl p-10 mt-10">

            <!-- FLASH MESSAGE -->
            @if(session('success'))
                <div class="bg-green-200 text-green-800 p-3 rounded mb-5">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-200 text-red-800 p-3 rounded mb-5">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ Auth::check() ? route('profile.update') : '#' }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if(Auth::check()) @method('PUT') @endif

                <!-- FOTO PROFIL -->
                <div class="flex flex-col items-center text-center mb-10">
                    <img src="{{ (Auth::check() && Auth::user()->profile_photo) ? asset('storage/' . Auth::user()->profile_photo) : asset('icons/profile.png') }}" id="profilePreview" class="w-32 h-32 rounded-full shadow mb-4">
                    @if(Auth::check())
                    <label class="mt-2 cursor-pointer bg-[#A63A2D] hover:bg-[#923223] text-white px-4 py-2 rounded">
                        Ubah Foto
                        <input type="file" name="profile_photo" id="profilePhotoInput" class="hidden" accept="image/*">
                    </label>
                    @endif
                </div>

                <!-- FORM PROFIL -->
                <div class="grid grid-cols-2 gap-10">
                    <div>
                        <label class="font-semibold">Nama Lengkap</label>
                        <input type="text" name="nama" value="{{ Auth::check() ? old('nama', Auth::user()->nama) : '' }}" class="mt-1 w-full p-3 rounded border shadow" {{ Auth::check() ? '' : 'disabled' }}>
                    </div>

                    <div>
                        <label class="font-semibold">ID User</label>
                        <input type="text" value="{{ Auth::check() ? Auth::user()->id : '' }}" class="mt-1 w-full p-3 rounded border shadow" disabled>
                    </div>

                    <div>
                        <label class="font-semibold">Kata Sandi</label>
                        <input type="password" name="kata_sandi" placeholder="Isi jika ingin ganti" class="mt-1 w-full p-3 rounded border shadow" {{ Auth::check() ? '' : 'disabled' }}>
                    </div>
                    <div>
                        <label class="font-semibold">Konfirmasi Kata Sandi</label>
                        <input type="password" name="kata_sandi_confirmation" placeholder="Ulangi kata sandi" class="mt-1 w-full p-3 rounded border shadow" {{ Auth::check() ? '' : 'disabled' }}>
                    </div>

                    <div>
                        <label class="font-semibold">Tanggal Bergabung</label>
                        <input type="text" value="{{ Auth::check() ? Auth::user()->created_at->format('d F Y') : '' }}" class="mt-1 w-full p-3 rounded border shadow" disabled>
                    </div>

                    <div>
                        <label class="font-semibold">Email</label>
                        <input type="email" name="email" value="{{ Auth::check() ? old('email', Auth::user()->email) : '' }}" class="mt-1 w-full p-3 rounded border shadow" {{ Auth::check() ? '' : 'disabled' }}>
                    </div>

                    <div>
                        <label class="font-semibold">No. Telepon</label>
                        <input type="text" name="phone" value="{{ Auth::check() ? old('phone', Auth::user()->phone) : '' }}" class="mt-1 w-full p-3 rounded border shadow" {{ Auth::check() ? '' : 'disabled' }}>
                    </div>

                    <!-- Role Admin -->
                    @if(Auth::check() && Auth::user()->peran === 'admin')
                    <div>
                        <label class="font-semibold">Role User</label>
                        <select name="peran" class="mt-1 w-full p-3 rounded border shadow">
                            <option value="admin" selected>Admin</option>
                        </select>
                    </div>
                    @endif
                </div>

                @if(Auth::check())
                <div class="flex justify-center mt-12">
                    <button type="submit" class="bg-[#A63A2D] hover:bg-[#923223] transition text-white px-10 py-3 rounded-xl font-bold text-lg shadow">
                        Perbarui
                    </button>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>

<!-- POPUP PESAN -->
<div id="messagePopup" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-[32rem] max-h-[80vh] overflow-y-auto relative">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-[#A63A2D]">Pesan</h3>
            <div class="flex gap-2">
                <button onclick="toggleMessagePopup()" class="text-gray-500 hover:text-gray-900 text-2xl">&times;</button>
            </div>
        </div>
        <div class="mb-4 bg-gray-50 rounded-xl p-3">
            <p class="text-sm font-semibold text-[#A63A2D] mb-2">Kirim Pesan ke Pengguna</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                <select id="recipientSelect" class="border rounded p-2 text-sm md:col-span-1"></select>
                <input id="composeIsi" type="text" class="border rounded p-2 text-sm md:col-span-2" placeholder="Tulis pesan singkat..." />
            </div>
            <div class="flex justify-end mt-2">
                <button class="px-3 py-1 bg-[#A63A2D] text-white rounded text-xs" onclick="sendMessage()">Kirim</button>
            </div>
        </div>
        <ul id="messageList" class="space-y-3">
            <li class="p-3 text-center text-gray-500">Memuat pesan...</li>
        </ul>
    </div>
</div>

<script>
function toggleMessagePopup() {
    const popup = document.getElementById('messagePopup');
    popup.classList.toggle('hidden');
    popup.classList.toggle('flex');
    if (!popup.classList.contains('hidden')) {
        loadRecipients();
        loadMessages();
    }
}
function loadMessages() {
    fetch('/api/pesan?only_inbox=1', {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        const list = document.getElementById('messageList');
        const mBadge = document.getElementById('messageBadge');
        if (mBadge) {
            const count = data.unread_count || 0;
            mBadge.textContent = count;
            mBadge.classList.toggle('hidden', count === 0);
        }
        if (Array.isArray(data.data) && data.data.length > 0) {
            list.innerHTML = data.data.map(m => {
                const confirmed = m.status === 'confirmed';
                const status = confirmed ? '<span class="text-green-700 text-xs ml-2">Dikonfirmasi</span>' : '';
                return `
                    <li class="p-3 bg-gray-100 rounded-xl shadow hover:shadow-md">
                        <p class="text-sm font-semibold">Pesan ${status}</p>
                        <p class="text-sm text-gray-700 mt-1">${m.isi}</p>
                        <div class="flex justify-end mt-2 text-xs gap-4">
                            ${!confirmed ? `<button class="text-green-700" onclick="confirmMessage(${m.id})">Konfirmasi</button>` : ''}
                            <button class="text-blue-700" onclick="replyMessage(${m.id})">Balas</button>
                        </div>
                    </li>
                `;
            }).join('');
        } else {
            list.innerHTML = '<li class="p-3 text-center text-gray-500">Tidak ada pesan</li>';
        }
    })
    .catch(() => {
        const list = document.getElementById('messageList');
        list.innerHTML = '<li class="p-3 text-center text-red-600">Gagal memuat pesan</li>';
    });
}
function loadRecipients() {
    fetch('/api/pengguna', {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(users => {
        const sel = document.getElementById('recipientSelect');
        if (!sel) return;
        const onlyPengguna = Array.isArray(users) ? users.filter(u => u.peran === 'pengguna') : [];
        sel.innerHTML = onlyPengguna.map(u => `<option value="${u.id}">${u.nama} (${u.email})</option>`).join('');
    });
}
function sendMessage() {
    const sel = document.getElementById('recipientSelect');
    const isi = document.getElementById('composeIsi');
    if (!sel || !isi) return;
    const penerima_id = sel.value;
    const text = isi.value.trim();
    if (!penerima_id || !text) return alert('Pilih penerima dan isi pesan');
    fetch('/api/pesan', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ penerima_id, isi: text })
    })
    .then(res => {
        if (!res.ok) throw new Error();
        isi.value = '';
        loadMessages();
    })
    .catch(() => alert('Gagal mengirim pesan'));
}
function confirmMessage(id) {
    fetch(`/api/pesan/${id}/confirm`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json'
        }
    }).then(() => loadMessages());
}
function replyMessage(id) {
    const isi = prompt('Tulis balasan:');
    if (!isi) return;
    fetch(`/api/pesan/${id}/reply`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ isi })
    }).then(() => loadMessages());
}
function loadNotifBadge() {
    fetch('/api/notifikasi', {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        const badge = document.getElementById('notifBadge');
        if (badge) {
            const count = data.unread_count || 0;
            badge.textContent = count;
            badge.classList.toggle('hidden', count === 0);
        }
    });
}
document.addEventListener('DOMContentLoaded', () => {
    loadMessages();
    loadNotifBadge();
});
document.getElementById('messagePopup')?.addEventListener('click', function(e) {
    if (e.target === this) toggleMessagePopup();
});
</script>

<script>
const input = document.getElementById('profilePhotoInput');
const preview = document.getElementById('profilePreview');

if(input){
    input.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });
});

document.querySelectorAll('.menu-item').forEach((item, index) => {
    item.addEventListener('click', function () {
        document.getElementById('indicator').style.top = (310 + index * 95) + 'px';
        switch(index){
            case 0: window.location.href = "/admin"; break;
            case 1: window.location.href = "/data-anggota"; break;
            case 2: window.location.href = "/kelola-buku"; break;
            case 3: window.location.href = "/laporan-peminjaman"; break;
            case 4: window.location.href = "/kelola-user"; break;
            case 5: window.location.href = "/pengaturan"; break;
        }
    });
});
</script>

</body>
</html>
