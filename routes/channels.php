<?php
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.challenge.{challengeeId}', function ($user, $challengeeId) {
    return (int) $user->id === (int) $challengeeId;
});

Broadcast::channel('user.turn.{roundId}', function ($user, $roundId) {
    return $user->gameRounds()->where('id', $roundId)->exists();
});

Broadcast::channel('game.round.{roundId}', function ($user, $roundId) {
    return $user->gameRounds()->where('id', $roundId)->exists(); // restrict to participants
});
