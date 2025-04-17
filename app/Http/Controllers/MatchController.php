<?php

namespace App\Http\Controllers;

use App\Repositories\MatchRepository;
use App\Services\LeagueService;
use App\Services\MatchService;
use App\Services\MatchSimulationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MatchController extends Controller
{
    private  MatchService $matchService;
    private MatchSimulationService $matchSimulationService;
    private MatchRepository $matchRepository;
    public function __construct( MatchRepository $matchRepository, MatchService $matchService,MatchSimulationService $matchSimulationService)
    {
        $this->matchRepository = $matchRepository;
        $this->matchService = $matchService;
        $this->matchSimulationService = $matchSimulationService;
    }

    public function index(): JsonResponse
    {
        $matches = $this->matchRepository->getAll();
        return response()->json($matches);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->all();
        $match = $this->matchRepository->create($data);
        return response()->json($match, 201);
    }

    public function update(Request $request, $matchId): JsonResponse
    {
        $newHomeScore = $request->input('home_team_score');
        $newAwayScore = $request->input('away_team_score');

        try {
            $this->matchSimulationService->updateMatchResult($matchId, $newHomeScore, $newAwayScore);
            return response()->json(['message' => 'Match updated successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update match: ' . $e->getMessage()], 500);
        }
    }

    public function lastWeekMatches(): JsonResponse
    {
        $matches = $this->matchService->getLastWeekMatches();
        return response()->json($matches);
    }
}
