<?php

namespace App\Services;

use App\Models\Team;
use App\Repositories\TeamRepository;

class TeamService
{
    private TeamRepository $teamRepository;

    public function __construct(TeamRepository $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    public function updateTeamStats(Team $homeTeam,Team $awayTeam,int $homeScore,int $awayScore): void
    {

        $homeTeam->goals_for += $homeScore;
        $homeTeam->goals_against += $awayScore;
        $awayTeam->goals_for += $awayScore;
        $awayTeam->goals_against += $homeScore;


        $homeTeam->matches_played++;
        $awayTeam->matches_played++;


        if ($homeScore > $awayScore) {
            $homeTeam->matches_won++;
            $homeTeam->points += 3;
            $awayTeam->matches_lost++;
        } elseif ($homeScore < $awayScore) {
            $awayTeam->matches_won++;
            $awayTeam->points += 3;
            $homeTeam->matches_lost++;
        } else {
            $homeTeam->matches_drawn++;
            $awayTeam->matches_drawn++;
            $homeTeam->points++;
            $awayTeam->points++;
        }

        $homeTeam->goal_difference = $homeTeam->goals_for - $homeTeam->goals_against;
        $awayTeam->goal_difference = $awayTeam->goals_for - $awayTeam->goals_against;

        $this->teamRepository->update($homeTeam->id, $homeTeam->toArray());
        $this->teamRepository->update($awayTeam->id, $awayTeam->toArray());
    }

    public function revertTeamStats(Team $homeTeam,Team $awayTeam,int $homeScore,int $awayScore): void
    {
        $homeTeam->goals_for -= $homeScore;
        $homeTeam->goals_against -= $awayScore;
        $awayTeam->goals_for -= $awayScore;
        $awayTeam->goals_against -= $homeScore;

        $homeTeam->goal_difference = $homeTeam->goals_for - $homeTeam->goals_against;
        $awayTeam->goal_difference = $awayTeam->goals_for - $awayTeam->goals_against;

        if ($homeScore > $awayScore) {
            $homeTeam->matches_won--;
            $awayTeam->matches_lost--;
        } elseif ($homeScore < $awayScore) {
            $awayTeam->matches_won--;
            $homeTeam->matches_lost--;
        } else {
            $homeTeam->matches_drawn--;
            $awayTeam->matches_drawn--;
        }
        $homeTeam->matches_played--;
        $awayTeam->matches_played--;
        $homeTeam->points -= $this->calculatePoints($homeScore, $awayScore);
        $awayTeam->points -= $this->calculatePoints($awayScore, $homeScore);

        $this->teamRepository->update($homeTeam->id, $homeTeam->toArray());
        $this->teamRepository->update($awayTeam->id, $awayTeam->toArray());
    }

    protected function calculatePoints(int $teamScore,int $opponentScore): int
    {
        if ($teamScore > $opponentScore) {
            return 3;
        } elseif ($teamScore == $opponentScore) {
            return 1;
        }
        return 0;
    }

}
