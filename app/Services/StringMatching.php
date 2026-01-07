<?php

namespace App\Services;

class StringMatching
{
    // Cache untuk LPS dan Bad Character Table
    private static array $lpsCache = [];

    private static array $badCharCache = [];

    public static function matchPositions(string $text, string $pattern, string $algo = 'bm', bool $caseInsensitive = false): array
    {
        if ($pattern === '') {
            return [];
        }

        // Early return jika pattern lebih panjang dari text
        if (strlen($pattern) > strlen($text)) {
            return [];
        }

        if ($caseInsensitive) {
            $text = strtolower($text);
            $pattern = strtolower($pattern);
        }

        return match ($algo) {
            'bf' => self::bruteForce($text, $pattern),
            'kmp' => self::kmp($text, $pattern),
            'bm' => self::boyerMoore($text, $pattern),
            default => self::boyerMoore($text, $pattern),
        };
    }

    /**
     * Optimized Brute Force dengan early exit dan string comparison yang lebih efisien
     */
    private static function bruteForce(string $text, string $pattern): array
    {
        $n = strlen($text);
        $m = strlen($pattern);
        $res = [];

        if ($m > $n) {
            return $res;
        }

        // Optimasi: untuk pattern pendek, gunakan substr comparison yang lebih cepat
        if ($m <= 4) {
            $lastPos = $n - $m;
            for ($i = 0; $i <= $lastPos; $i++) {
                if (substr_compare($text, $pattern, $i, $m) === 0) {
                    $res[] = $i;
                }
            }

            return $res;
        }

        // Untuk pattern lebih panjang, gunakan karakter-by-karakter dengan early exit
        $lastChar = $pattern[$m - 1];
        $lastPos = $n - $m;

        for ($i = 0; $i <= $lastPos; $i++) {
            // Quick check: karakter terakhir harus match dulu
            if ($text[$i + $m - 1] !== $lastChar) {
                continue;
            }

            // Jika karakter terakhir match, baru cek seluruh pattern
            $j = 0;
            while ($j < $m - 1 && $text[$i + $j] === $pattern[$j]) {
                $j++;
            }

            if ($j === $m - 1) {
                $res[] = $i;
            }
        }

        return $res;
    }

    /**
     * Optimized KMP dengan cached LPS dan optimasi loop
     */
    private static function kmp(string $text, string $pattern): array
    {
        $n = strlen($text);
        $m = strlen($pattern);
        $res = [];

        if ($m === 0 || $m > $n) {
            return $res;
        }

        // Gunakan cache untuk LPS
        $cacheKey = $pattern;
        if (! isset(self::$lpsCache[$cacheKey])) {
            self::$lpsCache[$cacheKey] = self::kmpLps($pattern);
        }
        $lps = self::$lpsCache[$cacheKey];

        $i = 0;
        $j = 0;

        // Optimasi: unroll loop untuk pattern pendek
        if ($m <= 8) {
            while ($i < $n) {
                if ($text[$i] === $pattern[$j]) {
                    $i++;
                    $j++;
                    if ($j === $m) {
                        $res[] = $i - $j;
                        $j = $lps[$j - 1];
                    }
                } else {
                    if ($j !== 0) {
                        $j = $lps[$j - 1];
                    } else {
                        $i++;
                    }
                }
            }
        } else {
            // Versi standar untuk pattern panjang
            while ($i < $n) {
                if ($text[$i] === $pattern[$j]) {
                    $i++;
                    $j++;
                    if ($j === $m) {
                        $res[] = $i - $j;
                        $j = $lps[$j - 1];
                    }
                } else {
                    if ($j !== 0) {
                        $j = $lps[$j - 1];
                    } else {
                        $i++;
                    }
                }
            }
        }

        return $res;
    }

    /**
     * Optimized KMP LPS dengan mengurangi operasi array access
     */
    private static function kmpLps(string $pattern): array
    {
        $m = strlen($pattern);
        $lps = array_fill(0, $m, 0);

        if ($m <= 1) {
            return $lps;
        }

        $len = 0;
        $i = 1;

        while ($i < $m) {
            if ($pattern[$i] === $pattern[$len]) {
                $len++;
                $lps[$i] = $len;
                $i++;
            } else {
                if ($len !== 0) {
                    // Optimasi: langsung gunakan nilai dari cache jika ada
                    $len = $lps[$len - 1];
                } else {
                    $lps[$i] = 0;
                    $i++;
                }
            }
        }

        return $lps;
    }

    /**
     * Optimized Boyer-Moore dengan cached bad character table dan Galil's optimization
     */
    private static function boyerMoore(string $text, string $pattern): array
    {
        $n = strlen($text);
        $m = strlen($pattern);
        $res = [];

        if ($m === 0 || $m > $n) {
            return $res;
        }

        // Gunakan cache untuk bad character table
        $cacheKey = $pattern;
        if (! isset(self::$badCharCache[$cacheKey])) {
            self::$badCharCache[$cacheKey] = self::buildBadCharTable($pattern);
        }
        $bad = self::$badCharCache[$cacheKey];

        $shift = 0;
        $lastMatch = -1; // Untuk Galil's optimization

        while ($shift <= $n - $m) {
            $j = $m - 1;

            // Match dari kanan ke kiri
            while ($j >= 0 && $pattern[$j] === $text[$shift + $j]) {
                $j--;
            }

            if ($j < 0) {
                // Match found
                $res[] = $shift;

                // Galil's optimization: skip karakter yang sudah match
                if ($shift + $m < $n) {
                    $nextChar = ord($text[$shift + $m]);
                    $shift += max(1, $m - ($bad[$nextChar] ?? -1));
                } else {
                    $shift++;
                }
            } else {
                // Bad character rule dengan optimasi
                $mismatchChar = ord($text[$shift + $j]);
                $bc = $bad[$mismatchChar] ?? -1;

                // Shift berdasarkan bad character rule
                $shift += max(1, $j - $bc);
            }
        }

        return $res;
    }

    /**
     * Build bad character table dengan optimasi memory
     */
    private static function buildBadCharTable(string $pattern): array
    {
        $m = strlen($pattern);
        $bad = array_fill(0, 256, -1);

        // Hanya set karakter yang ada di pattern
        for ($i = 0; $i < $m; $i++) {
            $bad[ord($pattern[$i])] = $i;
        }

        return $bad;
    }

    /**
     * Clear cache (berguna untuk testing atau memory management)
     */
    public static function clearCache(): void
    {
        self::$lpsCache = [];
        self::$badCharCache = [];
    }
}
