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
    public function index()
    {
        $buku = Buku::orderBy('created_at', 'desc')->get();
        $stats = [
            'totalJudul' => Buku::count(),
            'tersedia' => Buku::where('stok', '>', 0)->count(),
            'dipinjam' => Pinjaman::where('status', 'sedang_dipinjam')->count(),
            'bukuBaruBulanIni' => Buku::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count(),
        ];

        // Gunakan view yang sama untuk admin dan staff
        // Generate next book ID untuk form tambah
        $nextBookId = $this->getNextBookId();
        
        return view('kelola-buku', compact('buku', 'stats', 'nextBookId'));
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
