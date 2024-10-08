<?php

namespace App\Http\Controllers;

use App\Services\TeamService;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    protected $teamService;

    public function __construct(TeamService $teamService)
    {
        $this->teamService = $teamService;
    }

    public function index()
    {
        return response()->json($this->teamService->getAllTeams());
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $team = $this->teamService->createTeam($data);
        return response()->json($team, 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $team = $this->teamService->updateTeam($id, $data);
        return response()->json($team);
    }

    public function destroy($id)
    {
        $this->teamService->deleteTeam($id);
        return response()->json(null, 204);
    }
}
