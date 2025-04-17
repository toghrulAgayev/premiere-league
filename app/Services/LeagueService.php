<?php

namespace App\Services;

use App\Repositories\TeamRepository;
use App\Repositories\MatchRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LeagueService
{
    protected TeamRepository $teamRepository;
    protected MatchRepository $matchRepository;

    public function __construct(TeamRepository $teamRepository, MatchRepository $matchRepository)
    {
        $this->teamRepository = $teamRepository;
        $this->matchRepository = $matchRepository;
    }

    public function resetLeague(): void
    {
        $teams = $this->teamRepository->getAll();
        foreach ($teams as $team) {
            $this->teamRepository->update($team->id, [
                'points' => 0,
                'goals_for' => 0,
                'goals_against' => 0,
                'goal_difference' => 0,
                'matches_played' => 0,
                'matches_won' => 0,
                'matches_drawn' => 0,
                'matches_lost' => 0
            ]);
        }

        $this->matchRepository->deleteAll();
    }

    public function calculateWinProbabilities() : Collection
    {
        $teams = $this->teamRepository->getAll();
        $totalPoints = $teams->sum('points');
        if ($totalPoints === 0) {
            return collect([
                'message' => 'No points awarded yet, unable to calculate probabilities.'
            ]);
        }
        $teamsWithProbabilities = $teams->map(function ($team) use ($totalPoints) {
            $team->win_probability = round(($team->points / $totalPoints) * 100, 2);
            return $team;
        });
        return $teamsWithProbabilities->map(function ($team){
            return [
                'name' => $team->name,
                'win_probability' => $team->win_probability
            ];
        });
    }
}
