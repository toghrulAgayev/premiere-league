<?php

namespace App\Repositories;

use App\Models\Matches;

class MatchRepository
{
    public function getAll()
    {
        return Matches::with(['homeTeam', 'awayTeam'])
            ->get()
            ->map(function ($match) {
                return(object) [
                    'id' => $match->id,
                    'home_team' => $match->homeTeam->name,
                    'away_team' => $match->awayTeam->name,
                    'home_team_score' => $match->home_team_score,
                    'away_team_score' => $match->away_team_score,
                    'week' => $match->week,
                    'match_date' => $match->match_date,
                ];
            });
    }

    public function getById($id)
    {
        return Matches::findOrFail($id);
    }

    public function create(array $data)
    {
        return Matches::create($data);
    }

    public function update($id, array $data)
    {
        $match = $this->getById($id);
        $match->update($data);
        return $match;
    }

    public function delete($id)
    {
        $match = $this->getById($id);
        $match->delete();
    }

    public function deleteAll()
    {
        Matches::truncate();
    }

    public function getMatchesByWeek($week)
    {
        return Matches::with(['homeTeam', 'awayTeam'])
            ->where('week', $week)
            ->get()
            ->map(function ($match) {
                return [
                    'id' => $match->id,
                    'home_team' => $match->homeTeam->name,
                    'away_team' => $match->awayTeam->name,
                    'home_team_score' => $match->home_team_score,
                    'away_team_score' => $match->away_team_score,
                    'week' => $match->week,
                    'match_date' => $match->match_date,
                ];
            });
    }
}
