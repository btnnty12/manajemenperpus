<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Buku;
use App\Models\Pinjaman;
use Illuminate\Support\Facades\DB;

class SearchPageController extends Controller
{
    public function index()
    {
        // Ambil beberapa buku yang ada (bukan populer)
        $beberapaBuku = Buku::where('stok', '>', 0)
            ->inRandomOrder()
            ->limit(8)
            ->get();

        // Ambil semua genre yang ada
        $genres = Buku::select('genre')
            ->whereNotNull('genre')
            ->distinct()
            ->pluck('genre')
            ->filter()
            ->toArray();

        // Buku terbaru berdasarkan tahun terbit
        $bukuTerbaru = Buku::where('stok', '>', 0)
            ->orderByDesc('tahun_terbit')
            ->limit(6)
            ->get();

        // Ambil tahun min dan max untuk filter
        $minYear = Buku::min('tahun_terbit') ?? 2000;
        $maxYear = Buku::max('tahun_terbit') ?? date('Y');

        // Build a lightweight client-side array for demo/offline search features
        $dummy = \App\Models\Buku::dummyData();
        $allBooks = array_map(function($item) {
            return [
                'title' => $item['title'] ?? ($item['judul'] ?? ''),
                'img' => $item['img'] ?? ($item['cover'] ?? 'images/book.png'),
                'jenis' => $item['genre'] ?? 'Umum',
                'bahasa' => $item['bahasa'] ?? 'Indonesia',
                'tahun' => $item['year'] ?? ($item['tahun_terbit'] ?? null),
            ];
        }, array_values($dummy));

        return view('search', [
            'beberapaBuku' => $beberapaBuku,
            'genres' => $genres,
            'bukuTerbaru' => $bukuTerbaru,
            'minYear' => $minYear,
            'maxYear' => $maxYear,
            'allBooks' => $allBooks,
        ]);
    }
}
