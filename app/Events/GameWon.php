<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameWon implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public $roundId;
    public $winnerId;
    public $scores;
    public $lastScores;

    public function __construct($roundId, $winnerId, $scores, $lastScores)
    {
        $this->roundId = $roundId;
        $this->winnerId = $winnerId;
        $this->scores = $scores;
        $this->lastScores = $lastScores;
    }

    public function broadcastOn()
    {
        return new PrivateChannel("user.turn.{$this->roundId}");
    }

    public function broadcastWith()
    {
        return [
            'round_id'  => $this->roundId,
            'winner_id' => $this->winnerId,
            'summery'    => $this->scores,
            'history'    => $this->lastScores
        ];
    }
}
