<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Admin Dashboard - Perpustakaan</title>

  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style> 
    /* small customizations to match the feel */
    .sidebar-bg { background: #8a3b2b; }            /* coklat sidebar */
    .page-gradient { background: linear-gradient(180deg,#f7d77a 0%, #f6d99f 30%, #fff7ec 100%); }
    .banner-deep { background: #9b3d29; }           /* deep red/brown banner */
    .card-soft { background: rgba(255,255,255,0.92); }
    .stat-radius { border-radius: 14px; }
    .stat-num { font-size: 42px; font-weight: 800; }
    .activity-item { background: #f1d6a2; }        /* pale yellow item */
    /* small scrollbar for activity list */
    .scrollbar-thin::-webkit-scrollbar { height: 8px; width: 8px; }
    .scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.12); border-radius: 999px; }

    /* indikator kuning */
    #indicator {
        position: absolute;
        left: 0;
        top: 98px; 
        width: 75px;
        height: 38px;
        background-color: #F7DE68;
        border-radius: 0 20px 20px 0;
        box-shadow: 0 6px 10px rgba(0,0,0,0.35);
        transition: 0.3s ease-in-out;
        z-index: 0;
    }

    .menu-item {
        position: relative;
        z-index:    5;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .menu-item img {
        width: 26px;
        height: 26px;
    }
</style>

</head>
<body class="page-gradient min-h-screen text-gray-800">

  <div class="flex">

<!-- SIDEBAR -->
<div class="w-20 bg-[#a63a2d] min-h-screen flex flex-col items-center py-6 relative">

    <!-- INDIKATOR AKTIF -->
    <div id="indicator"></div>

    <!-- MENU ATAS -->
    <div class="flex flex-col items-center space-y-20 pt-20 w-full">
        <a href="{{ route('admin') }}" class="menu-item" aria-label="Dashboard"><x-icon name="home" class="w-7 h-7 text-white" /></a>
        <a href="{{ route('data.anggota') }}" class="menu-item" aria-label="Data Anggota"><x-icon name="anggota" class="w-7 h-7 text-white" /></a>
        <a href="{{ route('kelola.buku') }}" class="menu-item" aria-label="Kelola Buku"><x-icon name="buku" class="w-7 h-7 text-white" /></a>
        <a href="{{ route('laporan-peminjaman') }}" class="menu-item" aria-label="Laporan Peminjaman"><x-icon name="grafik" class="w-7 h-7 text-white" /></a>
        <a href="{{ route('kelola-user') }}" class="menu-item" aria-label="Kelola User"><x-icon name="user" class="w-7 h-7 text-white" /></a>
    </div>

    <!-- LOGOUT PALING BAWAH -->
    <a href="{{ url('/logout') }}" class="menu-item mt-auto mb-4" aria-label="Logout">
        <x-icon name="logout" class="w-7 h-7 text-white" />
    </a>
</div>

<!-- MAIN CONTENT -->
<div class="flex-1 py-6 px-10">

    <!-- TOPBAR -->
    <div class="flex justify-end items-center w-full py-4 px-10 text-white space-x-6">

        <!-- Divider kiri -->
        <div class="border-l border-white h-6"></div>

        <!-- Icon notif -->
        <button id="notifBtn" onclick="toggleNotifPopup()" class="relative">
            <x-icon name="notification" class="w-6 h-6 text-black hover:opacity-80 cursor-pointer" />
            <span id="notifBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
        </button>

        <!-- Divider kanan -->
        <div class="border-l border-white h-6"></div>

        <!-- Profile -->
        <div class="relative">
            <div id="adminProfileBtn" class="flex items-center space-x-2 cursor-pointer">
                <div class="bg-[#717BFF] w-10 h-10 rounded-full flex items-center justify-center text-white font-bold overflow-hidden">
                    @if(Auth::check() && Auth::user()->foto)
                        <img src="{{ asset('storage/' . Auth::user()->foto) }}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.parentElement.innerHTML='{{ strtoupper(substr(Auth::user()->nama ?? 'AD', 0, 2)) }}'">
                    @elseif(Auth::check() && Auth::user()->foto_url)
                        <img src="{{ Auth::user()->foto_url }}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.parentElement.innerHTML='{{ strtoupper(substr(Auth::user()->nama ?? 'AD', 0, 2)) }}'">
                    @else
                        {{ strtoupper(substr(Auth::user()->nama ?? 'AD', 0, 2)) }}
                    @endif
                </div>
                <div class="text-left">
                    <div class="text-black font-medium leading-tight">{{ Auth::user()->nama ?? 'Admin' }}</div>
                    <div class="text-xs text-gray-600">Admin</div>
                </div>
            </div>
            <div id="adminProfileDropdown" class="hidden absolute top-14 right-0 w-40 bg-white shadow-xl rounded-xl py-2 z-50 border border-gray-200">
                <a href="{{ route('pengaturan') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
                    <i class="fas fa-cog mr-2"></i> Pengaturan
                </a>
                <a href="/logout" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-semibold transition">
                    <i class="fas fa-sign-out-alt mr-2"></i> Keluar
                </a>
            </div>
        </div>
    </div>

    <!-- GARIS PEMBATAS PANJANG -->
    <div class="w-full border-b-2 border-white mb-6"></div>

      <!-- BANNER -->
      <section class="px-8 pt-8 pb-6">
        <div class="relative">
          <div class="rounded-2xl banner-deep text-white p-8 md:p-12 flex flex-col md:flex-row items-center gap-6">
            <!-- left character -->
            <div class="flex-1 flex items-center">
              <div class="max-w-md">
                <h1 class="text-5xl md:text-6xl font-extrabold leading-tight">Hi, Admin</h1>
                <p class="mt-3 text-white/90">Pantau aktivitas peminjaman dan koleksi terbaru hari ini.</p>
              </div>
            </div>

            <!-- right book illustration -->
            <div class="flex-1 flex justify-end">
              <x-icon name="book-open" class="w-20 h-20 md:w-24 md:h-24 text-white" />
            </div>
          </div>

          <!-- subtle horizontal white line like in design -->
          <div class="absolute left-8 right-8 -bottom-6 h-6"></div>
        </div>
      </section>

      <!-- MAIN GRID -->
      <main class="px-8 pb-12">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 -mt-2">

          <!-- left wide column (cards + chart) -->
          <div class="lg:col-span-2 space-y-6">

            <!-- STAT CARDS (three pastel cards) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
              <div class="stat-radius p-6 shadow-lg card-soft flex items-center gap-4 rounded-xl">
                <div class="w-16 h-16 rounded-lg flex items-center justify-center" style="background:#ead3f8">
                  <x-icon name="buku" class="w-11 h-11 text-gray-700" />
                </div>
                <div>
                  <div class="text-sm text-gray-600">Jumlah Buku</div>
                  <div class="stat-num text-gray-900">{{ $totalBuku ?? 0 }}</div>
                </div>
              </div>

              <div class="stat-radius p-6 shadow-lg card-soft flex items-center gap-4 rounded-xl">
                <div class="w-16 h-16 rounded-lg flex items-center justify-center" style="background:#dbe7ff">
                  <x-icon name="user" class="w-12 h-12 text-gray-700" />
                </div>
                <div>
                  <div class="text-sm text-gray-600">Jumlah User</div>
                  <div class="stat-num text-gray-900">{{ $totalUser ?? 0 }}</div>
                </div>
              </div>

              <div class="stat-radius p-6 shadow-lg card-soft flex items-center gap-4 rounded-xl">
                <div class="w-16 h-16 rounded-lg flex items-center justify-center" style="background:#dff3b8">
                  <x-icon name="buku" class="w-10 h-10 text-gray-700" />
                </div>
                <div>
                  <div class="text-sm text-gray-600">Buku Yang Sedang Dipinjam</div>
                  <div class="stat-num text-gray-900">{{ $totalPinjamanAktif ?? 0 }}</div>
                </div>
              </div>
            </div>

            <!-- chart & small stats header -->
              <div class="p-6 rounded-xl shadow-lg bg-white">
              <div class="flex justify-between items-center mb-4">
                <div>
                  <div class="text-sm text-gray-600">Peminjaman & Pengembalian Buku (Per bulan)</div>
                  <div class="text-2xl font-bold">Total Peminjam <span class="text-amber-600">{{ collect($chartData ?? [])->sum('peminjam') }}</span></div>
                </div>

                <div class="flex items-center gap-4">
                  <!-- legend -->
                  <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-sm" style="background:#3b82f6"></div>
                    <div class="text-sm text-gray-600">Peminjam</div>
                  </div>
                  <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-sm" style="background:#86efac"></div>
                    <div class="text-sm text-gray-600">Pengembalian</div>
                  </div>
                </div>
              </div>

              <canvas id="loanChart" class="w-full" height="160"></canvas>
            </div>

          </div>

          <!-- right column (activity list) -->
          <div class="space-y-6">

            <div class="p-6 rounded-2xl shadow-lg bg-white h-full">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Aktivitas Pengguna Terbaru</h3>
              </div>

              <div class="max-h-[520px] overflow-auto scrollbar-thin pr-2">
                <!-- repeated activity items -->
                @forelse($activities ?? [] as $act)
                <div class="flex items-center gap-4 p-3 mb-3 rounded-lg activity-item shadow-sm">

                  <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-white/80 bg-gray-300 flex items-center justify-center">
                    <span class="text-white font-bold text-sm">{{ strtoupper(substr($act['name'] ?? 'U', 0, 2)) }}</span>
                  </div>

                  <div class="flex-1">
                    <div class="text-sm">
                      <span class="font-semibold">{{ $act['name'] ?? 'Unknown' }}</span>
                      <span class="text-gray-700"> {{ $act['action'] ?? 'Aktivitas' }} Buku 
                        <span class="font-bold">{{ $act['book'] ?? '-' }}</span>
                      </span>
                    </div>

                    @if(!empty($act['note']))
                      <div class="text-xs text-red-600 mt-1">{{ $act['note'] }}</div>
                    @endif
                  </div>

                  <!-- ICON DENGAN DATA ATTRIBUTE UNTUK MODAL -->
                  <div>
                    @php
                      $actionType = strtolower($act['action'] ?? '');
                      $isPinjam = strpos($actionType, 'pinjam') !== false || strpos($actionType, 'ingin') !== false;
                      $isKembali = strpos($actionType, 'kembali') !== false || strpos($actionType, 'mengembalikan') !== false;
                    @endphp
                    @if($isPinjam)
                      <img src="{{ asset('images/icon-cekbuku.png') }}" class="w-5 h-5 cursor-pointer" 
                          data-name="{{ $act['name'] ?? '' }}" 
                          data-action="{{ $act['action'] ?? '' }}" 
                          data-book="{{ $act['book'] ?? '' }}" 
                          data-note="{{ $act['note'] ?? '' }}" 
                          data-type="Pinjam">
                    @elseif($isKembali)
                      <img src="{{ asset('images/icon-reminder.png') }}" class="w-5 h-5 cursor-pointer" 
                          data-name="{{ $act['name'] ?? '' }}" 
                          data-action="{{ $act['action'] ?? '' }}" 
                          data-book="{{ $act['book'] ?? '' }}" 
                          data-note="{{ $act['note'] ?? '' }}" 
                          data-type="Reminder">
                    @else
                      <img src="{{ asset('images/icon-denda.png') }}" class="w-5 h-5 cursor-pointer" 
                          data-name="{{ $act['name'] ?? '' }}" 
                          data-action="{{ $act['action'] ?? '' }}" 
                          data-book="{{ $act['book'] ?? '' }}" 
                          data-note="{{ $act['note'] ?? '' }}" 
                          data-type="Info">
                    @endif
                  </div>

                </div>
                @empty
                <div class="text-center py-8 text-gray-500">
                  <p>Tidak ada aktivitas terbaru</p>
                </div>
                @endforelse

              </div>
            </div>

          </div>

        </div>
        

        <!-- table lower section (optional) -->
        <div class="mt-8">
          <div class="p-6 rounded-xl shadow-lg bg-white">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold">Daftar Peminjaman Terakhir</h3>
              <div class="text-sm text-gray-500">Terbaru</div>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full text-left">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="py-3 px-4 text-sm text-gray-600">Judul Buku</th>
                    <th class="py-3 px-4 text-sm text-gray-600">Peminjam</th>
                    <th class="py-3 px-4 text-sm text-gray-600">Tanggal Pinjam</th>
                    <th class="py-3 px-4 text-sm text-gray-600">Status</th>
                    <th class="py-3 px-4 text-sm text-gray-600">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($recentLoans ?? [] as $loan)
                    @php
                      $statusClass = 'text-gray-600';
                      $statusText = ucfirst(str_replace('_', ' ', $loan->status));
                      
                      if ($loan->status === 'sedang_dipinjam') {
                          $tanggalJatuhTempo = $loan->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($loan->tanggal_jatuh_tempo) : null;
                          if ($tanggalJatuhTempo && \Carbon\Carbon::now()->gt($tanggalJatuhTempo)) {
                              $statusClass = 'text-red-600';
                              $statusText = 'Terlambat';
                          } else {
                              $statusClass = 'text-amber-600';
                              $statusText = 'Sedang Dipinjam';
                          }
                      } elseif ($loan->status === 'dikembalikan') {
                          $statusClass = 'text-green-600';
                          $statusText = 'Dikembalikan';
                      } elseif ($loan->status === 'menunggu_approval') {
                          $statusClass = 'text-yellow-600';
                          $statusText = 'Menunggu Approval';
                      } elseif ($loan->status === 'dapat_diambil') {
                          $statusClass = 'text-blue-600';
                          $statusText = 'Dapat Diambil';
                      }
                    @endphp
                    <tr class="border-t">
                      <td class="py-3 px-4">{{ $loan->buku->judul ?? '-' }}</td>
                      <td class="py-3 px-4">{{ $loan->pengguna->nama ?? '-' }}</td>
                      <td class="py-3 px-4">{{ $loan->tanggal_pinjam ? \Carbon\Carbon::parse($loan->tanggal_pinjam)->format('d M Y') : '-' }}</td>
                      <td class="py-3 px-4 {{ $statusClass }} font-semibold">{{ $statusText }}</td>
                      <td class="py-3 px-4">
                        <button class="text-sm text-indigo-600 hover:underline detail-btn"
                            data-book="{{ $loan->buku->judul ?? '-' }}"
                            data-user="{{ $loan->pengguna->nama ?? '-' }}"
                            data-date="{{ $loan->tanggal_pinjam ? \Carbon\Carbon::parse($loan->tanggal_pinjam)->format('d M Y') : '-' }}"
                            data-status="{{ $statusText }}">
                          Detail
                        </button>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="5" class="py-8 text-center text-gray-500">
                        <p>Tidak ada peminjaman terbaru</p>
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

          </div>
        </div>

      </main>

    </div> <!-- end main area -->
  </div>

  <!-- SCRIPTS: Chart -->
  <script>
    const chartData = @json($chartData ?? []);
    const ctx = document.getElementById('loanChart').getContext('2d');
    const loanChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: chartData.map(item => item.month),
        datasets: [
          {
            label: 'Peminjam',
            data: chartData.map(item => item.peminjam),
            backgroundColor: '#3b82f6',
            borderRadius: 6,
            barThickness: 18
          },
          {
            label: 'Pengembalian',
            data: chartData.map(item => item.pengembalian),
            backgroundColor: '#86efac',
            borderRadius: 6,
            barThickness: 18
          }
        ]
      },
      options: {
        plugins: { legend: { display: false } },
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            ticks: { stepSize: 10, color: '#333' },
            grid: { color: 'rgba(0,0,0,0.05)' }
          },
          x: {
            ticks: { color: '#333' },
            grid: { display: false }
          }
        }
      }
    });
  </script>

 <!-- SCRIPT PINDAH INDIKATOR -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const indicator = document.getElementById('indicator');
    const menuItems = document.querySelectorAll('.menu-item');

    function setActiveIndicator() {
        if (!indicator || !menuItems.length) return;

        const baseTop = 204; // posisi awal icon pertama
        const spacing = 95;  // jarak antar icon
        const path = window.location.pathname;

        // Deteksi aktif berdasarkan path yang mengandung segmen menu
        const mapPathToIndex = [
            { match: '/admin', index: 0 },
            { match: '/data-anggota', index: 1 },
            { match: '/kelola-buku', index: 2 },
            { match: '/laporan-peminjaman', index: 3 },
            { match: '/kelola-user', index: 4 },
        ];

        let activeIndex = 0;
        for (const m of mapPathToIndex) {
            if (path.startsWith(m.match)) {
                activeIndex = m.index;
                break;
            }
        }

        indicator.style.top = (baseTop + spacing * activeIndex) + 'px';
    }

    menuItems.forEach((item, index) => {
        item.addEventListener('click', () => {
            const baseTop = 204;
            const spacing = 95;
            indicator.style.top = (baseTop + spacing * index) + 'px';
            // navigasi memakai href bawaan <a>, jadi tidak perlu window.location manual
        });
    });

    setActiveIndicator();
});
</script>

