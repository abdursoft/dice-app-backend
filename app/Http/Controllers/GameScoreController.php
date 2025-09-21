<?php

namespace App\Http\Controllers;

use App\Events\ScoreStored;
use App\Events\TurnEvent;
use App\Events\GameWon;
use App\Models\GameScore;
use App\Models\GameRound;
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
        'round_id'  => 'required|exists:game_rounds,id',
    ]);

    $validated['player_id'] = $request->user()->id;

    $score = GameScore::create($validated);

    $gameRound = GameRound::where('id',$request->round_id)->first();

    $eventUser = $gameRound->first_player == $request->user()->id
        ? $gameRound->second_player
        : $gameRound->first_player;

    // ✅ Fetch total scores
    $scores = GameScore::where('round_id', $score->round_id)
        ->select('player_id')
        ->selectRaw('SUM(score) as total_score')
        ->groupBy('player_id')
        ->get();

    // ✅ Last 10 scores
    $lastScores = GameScore::where('round_id', $score->round_id)
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get()
        ->groupBy('player_id')
        ->map->pluck('score');

    // ✅ Check win condition
    $winner = null;
    foreach ($scores as $s) {
        if ($s->total_score >= $gameRound->challenge->challenge_score) {   // example win condition
            $winner = $s->player_id;
            break;
        }
    }

    // ✅ Fire broadcast events
    broadcast(new ScoreStored($score));

    if ($winner) {
        // Broadcast game end
        broadcast(new GameWon($score->round_id, $winner, $scores,$lastScores));
        // Mark round as completed
        $gameRound->status = 'completed';
        $gameRound->winner_id = $winner;
        $gameRound->save();
    } else {
        // Normal turn update
        broadcast(new TurnEvent(
            $score->round_id,
            $eventUser,
            $scores,
            $lastScores
        ));
    }

    return response()->json([
        'score' => $score,
        'winner' => $winner,
    ], 201);
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

    /**
     * delete game scores by round id
     */
    public function deleteScoreByRoundId($roundId)
    {
        $deleted = GameScore::where('round_id', $roundId)->delete();

        return response()->json(['deleted' => $deleted]);
    }
}
