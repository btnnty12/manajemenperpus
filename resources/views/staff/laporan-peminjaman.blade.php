@php
    $title = 'Laporan Peminjaman - Staff';
    use App\Models\Pinjaman;
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Facades\DB;
    
    $perPage = request('per_page', 10);
    $q = trim(request('q', ''));
    $statusFilter = request('status', '');
    $dateFilter = request('date', '');

    $pinjamanQuery = Pinjaman::with(['pengguna', 'buku'])->orderBy('created_at', 'desc');

    if ($q !== '') {
        $pinjamanQuery->where(function($s) use ($q) {
            $s->whereHas('pengguna', function($q2) use ($q) {
                $q2->where('nama', 'like', "%{$q}%");
            })->orWhereHas('buku', function($q3) use ($q) {
                $q3->where('judul', 'like', "%{$q}%");
            });
        });
    }

    if ($statusFilter === 'terlambat') {
        // hanya jika kolom tanggal_jatuh_tempo ada
        if (Schema::hasColumn('pinjaman', 'tanggal_jatuh_tempo')) {
            $pinjamanQuery->where('status', 'sedang_dipinjam')
                         ->whereDate('tanggal_jatuh_tempo', '<', now()->toDateString());
        } else {
            $pinjamanQuery->whereRaw('1 = 0'); // kosongkan hasil
        }
    } elseif ($statusFilter !== '') {
        $pinjamanQuery->where('status', $statusFilter);
    }

    if ($dateFilter) {
        // gunakan tanggal_pinjam jika tersedia
        $pinjamanQuery->whereDate('tanggal_pinjam', $dateFilter);
    }

    $pinjaman = $pinjamanQuery->paginate($perPage)->withQueryString();
    
    // Hitung statistik
    $totalDipinjam = Pinjaman::count();
    $sedangDipinjam = Pinjaman::where('status', 'sedang_dipinjam')->count();
    
    // Cek apakah kolom ada di database
    $hasTanggalJatuhTempo = Schema::hasColumn('pinjaman', 'tanggal_jatuh_tempo');
    $hasDenda = Schema::hasColumn('pinjaman', 'denda');
    
    // Hitung terlambat dengan pengecekan kolom
    $terlambat = 0;
    if ($hasTanggalJatuhTempo) {
        try {
            $terlambat = Pinjaman::where('status', 'sedang_dipinjam')
                ->whereNotNull('tanggal_jatuh_tempo')
                ->whereDate('tanggal_jatuh_tempo', '<', Carbon::now()->toDateString())
                ->count();
        } catch (\Exception $e) {
            $terlambat = 0;
        }
    }
    
    // Hitung total denda dengan pengecekan kolom
    $totalDenda = 0;
    if ($hasDenda) {
        try {
            $totalDenda = Pinjaman::whereNotNull('denda')->sum('denda') ?? 0;
        } catch (\Exception $e) {
            $totalDenda = 0;
        }
    }
