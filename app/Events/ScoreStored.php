<?php
namespace App\Events;

use App\Models\GameScore;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class ScoreStored implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public $score;

    public function __construct(GameScore $score)
    {
        $this->score = $score;
    }

    public function broadcastOn()
    {
        // Channel is tied to the round/game
        return new Channel("game.round.{$this->score->round_id}");
    }
}
