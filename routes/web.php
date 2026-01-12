<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffBukuController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\RiwayatPencarianController;
use App\Http\Controllers\UserPeminjamanController;
use App\Http\Controllers\PesanController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CreatePinjamanController;
use App\Http\Controllers\SearchPageController;
use App\Models\Buku;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman Umum
|--------------------------------------------------------------------------
*/
Route::view('/', 'welcome')->name('welcome');
Route::view('/welcome', 'welcome');

/*
|--------------------------------------------------------------------------
| DETAIL BUKU (TANPA LOGIN)
|--------------------------------------------------------------------------
*/
Route::get('/detail/{slug}', function (string $slug) {
    $books = Buku::dummyData();

    if (!isset($books[$slug])) {
        abort(404);
    }

    return view('detail', ['book' => $books[$slug]]);
})->name('detail');

/*
|--------------------------------------------------------------------------
| AUTH (Login - Register - Logout)
|--------------------------------------------------------------------------
*/
Route::get('/login', fn () => view('login'))->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');

Route::get('/register', fn () => view('register'))->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.process');

Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| HALAMAN SETELAH LOGIN
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::get('/home', [HomeController::class, 'index'])
        ->middleware('role:pengguna,staff,admin')
        ->name('home');

    Route::view('/notifikasi', 'notifikasi')
        ->middleware('role:pengguna,staff,admin')
        ->name('notifikasi');

    Route::get('/search', [SearchPageController::class, 'index'])
        ->middleware('role:pengguna,staff,admin')
        ->name('search');

    Route::get('/pengembalian-buku', [\App\Http\Controllers\PengembalianBukuController::class, 'index'])
        ->middleware('role:pengguna')
        ->name('pengembalian.index');

    Route::get('/pengembalian/create', function () {
        $user = [
            'nama' => session('nama', 'Pengguna'),
            'email' => session('email'),
            'role' => session('role'),
        ];
        return view('create', ['user' => $user]);
    })
        ->middleware('role:pengguna')
        ->name('pengembalian.create');

    Route::post('/pengembalian', [CreatePinjamanController::class, 'store'])
        ->middleware('role:pengguna')
        ->name('pengembalian.store');

    Route::get('/api/search-buku', [CreatePinjamanController::class, 'searchBuku'])
        ->middleware('role:pengguna')
        ->name('api.search-buku');

    Route::get('/create', fn () => redirect()->route('pengembalian.create'))
        ->middleware('role:pengguna');

    Route::get('/pengaturan', function () {
        $user = [
            'nama' => session('nama', 'Pengguna'),
            'email' => session('email'),
            'role' => session('role'),
        ];
        if (session('role') === 'admin') {
            return view('pengaturan', ['user' => $user]);
        }
        return view('pengaturan-user', ['user' => $user]);
    })
        ->middleware('role:pengguna,admin')
        ->name('pengaturan');

    Route::put('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::redirect('/settings', '/pengaturan');

    // Route notifikasi
    Route::get('/api/notifikasi', [\App\Http\Controllers\NotifikasiController::class, 'index'])
        ->name('notifikasi.index');
    Route::put('/api/notifikasi/{id}/read', [\App\Http\Controllers\NotifikasiController::class, 'markAsRead'])
        ->name('notifikasi.read');
    Route::put('/api/notifikasi/read-all', [\App\Http\Controllers\NotifikasiController::class, 'markAllAsRead'])
        ->name('notifikasi.read-all');

    // Route pesan
    Route::get('/api/pesan', [\App\Http\Controllers\PesanController::class, 'index'])
        ->name('pesan.index');
    Route::post('/api/pesan', [\App\Http\Controllers\PesanController::class, 'store'])
        ->middleware('role:admin,staff')
        ->name('pesan.store');
    // Endpoint untuk pengguna mengirim pesan ke admin/staff atau ke admin tertentu
    Route::post('/api/pesan/send', [\App\Http\Controllers\PesanController::class, 'storeForUser'])
        ->name('pesan.storeForUser');
    Route::put('/api/pesan/{id}/read', [\App\Http\Controllers\PesanController::class, 'markRead'])
        ->name('pesan.read');
    Route::put('/api/pesan/{id}/confirm', [\App\Http\Controllers\PesanController::class, 'confirm'])
        ->name('pesan.confirm');
    Route::post('/api/pesan/{id}/reply', [\App\Http\Controllers\PesanController::class, 'reply'])
        ->name('pesan.reply');
    Route::delete('/api/pesan/{id}', [PesanController::class, 'destroy'])
        ->name('pesan.destroy');
    Route::get('/api/pengguna', [\App\Http\Controllers\PenggunaController::class, 'index'])
        ->middleware('role:admin,staff')
        ->name('pengguna.index');
    // Statistik pengguna per role (ikut middleware admin/staff)
    Route::get('/api/pengguna/stats', [\App\Http\Controllers\PenggunaController::class, 'stats'])
        ->middleware('role:admin,staff')
        ->name('pengguna.stats');

    // Daftar admin/staff untuk keperluan pemilihan penerima pesan (tersedia untuk pengguna terautentikasi)
    Route::get('/api/pengguna/admins', [\App\Http\Controllers\PenggunaController::class, 'listAdmins'])
        ->name('pengguna.listAdmins');

    // Statistik pinjaman (dipakai oleh halaman laporan untuk sinkronisasi)
    Route::get('/api/pinjaman/stats', [\App\Http\Controllers\LaporanPeminjamanController::class, 'stats'])
        ->middleware('role:admin,staff')
        ->name('pinjaman.stats');

    // API pencarian buku (String Matching + filter)
    Route::get('/api/search', [SearchController::class, 'index'])->name('api.search');

    // API detail buku untuk admin & staff
    Route::get('/api/buku/{id}', [\App\Http\Controllers\KelolaBukuController::class, 'show'])
        ->middleware('role:admin,staff')
        ->name('api.buku.show');

    // API riwayat pencarian (berbasis session auth)
    Route::prefix('api')->group(function () {
        Route::get('/riwayat-pencarian', [RiwayatPencarianController::class, 'index'])->name('riwayat.index');
        Route::delete('/riwayat-pencarian', [RiwayatPencarianController::class, 'clear'])->name('riwayat.clear');
        Route::delete('/riwayat-pencarian/{keyword}', [RiwayatPencarianController::class, 'destroy'])->name('riwayat.destroy');
    });

    // Dashboard staff
    Route::view('/staff', 'staff')
        ->middleware('role:staff')
        ->name('staff');
});

