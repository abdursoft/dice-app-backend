<?php

namespace App\Http\Controllers;

use App\Models\GameRound;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GameRoundController extends Controller
{
    /**
     * Display a listing of game rounds.
     */
    public function index()
    {
        return response()->json(GameRound::with(['firstPlayer', 'secondPlayer'])->get());
    }

    /**
     * Store a newly created game round.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'message'       => 'nullable|string',
            'status'        => 'in:playing,pause,completed',
            'first_player'  => 'required|exists:users,id',
            'second_player' => 'required|exists:users,id',
        ]);

        $data['round_id'] = bin2hex(random_bytes(16)); // generate unique round id

        $round = GameRound::create($data);

        return response()->json($round, 201);
    }

    /**
     * Display a single game round.
     */
    public function show(GameRound $gameRound)
    {
        return response()->json($gameRound->load(['firstPlayer', 'secondPlayer']));
    }

    /**
     * Update a game round.
     */
    public function update(Request $request, GameRound $gameRound)
    {
        $data = $request->validate([
            'message' => 'nullable|string',
            'status'  => 'in:playing,pause,completed',
        ]);

        $gameRound->update($data);

        return response()->json($gameRound);
    }

    /**
     * Remove a game round.
     */
    public function destroy(GameRound $gameRound)
    {
        $gameRound->delete();

        return response()->json(['message' => 'Game round deleted successfully']);
    }
}
