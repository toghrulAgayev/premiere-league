<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Services\LeagueService;
use Illuminate\Http\JsonResponse;

class LeagueController extends Controller
{
    protected LeagueService $leagueService;

    public function __construct(LeagueService $leagueService)
    {
        $this->leagueService = $leagueService;
    }

    public function resetLeague(): JsonResponse
    {
        $this->leagueService->resetLeague();
        return response()->json(['message' => 'League reset successfully']);
    }

    public function predictions(): JsonResponse
    {
        return response()->json($this->leagueService->calculateWinProbabilities());
    }
}