/*
|--------------------------------------------------------------------------
| ADMIN AREA
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin');
    Route::get('/api/admin/stats', [AdminController::class, 'getStats'])->name('admin.api.stats');
    Route::get('/api/admin/chart-data', [AdminController::class, 'getChartData'])->name('admin.api.chart');
});

// Halaman operasional ADMIN
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/data-anggota', [\App\Http\Controllers\DataAnggotaController::class, 'index'])->name('data.anggota');
    Route::get('/kelola-buku', [\App\Http\Controllers\KelolaBukuController::class, 'index'])->name('kelola.buku');
    Route::get('/kelola-buku/create', [\App\Http\Controllers\KelolaBukuController::class, 'create'])->name('kelola-buku.create');
    Route::post('/kelola-buku', [\App\Http\Controllers\KelolaBukuController::class, 'store'])->name('kelola-buku.store');
    Route::get('/kelola-buku/{id}', [\App\Http\Controllers\KelolaBukuController::class, 'show'])->name('kelola-buku.show');
    Route::put('/kelola-buku/{id}', [\App\Http\Controllers\KelolaBukuController::class, 'update'])->name('kelola-buku.update');
    Route::delete('/kelola-buku/{id}', [\App\Http\Controllers\KelolaBukuController::class, 'destroy'])->name('kelola-buku.destroy');
    // Sinkronisasi data dummy ke DB (protected oleh middleware admin)
    Route::post('/kelola-buku/sync', [\App\Http\Controllers\KelolaBukuController::class, 'syncDummy'])->name('kelola-buku.sync');
    Route::post('/kelola-buku/import', [StaffBukuController::class, 'import'])->name('kelola-buku.import');
    Route::get('/laporan-peminjaman', [AdminController::class, 'laporanPeminjaman'])->name('laporan-peminjaman');
    Route::view('/kelola-user', 'kelola-user')->name('kelola-user');
    Route::post('/kelola-user', [\App\Http\Controllers\PenggunaController::class, 'store'])->name('kelola-user.store');
    // API routes for pinjaman used by admin UI (laporan-peminjaman)
    Route::post('/api/pinjaman/{id}/approve', [\App\Http\Controllers\ApprovePinjamanController::class, 'approve'])->name('api.pinjaman.approve');
    Route::post('/api/pinjaman/{id}/reject', [\App\Http\Controllers\ApprovePinjamanController::class, 'reject'])->name('api.pinjaman.reject');
    Route::post('/api/pinjaman/{id}/confirm-taken', [\App\Http\Controllers\ApprovePinjamanController::class, 'confirmTaken'])->name('api.pinjaman.confirmTaken');
    Route::post('/api/pinjaman/{id}/return', [\App\Http\Controllers\PinjamanController::class, 'returnBook'])->name('api.pinjaman.return');});

// Halaman operasional STAFF (path terpisah tapi UI sama)
Route::middleware(['auth', 'role:staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/', [\App\Http\Controllers\StaffController::class, 'dashboard'])->name('dashboard');
    Route::get('/data-anggota', [\App\Http\Controllers\DataAnggotaController::class, 'index'])->name('data-anggota');
    Route::get('/kelola-buku', [\App\Http\Controllers\KelolaBukuController::class, 'index'])->name('kelola-buku');
    Route::get('/kelola-buku/create', [\App\Http\Controllers\KelolaBukuController::class, 'create'])->name('kelola-buku.create');
    Route::post('/kelola-buku', [\App\Http\Controllers\KelolaBukuController::class, 'store'])->name('kelola-buku.store');
    Route::get('/kelola-buku/{id}', [\App\Http\Controllers\KelolaBukuController::class, 'show'])->name('kelola-buku.show');
    Route::put('/kelola-buku/{id}', [\App\Http\Controllers\KelolaBukuController::class, 'update'])->name('kelola-buku.update');
    Route::delete('/kelola-buku/{id}', [\App\Http\Controllers\KelolaBukuController::class, 'destroy'])->name('kelola-buku.destroy');
    // Sinkronisasi data dummy ke DB (protected oleh middleware staff)
    Route::post('/kelola-buku/sync', [\App\Http\Controllers\KelolaBukuController::class, 'syncDummy'])->name('staff.kelola-buku.sync');
    Route::view('/kelola-buku/import', 'staff.kelola-buku-import')->name('kelola-buku.import');
    Route::get('/kelola-buku/export', [StaffBukuController::class, 'export'])->name('kelola-buku.export');
    Route::view('/laporan-peminjaman', 'staff.laporan-peminjaman')->name('laporan-peminjaman');
    Route::view('/pengaturan', 'staff.pengaturan')->name('pengaturan');
    Route::post('/kelola-buku/import', [StaffBukuController::class, 'import'])->name('kelola-buku.import.process');
    
    // Route untuk log performa pencarian (tanpa controller baru)
    Route::post('/log-search', function (\Illuminate\Http\Request $request) {
        if (!\Illuminate\Support\Facades\Auth::check()) {
            return response()->json(['success' => false], 401);
        }
        
        try {
            \App\Models\LogPencarian::create([
                'pengguna_id' => \Illuminate\Support\Facades\Auth::id(),
                'kata_kunci' => $request->input('kata_kunci', ''),
                'jumlah_hasil' => $request->input('jumlah_hasil', 0),
                'algorithm' => $request->input('algorithm', 'bf'),
                'process_time_ms' => $request->input('process_time_ms', 0),
            ]);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    })->name('log-search');

    // API routes for staff pinjaman actions
    Route::post('/api/pinjaman/{id}/approve', [\App\Http\Controllers\ApprovePinjamanController::class, 'approve'])->name('staff.api.pinjaman.approve');
    Route::post('/api/pinjaman/{id}/reject', [\App\Http\Controllers\ApprovePinjamanController::class, 'reject'])->name('staff.api.pinjaman.reject');
    Route::post('/api/pinjaman/{id}/confirm-taken', [\App\Http\Controllers\ApprovePinjamanController::class, 'confirmTaken'])->name('staff.api.pinjaman.confirmTaken');
    Route::post('/api/pinjaman/{id}/return', [\App\Http\Controllers\PinjamanController::class, 'returnBook'])->name('staff.api.pinjaman.return');
});

// Route untuk pencarian string matching (bisa digunakan oleh semua role yang sudah login)
Route::middleware(['auth'])->group(function () {
    Route::post('/api/string-match', function (\Illuminate\Http\Request $request) {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'text' => 'required|string',
            'pattern' => 'required|string',
            'algorithm' => 'string|in:bf,kmp,bm',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $text = $request->input('text');
            $pattern = $request->input('pattern');
            $algorithm = $request->input('algorithm', 'bm');

            $startTime = microtime(true);
            $positions = \App\Services\StringMatching::matchPositions($text, $pattern, $algorithm, true);
            $processTime = (microtime(true) - $startTime) * 1000; // dalam ms

            return response()->json([
                'success' => true,
                'data' => [
                    'positions' => $positions,
                    'hasMatch' => count($positions) > 0,
                    'algorithm' => $algorithm,
                    'process_time_ms' => $processTime,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat melakukan pencarian',
            ], 500);
        }
    })->name('api.string-match');
});

// Halaman peminjaman untuk pengguna
Route::middleware(['auth', 'role:pengguna'])->group(function () {
    Route::get('/peminjaman', [UserPeminjamanController::class, 'index'])->name('peminjaman');
});
