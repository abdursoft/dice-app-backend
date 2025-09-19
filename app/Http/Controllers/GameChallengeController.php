<?php

namespace App\Http\Controllers;

use App\Events\ChallengeEvent;
use App\Models\GameChallenge;
use Illuminate\Http\Request;

class GameChallengeController extends Controller
{
    /**
     * Display a listing of challenges.
     */
    public function index(Request $request)
    {
        $challenges = GameChallenge::where('challengee_id',$request->user()->id)
        ->with([
            'challenger' => function($q){
                $q->select('id','name','avatar','token');
            },
            'challengee' => function($q){
                $q->select('id','name','avatar','token');
            },
            'gameround'
        ])
        ->where('status', 'pending')
        ->orderBy('id','desc')->get();
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
     * Accept game challenge
     */
    public function acceptChallenge(Request $request,$id){
        $challenge = GameChallenge::find($id);
        if(!$challenge){
            return response()->json([],422);
        }
        $challenge->status = 'accepted';
        $challenge->save();
        $challenge = $challenge->load([
            'challenger' => function($q){
                $q->select('id','name','avatar','token');
            },
            'challengee' => function($q){
                $q->select('id','name','avatar','token');
            },
            'gameround'
        ]);
        return response()->json($challenge);
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
