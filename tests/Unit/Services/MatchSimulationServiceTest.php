<?php

namespace Tests\Unit\Services;

use App\Models\Matches;
use App\Models\Team;
use App\Repositories\MatchRepository;
use App\Repositories\TeamRepository;
use App\Services\FixtureGeneratorService;
use App\Services\MatchSimulationService;
use App\Services\TeamService;
use Carbon\Carbon;
use Mockery;
use Tests\TestCase;
use Illuminate\Support\Collection;
use Mockery\MockInterface;

class MatchSimulationServiceTest extends TestCase
{
    private MockInterface $matchRepositoryMock;
    private MockInterface $teamRepositoryMock;
    private MockInterface $fixtureGeneratorServiceMock;
    private MockInterface $teamServiceMock;
    private MatchSimulationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->matchRepositoryMock = Mockery::mock(MatchRepository::class);
        $this->teamRepositoryMock = Mockery::mock(TeamRepository::class);
        $this->fixtureGeneratorServiceMock = Mockery::mock(FixtureGeneratorService::class);
        $this->teamServiceMock = Mockery::mock(TeamService::class);
        
        $this->service = new MatchSimulationService(
            $this->matchRepositoryMock, 
            $this->teamRepositoryMock,
            $this->fixtureGeneratorServiceMock,
            $this->teamServiceMock
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createMockTeam(int $id, string $name, int $strength): MockInterface
    {
        $team = Mockery::mock(Team::class)->shouldIgnoreMissing();
        $team->id = $id;
        $team->name = $name;
        $team->strength = $strength;
        $team->shouldReceive('getAttribute')->with('strength')->andReturn($strength);
        return $team;
    }

    public function test_playNextWeek_when_no_matches_played_yet()
    {
        $team1 = $this->createMockTeam(1, 'Team A', 80);
        $team2 = $this->createMockTeam(2, 'Team B', 75);
        $teams = collect([$team1, $team2]);
        
        $fixtures = [
            1 => [
                ['home' => 1, 'away' => 2]
            ]
        ];
        
        $teamsCollection = Mockery::mock(\Illuminate\Database\Eloquent\Collection::class);
        $teamsCollection->shouldReceive('pluck')->andReturn(collect([1, 2]));
        $teamsCollection->shouldReceive('count')->andReturn(2);
        
        $this->teamRepositoryMock->shouldReceive('getAll')
            ->once()
            ->andReturn($teamsCollection);
            
        $this->matchRepositoryMock->shouldReceive('getAll')
            ->twice()
            ->andReturn(collect([]));
            
        $this->fixtureGeneratorServiceMock->shouldReceive('generateFixtures')
            ->with(Mockery::type('Illuminate\Database\Eloquent\Collection'), Mockery::type('Illuminate\Support\Collection'))
            ->once()
            ->andReturn($fixtures);
            
        $this->teamRepositoryMock->shouldReceive('getById')
            ->with(1)
            ->once()
            ->andReturn($team1);
            
        $this->teamRepositoryMock->shouldReceive('getById')
            ->with(2)
            ->once()
            ->andReturn($team2);
            
        $this->matchRepositoryMock->shouldReceive('create')
            ->with(Mockery::on(function ($arg) {
                return is_array($arg) &&
                       isset($arg['home_team_id']) && $arg['home_team_id'] === 1 &&
                       isset($arg['away_team_id']) && $arg['away_team_id'] === 2 &&
                       isset($arg['home_team_score']) && is_int($arg['home_team_score']) &&
                       isset($arg['away_team_score']) && is_int($arg['away_team_score']) &&
                       isset($arg['week']) && $arg['week'] === 1 &&
                       isset($arg['match_date']);
            }))
            ->once()
            ->andReturn(Mockery::mock(Matches::class));
            
        $this->teamServiceMock->shouldReceive('updateTeamStats')
            ->with($team1, $team2, Mockery::type('int'), Mockery::type('int'))
            ->once();
        
        $result = $this->service->playNextWeek();
        
        $this->assertEquals('Next week simulated successfully', $result);
    }

    public function test_playNextWeek_when_no_more_games()
    {
        $team1 = $this->createMockTeam(1, 'Team A', 80);
        $team2 = $this->createMockTeam(2, 'Team B', 75);
        $team3 = $this->createMockTeam(3, 'Team C', 70);
        
        $teamsCollection = Mockery::mock(\Illuminate\Database\Eloquent\Collection::class);
        $teamsCollection->shouldReceive('count')->andReturn(3);
        
        $lastMatch = (object)[
            'week' => 2
        ];
        $matchesPlayedCollection = collect([$lastMatch]);
        
        $this->teamRepositoryMock->shouldReceive('getAll')
            ->once()
            ->andReturn($teamsCollection);
            
        $this->matchRepositoryMock->shouldReceive('getAll')
            ->once()
            ->andReturn($matchesPlayedCollection);
        
        $this->matchRepositoryMock->shouldReceive('getAll')
            ->once()
            ->andReturn(collect([(object)[
                'week' => 2
            ]]));
        
        $fixtures = [];
        $this->fixtureGeneratorServiceMock->shouldReceive('generateFixtures')
            ->andReturn($fixtures);
        
        $result = $this->service->playNextWeek();
        
        $this->assertEquals('No more games to simulate', $result);
    }
    
    public function test_updateMatchResult_updates_score_and_team_stats()
    {
        $matchId = 1;
        $newHomeScore = 3;
        $newAwayScore = 1;
        
        $mockMatch = Mockery::mock(Matches::class);
        $mockMatch->shouldReceive('getAttribute')->with('home_team_id')->andReturn(1);
        $mockMatch->shouldReceive('getAttribute')->with('away_team_id')->andReturn(2);
        $mockMatch->shouldReceive('getAttribute')->with('home_team_score')->andReturn(2);
        $mockMatch->shouldReceive('getAttribute')->with('away_team_score')->andReturn(2);
        
        $mockMatch->shouldReceive('__get')->with('home_team_id')->andReturn(1);
        $mockMatch->shouldReceive('__get')->with('away_team_id')->andReturn(2);
        $mockMatch->shouldReceive('__get')->with('home_team_score')->andReturn(2);
        $mockMatch->shouldReceive('__get')->with('away_team_score')->andReturn(2);
        
        $mockMatch->shouldReceive('setAttribute')->with('home_team_score', $newHomeScore)->once();
        $mockMatch->shouldReceive('setAttribute')->with('away_team_score', $newAwayScore)->once();
        
        $mockMatch->shouldReceive('__set')->with('home_team_score', $newHomeScore);
        $mockMatch->shouldReceive('__set')->with('away_team_score', $newAwayScore);
        
        $mockMatch->shouldReceive('save')->andReturn(true);
        
        $homeTeam = $this->createMockTeam(1, 'Home Team', 80);
        $awayTeam = $this->createMockTeam(2, 'Away Team', 75);
        
        $this->matchRepositoryMock->shouldReceive('getById')
            ->with($matchId)
            ->once()
            ->andReturn($mockMatch);
            
        $this->teamRepositoryMock->shouldReceive('getById')
            ->with(1)
            ->once()
            ->andReturn($homeTeam);
            
        $this->teamRepositoryMock->shouldReceive('getById')
            ->with(2)
            ->once()
            ->andReturn($awayTeam);
            
        $this->teamServiceMock->shouldReceive('revertTeamStats')
            ->with($homeTeam, $awayTeam, 2, 2)
            ->once();
            
        $this->teamServiceMock->shouldReceive('updateTeamStats')
            ->with($homeTeam, $awayTeam, $newHomeScore, $newAwayScore)
            ->once();
        
        $this->service->updateMatchResult($matchId, $newHomeScore, $newAwayScore);
        
        $this->assertTrue(true, "Match updated successfully");
    }
}