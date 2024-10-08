<?php

namespace App\Services;

use App\Repositories\TeamRepository;
use App\Repositories\MatchRepository;
use Carbon\Carbon;

class LeagueService
{
    protected $teamRepository;
    protected $matchRepository;

    public function __construct(TeamRepository $teamRepository, MatchRepository $matchRepository)
    {
        $this->teamRepository = $teamRepository;
        $this->matchRepository = $matchRepository;
    }

    public function playNextWeek()
    {
        $teams = $this->teamRepository->getAll();
        $matchesPlayed = $this->matchRepository->getAll();
        $fixtures = $this->generateFixtures($teams, $matchesPlayed);
        $lastPlayedMatch = $this->matchRepository->getAll()->sortByDesc('week')->first();
        $currentWeek = 1;
        if($lastPlayedMatch!= null && $lastPlayedMatch->week ==  count($teams)- 1)
        {
            return 'No more games to simulate';
        }

        if($lastPlayedMatch != null)
        {
            $currentWeek = $lastPlayedMatch->week + 1;
        }


        foreach ($fixtures as $week => $weekFixtures) {
            foreach ($weekFixtures as $fixture) {
                $this->simulateMatch($fixture['home'], $fixture['away'],$currentWeek);
            }
            break;
        }
        return 'Next week simulated successfully';
    }

    public function playAllWeeks()
    {
        $teams = $this->teamRepository->getAll();
        $totalWeeks = count($teams) - 1;

        for ($week = 1; $week <= $totalWeeks; $week++) {
            $this->playNextWeek();
        }
    }

    protected function generateFixtures($teams, $matchesPlayed)
    {
        $teamIds = $teams->pluck('id')->toArray();
        $teamCount = count($teamIds);
        $totalWeeks = $teamCount - 1;

        $weekFixtures = array_fill(1, $totalWeeks, []);
        $teamSchedule = array_fill(1, $totalWeeks, []);

        for ($i = 0; $i < count($teamIds); $i++) {
            for ($j = $i + 1; $j < count($teamIds); $j++) {
                if (!$matchesPlayed->where('home_team_id', $teamIds[$i])
                    ->where('away_team_id', $teamIds[$j])
                    ->first()) {
                    for ($week = 1; $week <= $totalWeeks; $week++) {
                        if (!in_array($teamIds[$i], $teamSchedule[$week]) && !in_array($teamIds[$j], $teamSchedule[$week])) {
                            $weekFixtures[$week][] = [
                                'home' => $teamIds[$i],
                                'away' => $teamIds[$j]
                            ];

                            $teamSchedule[$week][] = $teamIds[$i];
                            $teamSchedule[$week][] = $teamIds[$j];

                            break;
                        }
                    }
                }
            }
        }

        return $weekFixtures;
    }


    protected function simulateMatch($homeTeamId, $awayTeamId,$week)
    {
        $homeTeam = $this->teamRepository->getById($homeTeamId);
        $awayTeam = $this->teamRepository->getById($awayTeamId);

        $homeScore = $this->generateScore($homeTeam->strength);
        $awayScore = $this->generateScore($awayTeam->strength);

        $matchData = [
            'home_team_id' => $homeTeamId,
            'away_team_id' => $awayTeamId,
            'home_team_score' => $homeScore,
            'away_team_score' => $awayScore,
            'week' => $week,
            'match_date' => Carbon::now()
        ];
        $this->matchRepository->create($matchData);


        $this->updateTeamStats($homeTeam, $awayTeam, $homeScore, $awayScore);
    }

    protected function generateScore($teamStrength)
    {
        $maxScore = 6;

        $adjustedScore = rand(0, $maxScore);

        if ($teamStrength >= 80) {
            $adjustedScore = rand(2, $maxScore);
        } elseif ($teamStrength < 60) {
            $adjustedScore = rand(0, 2);
        }

        return $adjustedScore;
    }


    protected function updateTeamStats($homeTeam, $awayTeam, $homeScore, $awayScore)
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

    public function resetLeague()
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

    public function updateMatchResult($matchId, $newHomeScore, $newAwayScore)
    {

        $match = $this->matchRepository->getById($matchId);
        $homeTeam = $this->teamRepository->getById($match->home_team_id);
        $awayTeam = $this->teamRepository->getById($match->away_team_id);

        $this->revertTeamStats($homeTeam, $awayTeam, $match->home_team_score, $match->away_team_score);

        $match->home_team_score = $newHomeScore;
        $match->away_team_score = $newAwayScore;
        $match->save();

        $this->updateTeamStats($homeTeam, $awayTeam, $newHomeScore, $newAwayScore);
    }

    protected function revertTeamStats($homeTeam, $awayTeam, $homeScore, $awayScore)
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

        $homeTeam->save();
        $awayTeam->save();
    }

    protected function calculatePoints($teamScore, $opponentScore)
    {
        if ($teamScore > $opponentScore) {
            return 3;
        } elseif ($teamScore == $opponentScore) {
            return 1;
        }
        return 0;
    }

    public function calculateWinProbabilities()
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