@endphp
<x-staff-layout :title="$title">
    <section class="px-2 sm:px-4 pb-10 space-y-6">
        <div>
            <h1 class="text-3xl font-bold text-[#7c1d0f]">Laporan Peminjaman (Staff)</h1>
            <p class="text-sm text-gray-700 mt-1">Pantau peminjaman, pengembalian, dan keterlambatan.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div class="bg-[#b94a36] text-white rounded-lg p-4 text-center shadow">
                <h2 class="text-2xl font-bold">{{ $totalDipinjam }}</h2>
                <p class="text-sm mt-1">Total Buku Dipinjam</p>
            </div>
            <div class="bg-[#b94a36] text-white rounded-lg p-4 text-center shadow">
                <h2 class="text-2xl font-bold">{{ $sedangDipinjam }}</h2>
                <p class="text-sm mt-1">Sedang Dipinjam</p>
            </div>
            <div class="bg-[#b94a36] text-white rounded-lg p-4 text-center shadow">
                <h2 class="text-2xl font-bold">{{ $terlambat }}</h2>
                <p class="text-sm mt-1">Terlambat</p>
            </div>
            <div class="bg-[#b94a36] text-white rounded-lg p-4 text-center shadow">
                <h2 class="text-2xl font-bold">Rp {{ number_format($totalDenda, 0, ',', '.') }}</h2>
                <p class="text-sm mt-1">Denda Terkumpul</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-4">
            <form id="filterForm" method="GET" class="flex flex-wrap gap-3 items-center mb-4 w-full">
                <div class="flex items-center border rounded-lg px-3 py-2 flex-1 min-w-[200px]">
                    <x-icon name="search" class="w-5 h-5 text-gray-500 mr-2" />
                    <input type="text" name="q" id="searchInput" placeholder="Cari nama / judul buku" value="{{ request('q') }}" class="w-full outline-none text-sm" oninput="debouncedSubmit()">
                </div>
                <select name="status" id="statusFilter" class="border rounded-lg px-3 py-2 text-sm" onchange="debouncedSubmit()">
                    <option value="" {{ request('status') == '' ? 'selected' : '' }}>Semua Status</option>
                    <option value="menunggu_approval" {{ request('status') == 'menunggu_approval' ? 'selected' : '' }}>Menunggu Approval</option>
                    <option value="dapat_diambil" {{ request('status') == 'dapat_diambil' ? 'selected' : '' }}>Dapat Diambil</option>
                    <option value="sedang_dipinjam" {{ request('status') == 'sedang_dipinjam' ? 'selected' : '' }}>Sedang Dipinjam</option>
                    <option value="terlambat" {{ request('status') == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                    <option value="dikembalikan" {{ request('status') == 'dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
                </select>
                <input type="date" name="date" id="dateFilter" value="{{ request('date') }}" class="border rounded-lg px-3 py-2 text-sm" onchange="debouncedSubmit()">

                <select name="per_page" id="perPageSelect" class="border rounded-lg px-3 py-2 text-sm" onchange="debouncedSubmit()">
                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 / halaman</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 / halaman</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 / halaman</option>
                </select>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-[#b54a38] text-white">
                        <tr>
                            <th class="px-4 py-3 text-left">ID Transaksi</th>
                            <th class="px-4 py-3 text-left">Nama Anggota</th>
                            <th class="px-4 py-3 text-left">Judul Buku</th>
                            <th class="px-4 py-3 text-left">Tanggal Pinjam</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Denda</th>
                            <th class="px-4 py-3 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="pinjamanTable" class="divide-y">
                        @foreach($pinjaman as $row)
                        @php
                            $statusDisplay = '';
                            $statusClass = '';
                            $isTerlambat = false;
                            
                            if ($row->status === 'menunggu_approval') {
                                $statusDisplay = 'Menunggu Approval';
                                $statusClass = 'bg-yellow-100 text-yellow-700';
                            } elseif ($row->status === 'dapat_diambil') {
                                $statusDisplay = 'Dapat Diambil';
                                $statusClass = 'bg-blue-100 text-blue-700';
                            } elseif ($row->status === 'dikembalikan') {
                                $statusDisplay = 'Dikembalikan';
                                $statusClass = 'bg-green-100 text-green-700';
                            } elseif ($row->status === 'sedang_dipinjam') {
                                if ($hasTanggalJatuhTempo && property_exists($row, 'tanggal_jatuh_tempo') && $row->tanggal_jatuh_tempo) {
                                    try {
                                        if (Carbon::parse($row->tanggal_jatuh_tempo)->isPast()) {
                                            $statusDisplay = 'Terlambat';
                                            $statusClass = 'bg-red-100 text-red-700';
                                            $isTerlambat = true;
                                        } else {
                                            $statusDisplay = 'Sedang Dipinjam';
                                            $statusClass = 'bg-amber-100 text-amber-700';
                                        }
                                    } catch (\Exception $e) {
                                        $statusDisplay = 'Sedang Dipinjam';
                                        $statusClass = 'bg-amber-100 text-amber-700';
                                    }
                                } else {
                                    $statusDisplay = 'Sedang Dipinjam';
                                    $statusClass = 'bg-amber-100 text-amber-700';
                                }
                            } else {
                                $statusDisplay = ucfirst(str_replace('_', ' ', $row->status));
                                $statusClass = 'bg-gray-100 text-gray-700';
                            }
                            
                            // Handle denda dengan pengecekan kolom
                            $dendaValue = 0;
                            if ($hasDenda) {
                                try {
                                    if (property_exists($row, 'denda') && $row->denda !== null && $row->denda > 0) {
                                        $dendaValue = $row->denda;
                                    }
                                } catch (\Exception $e) {
                                    $dendaValue = 0;
                                }
                            }
                            $dendaDisplay = $dendaValue > 0 ? 'Rp ' . number_format($dendaValue, 0, ',', '.') : '-';
                        @endphp
                        <tr class="odd:bg-gray-50 pinjaman-row" 
                            data-nama="{{ strtolower($row->pengguna->nama ?? '') }}" 
                            data-judul="{{ strtolower($row->buku->judul ?? '') }}"
                            data-status="{{ $isTerlambat ? 'terlambat' : $row->status }}"
                            data-tanggal="{{ $row->tanggal_pinjam ? Carbon::parse($row->tanggal_pinjam)->format('Y-m-d') : '' }}">
                            <td class="px-4 py-2">P-{{ str_pad($row->id, 3, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-4 py-2">{{ $row->pengguna->nama ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $row->buku->judul ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $row->tanggal_pinjam ? Carbon::parse($row->tanggal_pinjam)->format('d M Y') : '-' }}</td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                    {{ $statusDisplay }}
                                </span>
                            </td>
                            <td class="px-4 py-2">{{ $dendaDisplay }}</td>
                            <td class="px-4 py-2">
                                <div class="flex gap-2 items-center">
                                    @if($row->status === 'menunggu_approval')
                                        <button onclick="approvePinjaman({{ $row->id }})" 
                                                class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700 transition font-semibold"
                                                title="Setujui peminjaman">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button onclick="rejectPinjaman({{ $row->id }})" 
                                                class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700 transition font-semibold"
                                                title="Tolak peminjaman">
                                            <i class="fas fa-times"></i> Tolak
                                        </button>
                                    @elseif($row->status === 'dapat_diambil')
                                        <button onclick="confirmAmbil({{ $row->id }})" 
                                                class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700 transition font-semibold"
                                                title="Konfirmasi buku sudah diambil">
                                            <i class="fas fa-check-circle"></i> Konfirmasi
                                        </button>
                                        <button onclick="showDetailStaff({{ $row->id }}, '{{ addslashes($row->pengguna->nama ?? '') }}', '{{ addslashes($row->buku->judul ?? '') }}', '{{ $row->tanggal_pinjam ? Carbon::parse($row->tanggal_pinjam)->format('d/m/Y') : '-' }}', '{{ $row->tanggal_jatuh_tempo ? Carbon::parse($row->tanggal_jatuh_tempo)->format('d/m/Y') : '-' }}', '{{ $statusDisplay }}', '{{ $dendaValue }}')" 
                                                class="bg-gray-600 text-white px-3 py-1 rounded text-xs hover:bg-gray-700 transition"
                                                title="Lihat detail">
                                            <i class="fas fa-info-circle"></i>
                                        </button>
                                    @else
                                        <button onclick="showDetailStaff({{ $row->id }}, '{{ addslashes($row->pengguna->nama ?? '') }}', '{{ addslashes($row->buku->judul ?? '') }}', '{{ $row->tanggal_pinjam ? Carbon::parse($row->tanggal_pinjam)->format('d/m/Y') : '-' }}', '{{ $row->tanggal_jatuh_tempo ? Carbon::parse($row->tanggal_jatuh_tempo)->format('d/m/Y') : '-' }}', '{{ $statusDisplay }}', '{{ $dendaValue }}')" 
                                                class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700 transition"
                                                title="Lihat detail">
                                            <i class="fas fa-info-circle"></i> Detail
                                        </button>
                                        @if($row->status === 'sedang_dipinjam' && !$isTerlambat)
                                            <button onclick="returnBookStaff({{ $row->id }})" 
                                                    class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700 transition"
                                                    title="Konfirmasi pengembalian">
                                                <i class="fas fa-undo"></i> Kembalikan
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                @if($pinjaman->isEmpty())
                <div class="text-center py-8 text-gray-500">
                    <p>Tidak ada data yang ditemukan</p>
                </div>
                @endif

                <div class="mt-4">
                    {{ $pinjaman->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </section>

    <script>
        // Server-side search: debounce and submit the GET form
        let _debounceTimer;
        function debouncedSubmit() {
            clearTimeout(_debounceTimer);
            _debounceTimer = setTimeout(() => document.getElementById('filterForm').submit(), 250);
        }

        document.addEventListener('DOMContentLoaded', function() {
            // no-op
        });

        // ======================================================
        // FUNGSI APPROVE & REJECT PINJAMAN
        // ======================================================
        function approvePinjaman(id) {
            if (!confirm('Apakah Anda yakin ingin menyetujui peminjaman ini?')) {
                return;
            }
            
            fetch(`/staff/api/pinjaman/${id}/approve`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Peminjaman berhasil disetujui!');
                    location.reload();
                } else {
                    alert('Gagal menyetujui peminjaman: ' + (data.message || 'Terjadi kesalahan'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menyetujui peminjaman');
            });
        }

        function rejectPinjaman(id) {
            if (!confirm('Apakah Anda yakin ingin menolak peminjaman ini? Peminjaman akan dihapus dari sistem.')) {
                return;
            }
            
            fetch(`/staff/api/pinjaman/${id}/reject`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Peminjaman berhasil ditolak!');
                    location.reload();
                } else {
                    alert('Gagal menolak peminjaman: ' + (data.message || 'Terjadi kesalahan'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menolak peminjaman');
            });
        }

        // ======================================================
        // FUNGSI DETAIL & AKSI LAINNYA
        // ======================================================
        function showDetailStaff(id, nama, judul, tglPinjam, tglJatuhTempo, status, denda) {
            alert(`Detail Peminjaman\n\nID: P-${String(id).padStart(6, '0')}\nNama: ${nama}\nJudul: ${judul}\nTanggal Pinjam: ${tglPinjam}\nJatuh Tempo: ${tglJatuhTempo}\nStatus: ${status}\nDenda: ${denda > 0 ? 'Rp ' + parseInt(denda).toLocaleString('id-ID') : '-'}`);
        }

        function confirmAmbil(id) {
            if (!confirm('Apakah Anda yakin buku sudah diambil oleh peminjam?')) {
                return;
            }
            
            fetch(`/staff/api/pinjaman/${id}/confirm-taken`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Status berhasil diupdate! Buku sedang dipinjam.');
                    location.reload();
                } else {
                    alert('Gagal mengupdate status: ' + (data.message || 'Terjadi kesalahan'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengupdate status');
            });
        }

        function returnBookStaff(id) {
            if (!confirm('Apakah Anda yakin buku sudah dikembalikan?')) {
                return;
            }
            
            fetch(`/staff/api/pinjaman/${id}/return`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success || data.message) {
                    alert('Buku berhasil dikembalikan!');
                    location.reload();
                } else {
                    alert('Gagal mengembalikan buku: ' + (data.message || 'Terjadi kesalahan'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengembalikan buku');
            });
        }
    </script>
</x-staff-layout>

