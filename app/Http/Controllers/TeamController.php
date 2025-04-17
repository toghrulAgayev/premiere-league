<?php

namespace App\Http\Controllers;

use App\Repositories\TeamRepository;
use App\Services\TeamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    private TeamRepository $teamRepository;

    public function __construct(TeamRepository $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    public function index(): JsonResponse
    {
        return response()->json($this->teamRepository->getAll());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->all();
        $team = $this->teamRepository->create($data);
        return response()->json($team, 201);
    }

    public function update(Request $request,int $id): JsonResponse
    {
        $data = $request->all();
        $team = $this->teamRepository->update($id, $data);
        return response()->json($team);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->teamRepository->delete($id);
        return response()->json(null, 204);
    }
}
