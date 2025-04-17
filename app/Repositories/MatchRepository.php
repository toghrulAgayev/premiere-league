<?php

namespace App\Repositories;

use App\Models\Matches;
use Illuminate\Support\Collection as SupportCollection;

class MatchRepository
{
    private $matchModel;

    public function __construct(Matches $matchModel)
    {
        $this->matchModel = $matchModel;
    }

    public function getAll(): SupportCollection
    {
        return $this->matchModel->with(['homeTeam', 'awayTeam'])
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

    public function getById(int $id): ?Matches
    {
        return $this->matchModel->where('id', $id)->first();
    }

    public function create(array $data): Matches
    {
        return $this->matchModel->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $match = $this->getById($id);
        if($match)
        {
            return $match->update($data);
        }
        return false;
    }

    public function delete(int $id): bool
    {
        $match = $this->getById($id);
        if($match)
        {
            return $match->delete();
        }
        return false;
    }

    public function deleteAll(): void
    {
        $this->matchModel->truncate();
    }

    public function getMatchesByWeek($week): SupportCollection
    {
        return $this->matchModel->with(['homeTeam', 'awayTeam'])
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
