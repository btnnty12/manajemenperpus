<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class StaffBukuController extends Controller
{
    /**
     * Export data buku ke Excel (CSV format yang bisa dibuka di Excel)
     */
    public function export()
    {
        $buku = Buku::select('judul', 'penulis', 'genre', 'tahun_terbit', 'stok', 'deskripsi')
            ->orderBy('judul')
            ->get();

        $filename = 'data_buku_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        // Tambahkan BOM untuk UTF-8 agar Excel membaca dengan benar
        $callback = function() use ($buku) {
            $file = fopen('php://output', 'w');
            
            // Tambahkan BOM untuk UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header kolom
            fputcsv($file, [
                'Judul',
                'Penulis',
                'Kategori',
                'Tahun Terbit',
                'Stok',
                'Deskripsi'
            ], ';'); // Gunakan semicolon untuk kompatibilitas Excel Indonesia
            
            // Data buku
            foreach ($buku as $row) {
                fputcsv($file, [
                    $row->judul ?? '',
                    $row->penulis ?? '',
                    $row->genre ?? '',
                    $row->tahun_terbit ?? '',
                    $row->stok ?? 0,
                    $row->deskripsi ?? ''
                ], ';');
            }
            
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Import data buku dari file CSV (ekspor Excel sebagai CSV)
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return back()->withErrors(['file' => 'File tidak bisa dibaca']);
        }

        // Deteksi delimiter: coba semicolon terlebih dahulu (sesuai export), fallback comma
        $delimiter = ';';
        $firstLine = fgets($handle);
        if ($firstLine !== false && substr_count($firstLine, ',') > substr_count($firstLine, ';')) {
            $delimiter = ',';
        }
        // Reset pointer ke awal
        rewind($handle);

        $rowIndex = 0;
        $imported = 0;
        $failed = 0;
        $errors = [];

        // Baca header
        $header = fgetcsv($handle, 0, $delimiter);
        if (!$header) {
            fclose($handle);
            return back()->withErrors(['file' => 'Header tidak ditemukan pada file']);
        }

        // Normalisasi header
        $normalized = array_map(function ($h) {
            return strtolower(trim($h));
        }, $header);

        // Pemetaan kolom yang diharapkan
        $map = [
            'judul' => array_search('judul', $normalized, true),
            'penulis' => array_search('penulis', $normalized, true),
            'kategori' => array_search('kategori', $normalized, true),
            'tahun_terbit' => array_search('tahun terbit', $normalized, true),
            'stok' => array_search('stok', $normalized, true),
            'deskripsi' => array_search('deskripsi', $normalized, true),
        ];

        // Validasi minimal kolom wajib
        if ($map['judul'] === false || $map['penulis'] === false) {
            fclose($handle);
            return back()->withErrors(['file' => 'File harus memiliki kolom Judul dan Penulis (header tepat)']);
        }

        // Proses data baris
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowIndex++;
            // Lewati baris kosong
            if (count(array_filter($row, fn($v) => trim((string)$v) !== '')) === 0) {
                continue;
            }

            try {
                $judul = isset($row[$map['judul']]) ? trim($row[$map['judul']]) : '';
                $penulis = isset($row[$map['penulis']]) ? trim($row[$map['penulis']]) : '';
                $kategori = $map['kategori'] !== false && isset($row[$map['kategori']]) ? trim($row[$map['kategori']]) : null;
                $tahun = $map['tahun_terbit'] !== false && isset($row[$map['tahun_terbit']]) ? (int) trim($row[$map['tahun_terbit']]) : null;
                $stok = $map['stok'] !== false && isset($row[$map['stok']]) ? (int) trim($row[$map['stok']]) : 0;
                $deskripsi = $map['deskripsi'] !== false && isset($row[$map['deskripsi']]) ? trim($row[$map['deskripsi']]) : null;

                if ($judul === '' || $penulis === '') {
                    $failed++;
                    $errors[] = "Baris {$rowIndex}: Judul/Penulis kosong";
                    continue;
                }

                Buku::updateOrCreate(
                    ['judul' => $judul],
                    [
                        'penulis' => $penulis,
                        'genre' => $kategori,
                        'tahun_terbit' => $tahun,
                        'stok' => $stok,
                        'deskripsi' => $deskripsi,
                    ]
                );

                $imported++;
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = "Baris {$rowIndex}: " . ($e->getMessage() ?? 'Gagal diproses');
            }
        }

        fclose($handle);

        $message = "Import selesai. Berhasil: {$imported}, Gagal: {$failed}.";
        return back()->with('success', $message)->with('import_errors', $errors);
    }
}
