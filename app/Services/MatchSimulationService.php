<?php

namespace App\Services;

use App\Models\Team;
use App\Repositories\MatchRepository;
use App\Repositories\TeamRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MatchSimulationService
{

    private MatchRepository $matchRepository;
    private TeamRepository $teamRepository;
    private FixtureGeneratorService $fixtureGeneratorService;
    private  TeamService $teamService;


    public function __construct(MatchRepository $matchRepository, TeamRepository $teamRepository, FixtureGeneratorService $fixtureGeneratorService, TeamService $teamService)
    {
        $this->matchRepository = $matchRepository;
        $this->teamRepository = $teamRepository;
        $this->fixtureGeneratorService = $fixtureGeneratorService;
        $this->teamService = $teamService;
    }


    public function playNextWeek(): string
    {
        $teams = $this->teamRepository->getAll();
        $matchesPlayed = $this->matchRepository->getAll();
        $fixtures = $this->fixtureGeneratorService->generateFixtures($teams, $matchesPlayed);
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

    public function playAllWeeks(): void
    {
        $teams = $this->teamRepository->getAll();
        $totalWeeks = count($teams) - 1;

        for ($week = 1; $week <= $totalWeeks; $week++) {
            $this->playNextWeek();
        }
    }

    protected function simulateMatch(int $homeTeamId,int $awayTeamId,int $week): void
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


        $this->teamService->updateTeamStats($homeTeam, $awayTeam, $homeScore, $awayScore);
    }

    protected function generateScore(int $teamStrength): int
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

    public function updateMatchResult(int $matchId,int $newHomeScore,int $newAwayScore): void
    {

        $match = $this->matchRepository->getById($matchId);
        $homeTeam = $this->teamRepository->getById($match->home_team_id);
        $awayTeam = $this->teamRepository->getById($match->away_team_id);

        $this->teamService->revertTeamStats($homeTeam, $awayTeam, $match->home_team_score, $match->away_team_score);

        $match->home_team_score = $newHomeScore;
        $match->away_team_score = $newAwayScore;
        $match->save();

        $this->teamService->updateTeamStats($homeTeam, $awayTeam, $newHomeScore, $newAwayScore);
    }


}
