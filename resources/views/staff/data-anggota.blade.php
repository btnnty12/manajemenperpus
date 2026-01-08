@php
    $title = 'Data Anggota - Staff';
    use App\Models\Pengguna;
    $anggota = Pengguna::where('peran', 'pengguna')->get();
@endphp
<x-staff-layout :title="$title">
    <section class="px-2 sm:px-4 pb-10 space-y-6">
        <div>
            <h1 class="text-3xl font-bold text-[#7c1d0f]">Data Anggota (Staff)</h1>
            <p class="text-sm text-gray-700 mt-1">Kelola dan pantau anggota perpustakaan.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            @php
                $totalAnggota = Pengguna::where('peran', 'pengguna')->count();
                $aktif = Pengguna::where('peran', 'pengguna')->count(); // Asumsi semua aktif jika tidak ada field status
                $nonaktif = 0;
                $anggotaBaru = Pengguna::where('peran', 'pengguna')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count();
            @endphp
            <div class="bg-[#b94a36] text-white rounded-lg p-4 text-center shadow">
                <h2 class="text-2xl font-bold">{{ $totalAnggota }}</h2>
                <p class="text-sm mt-1">Total Anggota</p>
            </div>
            <div class="bg-[#b94a36] text-white rounded-lg p-4 text-center shadow">
                <h2 class="text-2xl font-bold">{{ $aktif }}</h2>
                <p class="text-sm mt-1">Aktif</p>
            </div>
            <div class="bg-[#b94a36] text-white rounded-lg p-4 text-center shadow">
                <h2 class="text-2xl font-bold">{{ $nonaktif }}</h2>
                <p class="text-sm mt-1">Nonaktif</p>
            </div>
            <div class="bg-[#b94a36] text-white rounded-lg p-4 text-center shadow">
                <h2 class="text-2xl font-bold">{{ $anggotaBaru }}</h2>
                <p class="text-sm mt-1">Anggota Baru</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                <div class="bg-white rounded-lg px-3 py-2 flex items-center border">
                    <x-icon name="search" class="w-5 h-5 text-gray-500 mr-2" />
                    <input type="text" id="searchInput" placeholder="Cari anggota (nama/email)" class="w-full outline-none text-sm" onkeyup="filterTable()">
                </div>
                <select id="statusFilter" class="border rounded-lg px-3 py-2 text-sm" onchange="filterTable()">
                    <option value="">Semua Status</option>
                    <option value="Aktif">Aktif</option>
                    <option value="Nonaktif">Nonaktif</option>
                </select>
                <input type="date" id="dateFilter" class="border rounded-lg px-3 py-2 text-sm" onchange="filterTable()">
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-[#b54a38] text-white">
                        <tr>
                            <th class="px-4 py-3 text-left">ID Anggota</th>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-left">Nama</th>
                            <th class="px-4 py-3 text-left">Email</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Denda</th>
                            <th class="px-4 py-3 text-left">Opsi</th>
                        </tr>
                    </thead>
                    <tbody id="anggotaTable" class="divide-y">
                        @forelse($anggota ?? [] as $item)
                        @php
                            // Hitung total denda dari pinjaman yang belum dikembalikan atau telat
                            $totalDenda = 0;
                            try {
                                if (\Illuminate\Support\Facades\Schema::hasColumn('pinjaman', 'denda')) {
                                    $totalDenda = \App\Models\Pinjaman::where('pengguna_id', $item->id)
                                        ->where(function($q) {
                                            $q->where('status', 'sedang_dipinjam')
                                              ->orWhere('status', 'dikembalikan');
                                        })
                                        ->sum('denda') ?? 0;
                                }
                            } catch (\Exception $e) {
                                $totalDenda = 0;
                            }
                            
                            // Cek apakah ada pinjaman aktif
                            $hasActiveLoan = \App\Models\Pinjaman::where('pengguna_id', $item->id)
                                ->where('status', 'sedang_dipinjam')
                                ->exists();
                            
                            // Status: Aktif jika ada pinjaman aktif, atau jika pernah pinjam buku
                            $hasLoanHistory = \App\Models\Pinjaman::where('pengguna_id', $item->id)->exists();
                            $status = $hasActiveLoan ? 'Aktif' : ($hasLoanHistory ? 'Aktif' : 'Aktif');
                        @endphp
                        <tr class="odd:bg-gray-50 anggota-row hover:bg-gray-50" data-nama="{{ strtolower($item->nama) }}" data-email="{{ strtolower($item->email) }}" data-status="{{ $status }}">
                            <td class="px-4 py-2 font-semibold">AG-{{ str_pad($item->id, 3, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-4 py-2">{{ $item->created_at->format('d/m/Y') }}</td>
                            <td class="px-4 py-2">{{ $item->nama }}</td>
                            <td class="px-4 py-2">{{ $item->email }}</td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $hasActiveLoan ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $status }}
                                </span>
                            </td>
                            <td class="px-4 py-2 font-semibold {{ $totalDenda > 0 ? 'text-red-600' : 'text-gray-600' }}">
                                Rp {{ number_format($totalDenda, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-2">
                                <div class="flex gap-2">
                                    <button onclick="showDetailAnggota({{ $item->id }})" class="p-1 hover:bg-gray-200 rounded cursor-pointer" title="Detail">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    <button onclick="editAnggota({{ $item->id }})" class="p-1 hover:bg-gray-200 rounded cursor-pointer" title="Edit">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button onclick="deleteAnggota({{ $item->id }})" class="p-1 hover:bg-gray-200 rounded cursor-pointer" title="Hapus">
                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">Tidak ada data anggota</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div id="noResults" class="hidden text-center py-8 text-gray-500">
                    <p>Tidak ada data yang ditemukan</p>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Menggunakan StringMatchingService dari service
        const StringMatching = window.StringMatchingService;

        // Fungsi untuk log performa (mengirim ke backend)
        async function logSearchPerformance(keyword, algorithm, processTime, resultCount) {
            if (!keyword || keyword.trim() === '') return;
            
            try {
                const response = await fetch('{{ route("staff.log-search") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        kata_kunci: keyword,
                        algorithm: algorithm,
                        process_time_ms: parseFloat(processTime.toFixed(5)),
                        jumlah_hasil: resultCount
                    })
                });
                
                if (!response.ok) {
                    throw new Error('Failed to log');
                }
            } catch (error) {
                // Silent fail - tidak tampilkan error di UI
                // Log hanya untuk debugging
                if (console && console.log) {
                    console.log('Search performance logged:', { 
                        algorithm, 
                        processTime: processTime.toFixed(5) + 'ms', 
                        resultCount 
                    });
                }
            }
        }

        // ======================================================
        // FUNGSI FILTER TABLE DENGAN ALGORITMA
        // ======================================================
        let searchTimeout;
        
        function filterTable() {
            // Debounce untuk menghindari terlalu banyak eksekusi
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                executeSearch();
            }, 150); // Delay 150ms
        }
        
        async function executeSearch() {
            const searchInput = document.getElementById('searchInput');
            const statusFilter = document.getElementById('statusFilter');
            const rows = document.querySelectorAll('.anggota-row');
            const noResults = document.getElementById('noResults');
            
            if (!searchInput || !statusFilter || !rows.length) return;
            
            const searchValue = searchInput.value.trim().toLowerCase();
            const statusValue = statusFilter.value;
            let visibleCount = 0;

            // Jika tidak ada input search, tampilkan semua berdasarkan filter status
            if (!searchValue) {
                rows.forEach(row => {
                    const status = row.getAttribute('data-status');
                    const matchesStatus = !statusValue || status === statusValue;
                    row.style.display = matchesStatus ? '' : 'none';
                    if (matchesStatus) visibleCount++;
                });
                
                if (visibleCount === 0) {
                    noResults.classList.remove('hidden');
                } else {
                    noResults.classList.add('hidden');
                }
                return;
            }

            // Pilih algoritma terbaik berdasarkan panjang pattern
            const algorithm = StringMatching.selectBestAlgorithm(searchValue);
            
            // Mulai timer untuk mengukur performa
            const startTime = performance.now();

            // Lakukan pencarian dengan algoritma string matching menggunakan service
            for (const row of rows) {
                const nama = row.getAttribute('data-nama') || '';
                const email = row.getAttribute('data-email') || '';
                const status = row.getAttribute('data-status') || '';
                
                // Gunakan algoritma string matching untuk mencari pattern
                const namaMatches = await StringMatching.searchWithAlgorithm(nama, searchValue, algorithm);
                const emailMatches = await StringMatching.searchWithAlgorithm(email, searchValue, algorithm);
                const matchesSearch = namaMatches.length > 0 || emailMatches.length > 0;
                const matchesStatus = !statusValue || status === statusValue;
                
                if (matchesSearch && matchesStatus) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            }

            // Hitung waktu eksekusi dalam milidetik
            const processTime = performance.now() - startTime;

            // Log performa algoritma (tanpa tampilkan di UI)
            logSearchPerformance(searchValue, algorithm, processTime, visibleCount);

            // Tampilkan pesan jika tidak ada hasil
            if (visibleCount === 0) {
                noResults.classList.remove('hidden');
            } else {
                noResults.classList.add('hidden');
            }
        }
        
        // Inisialisasi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            // Pastikan fungsi filterTable tersedia secara global
            window.filterTable = filterTable;
        });

        // ======================================================
        // FUNGSI AKSI ANGGOTA (Detail, Edit, Delete)
        // ======================================================
        function showDetailAnggota(id) {
            // Ambil data dari baris tabel
            const row = document.querySelector(`tr[data-id="${id}"]`) || 
                       Array.from(document.querySelectorAll('.anggota-row')).find(r => {
                           const idCell = r.querySelector('td:first-child');
                           return idCell && idCell.textContent.includes(id.toString().padStart(3, '0'));
                       });
            
            if (!row) {
                alert('Data anggota tidak ditemukan');
                return;
            }

            const cells = row.querySelectorAll('td');
            const data = {
                id: cells[0]?.textContent.trim() || '',
                tanggal: cells[1]?.textContent.trim() || '',
                nama: cells[2]?.textContent.trim() || '',
                email: cells[3]?.textContent.trim() || '',
                status: cells[4]?.textContent.trim() || '',
                denda: cells[5]?.textContent.trim() || 'Rp 0'
            };

            alert(`Detail Anggota:\n\nID: ${data.id}\nTanggal Daftar: ${data.tanggal}\nNama: ${data.nama}\nEmail: ${data.email}\nStatus: ${data.status}\nTotal Denda: ${data.denda}`);
        }

        function editAnggota(id) {
            // TODO: Implementasi modal edit atau redirect ke halaman edit
            alert(`Fungsi edit untuk anggota ID: ${id} akan segera tersedia`);
        }

        function deleteAnggota(id) {
            // Ambil nama dari baris tabel
            const row = Array.from(document.querySelectorAll('.anggota-row')).find(r => {
                const idCell = r.querySelector('td:first-child');
                return idCell && idCell.textContent.includes(id.toString().padStart(3, '0'));
            });
            
            const nama = row ? row.querySelector('td:nth-child(3)')?.textContent.trim() : 'anggota ini';
            
            if (confirm(`Apakah Anda yakin ingin menghapus anggota "${nama}"?`)) {
                // TODO: Implementasi API call untuk delete
                fetch(`/api/pengguna/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Data anggota berhasil dihapus');
                        location.reload();
                    } else {
                        alert('Gagal menghapus data anggota: ' + (data.message || 'Terjadi kesalahan'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menghapus data anggota');
                });
            }
        }

        // Tambahkan data-id attribute untuk memudahkan pencarian
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.anggota-row').forEach((row, index) => {
                const idCell = row.querySelector('td:first-child');
                if (idCell) {
                    const idMatch = idCell.textContent.match(/AG-(\d+)/);
                    if (idMatch) {
                        row.setAttribute('data-id', idMatch[1]);
                    }
                }
            });
        });
    </script>
</x-staff-layout>
