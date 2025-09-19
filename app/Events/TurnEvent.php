<?php

namespace App\Events;

use App\Models\Gameturn;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TurnEvent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public $roundId;
    public $nextUserId;
    public $scores;
    public $lastScores;

    public function __construct($roundId, $nextUserId, $scores, $lastScores)
    {
        $this->roundId = $roundId;
        $this->nextUserId = $nextUserId;
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
            'round_id'     => $this->roundId,
            'next_user_id' => $this->nextUserId,
            'summery'      => $this->scores,
            'history'      => $this->lastScores
        ];
    }
}
