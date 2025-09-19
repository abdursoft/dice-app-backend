<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameChallenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'challenge_score',
        'challenger_id',
        'challengee_id',
        'status',
    ];

    // Challenger relation
    public function challenger()
    {
        return $this->belongsTo(User::class, 'challenger_id');
    }

    // Challengee relation
    public function challengee()
    {
        return $this->belongsTo(User::class, 'challengee_id');
    }

    // game round relation
    public function gameround(){
        return $this->hasOne(GameRound::class);
    }
}
