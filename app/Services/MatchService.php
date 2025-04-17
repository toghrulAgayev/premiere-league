<?php

namespace App\Services;

use App\Repositories\MatchRepository;
use Illuminate\Support\Collection;

class MatchService
{
    protected MatchRepository $matchRepository;

    public function __construct(MatchRepository $matchRepository)
    {
        $this->matchRepository = $matchRepository;
    }

    public function getLastWeekMatches(): Collection
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
