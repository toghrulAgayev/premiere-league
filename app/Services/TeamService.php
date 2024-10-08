<?php

namespace App\Services;

use App\Repositories\TeamRepository;

class TeamService
{
    protected $teamRepository;

    public function __construct(TeamRepository $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    public function getAllTeams()
    {
        return $this->teamRepository->getAll();
    }

    public function createTeam(array $data)
    {
        return $this->teamRepository->create($data);
    }

    public function updateTeam($id, array $data)
    {
        return  $this->teamRepository->update($id, $data);
    }

    public function deleteTeam($id)
    {
        $this->teamRepository->delete($id);
    }

}
