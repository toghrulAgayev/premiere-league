<?php

namespace App\Repositories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Collection;

class TeamRepository
{
    private Team $teamModel;
    public function __construct(Team $teamModel)
    {
        $this->teamModel = $teamModel;
    }
    public function getAll() : Collection
    {
        return $this->teamModel->orderBy('points', 'desc')->get();
    }

    public function getById(int $id): ?Team
    {
        return $this->teamModel->where('id', $id)->first();
    }

    public function create(array $data): Team
    {
        return $this->teamModel->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $team = $this->getById($id);
        if($team)
        {
            return $team->update($data);
        }
        return false;
    }

    public function delete(int $id): bool
    {
        $team = $this->getById($id);
        if($team)
        {
            return $team->delete();
        }
        return false;
    }
}
