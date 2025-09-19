<?php

namespace App\Events;

use App\Models\GameChallenge;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChallengeEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $challenge;

    public function __construct(GameChallenge $challenge)
    {
        $this->challenge = $challenge->load([
            'challenger' => function ($q) {
            $q->select('id', 'name', 'avatar', 'highest_score', 'token'); },
            'challengee' => function ($q) {
            $q->select('id', 'name', 'avatar', 'highest_score', 'token'); },
            'gameround'
        ]);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("user.challenge.{$this->challenge->challengee_id}"),
        ];
    }
}
