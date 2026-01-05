<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sudoku Game Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the Sudoku game, including
    | difficulty levels, score multipliers, and other game settings.
    |
    | Note: Types cannot be strictly enforced here as this is a PHP array.
    |
    */

    'difficulties' => [
        'Easy' => [
            'min_givens' => 51,
            'max_givens' => 64,
            'score_multiplier' => 1.0,
            'scored' => true,
        ],
        'Medium' => [
            'min_givens' => 41,
            'max_givens' => 50,
            'score_multiplier' => 1.5,
            'scored' => true,
        ],
        'Hard' => [
            'min_givens' => 29,
            'max_givens' => 40,
            'score_multiplier' => 2.5,
            'scored' => true,
        ],
        'Extreme' => [
            'min_givens' => 23,
            'max_givens' => 28,
            'score_multiplier' => 4.0,
            'scored' => true,
        ],
        'Test' => [
            'min_givens' => 79,
            'max_givens' => 79,
            'score_multiplier' => 0.0,
            'scored' => false,
        ],
    ],

    // difficulties for daily challenges
    'daily_difficulties' => ['Easy', 'Medium', 'Hard', 'Extreme'],

    // probabilities for daily challenges (sum to 100)
    'daily_probabilities' => [
        'Easy' => 20,
        'Medium' => 40,
        'Hard' => 35,
        'Extreme' => 5,
    ],
];
