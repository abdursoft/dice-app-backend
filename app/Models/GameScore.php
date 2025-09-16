<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'score',
        'player_id',
        'round_id',
    ];

    /**
     * A score belongs to a player (user).
     */
    public function player()
    {
        return $this->belongsTo(User::class, 'player_id');
    }

    /**
     * A score belongs to a game round.
     */
    public function round()
    {
        return $this->belongsTo(GameRound::class, 'round_id');
    }
}
