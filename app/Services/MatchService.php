<?php

namespace App\Services;

use App\Repositories\MatchRepository;

class MatchService
{
    protected $matchRepository;

    public function __construct(MatchRepository $matchRepository)
    {
        $this->matchRepository = $matchRepository;
    }

    public function getAllMatches()
    {
        return $this->matchRepository->getAll();
    }

    public function createMatch(array $data)
    {
        return $this->matchRepository->create($data);
    }

    public function getLastWeekMatches()
    {
        $nextWeek = $this->getLastPlayedWeek();
        return $this->matchRepository->getMatchesByWeek($nextWeek);
    }

    protected function getLastPlayedWeek()
    {
        $lastPlayedMatch = $this->matchRepository->getAll()->sortByDesc('week')->first();
        $lastWeek = 1;
        if($lastPlayedMatch != null)
        {
            $lastWeek = $lastPlayedMatch->week;
        }
        return $lastWeek;
    }
}
