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
        'status',
        'first_player',
        'second_player',
    ];

    /**
     * Relationship: First Player (User)
     */
    public function firstPlayer()
    {
        return $this->belongsTo(User::class, 'first_player');
    }

    /**
     * Relationship: Second Player (User)
     */
    public function secondPlayer()
    {
        return $this->belongsTo(User::class, 'second_player');
    }
}
