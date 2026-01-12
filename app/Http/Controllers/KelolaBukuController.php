<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Pinjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KelolaBukuController extends Controller
{
    // Mapping kategori ke rak buku
    private function getRakByKategori($kategori)
    {
        $mapping = [
            'Teknologi' => 'A1',
            'Keamanan' => 'A2',
            'Pemrograman' => 'B1',
            'Psikologi' => 'C1',
            'Akuntansi' => 'D1',
            'Manajemen' => 'E1',
            'Statistik' => 'F1',
            'AI' => 'A3',
            'Budaya' => 'G1',
            'Bahasa' => 'H1',
            'Matematika' => 'F2',
            'Desain' => 'I1',
            'Fiksi' => 'J1',
            'Sejarah' => 'K1',
        ];
        
        return $mapping[$kategori] ?? 'L1'; // Default rak untuk kategori yang tidak terdaftar
    }
    
    // Generate ID buku berikutnya
    private function getNextBookId()
    {
        $lastBook = Buku::orderBy('id', 'desc')->first();
        $nextId = $lastBook ? $lastBook->id + 1 : 1;
        return $nextId;
    }
    // Tampilkan halaman kelola buku
    public function index(Request $request)
    {
        // Server-side pagination + filtering
        $q = trim((string) $request->query('q', ''));
        $kategori = $request->query('kategori', '');
        $status = $request->query('status', '');
        $perPage = (int) $request->query('per_page', 10);
        if ($perPage <= 0) $perPage = 10;

        $query = Buku::query();

        if ($q !== '') {
            $query->where(function($sub) use ($q) {
                $sub->where('judul', 'like', "%{$q}%")
                    ->orWhere('penulis', 'like', "%{$q}%");
            });
        }

        if ($kategori) {
            $query->where('genre', $kategori);
        }

        if ($status) {
            if ($status === 'Tersedia') {
                $query->where('stok', '>', 0);
            } elseif ($status === 'Tidak Tersedia') {
                $query->where('stok', '<=', 0);
            }
        }

        $buku = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        $stats = [
            'totalJudul' => Buku::count(),
            'totalEksemplar' => Buku::sum('stok'),
            'tersedia' => Buku::where('stok', '>', 0)->count(),
            'dipinjam' => Pinjaman::where('status', 'sedang_dipinjam')->count(),
            'bukuBaruBulanIni' => Buku::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count(),
        ];

        // Gunakan view yang sama untuk admin dan staff
        // Generate next book ID untuk form tambah
        $nextBookId = $this->getNextBookId();
        
        return view('kelola-buku', compact('buku', 'stats', 'nextBookId', 'q', 'kategori', 'status', 'perPage'));
    }

    // Sinkronisasi data dummy ke DB (idempotent, bisa dipanggil oleh admin/staff)
    public function syncDummy(Request $request)
    {
        $now = now();

        $data = [
            ['Laskar Pelangi', 'Andrea Hirata', 'Sastra Indonesia', 2005],
            ['Bumi Manusia', 'Pramoedya Ananta Toer', 'Sejarah & Sastra', 1980],
            ['Negeri 5 Menara', 'Ahmad Fuadi', 'Motivasi', 2009],
            ['Ayat-Ayat Cinta', 'Habiburrahman El Shirazy', 'Religi', 2004],
            ['Filosofi Teras', 'Henry Manampiring', 'Psikologi', 2018],
            ['Atomic Habits (Terjemahan)', 'James Clear', 'Pengembangan Diri', 2019],
            ['Sapiens', 'Yuval Noah Harari', 'Sejarah', 2017],
            ['Rich Dad Poor Dad', 'Robert T. Kiyosaki', 'Keuangan', 2015],
            ['Pemrograman Web Laravel', 'Eko Kurniawan Khannedy', 'Teknologi', 2022],
            ['Basis Data', 'Abdul Kadir', 'Teknologi', 2018],
            ['Algoritma & Struktur Data', 'Rinaldi Munir', 'Teknologi', 2019],
            ['Sistem Informasi Manajemen', 'Jogiyanto', 'Manajemen', 2017],
            ['Manajemen Perpustakaan', 'Sulistyo Basuki', 'Perpustakaan', 2016],
            ['Metodologi Penelitian', 'Sugiyono', 'Pendidikan', 2020],
            ['Statistika untuk Penelitian', 'Sudjana', 'Pendidikan', 2019],
            ['Pengantar Ilmu Komunikasi', 'Deddy Mulyana', 'Sosial', 2018],
            ['Ilmu Politik', 'Miriam Budiardjo', 'Sosial', 2017],
            ['Hukum Perdata', 'Subekti', 'Hukum', 2016],
            ['Hukum Tata Negara', 'Jimly Asshiddiqie', 'Hukum', 2018],
            ['Akuntansi Dasar', 'Warren Reeve Fess', 'Akuntansi', 2020],
        ];

        $books = [];
        foreach ($data as $index => $item) {
            $books[] = [
                'judul' => $item[0],
                'penulis' => $item[1],
                'genre' => $item[2],
                'deskripsi' => "Buku {$item[0]} karya {$item[1]} yang umum tersedia di perpustakaan Indonesia.",
                'cover' => $index % 2 === 0 ? "covers/buku-" . ($index + 1) . ".jpg" : "https://picsum.photos/300/400?random=" . ($index + 1),
                'tahun_terbit' => $item[3],
                'stok' => rand(3, 15),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Gandakan sampai ~100 data dan beri label edisi
        $finalBooks = [];
        for ($i = 1; $i <= 5; $i++) {
            foreach ($books as $buku) {
                if (count($finalBooks) >= 100) break 2;
                $bukuCopy = $buku;
                $bukuCopy['judul'] = $bukuCopy['judul'] . " (Edisi {$i})";
                $finalBooks[] = $bukuCopy;
            }
        }

        $inserted = 0;
        foreach ($finalBooks as $b) {
            $model = Buku::firstOrCreate(
                ['judul' => $b['judul']],
                [
                    'penulis' => $b['penulis'],
                    'genre' => $b['genre'],
                    'deskripsi' => $b['deskripsi'],
                    'cover' => $b['cover'],
                    'tahun_terbit' => $b['tahun_terbit'],
                    'stok' => $b['stok'],
                ]
            );
            if ($model->wasRecentlyCreated) $inserted++;
        }

        // Redirect kembali ke halaman kelola sesuai peran
        $route = (auth()->check() && auth()->user()->peran === 'staff') ? 'staff.kelola-buku' : 'kelola.buku';

        return redirect()->route($route)->with('success', "Sinkronisasi selesai: {$inserted} buku ditambahkan dari data dummy");
    }

    // Simpan buku baru (ADMIN & STAFF)
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'penulis' => 'required|string|max:255',
            'genre' => 'nullable|string|max:100',
            'kategori' => 'nullable|string|max:100', // Support both 'genre' and 'kategori' for compatibility
            'tahun_terbit' => 'required|integer|min:1500|max:2099',
            'stok' => 'required|integer|min:0',
            'deskripsi' => 'nullable|string',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
        ]);

        $coverPath = null;
        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('book-covers', 'public');
        }

        Buku::create([
            'judul' => $request->input('judul'),
            'penulis' => $request->input('penulis'),
            'genre' => $request->input('genre') ?? $request->input('kategori'), // Use genre first, fallback to kategori
            'tahun_terbit' => (int) $request->input('tahun_terbit'),
            'stok' => (int) $request->input('stok'),
            'deskripsi' => $request->input('deskripsi'),
            'cover' => $coverPath,
        ]);

        // Redirect berdasarkan role user
        $user = auth()->user();
        if ($user && $user->peran === 'staff') {
            return redirect()
                ->route('staff.kelola-buku')
                ->with('success', 'Buku berhasil ditambahkan');
        }
        
        return redirect()
            ->route('kelola.buku')
            ->with('success', 'Buku berhasil ditambahkan');
    }

    public function show($id)
    {
        // Validasi ID
        if (!$id || $id === 'undefined' || $id === 'null') {
            return response()->json(['error' => 'ID buku tidak valid'], 400);
        }
        
        $buku = Buku::find($id);
        if (!$buku) {
            return response()->json(['error' => 'Buku tidak ditemukan'], 404);
        }
        
        $data = $buku->toArray();
        // Tambahkan URL cover jika ada
        if ($buku->cover) {
            $data['cover_url'] = $buku->cover_url;
        }
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        // Validasi ID
        if (!$id || $id === 'undefined' || $id === 'null') {
            return response()->json(['error' => 'ID buku tidak valid'], 400);
        }
        
        $buku = Buku::find($id);
        if (!$buku) {
            return response()->json(['error' => 'Buku tidak ditemukan'], 404);
        }
        $validated = $request->validate([
            'judul' => 'sometimes|required|string|max:255',
            'penulis' => 'sometimes|required|string|max:255',
            'genre' => 'nullable|string|max:100',
            'kategori' => 'nullable|string|max:100', // Support both for compatibility
            'deskripsi' => 'nullable|string',
            'tahun_terbit' => 'sometimes|required|integer|min:1500|max:2099',
            'stok' => 'sometimes|required|integer|min:0',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
        ]);

        $payload = [];
        if (isset($validated['judul'])) $payload['judul'] = $validated['judul'];
        if (isset($validated['penulis'])) $payload['penulis'] = $validated['penulis'];
        // Use genre first, fallback to kategori
        if (isset($validated['genre'])) {
            $payload['genre'] = $validated['genre'];
        } elseif (array_key_exists('kategori', $validated)) {
            $payload['genre'] = $validated['kategori'];
        }
        if (isset($validated['deskripsi'])) $payload['deskripsi'] = $validated['deskripsi'];
        if (isset($validated['tahun_terbit'])) $payload['tahun_terbit'] = $validated['tahun_terbit'];
        if (isset($validated['stok'])) $payload['stok'] = $validated['stok'];

        // Handle cover upload
        if ($request->hasFile('cover')) {
            // Hapus cover lama jika ada
            if ($buku->cover && Storage::disk('public')->exists($buku->cover)) {
                Storage::disk('public')->delete($buku->cover);
            }
            // Simpan cover baru
            $payload['cover'] = $request->file('cover')->store('book-covers', 'public');
        }

        try {
            $buku->update($payload);
            
            // Refresh model untuk mendapatkan data terbaru
            $buku->refresh();
            
            // Jika request adalah AJAX, return JSON
            if ($request->wantsJson() || $request->expectsJson()) {
                $data = $buku->toArray();
                if ($buku->cover) {
                    $data['cover_url'] = $buku->cover_url;
                }
                return response()->json([
                    'success' => true,
                    'message' => 'Buku berhasil diperbarui',
                    'data' => $data
                ]);
            }
            
            // Redirect berdasarkan role user
            $user = auth()->user();
            if ($user && $user->peran === 'staff') {
                return redirect()
                    ->route('staff.kelola-buku')
                    ->with('success', 'Buku berhasil diperbarui');
            }
            
            return redirect()
                ->route('kelola.buku')
                ->with('success', 'Buku berhasil diperbarui');
        } catch (\Exception $e) {
            \Log::error('Error updating buku: ' . $e->getMessage());
            
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Gagal memperbarui buku: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['error' => 'Gagal memperbarui buku: ' . $e->getMessage()]);
        }
    }

    // Hapus buku (ADMIN & STAFF)
    public function destroy(Request $request, $id)
    {
        // Validasi ID
        if (!$id || $id === 'undefined' || $id === 'null') {
            return response()->json(['error' => 'ID buku tidak valid'], 400);
        }
        
        $buku = Buku::find($id);
        if (!$buku) {
            return response()->json(['error' => 'Buku tidak ditemukan'], 404);
        }
        
        try {
            $buku->delete();
            
            // Jika request adalah AJAX, return JSON
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Buku berhasil dihapus'
                ]);
            }
            
            // Redirect berdasarkan role user
            $user = auth()->user();
            if ($user && $user->peran === 'staff') {
                return redirect()
                    ->route('staff.kelola-buku')
                    ->with('success', 'Buku berhasil dihapus');
            }
            
            return redirect()
                ->route('kelola.buku')
                ->with('success', 'Buku berhasil dihapus');
        } catch (\Exception $e) {
            \Log::error('Error deleting buku: ' . $e->getMessage());
            
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Gagal menghapus buku. Pastikan buku tidak sedang dipinjam.'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Gagal menghapus buku. Pastikan buku tidak sedang dipinjam.');
        }
    }

    public function importProcess(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
        ]);

        $content = file_get_contents($request->file('file')->getRealPath());
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $lines = preg_split('/\r\n|\r|\n/', trim($content));
        if (count($lines) > 0) {
            array_shift($lines);
        }
        foreach ($lines as $line) {
            if (trim($line) === '') continue;
            $cols = array_map('trim', explode(';', $line));
            $judul = $cols[0] ?? null;
            $penulis = $cols[1] ?? null;
            $kategori = $cols[2] ?? null;
            $tahun = isset($cols[3]) ? (int) $cols[3] : null;
            $stok = isset($cols[4]) ? (int) $cols[4] : 0;
            $deskripsi = $cols[5] ?? null;
            if (! $judul || ! $penulis || ! $kategori || ! $tahun) continue;
            Buku::create([
                'judul' => $judul,
                'penulis' => $penulis,
                'genre' => $kategori,
                'tahun_terbit' => $tahun,
                'stok' => $stok,
                'deskripsi' => $deskripsi,
            ]);
        }

        // Redirect berdasarkan role user
        $user = auth()->user();
        if ($user && $user->peran === 'staff') {
            return redirect()
                ->route('staff.kelola-buku')
                ->with('success', 'Import berhasil');
        }
        
        return redirect()->back()->with('success', 'Import berhasil');
    }


}
