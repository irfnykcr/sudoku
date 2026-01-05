<?php

namespace App\Http\Controllers;

use App\Models\SudokuGame;
use App\Services\SudokuGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GameController extends Controller {
    public function load(Request $request) {
        $user = auth('api')->user();
        
        if (!$user) {
             return response()->json(['game' => null]);
        }

        $game = SudokuGame::where('user', $user->user)
            ->where('type', 'normal')
            ->first();

        if (!$game) {
            return response()->json(['game' => null]);
        }

        return response()->json([
            'game' => $this->formatGameResponse($game)
        ]);
    }

    public function start(Request $request) {
        $difficulties = implode(',', array_keys(config('sudoku.difficulties')));
        $request->validate([
            'difficulty' => 'required|in:' . $difficulties,
        ]);

        $user = auth('api')->user();
        $difficulty = $request->input('difficulty');

        $generated = SudokuGenerator::generate($difficulty);

        if (!$user) {
            session(['guest_game' => [
                'solution' => $generated['solution'],
                'difficulty' => $difficulty,
                'puzzle' => $generated['puzzle'],
                'start_time' => time()
            ]]);
            
            $isScored = config("sudoku.difficulties.{$difficulty}.scored", true);

            return response()->json(['game' => [
                'id' => 0,
                'type' => 'normal',
                'difficulty' => $difficulty,
                'puzzle' => $generated['puzzle'],
                'values' => $generated['puzzle'],
                'notesMask' => array_fill(0, 81, 0),
                'elapsedSeconds' => 0,
                'isCompleted' => false,
                'isReplay' => !$isScored,
                'score' => 0
            ]]);
        }

        $game = SudokuGame::updateOrCreate(
            ['user' => $user->user, 'type' => 'normal'],
            [
                'difficulty' => $difficulty,
                'puzzle' => $generated['puzzle'],
                'solution' => $generated['solution'],
                'current_state' => $generated['puzzle'],
                'notes' => array_fill(0, 81, 0),
                'elapsed_seconds' => 0,
                'is_completed' => false,
                'is_replay' => false,
            ]
        );

        return response()->json([
            'game' => $this->formatGameResponse($game)
        ]);
    }

    public function save(Request $request) {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['success' => true]); // dont save guest sessions
        }

        $request->validate([
            'id' => 'integer|exists:games,id',
            'values' => 'required|array|size:81',
            'notes' => 'array|size:81',
            'elapsed_seconds' => 'required|integer|min:0',
        ]);

        $query = SudokuGame::where('user', $user->user);
        
        if ($request->has('id')) {
            $game = $query->where('id', $request->input('id'))->firstOrFail();
        } else {
            $game = $query->where('type', 'normal')->firstOrFail();
        }

        if ($game->is_completed) {
            return response()->json(['message' => 'Game already completed'], 400);
        }

        $elapsed = max((int)$request->input('elapsed_seconds'), $game->elapsed_seconds);

        $game->update([
            'current_state' => $request->input('values'),
            'notes' => $request->input('notes') ?? $game->notes,
            'elapsed_seconds' => $elapsed,
        ]);

        return response()->json(['success' => true]);
    }

    public function reset(Request $request) {
        $user = auth('api')->user();
        
        if (!$user) {
            // restore guest session if data is provided
            if ($request->has(['puzzle', 'difficulty'])) {
                session(['guest_game' => [
                    'puzzle' => $request->input('puzzle'),
                    'difficulty' => $request->input('difficulty'),
                    'start_time' => time(),
                ]]);
            }
            return response()->json(['success' => true]);
        }

        $query = SudokuGame::where('user', $user->user);
        
        if ($request->has('id')) {
            $game = $query->where('id', $request->input('id'))->firstOrFail();
        } else {
            $game = $query->where('type', 'normal')->firstOrFail();
        }

        $isReplay = $game->is_completed || $game->is_replay;

        $game->update([
            'current_state' => $game->puzzle,
            'notes' => array_fill(0, 81, 0),
            'elapsed_seconds' => 0,
            'is_completed' => false,
            'is_replay' => $isReplay,
        ]);

        return response()->json(['success' => true]);
    }

    public function check(Request $request) {
        $user = auth('api')->user();

        if (!$user) {
            $guestGame = session('guest_game');
            if (!$guestGame) {
                return response()->json(['message' => 'No active guest game'], 404);
            }

            $current = $request->input('values');
            $original = $guestGame['puzzle'];
            
            if ($this->isValidSudoku($current, $original)) {
                $elapsed = $request->input('elapsed_seconds', 0);
                $score = $this->calculateScore($guestGame['difficulty'], $elapsed);
                
                // clear session after completion
                session()->forget('guest_game');

                return response()->json([
                    'completed' => true,
                    'score' => $score,
                    'is_replay' => false,
                    'unlocked_achievements' => []
                ]);
            }
            
            return response()->json(['completed' => false]);
        }

        $request->validate([
            'id' => 'integer|exists:games,id',
            'values' => 'array|size:81',
            'elapsed_seconds' => 'integer|min:0',
        ]);

        $query = SudokuGame::where('user', $user->user);
        
        if ($request->has('id')) {
            $game = $query->where('id', $request->input('id'))->firstOrFail();
        } else {
            $game = $query->where('type', 'normal')->firstOrFail();
        }

        if ($game->is_completed) {
            return response()->json(['message' => 'Game already completed'], 400);
        }

        $current = $request->input('values') ?? $game->current_state;
        $original = $game->puzzle;
        
        if ($this->isValidSudoku($current, $original)) {
            $inputElapsed = $request->input('elapsed_seconds');
            
            $finalElapsed = $inputElapsed !== null 
                ? max((int)$inputElapsed, $game->elapsed_seconds) 
                : $game->elapsed_seconds;

            $score = $this->calculateScore($game->difficulty, $finalElapsed);
            $unlocked = [];

            $isScored = config("sudoku.difficulties.{$game->difficulty}.scored", true);

            if (!$game->is_replay && $isScored) {
                $user->total_score += $score;
                if ($score > $user->best_score) {
                    $user->best_score = $score;
                }

                $difficulties = array_keys(config('sudoku.difficulties'));
                $defaultStats = array_fill_keys($difficulties, 0);
                $defaultStats['Daily'] = 0;
                
                $stats = $user->stats ?? $defaultStats;
                $key = $game->type === 'daily' ? 'Daily' : $game->difficulty;
                if (isset($stats[$key])) {
                    $stats[$key]++;
                } else {
                    $stats[$key] = 1;
                }
                $user->stats = $stats;
                
                $user->save();

                $unlocked = $this->checkAchievements($user, $game);
            }

            $game->update([
                'is_completed' => true,
                'current_state' => $current,
                'elapsed_seconds' => $finalElapsed,
                'score' => $score
            ]);

            return response()->json([
                'completed' => true,
                'score' => $score,
                'is_replay' => $game->is_replay || !$isScored,
                'unlocked_achievements' => $unlocked
            ]);
        }

        return response()->json(['completed' => false]);
    }

    private function calculateScore(string $difficulty, int $elapsed): int {
        $m = config("sudoku.difficulties.{$difficulty}.score_multiplier", 1.0);
        
        $base = 1000 * $m;
        
        $timeFactor = 3600 / max(300, $elapsed);
        
        return (int)floor($base * $timeFactor);
    }

    private function checkAchievements($user, $game): array {
        $unlocked = [];
        
        $totalGames = array_sum($user->stats ?? []);

        if ($totalGames === 1) {
            $unlocked[] = $this->unlockAchievement($user, 'first_win');
        }

        if ($game->elapsed_seconds < 300) {
            $unlocked[] = $this->unlockAchievement($user, 'speed_demon');
        }

        if ($game->difficulty === 'Extreme') {
            $unlocked[] = $this->unlockAchievement($user, 'extreme_solver');
        }

        return array_filter($unlocked);
    }

    private function unlockAchievement($user, $slug) {
        $achievement = \DB::table('achievements')->where('slug', $slug)->first();
        if (!$achievement) return null;

        $exists = \DB::table('user_achievements')
            ->where('user_id', $user->id)
            ->where('achievement_id', $achievement->id)
            ->exists();

        if (!$exists) {
            \DB::table('user_achievements')->insert([
                'user_id' => $user->id,
                'achievement_id' => $achievement->id,
                'unlocked_at' => now()
            ]);
            return $achievement;
        }
        return null;
    }

    private function isValidSudoku(array $current, array $original): bool {
        for ($i = 0; $i < 81; $i++) {
            if ($original[$i] !== 0 && $current[$i] !== $original[$i]) {
                return false;
            }
        }

        for ($r = 0; $r < 9; $r++) {
            $seen = [];
            for ($c = 0; $c < 9; $c++) {
                $val = $current[$r * 9 + $c];
                if ($val < 1 || $val > 9 || isset($seen[$val])) return false;
                $seen[$val] = true;
            }
        }

        for ($c = 0; $c < 9; $c++) {
            $seen = [];
            for ($r = 0; $r < 9; $r++) {
                $val = $current[$r * 9 + $c];
                if ($val < 1 || $val > 9 || isset($seen[$val])) return false;
                $seen[$val] = true;
            }
        }

        for ($br = 0; $br < 3; $br++) {
            for ($bc = 0; $bc < 3; $bc++) {
                $seen = [];
                for ($r = 0; $r < 3; $r++) {
                    for ($c = 0; $c < 3; $c++) {
                        $idx = ($br * 3 + $r) * 9 + ($bc * 3 + $c);
                        $val = $current[$idx];
                        if ($val < 1 || $val > 9 || isset($seen[$val])) return false;
                        $seen[$val] = true;
                    }
                }
            }
        }

        return true;
    }

    public function daily(Request $request) {
        $user = auth('api')->user();
        $date = $request->input('date') ?? date('Y-m-d');
        
        if (strtotime($date) > strtotime(date('Y-m-d'))) {
            return response()->json(['error' => 'Cannot play future games'], 400);
        }

        $game = SudokuGame::where('user', $user->user)
            ->where('type', 'daily')
            ->where('daily_date', $date)
            ->first();

        if (!$game) {
            $seed = (int)str_replace('-', '', $date);
            
            // weighted random selection for difficulty
            mt_srand($seed);
            $rand = mt_rand(1, 100);
            $cumulative = 0;
            $diffi = 'Medium'; // default
            
            $probs = config('sudoku.daily_probabilities');
            foreach ($probs as $difficulty => $chance) {
                $cumulative += $chance;
                if ($rand <= $cumulative) {
                    $diffi = $difficulty;
                    break;
                }
            }

            $generated = SudokuGenerator::generate($diffi, $seed);

            $game = SudokuGame::create([
                'user' => $user->user,
                'type' => 'daily',
                'daily_date' => $date,
                'difficulty' => $diffi,
                'puzzle' => $generated['puzzle'],
                'solution' => $generated['solution'],
                'current_state' => $generated['puzzle'],
                'notes' => array_fill(0, 81, 0),
                'elapsed_seconds' => 0,
                'is_completed' => false,
                'is_replay' => false,
            ]);
        }

        return response()->json([
            'game' => $this->formatGameResponse($game)
        ]);
    }

    public function calendar(Request $request) {
        $user = auth('api')->user();
        $year = $request->input('year') ?? date('Y');
        $month = $request->input('month') ?? date('m');
        
        $startDate = "$year-$month-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        $games = SudokuGame::where('user', $user->user)
            ->where('type', 'daily')
            ->whereBetween('daily_date', [$startDate, $endDate])
            ->get();

        $calendar = [];
        foreach ($games as $game) {
            $date = $game->daily_date instanceof \DateTimeInterface 
                ? $game->daily_date->format('Y-m-d') 
                : $game->daily_date;
                
            $calendar[$date] = [
                'status' => $game->is_completed ? 'completed' : 'in_progress',
            ];
        }

        return response()->json(['calendar' => $calendar]);
    }

    public function stats(Request $request) {
        $user = auth('api')->user();
        $achievements = \DB::table('user_achievements')
            ->join('achievements', 'user_achievements.achievement_id', '=', 'achievements.id')
            ->where('user_achievements.user_id', $user->id)
            ->select('achievements.*', 'user_achievements.unlocked_at')
            ->get();

        $difficulties = array_keys(config('sudoku.difficulties'));
        $defaultStats = array_fill_keys($difficulties, 0);
        $defaultStats['Daily'] = 0;

        return response()->json([
            'best_score' => $user->best_score,
            'total_score' => $user->total_score,
            'stats' => $user->stats ?? $defaultStats,
            'achievements' => $achievements
        ]);
    }

    private function formatGameResponse(SudokuGame $game) {
        $isScored = config("sudoku.difficulties.{$game->difficulty}.scored", true);
        return [
            'id' => $game->id,
            'type' => $game->type,
            'daily_date' => $game->daily_date,
            'difficulty' => $game->difficulty,
            'puzzle' => $game->puzzle,
            'values' => $game->current_state,
            'notesMask' => $game->notes,
            'elapsedSeconds' => $game->elapsed_seconds,
            'isCompleted' => $game->is_completed,
            'isReplay' => $game->is_replay || !$isScored,
            'score' => $game->score,
        ];
    }
}
