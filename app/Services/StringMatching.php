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

        // Use mb functions and operate on character arrays to be UTF-8 safe
        if ($caseInsensitive) {
            $text = mb_strtolower($text, 'UTF-8');
            $pattern = mb_strtolower($pattern, 'UTF-8');
        }

        // Early return jika pattern lebih panjang dari text (character length)
        if (mb_strlen($pattern, 'UTF-8') > mb_strlen($text, 'UTF-8')) {
            return [];
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
        // Work with UTF-8 character arrays for safety
        $textArr = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $patArr = preg_split('//u', $pattern, -1, PREG_SPLIT_NO_EMPTY);
        $n = count($textArr);
        $m = count($patArr);
        $res = [];

        if ($m > $n) {
            return $res;
        }

        // Optimasi: untuk pattern pendek, gunakan join substr comparison
        if ($m <= 4) {
            $lastPos = $n - $m;
            for ($i = 0; $i <= $lastPos; $i++) {
                $substr = implode('', array_slice($textArr, $i, $m));
                if ($substr === $pattern) {
                    $res[] = $i;
                }
            }

            return $res;
        }

        // Untuk pattern lebih panjang, gunakan karakter-by-karakter dengan early exit
        $lastChar = $patArr[$m - 1];
        $lastPos = $n - $m;

        for ($i = 0; $i <= $lastPos; $i++) {
            if ($textArr[$i + $m - 1] !== $lastChar) {
                continue;
            }

            $j = 0;
            while ($j < $m - 1 && $textArr[$i + $j] === $patArr[$j]) {
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
        // Use character arrays for multibyte safety
        $textArr = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $patArr = preg_split('//u', $pattern, -1, PREG_SPLIT_NO_EMPTY);
        $n = count($textArr);
        $m = count($patArr);
        $res = [];

        if ($m === 0 || $m > $n) {
            return $res;
        }

        // Gunakan cache untuk LPS (cache key is pattern string)
        $cacheKey = $pattern;
        if (! isset(self::$lpsCache[$cacheKey])) {
            self::$lpsCache[$cacheKey] = self::kmpLps($pattern);
        }
        $lps = self::$lpsCache[$cacheKey];

        $i = 0;
        $j = 0;

        while ($i < $n) {
            if ($textArr[$i] === $patArr[$j]) {
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

        return $res;
    }

    /**
     * Optimized KMP LPS dengan mengurangi operasi array access
     */
    private static function kmpLps(string $pattern): array
    {
        // Work with multibyte characters
        $patArr = preg_split('//u', $pattern, -1, PREG_SPLIT_NO_EMPTY);
        $m = count($patArr);
        $lps = array_fill(0, $m, 0);

        if ($m <= 1) {
            return $lps;
        }

        $len = 0;
        $i = 1;

        while ($i < $m) {
            if ($patArr[$i] === $patArr[$len]) {
                $len++;
                $lps[$i] = $len;
                $i++;
            } else {
                if ($len !== 0) {
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
        // Use character arrays for multibyte safety
        $textArr = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $patArr = preg_split('//u', $pattern, -1, PREG_SPLIT_NO_EMPTY);
        $n = count($textArr);
        $m = count($patArr);
        $res = [];

        if ($m === 0 || $m > $n) {
            return $res;
        }

        // Gunakan cache untuk bad character table (keyed by pattern string)
        $cacheKey = $pattern;
        if (! isset(self::$badCharCache[$cacheKey])) {
            self::$badCharCache[$cacheKey] = self::buildBadCharTable($pattern);
        }
        $bad = self::$badCharCache[$cacheKey];

        $shift = 0;

        while ($shift <= $n - $m) {
            $j = $m - 1;

            // Match dari kanan ke kiri
            while ($j >= 0 && $patArr[$j] === $textArr[$shift + $j]) {
                $j--;
            }

            if ($j < 0) {
                $res[] = $shift;

                // Galil-like optimization
                if ($shift + $m < $n) {
                    $nextChar = $textArr[$shift + $m];
                    $bc = $bad[$nextChar] ?? -1;
                    $shift += max(1, $m - ($bc));
                } else {
                    $shift++;
                }
            } else {
                $mismatchChar = $textArr[$shift + $j];
                $bc = $bad[$mismatchChar] ?? -1;
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
        // Build table keyed by multibyte character
        $patArr = preg_split('//u', $pattern, -1, PREG_SPLIT_NO_EMPTY);
        $m = count($patArr);
        $bad = [];

        for ($i = 0; $i < $m; $i++) {
            $bad[$patArr[$i]] = $i;
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
