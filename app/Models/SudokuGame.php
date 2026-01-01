<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SudokuGame extends Model {
    protected $table = 'games';

    protected $fillable = [
        'user',
        'type',
        'daily_date',
        'difficulty',
        'puzzle',
        'solution',
        'current_state',
        'notes',
        'elapsed_seconds',
        'is_completed',
    ];

    protected $casts = [
        'puzzle' => 'array',
        'solution' => 'array',
        'current_state' => 'array',
        'notes' => 'array',
        'is_completed' => 'boolean',
        'daily_date' => 'date',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user', 'user');
    }
}