<!-- MODAL DETAIL AKTIVITAS -->
<div id="activityModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-xl p-6 w-96 relative">
    <button id="closeModal" class="absolute top-3 right-3 text-gray-500 hover:text-gray-900">&times;</button>
    <h3 class="text-lg font-semibold mb-4">Detail Aktivitas</h3>
    <div id="modalContent" class="text-gray-700"></div>
  </div>
</div>

<script>
const tableModal = document.getElementById('activityModal'); // pakai modal yang sama
const tableModalContent = document.getElementById('modalContent');

document.querySelectorAll('.detail-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const book = btn.dataset.book;
    const user = btn.dataset.user;
    const date = btn.dataset.date;
    const status = btn.dataset.status;

    tableModalContent.innerHTML = `
      <p><span class="font-semibold">Buku:</span> ${book}</p>
      <p><span class="font-semibold">Peminjam:</span> ${user}</p>
      <p><span class="font-semibold">Tanggal Pinjam:</span> ${date}</p>
      <p><span class="font-semibold">Status:</span> ${status}</p>
    `;

    tableModal.classList.remove('hidden');
    tableModal.classList.add('flex');
  });
});
</script>

<!-- SCRIPT MODAL -->
<script>
const modal = document.getElementById('activityModal');
const modalContent = document.getElementById('modalContent');
const closeModal = document.getElementById('closeModal');

