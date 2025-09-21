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
        'winner_id'
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

    // game turn relation
    public function gameturn(){
       return $this->hasOne(GameTurn::class);
    }

    public function winner(){
        return $this->belongsTo(User::class, 'winner_id');
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


    // Static function to get player stats
    public static function getUserStats($userId)
    {
        // 1. Total played games
        $totalPlayed = self::where(function ($q) use ($userId) {
            $q->where('first_player', $userId)
            ->orWhere('second_player', $userId);
        })->count();

        // 2. Total wins
        $totalWins = self::where('winner_id', $userId)->count();

        // 3. Total losses
        $totalLosses = $totalPlayed - $totalWins;

        // 4,5,6: Get scores with round check
        $roundIds = self::where(function ($q) use ($userId) {
            $q->where('first_player', $userId)
            ->orWhere('second_player', $userId);
        })->pluck('id'); // get all round IDs user played

        $scores = GameScore::whereIn('round_id', $roundIds)
                    ->where('player_id', $userId)
                    ->pluck('score');

        $averageScore = $scores->count() ? round($scores->avg(), 2) : 0;
        $highestScore = $scores->count() ? $scores->max() : 0;
        $lowestScore  = $scores->count() ? $scores->min() : 0;

        return [
            'total_played'   => $totalPlayed,
            'total_wins'     => $totalWins,
            'total_losses'   => $totalLosses,
            'average_score'  => $averageScore,
            'highest_score'  => $highestScore,
            'lowest_score'   => $lowestScore,
        ];
    }

}
