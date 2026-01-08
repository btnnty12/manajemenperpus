<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Buku;
use App\Models\Pinjaman;
use App\Models\Notifikasi;
use App\Models\Pengguna;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class CreatePinjamanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Generate ID peminjam otomatis (format: P-YYYYMMDD-XXXX)
        $idPeminjaman = 'P-' . Carbon::now()->format('Ymd') . '-' . str_pad(Pinjaman::count() + 1, 4, '0', STR_PAD_LEFT);
        
        // ID Anggota adalah ID user
        $idAnggota = str_pad($user->id, 8, '0', STR_PAD_LEFT);

        // Jika ada buku_id dari query string, auto-fill form
        $buku = null;
        if ($request->has('buku_id')) {
            $buku = Buku::find($request->buku_id);
        }

        return view('create', [
            'user' => $user,
            'idPeminjaman' => $idPeminjaman,
            'idAnggota' => $idAnggota,
            'buku' => $buku,
        ]);
    }

    public function searchBuku(Request $request)
    {
        $keyword = $request->input('q', '');
        
        if (strlen($keyword) < 2) {
            return response()->json(['data' => []]);
        }

        // Tokenize keyword and perform order-independent search across fields (case-insensitive)
        $terms = preg_split('/\s+/', trim($keyword));

        $q = Buku::query()->where('stok', '>', 0)->limit(10);
        foreach ($terms as $t) {
            $t = trim($t);
            if ($t === '') continue;
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $t);
            $like = '%' . mb_strtolower($escaped, 'UTF-8') . '%';

            $q->where(function ($qq) use ($like) {
                $qq->whereRaw('LOWER(judul) LIKE ?', [$like])
                   ->orWhereRaw('LOWER(penulis) LIKE ?', [$like]);
            });
        }

        $buku = $q->get()->map(function ($b) {
            return [
                'id' => $b->id,
                'judul' => $b->judul,
                'penulis' => $b->penulis,
                'tahun_terbit' => $b->tahun_terbit,
                'stok' => $b->stok,
            ];
        });

        return response()->json(['data' => $buku]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $request->validate([
            'buku_id' => 'required|exists:buku,id',
        ]);

        $buku = Buku::findOrFail($request->buku_id);

        // Validasi stok
        if ($buku->stok < 1) {
            return back()->withErrors(['buku_id' => 'Stok buku tidak tersedia'])->withInput();
        }

        // Cek apakah sudah meminjam buku ini dan belum dikembalikan
        $existing = Pinjaman::where('pengguna_id', $user->id)
            ->where('buku_id', $buku->id)
            ->where('status', 'sedang_dipinjam')
            ->first();

        if ($existing) {
            return back()->withErrors(['buku_id' => 'Anda sudah meminjam buku ini'])->withInput();
        }

        $payload = [
            'pengguna_id' => $user->id,
            'buku_id' => $buku->id,
            'status' => 'menunggu_approval',
        ];
        if (Schema::hasColumn('pinjaman', 'tanggal_pinjam')) {
            $payload['tanggal_pinjam'] = Carbon::now()->toDateString();
        }
        if (Schema::hasColumn('pinjaman', 'tanggal_jatuh_tempo')) {
            $payload['tanggal_jatuh_tempo'] = Carbon::now()->addDays(7)->toDateString();
        }
        if (Schema::hasColumn('pinjaman', 'denda')) {
            $payload['denda'] = 0;
        }
        $pinjaman = Pinjaman::create($payload);

        // Jangan kurangi stok dulu, tunggu approval

        // Buat notifikasi untuk pengguna bahwa pengajuan berhasil
        Notifikasi::create([
            'pengguna_id' => $user->id,
            'judul' => 'Pengajuan Peminjaman Berhasil',
            'pesan' => "Pengajuan peminjaman buku '{$buku->judul}' telah berhasil dikirim. Menunggu persetujuan admin/staff.",
            'tipe' => 'info',
            'dibaca' => false,
            'link' => route('pengembalian.index'),
        ]);

        // Buat notifikasi untuk semua admin dan staff bahwa ada pengajuan baru
        $adminStaff = Pengguna::whereIn('peran', ['admin', 'staff'])->get();
        foreach ($adminStaff as $as) {
            // Tentukan route berdasarkan role
            $link = ($as->peran === 'staff') ? route('staff.laporan-peminjaman') : route('laporan-peminjaman');
            
            Notifikasi::create([
                'pengguna_id' => $as->id,
                'judul' => 'Pengajuan Peminjaman Baru',
                'pesan' => "{$user->nama} mengajukan peminjaman buku '{$buku->judul}'. Silakan review dan approve di halaman Laporan Peminjaman.",
                'tipe' => 'warning',
                'dibaca' => false,
                'link' => $link,
            ]);
        }

        return redirect()->route('pengembalian.index')
            ->with('success', 'Peminjaman berhasil diajukan! Menunggu persetujuan admin/staff.');
    }
}
