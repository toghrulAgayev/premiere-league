<?php

namespace App\Http\Controllers;

use App\Services\MatchSimulationService;
use Illuminate\Http\JsonResponse;

class MatchSimulationController extends Controller
{
    private MatchSimulationService $matchSimulationService;

    public function __construct(MatchSimulationService $matchSimulationService)
    {
        $this->matchSimulationService = $matchSimulationService;
    }

    public function simulateNextWeek(): JsonResponse
    {
        $response = $this->matchSimulationService->playNextWeek();
        return response()->json(['message' => $response]);
    }

    public function simulateAllWeeks(): JsonResponse
    {
        $this->matchSimulationService->playAllWeeks();
        return response()->json(['message' => 'All weeks simulated successfully']);
    }


}
