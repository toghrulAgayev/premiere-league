<?php

namespace App\Http\Controllers;

use App\Services\LeagueService;
use App\Services\MatchService;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    protected $leagueService;
    protected $matchService;
    public function __construct(LeagueService $leagueService, MatchService $matchService)
    {
        $this->leagueService = $leagueService;
        $this->matchService = $matchService;
    }

    public function index()
    {
        $matches = $this->matchService->getAllMatches();
        return response()->json($matches);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $match = $this->matchService->createMatch($data);
        return response()->json($match, 201);
    }

    public function update(Request $request, $matchId)
    {
        $newHomeScore = $request->input('home_team_score');
        $newAwayScore = $request->input('away_team_score');

        try {
            $this->leagueService->updateMatchResult($matchId, $newHomeScore, $newAwayScore);
            return response()->json(['message' => 'Match updated successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update match: ' . $e->getMessage()], 500);
        }
    }

    public function lastWeekMatches()
    {
        return $this->matchService->getLastWeekMatches();
    }
}
