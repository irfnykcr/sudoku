<?php

namespace App\Http\Controllers;

use App\Models\SudokuGame;
use App\Services\SudokuGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GameController extends Controller {
    public function load(Request $request) {
        $user = Auth::user();
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
        $request->validate([
            'difficulty' => 'required|in:Easy,Medium,Hard,Extreme',
        ]);

        $user = Auth::user();
        $difficulty = $request->input('difficulty');

        // generate new puzzle
        $generated = SudokuGenerator::generate($difficulty);

        $game = SudokuGame::updateOrCreate(
            ['user' => $user->user, 'type' => 'normal'],
            [
                'difficulty' => $difficulty,
                'puzzle' => $generated['puzzle'],
                'solution' => $generated['solution'],
                'current_state' => $generated['puzzle'], // start with puzzle state
                'notes' => array_fill(0, 81, 0),
                'elapsed_seconds' => 0,
                'is_completed' => false,
            ]
        );

        return response()->json([
            'game' => $this->formatGameResponse($game)
        ]);
    }

    public function save(Request $request) {
        $request->validate([
            'values' => 'required|array|size:81',
            'notes' => 'array|size:81',
            'elapsed_seconds' => 'required|integer|min:0',
        ]);

        $user = Auth::user();
        $game = SudokuGame::where('user', $user->user)
            ->where('type', 'normal')
            ->firstOrFail();

        if ($game->is_completed) {
            return response()->json(['message' => 'Game already completed'], 400);
        }

        // Ensure time never goes backwards (monotonic check)
        // We take the maximum of the stored time and the reported time.
        // This prevents users from sending a lower time (or the same time repeatedly to freeze it, 
        // although we can't fully prevent freezing without server-side ticks).
        $elapsed = max((int)$request->input('elapsed_seconds'), $game->elapsed_seconds);

        $game->update([
            'current_state' => $request->input('values'),
            'notes' => $request->input('notes') ?? $game->notes,
            'elapsed_seconds' => $elapsed,
        ]);

        return response()->json(['success' => true]);
    }

    public function reset(Request $request) {
        $user = Auth::user();
        $game = SudokuGame::where('user', $user->user)
            ->where('type', 'normal')
            ->firstOrFail();

        $game->update([
            'current_state' => $game->puzzle,
            'notes' => array_fill(0, 81, 0),
            'elapsed_seconds' => 0,
            'is_completed' => false,
        ]);

        return response()->json(['success' => true]);
    }

    public function check(Request $request) {
        $request->validate([
            'values' => 'array|size:81',
            'elapsed_seconds' => 'integer|min:0',
        ]);

        $user = Auth::user();
        $game = SudokuGame::where('user', $user->user)
            ->where('type', 'normal')
            ->firstOrFail();

        $current = $request->input('values') ?? $game->current_state;
        $original = $game->puzzle;
        
        if ($this->isValidSudoku($current, $original)) {
            $inputElapsed = $request->input('elapsed_seconds');
            // Ensure monotonic time: take max of input and stored
            $finalElapsed = $inputElapsed !== null 
                ? max((int)$inputElapsed, $game->elapsed_seconds) 
                : $game->elapsed_seconds;

            $game->update([
                'is_completed' => true,
                'current_state' => $current,
                'elapsed_seconds' => $finalElapsed
            ]);
            return response()->json(['completed' => true]);
        }

        return response()->json(['completed' => false]);
    }

    private function isValidSudoku(array $current, array $original): bool {
        // verify original clues are intact
        for ($i = 0; $i < 81; $i++) {
            if ($original[$i] !== 0 && $current[$i] !== $original[$i]) {
                return false;
            }
        }

        // verify board is full and valid
        // rows
        for ($r = 0; $r < 9; $r++) {
            $seen = [];
            for ($c = 0; $c < 9; $c++) {
                $val = $current[$r * 9 + $c];
                if ($val < 1 || $val > 9 || isset($seen[$val])) return false;
                $seen[$val] = true;
            }
        }

        // cols
        for ($c = 0; $c < 9; $c++) {
            $seen = [];
            for ($r = 0; $r < 9; $r++) {
                $val = $current[$r * 9 + $c];
                if ($val < 1 || $val > 9 || isset($seen[$val])) return false;
                $seen[$val] = true;
            }
        }

        // boxes
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

    private function formatGameResponse(SudokuGame $game) {
        return [
            'difficulty' => $game->difficulty,
            'puzzle' => $game->puzzle,
            'values' => $game->current_state,
            'notesMask' => $game->notes,
            'elapsedSeconds' => $game->elapsed_seconds,
            'isCompleted' => $game->is_completed,
            // we do not send the solution to the client
        ];
    }
}
