<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameTurn extends Model
{
    protected $fillable = [
        'user_id',
        'game_round_id',
    ];

    // relation with users 
    public function user(){
        $this->belongsTo(User::class, 'user_id');
    }

    // relation with round 
    public function gameround(){
        $this->belongsTo(GameRound::class, 'game_round_id');
    }
}
