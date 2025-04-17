<?php

namespace Tests\Unit\Services;

use App\Repositories\MatchRepository;
use App\Services\MatchService;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class MatchServiceTest extends TestCase
{
    private $matchRepositoryMock;
    private MatchService $matchService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->matchRepositoryMock = Mockery::mock(MatchRepository::class);
        $this->matchService = new MatchService($this->matchRepositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_getLastWeekMatches_returns_matches_for_last_week()
    {
        $lastWeekMatches = new Collection([
            (object)[
                'id' => 1,
                'home_team' => 'Liverpool',
                'away_team' => 'Manchester United',
                'home_team_score' => 2,
                'away_team_score' => 1,
                'week' => 3
            ]
        ]);

        $allMatches = new Collection([
            (object)[
                'id' => 1,
                'week' => 3
            ],
            (object)[
                'id' => 2,
                'week' => 2
            ]
        ]);

        $this->matchRepositoryMock->shouldReceive('getAll')
            ->once()
            ->andReturn($allMatches);

        $this->matchRepositoryMock->shouldReceive('getMatchesByWeek')
            ->with(3)
            ->once()
            ->andReturn($lastWeekMatches);

        $result = $this->matchService->getLastWeekMatches();

        $this->assertCount(1, $result);
        $this->assertEquals('Liverpool', $result[0]->home_team);
        $this->assertEquals('Manchester United', $result[0]->away_team);
    }

    public function test_getLastWeekMatches_returns_week_one_when_no_matches()
    {
        $emptyCollection = new Collection();
        $weekOneMatches = new Collection([
            (object)[
                'id' => 1,
                'home_team' => 'Arsenal',
                'away_team' => 'Chelsea',
                'week' => 1
            ]
        ]);

        $this->matchRepositoryMock->shouldReceive('getAll')
            ->once()
            ->andReturn($emptyCollection);

        $this->matchRepositoryMock->shouldReceive('getMatchesByWeek')
            ->with(1)
            ->once()
            ->andReturn($weekOneMatches);

        $result = $this->matchService->getLastWeekMatches();

        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]->week);
    }
}