const activityIcons = document.querySelectorAll('.activity-item img');

activityIcons.forEach(icon => {
  icon.addEventListener('click', () => {
    const name = icon.dataset.name;
    const action = icon.dataset.action;
    const book = icon.dataset.book;
    const note = icon.dataset.note;
    const type = icon.dataset.type;

    modalContent.innerHTML = `
      <p><span class="font-semibold">Nama:</span> ${name}</p>
      <p><span class="font-semibold">Aksi:</span> ${action}</p>
      <p><span class="font-semibold">Buku:</span> ${book}</p>
      ${note ? `<p><span class="font-semibold">Catatan:</span> ${note}</p>` : ''}
      <p><span class="font-semibold">Jenis:</span> ${type}</p>
    `;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
  });
});

closeModal.addEventListener('click', () => {
  modal.classList.add('hidden');
  modal.classList.remove('flex');
});

modal.addEventListener('click', (e) => {
  if(e.target === modal){
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  }
});

// ======================================================
// FUNGSI NOTIFIKASI
// ======================================================
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

// Load notifikasi saat halaman dimuat dan update setiap 30 detik
document.addEventListener('DOMContentLoaded', () => {
    loadNotifikasi();
    notifInterval = setInterval(loadNotifikasi, 30000); // Update setiap 30 detik
});

// Tutup popup saat klik di luar
document.getElementById('notifPopup').addEventListener('click', function(e) {
    if (e.target === this) {
        toggleNotifPopup();
    }
});
</script>

<!-- POPUP NOTIFIKASI ADMIN -->
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
// ======================================================
// FUNGSI PROFILE DROPDOWN ADMIN
// ======================================================
const adminProfileBtn = document.getElementById('adminProfileBtn');
const adminProfileDropdown = document.getElementById('adminProfileDropdown');

if (adminProfileBtn && adminProfileDropdown) {
    adminProfileBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        adminProfileDropdown.classList.toggle('hidden');
    });

    // Tutup dropdown saat klik di luar
    document.addEventListener('click', function(e) {
        if (!adminProfileBtn.contains(e.target) && !adminProfileDropdown.contains(e.target)) {
            adminProfileDropdown.classList.add('hidden');
        }
    });
}
</script>

</body>
</html>
