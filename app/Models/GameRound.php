<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameRound extends Model
{
    use HasFactory;

    protected $fillable = [
        'round_id',
        'message',
        'game_turn',
        'status',
        'game_challenge_id',
        'first_player',
        'second_player',
    ];

    // Each round belongs to a challenge
    public function challenge()
    {
        return $this->belongsTo(GameChallenge::class, 'game_challenge_id');
    }

    // First player relation
    public function firstPlayer()
    {
        return $this->belongsTo(User::class, 'first_player');
    }

    // Second player relation
    public function secondPlayer()
    {
        return $this->belongsTo(User::class, 'second_player');
    }

    // Auto-generate round_id if not set
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->round_id)) {
                $model->round_id = bin2hex(random_bytes(6)); // 12 chars unique ID
            }
        });
    }
}
