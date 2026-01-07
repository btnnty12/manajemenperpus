<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Pengguna;
use App\Models\Pinjaman;
use App\Models\Activity;
use App\Models\Notifikasi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        
        // Statistik Card 1: Sedang Dipinjam
        $sedangDipinjam = Pinjaman::where('status', 'sedang_dipinjam')->count();
        
        // Statistik Card 2: Notifikasi Hari Ini
        $notifikasiHariIni = Notifikasi::whereDate('created_at', Carbon::today())->count();
        
        // Statistik Card 3: Pengembalian Minggu Ini
        $pengembalianMingguIni = Pinjaman::where('status', 'dikembalikan')
            ->whereBetween('tanggal_kembali', [
                Carbon::now()->startOfWeek()->toDateString(),
                Carbon::now()->endOfWeek()->toDateString()
            ])
            ->count();
        
        // Peminjaman Aktif (untuk tabel)
        $peminjamanAktif = Pinjaman::with(['pengguna', 'buku'])
            ->whereIn('status', ['sedang_dipinjam', 'dapat_diambil', 'menunggu_approval'])
            ->orderBy('tanggal_jatuh_tempo', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($pinjam) {
                $isTerlambat = false;
                if ($pinjam->tanggal_jatuh_tempo && Carbon::parse($pinjam->tanggal_jatuh_tempo)->isPast() && $pinjam->status === 'sedang_dipinjam') {
                    $isTerlambat = true;
                }
                
                return [
                    'id' => $pinjam->id,
                    'user' => $pinjam->pengguna->nama ?? 'Unknown',
                    'judul' => $pinjam->buku->judul ?? 'Unknown',
                    'due' => $pinjam->tanggal_jatuh_tempo ? Carbon::parse($pinjam->tanggal_jatuh_tempo)->format('d M') : '-',
                    'status' => $isTerlambat ? 'Telat' : ucfirst(str_replace('_', ' ', $pinjam->status)),
                    'isTerlambat' => $isTerlambat,
                ];
            });
        
        // Profil Staff Stats
        $peminjamanAktifStaff = Pinjaman::whereIn('status', ['sedang_dipinjam', 'dapat_diambil', 'menunggu_approval'])->count();
        $pengembalianHariIni = Pinjaman::where('status', 'dikembalikan')
            ->whereDate('tanggal_kembali', Carbon::today())
            ->count();
        
        // Aktivitas Terbaru
        $aktivitasTerbaru = Activity::with('pengguna')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($act) {
                $meta = is_array($act->meta) ? $act->meta : json_decode($act->meta, true) ?? [];
                $bukuJudul = $meta['buku_judul'] ?? '';
                
                $aksi = '';
                if ($act->type === 'pinjam_buku') {
                    $aksi = 'Meminjam';
                } elseif ($act->type === 'kembalikan_buku') {
                    $aksi = 'Mengembalikan';
                } elseif ($act->type === 'cari_buku') {
                    $aksi = 'Mencari';
                    $bukuJudul = $meta['keyword'] ?? '';
                } else {
                    $aksi = ucfirst(str_replace('_', ' ', $act->type));
                }
                
                return [
                    'user' => $act->pengguna->nama ?? 'Unknown',
                    'aksi' => $aksi,
                    'judul' => $bukuJudul ?: ($act->description ?? ''),
                    'waktu' => $act->created_at->diffForHumans(),
                ];
            });
        
        return view('staff', compact(
            'sedangDipinjam',
            'notifikasiHariIni',
            'pengembalianMingguIni',
            'peminjamanAktif',
            'peminjamanAktifStaff',
            'pengembalianHariIni',
            'aktivitasTerbaru',
            'user'
        ));
    }
}

