<?php

namespace Tests\Unit;

use App\Services\StringMatching;
use Tests\TestCase;

class StringMatchingTest extends TestCase
{
    private array $testCases = [
        [
            'text' => 'ABABDABACDABABCABAB',
            'pattern' => 'ABABCABAB',
            'expected' => [10],
            'description' => 'Pattern found in the middle of text',
        ],
        [
            'text' => 'AABAACAADAABAABA',
            'pattern' => 'AABA',
            'expected' => [0, 9, 12],
            'description' => 'Multiple occurrences of pattern',
        ],
        [
            'text' => 'This is a test string',
            'pattern' => 'test',
            'expected' => [10],
            'description' => 'Simple word search',
        ],
        [
            'text' => 'ABCABCABC',
            'pattern' => 'ABC',
            'expected' => [0, 3, 6],
            'description' => 'Repeating pattern',
        ],
        [
            'text' => 'Hello World',
            'pattern' => 'notfound',
            'expected' => [],
            'description' => 'Pattern not found',
        ],
        [
            'text' => 'CaseSensitive',
            'pattern' => 'sensitive',
            'expected' => [],
            'case_sensitive' => true,
            'description' => 'Case sensitive search',
        ],
        [
            'text' => 'CaseInsensitive',
            'pattern' => 'insensitive',
            'expected' => [4],
            'case_sensitive' => false,
            'description' => 'Case insensitive search',
        ],
    ];

    public function test_brute_force_algorithm()
    {
        foreach ($this->testCases as $testCase) {
            $caseInsensitive = $testCase['case_sensitive'] ?? false;
            $result = StringMatching::matchPositions(
                $testCase['text'],
                $testCase['pattern'],
                'bf',
                ! $caseInsensitive
            );
            $this->assertEquals(
                $testCase['expected'],
                $result,
                "Brute Force failed: {$testCase['description']}"
            );
        }
    }

    public function test_kmp_algorithm()
    {
        foreach ($this->testCases as $testCase) {
            $caseInsensitive = $testCase['case_sensitive'] ?? false;
            $result = StringMatching::matchPositions(
                $testCase['text'],
                $testCase['pattern'],
                'kmp',
                ! $caseInsensitive
            );
            $this->assertEquals(
                $testCase['expected'],
                $result,
                "KMP failed: {$testCase['description']}"
            );
        }
    }

    public function test_boyer_moore_algorithm()
    {
        foreach ($this->testCases as $testCase) {
            $caseInsensitive = $testCase['case_sensitive'] ?? false;
            $result = StringMatching::matchPositions(
                $testCase['text'],
                $testCase['pattern'],
                'bm',
                ! $caseInsensitive
            );
            $this->assertEquals(
                $testCase['expected'],
                $result,
                "Boyer-Moore failed: {$testCase['description']}"
            );
        }
    }

    public function test_empty_pattern()
    {
        $result = StringMatching::matchPositions('any text', '');
        $this->assertEquals([], $result, 'Empty pattern should return empty array');
    }

    public function test_pattern_longer_than_text()
    {
        $result = StringMatching::matchPositions('short', 'much longer pattern');
        $this->assertEquals([], $result, 'Should return empty array when pattern is longer than text');
    }
}
