<?php

namespace App\Services;


use Illuminate\Support\Collection;

class FixtureGeneratorService
{

    public function generateFixtures($teams, $matchesPlayed): array
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

}
