<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Services\LeagueService;

class LeagueController extends Controller
{
    protected $leagueService;

    public function __construct(LeagueService $leagueService)
    {
        $this->leagueService = $leagueService;
    }

    public function simulateNextWeek()
    {
        $response = $this->leagueService->playNextWeek();
        return response()->json(['message' => $response]);
    }

    public function simulateAllWeeks()
    {
        $this->leagueService->playAllWeeks();
        return response()->json(['message' => 'All weeks simulated successfully']);
    }


    public function resetLeague()
    {
        $this->leagueService->resetLeague();
        return response()->json(['message' => 'League reset successfully']);
    }

    public function predictions()
    {
        return response()->json($this->leagueService->calculateWinProbabilities());
    }
}
