<?php

namespace App\Http\Controllers;

use App\Models\GameScore;
use Illuminate\Http\Request;

class GameScoreController extends Controller
{
    /**
     * Display a listing of scores.
     */
    public function index()
    {
        $scores = GameScore::with(['player', 'round'])->get();
        return response()->json($scores);
    }

    /**
     * Store a newly created score.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'score'     => 'required|integer',
            'player_id' => 'required|exists:users,id',
            'round_id'  => 'required|exists:game_rounds,id',
        ]);

        $score = GameScore::create($validated);

        return response()->json($score, 201);
    }

    /**
     * Display a single score.
     */
    public function show(GameScore $gameScore)
    {
        return response()->json($gameScore->load(['player', 'round']));
    }

    /**
     * Update a score.
     */
    public function update(Request $request, GameScore $gameScore)
    {
        $validated = $request->validate([
            'score'     => 'sometimes|integer',
            'player_id' => 'sometimes|exists:users,id',
            'round_id'  => 'sometimes|exists:game_rounds,id',
        ]);

        $gameScore->update($validated);

        return response()->json($gameScore);
    }

    /**
     * Remove a score.
     */
    public function destroy(GameScore $gameScore)
    {
        $gameScore->delete();

        return response()->json(null, 204);
    }
}
