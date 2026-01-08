<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pinjaman;
use App\Models\Notifikasi;
use Carbon\Carbon;

$sedang = Pinjaman::where('status','sedang_dipinjam')->count();
$notifikasi = Notifikasi::whereDate('created_at', Carbon::today())->count();
$pengembalianQuery = Pinjaman::where('status','dikembalikan');
if (\Illuminate\Support\Facades\Schema::hasColumn('pinjaman','tanggal_kembali')) {
    $pengembalianQuery->whereBetween('tanggal_kembali', [Carbon::now()->startOfWeek()->toDateString(), Carbon::now()->endOfWeek()->toDateString()]);
} else {
    $pengembalianQuery->whereBetween('updated_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
}
$pengembalian = $pengembalianQuery->count();

echo "sedang_dipinjam={$sedang}\nnotifikasi_hari_ini={$notifikasi}\npengembalian_minggu_ini={$pengembalian}\n";