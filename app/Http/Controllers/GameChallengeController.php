<?php

namespace App\Http\Controllers;

use App\Models\GameChallenge;
use Illuminate\Http\Request;

class GameChallengeController extends Controller
{
    /**
     * Display a listing of challenges.
     */
    public function index()
    {
        $challenges = GameChallenge::with(['challenger', 'challengee'])->get();
        return response()->json($challenges);
    }

    /**
     * Store a newly created challenge.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'challenge_score' => 'nullable|integer|min:1',
            'challenger_id'   => 'required|exists:users,id',
            'challengee_id'   => 'required|exists:users,id',
            'status'          => 'in:pending,accepted,declined',
        ]);

        $challenge = GameChallenge::create($data);

        return response()->json($challenge, 201);
    }

    /**
     * Display the specified challenge.
     */
    public function show(GameChallenge $gameChallenge)
    {
        return response()->json($gameChallenge->load(['challenger', 'challengee']));
    }

    /**
     * Update the specified challenge.
     */
    public function update(Request $request, GameChallenge $gameChallenge)
    {
        $data = $request->validate([
            'challenge_score' => 'nullable|integer|min:1',
            'status'          => 'in:pending,accepted,declined',
        ]);

        $gameChallenge->update($data);

        return response()->json($gameChallenge);
    }

    /**
     * Remove the specified challenge.
     */
    public function destroy(GameChallenge $gameChallenge)
    {
        $gameChallenge->delete();
        return response()->json(['message' => 'Challenge deleted successfully']);
    }
}
