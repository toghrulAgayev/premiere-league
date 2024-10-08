<?php

namespace App\Repositories;

use App\Models\Team;

class TeamRepository
{
    public function getAll()
    {
        return Team::orderBy('points','desc')->get();
    }

    public function getById($id)
    {
        return Team::findOrFail($id);
    }

    public function create(array $data)
    {
        return Team::create($data);
    }

    public function update($id, array $data)
    {
        $team = $this->getById($id);
        $team->update($data);
        return $team;
    }

    public function delete($id)
    {
        $team = $this->getById($id);
        $team->delete();
    }
}
