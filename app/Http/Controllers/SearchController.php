<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use Illuminate\Support\Facades\DB;
use App\Models\LogPencarian;
use App\Models\Activity;
use App\Services\StringMatching;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:1|max:255',
            'algo' => ['string', Rule::in(['bm', 'kmp', 'bf'])],
            'case' => 'boolean',
            'per_page' => 'integer|min:1|max:100',
            'page' => 'integer|min:1',
            'genre' => 'nullable|string',
            'tahun' => 'nullable|integer|min:1900|max:2100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $validated = $validator->validated();
            $searchQuery = $validated['q'];
            $algorithm = $validated['algo'] ?? 'bm';
            $caseInsensitive = filter_var($validated['case'] ?? true, FILTER_VALIDATE_BOOL);
            $perPage = (int) ($validated['per_page'] ?? 10);
            $currentPage = (int) ($validated['page'] ?? 1);
            $filterGenre = $validated['genre'] ?? null;
            $filterTahun = $validated['tahun'] ?? null;

            // ======================================================
            // START TIMER - untuk hitung waktu proses
            // ======================================================
            $startTime = microtime(true);

            // ======================================================
            // Pre-filter database (tokenized, order-independent, case-insensitive)
            // ======================================================
            $terms = preg_split('/\s+/', trim($searchQuery));

            $booksQuery = Buku::query()
                ->select(['id', 'judul', 'penulis', 'genre', 'tahun_terbit', 'deskripsi']);

            foreach ($terms as $term) {
                $term = trim($term);
                if ($term === '') continue;

                // Escape wildcard characters to avoid accidental matches
                $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term);
                $like = '%' . mb_strtolower($escaped, 'UTF-8') . '%';

                $booksQuery->where(function ($q) use ($like) {
                    $q->whereRaw('LOWER(judul) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(penulis) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(deskripsi) LIKE ?', [$like]);
                });
            }

            // Apply filter genre jika ada
            if ($filterGenre) {
                $booksQuery->where('genre', $filterGenre);
            }

            // Apply filter tahun jika ada
            if ($filterTahun) {
                $booksQuery->where('tahun_terbit', '<=', $filterTahun);
            }

            $books = $booksQuery->get();

            $results = [];

            foreach ($books as $book) {
                $fields = [
                    'judul' => $book->judul ?? '',
                    'penulis' => $book->penulis ?? '',
                    'deskripsi' => $book->deskripsi ?? '',
                ];

                $matches = [];

                // Use per-term matching so multi-word queries (order-independent) are matched
                $terms = preg_split('/\s+/', trim($searchQuery));

                foreach ($fields as $fieldName => $fieldValue) {
                    $allPositions = [];
                    foreach ($terms as $term) {
                        $term = trim($term);
                        if ($term === '') continue;
                        $pos = StringMatching::matchPositions(
                            $fieldValue,
                            $term,
                            $algorithm,
                            $caseInsensitive
                        );
                        if (! empty($pos)) {
                            $allPositions = array_merge($allPositions, $pos);
                        }
                    }

                    if (! empty($allPositions)) {
                        $matches[$fieldName] = [
                            'positions' => array_values(array_unique($allPositions)),
                            'snippet' => $this->getSnippetMultiple($fieldValue, $terms, $caseInsensitive),
                        ];
                    }
                }

                if (! empty($matches)) {
                    $results[] = [
                        'id' => $book->id,
                        'judul' => $book->judul,
                        'penulis' => $book->penulis,
                        'genre' => $book->genre,
                        'tahun_terbit' => $book->tahun_terbit,
                        'stok' => $book->stok ?? 0,
                        'available' => ($book->stok ?? 0) > 0,
                        'cover_url' => $book->cover_url ?? null,
                        'matches' => $matches,
                    ];
                }
            }

            // Pagination manual
            $total = count($results);
            $resultsPaginated = array_slice($results, ($currentPage - 1) * $perPage, $perPage);

            // ======================================================
            // END TIMER — hitung durasi proses
            // ======================================================
            $processTime = (microtime(true) - $startTime) * 1000; // dalam ms

            // ======================================================
            // SIMPAN LOG PENCARIAN
            // ======================================================
            if (Auth::check()) {
                LogPencarian::create([
                    'pengguna_id' => Auth::id(),
                    'kata_kunci' => $searchQuery,
                    'jumlah_hasil' => $total,
                    'algorithm' => $algorithm,
                    'process_time_ms' => $processTime,
                ]);

                // Log aktivitas pencarian
                Activity::create([
                    'pengguna_id' => Auth::id(),
                    'type' => 'cari_buku',
                    'description' => "Mencari buku dengan kata kunci: {$searchQuery}",
                    'meta' => [
                        'keyword' => $searchQuery,
                        'jumlah_hasil' => $total,
                        'algorithm' => $algorithm,
                        'process_time_ms' => $processTime,
                    ],
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'query' => $searchQuery,
                    'algorithm' => $algorithm,
                    'process_time_ms' => $processTime,
                    'case_insensitive' => $caseInsensitive,
                    'pagination' => [
                        'total' => $total,
                        'per_page' => $perPage,
                        'current_page' => $currentPage,
                        'last_page' => ceil($total / $perPage),
                    ],
                    'results' => $resultsPaginated,
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error('Search error: '.$e->getMessage(), [
                'query' => $request->query('q'),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat melakukan pencarian',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    private function getSnippetMultiple(string $text, array $terms, bool $caseInsensitive): string
    {
        $length = 100;

        // Find earliest occurrence among terms
        $pos = false;
        foreach ($terms as $t) {
            $t = trim($t);
            if ($t === '') continue;
            $p = $caseInsensitive ? mb_stripos($text, $t, 0, 'UTF-8') : mb_strpos($text, $t, 0, 'UTF-8');
            if ($p !== false && ($pos === false || $p < $pos)) {
                $pos = $p;
            }
        }

        if ($pos === false) {
            $plain = mb_substr($text, 0, $length, 'UTF-8');
            return mb_strlen($text, 'UTF-8') > $length ? $plain.'...' : $plain;
        }

        $start = (int) max(0, $pos - ($length / 2));
        $snippet = mb_substr($text, $start, $length, 'UTF-8');

        if ($start > 0) {
            $snippet = '...'.ltrim($snippet);
        }

        if ($start + $length < mb_strlen($text, 'UTF-8')) {
            $snippet = rtrim($snippet).'...';
        }

        // Highlight all terms
        foreach ($terms as $t) {
            $t = trim($t);
            if ($t === '') continue;
            $quoted = preg_quote($t, '/');
            $flags = $caseInsensitive ? 'iu' : 'u';
            $snippet = preg_replace("/($quoted)/{$flags}", '<mark>$1</mark>', $snippet);
        }

        return $snippet;
    }

    private function getSnippet(string $text, string $query, bool $caseInsensitive): string
    {
        $length = 100;

        $pos = $caseInsensitive
            ? mb_stripos($text, $query, 0, 'UTF-8')
            : mb_strpos($text, $query, 0, 'UTF-8');

        if ($pos === false) {
            $plain = mb_substr($text, 0, $length, 'UTF-8');
            return mb_strlen($text, 'UTF-8') > $length ? $plain.'...' : $plain;
        }

        $start = (int) max(0, $pos - ($length / 2));
        $snippet = mb_substr($text, $start, $length, 'UTF-8');

        if ($start > 0) {
            $snippet = '...'.ltrim($snippet);
        }

        if ($start + $length < mb_strlen($text, 'UTF-8')) {
            $snippet = rtrim($snippet).'...';
        }

        // Highlight query occurrences inside snippet (case-insensitive if requested)
        $quoted = preg_quote($query, '/');
        $flags = $caseInsensitive ? 'iu' : 'u';
        $snippetHighlighted = preg_replace("/($quoted)/{$flags}", '<mark>$1</mark>', $snippet);

        return $snippetHighlighted;
    }
}
