<?php

declare(strict_types=1);

namespace Ifsware\Omnisearch;

final class OmnisearchFuzzyMatcher
{
    /**
     * Score how well $query matches $text. Returns 0 (no match) to 100 (exact).
     */
    public static function score(string $query, string $text): int
    {
        $query = mb_strtolower(mb_trim($query));
        $text = mb_strtolower($text);

        if ($query === '') {
            return 0;
        }

        // Exact match
        if ($text === $query) {
            return 100;
        }

        // Starts with query
        if (str_starts_with($text, $query)) {
            return 90;
        }

        // Contains exact query
        if (str_contains($text, $query)) {
            return 80;
        }

        // Every query word is a prefix of some word in text
        $queryWords = preg_split('/\s+/u', $query, flags: PREG_SPLIT_NO_EMPTY) ?: [];
        $textWords = preg_split('/[\s\-_]+/u', $text, flags: PREG_SPLIT_NO_EMPTY) ?: [];

        if ($queryWords !== [] && self::allWordsHavePrefix($queryWords, $textWords)) {
            return 70;
        }

        // Every query word is contained anywhere in text
        if ($queryWords !== [] && self::allWordsContained($queryWords, $text)) {
            return 60;
        }

        // Full query is a subsequence of text
        if (self::isSubsequence($query, $text)) {
            return 50;
        }

        // Each query word is a subsequence of text
        if ($queryWords !== [] && self::allWordsSubsequence($queryWords, $text)) {
            return 40;
        }

        return 0;
    }

    public static function matches(string $query, string $text): bool
    {
        return self::score($query, $text) > 0;
    }

    /**
     * @param  array<int, string>  $queryWords
     * @param  array<int, string>  $textWords
     */
    private static function allWordsHavePrefix(array $queryWords, array $textWords): bool
    {
        foreach ($queryWords as $qw) {
            $found = false;

            foreach ($textWords as $tw) {
                if (str_starts_with($tw, $qw)) {
                    $found = true;
                    break;
                }
            }

            if (! $found) {
                return false;
            }
        }

        return true;
    }

    /** @param array<int, string> $queryWords */
    private static function allWordsContained(array $queryWords, string $text): bool
    {
        foreach ($queryWords as $qw) {
            if (! str_contains($text, $qw)) {
                return false;
            }
        }

        return true;
    }

    /** @param array<int, string> $queryWords */
    private static function allWordsSubsequence(array $queryWords, string $text): bool
    {
        foreach ($queryWords as $qw) {
            if (! self::isSubsequence($qw, $text)) {
                return false;
            }
        }

        return true;
    }

    private static function isSubsequence(string $needle, string $haystack): bool
    {
        $ni = 0;
        $nlen = mb_strlen($needle);
        $hlen = mb_strlen($haystack);

        for ($hi = 0; $hi < $hlen && $ni < $nlen; $hi++) {
            if (mb_substr($haystack, $hi, 1) === mb_substr($needle, $ni, 1)) {
                $ni++;
            }
        }

        return $ni === $nlen;
    }
}
