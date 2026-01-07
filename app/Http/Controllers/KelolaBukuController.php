<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Pinjaman;
use Illuminate\Http\Request;

class KelolaBukuController extends Controller
{
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

        return view('kelola-buku', compact('buku', 'stats'));
    }

    // Simpan buku baru (ADMIN)
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string',
            'penulis' => 'required|string',
            'kategori' => 'required|string',
            'tahun_terbit' => 'required|integer|min:1500|max:2099',
            'stok' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
        ]);

        Buku::create([
            'judul' => $request->input('judul'),
            'penulis' => $request->input('penulis'),
            'genre' => $request->input('kategori'),
            'tahun_terbit' => (int) $request->input('tahun_terbit'),
            'stok' => (int) $request->input('stok'),
            'deskripsi' => $request->input('deskripsi'),
        ]);

        return redirect()
            ->route('kelola-buku.index')
            ->with('success', 'Buku berhasil ditambahkan');
    }

    public function show($id)
    {
        $buku = Buku::findOrFail($id);
        return response()->json($buku);
    }

    public function update(Request $request, $id)
    {
        $buku = Buku::findOrFail($id);
        $validated = $request->validate([
            'judul' => 'sometimes|required|string|max:255',
            'penulis' => 'sometimes|required|string|max:255',
            'kategori' => 'nullable|string|max:100',
            'deskripsi' => 'nullable|string',
            'tahun_terbit' => 'sometimes|required|integer|min:1500|max:2099',
            'stok' => 'sometimes|required|integer|min:0',
        ]);

        $payload = [];
        if (isset($validated['judul'])) $payload['judul'] = $validated['judul'];
        if (isset($validated['penulis'])) $payload['penulis'] = $validated['penulis'];
        if (array_key_exists('kategori', $validated)) $payload['genre'] = $validated['kategori'];
        if (isset($validated['deskripsi'])) $payload['deskripsi'] = $validated['deskripsi'];
        if (isset($validated['tahun_terbit'])) $payload['tahun_terbit'] = $validated['tahun_terbit'];
        if (isset($validated['stok'])) $payload['stok'] = $validated['stok'];

        $buku->update($payload);

        return redirect()
            ->route('kelola-buku.index')
            ->with('success', 'Buku berhasil diperbarui');
    }

    // Hapus buku (ADMIN)
    public function destroy($id)
    {
        Buku::findOrFail($id)->delete();

        return redirect()
            ->route('kelola-buku.index')
            ->with('success', 'Buku berhasil dihapus');
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

        return redirect()->back()->with('success', 'Import berhasil');
    }
}
