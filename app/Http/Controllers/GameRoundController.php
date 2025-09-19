<?php

namespace App\Http\Controllers;

use App\Events\ChallengeEvent;
use App\Models\GameChallenge;
use App\Models\GameRound;
use Illuminate\Http\Request;

class GameRoundController extends Controller
{
    /**
     * Display all rounds.
     */
    public function index()
    {
        $rounds = GameRound::with(['challenge', 'firstPlayer', 'secondPlayer'])->get();
        return response()->json($rounds);
    }

    /**
     * Store a new round.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'message'            => 'nullable|string',
            'game_turn'          => 'nullable|integer|min:0',
            'status'             => 'in:playing,pause,completed',
            'game_challenge_id'  => 'required|exists:game_challenges,id',
            'first_player'       => 'required|exists:users,id',
            'second_player'      => 'required|exists:users,id',
        ]);

        $round = GameRound::create($data);

        $challenge = GameChallenge::find($request->game_challenge_id);
        
        // broadcast to the challengee
        broadcast( new ChallengeEvent($challenge) );

        return response()->json($round, 201);
    }

    /**
     * Show a single round.
     */
    public function show(GameRound $gameRound)
    {
        return response()->json($gameRound->load(['challenge', 'firstPlayer', 'secondPlayer']));
    }

    /**
     * Update a round.
     */
    public function update(Request $request, GameRound $gameRound)
    {
        $data = $request->validate([
            'message'   => 'nullable|string',
            'game_turn' => 'nullable|integer|min:0',
            'status'    => 'in:playing,pause,completed',
        ]);

        $gameRound->update($data);

        return response()->json($gameRound);
    }

    /**
     * Delete a round.
     */
    public function destroy(GameRound $gameRound)
    {
        $gameRound->delete();
        return response()->json(['message' => 'Round deleted successfully']);
    }
}
