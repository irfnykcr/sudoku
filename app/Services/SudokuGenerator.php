<?php

namespace App\Services;

class SudokuGenerator {
    private const SIZE = 9;
    private const CELL_COUNT = 81;

    public static function generate(string $difficulty, ?int $seed = null): array {
        if ($seed !== null) {
            mt_srand($seed);
        }

        $config = config('sudoku.difficulties.' . $difficulty) ?? config('sudoku.difficulties.Easy');
        $targetGivens = rand($config['min_givens'], $config['max_givens']);

        $empty = array_fill(0, self::CELL_COUNT, 0);
        $solution = self::fillRandom($empty);
        
        if (!$solution) {
            // fallback if generation fails (?)
            return self::generate($difficulty, $seed ? $seed + 1 : null);
        }

        $puzzle = $solution;
        $indices = range(0, self::CELL_COUNT - 1);
        shuffle($indices);

        $givens = self::CELL_COUNT;

        foreach ($indices as $idx) {
            if ($givens <= $targetGivens) {
                break;
            }

            $prev = $puzzle[$idx];
            $puzzle[$idx] = 0;

            // check if unique solution remains
            if (self::countSolutions($puzzle, 2) !== 1) {
                $puzzle[$idx] = $prev;
            } else {
                $givens--;
            }
        }

        return [
            'puzzle' => $puzzle,
            'solution' => $solution,
            'difficulty' => $difficulty
        ];
    }

    private static function fillRandom(array $board): ?array {
        return self::search($board, true) ? $board : null;
    }

    private static function countSolutions(array $board, int $limit = 2): int {
        return self::searchCount($board, $limit);
    }

    private static function search(array &$board, bool $randomize): bool {
        $best = self::selectCell($board);
        if ($best['idx'] === -1) return true;
        if ($best['count'] === 0) return false;

        $idx = $best['idx'];
        $mask = $best['mask'];
        
        $bits = [];
        while ($mask) {
            $bit = $mask & -$mask;
            $bits[] = $bit;
            $mask ^= $bit;
        }

        if ($randomize) {
            shuffle($bits);
        }

        foreach ($bits as $bit) {
            $board[$idx] = self::bitToDigit($bit);
            if (self::search($board, $randomize)) return true;
            $board[$idx] = 0;
        }

        return false;
    }

    private static function searchCount(array $board, int $limit): int {
        $best = self::selectCell($board);
        if ($best['idx'] === -1) return 1;
        if ($best['count'] === 0) return 0;

        $idx = $best['idx'];
        $mask = $best['mask'];
        $total = 0;

        while ($mask) {
            $bit = $mask & -$mask;
            $mask ^= $bit;
            
            $board[$idx] = self::bitToDigit($bit);
            $total += self::searchCount($board, $limit - $total);
            $board[$idx] = 0;

            if ($total >= $limit) return $total;
        }

        return $total;
    }

    private static function selectCell(array $board): array {
        $bestIdx = -1;
        $bestCount = 10;
        $bestMask = 0;

        for ($idx = 0; $idx < self::CELL_COUNT; $idx++) {
            if ($board[$idx]) continue;

            $mask = self::candidatesMask($board, $idx);
            $count = self::popCount($mask);

            if ($count === 0) return ['idx' => $idx, 'mask' => 0, 'count' => 0];
            if ($count < $bestCount) {
                $bestIdx = $idx;
                $bestCount = $count;
                $bestMask = $mask;
                if ($count === 1) break;
            }
        }

        return ['idx' => $bestIdx, 'mask' => $bestMask, 'count' => $bestCount];
    }

    private static function candidatesMask(array $board, int $idx): int {
        $used = 0;
        $peers = self::getPeers($idx);
        
        foreach ($peers as $p) {
            $v = $board[$p];
            if ($v) $used |= 1 << ($v - 1);
        }

        return (~$used) & 0b111111111;
    }

    private static function getPeers(int $idx): array {
        static $cache = [];
        if (isset($cache[$idx])) return $cache[$idx];

        $r = intdiv($idx, 9);
        $c = $idx % 9;
        $b = intdiv($r, 3) * 3 + intdiv($c, 3);

        $peers = [];
        
        // row
        for ($i = 0; $i < 9; $i++) $peers[] = $r * 9 + $i;
        // col
        for ($i = 0; $i < 9; $i++) $peers[] = $i * 9 + $c;
        // box
        $br = intdiv($b, 3) * 3;
        $bc = ($b % 3) * 3;
        for ($rr = $br; $rr < $br + 3; $rr++) {
            for ($cc = $bc; $cc < $bc + 3; $cc++) {
                $peers[] = $rr * 9 + $cc;
            }
        }

        $peers = array_unique($peers);
        $peers = array_diff($peers, [$idx]);
        
        return $cache[$idx] = array_values($peers);
    }

    private static function popCount(int $n): int {
        $c = 0;
        while ($n) {
            $c += $n & 1;
            $n >>= 1;
        }
        return $c;
    }

    private static function bitToDigit(int $bit): int {
        return log($bit, 2) + 1;
    }
}
