<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pinjaman;
use App\Models\Notifikasi;
use Carbon\Carbon;

class SendReturnReminderNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifikasi:reminder-pengembalian';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim notifikasi reminder H-1 sebelum tanggal pengembalian buku';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Ambil pinjaman yang jatuh tempo besok (H-1)
        $besok = Carbon::tomorrow();
        
        $pinjamanBesok = Pinjaman::where('status', 'sedang_dipinjam')
            ->whereDate('tanggal_jatuh_tempo', $besok->toDateString())
            ->with(['pengguna', 'buku'])
            ->get();

        $count = 0;

        foreach ($pinjamanBesok as $pinjam) {
            // Cek apakah sudah ada notifikasi untuk pinjaman ini hari ini
            $notifikasiHariIni = Notifikasi::where('pengguna_id', $pinjam->pengguna_id)
                ->where('judul', 'LIKE', '%Pengembalian Buku%')
                ->whereDate('created_at', Carbon::today())
                ->where('link', route('pengembalian.index'))
                ->exists();

            if (!$notifikasiHariIni) {
                $judulBuku = $pinjam->buku ? $pinjam->buku->judul : 'Buku';
                Notifikasi::create([
                    'pengguna_id' => $pinjam->pengguna_id,
                    'judul' => 'Reminder Pengembalian Buku',
                    'pesan' => "Buku '{$judulBuku}' harus dikembalikan besok ({$besok->format('d F Y')}). Jangan lupa untuk mengembalikan tepat waktu!",
                    'tipe' => 'warning',
                    'dibaca' => false,
                    'link' => route('pengembalian.index'),
                ]);
                $count++;
            }
        }

        $this->info("Notifikasi reminder berhasil dikirim ke {$count} pengguna.");
        return 0;
    }
}
