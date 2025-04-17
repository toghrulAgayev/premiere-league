<?php

namespace Tests\Unit\Services;

use App\Models\Team;
use App\Repositories\MatchRepository;
use App\Repositories\TeamRepository;
use App\Services\LeagueService;
use Mockery;
use Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Mockery\MockInterface;

class LeagueServiceTest extends TestCase
{
    private MockInterface $teamRepositoryMock;
    private MockInterface $matchRepositoryMock;
    private LeagueService $leagueService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->teamRepositoryMock = Mockery::mock(TeamRepository::class);
        $this->matchRepositoryMock = Mockery::mock(MatchRepository::class);
        $this->leagueService = new LeagueService(
            $this->teamRepositoryMock, 
            $this->matchRepositoryMock
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createEloquentCollectionMock(array $items): MockInterface
    {
        $collection = Mockery::mock(EloquentCollection::class);
        $collection->shouldReceive('all')->andReturn($items);
        $collection->shouldReceive('toArray')->andReturn($items);
        $collection->shouldReceive('sum')->with('points')->andReturn(collect($items)->sum('points'));
        $collection->shouldReceive('map')->andReturnUsing(function ($callback) use ($items) {
            return collect($items)->map($callback);
        });
        $collection->shouldReceive('first')->andReturn(collect($items)->first());
        $collection->shouldReceive('each')->andReturnUsing(function ($callback) use ($items, $collection) {
             collect($items)->each($callback);
             return $collection;
        });
        
        $collection->shouldReceive('getIterator')->andReturnUsing(function() use ($items) {
            return new \ArrayIterator($items);
        });

        return $collection;
    }

    public function test_resetLeague_resets_all_team_stats_and_deletes_matches()
    {
        $team1 = (object)[
            'id' => 1,
            'name' => 'Team A',
            'points' => 10,
            'goals_for' => 15,
            'goals_against' => 8
        ];
        
        $team2 = (object)[
            'id' => 2,
            'name' => 'Team B',
            'points' => 8,
            'goals_for' => 12,
            'goals_against' => 10
        ];
        
        $teams = [$team1, $team2];
        $teamsCollectionMock = $this->createEloquentCollectionMock($teams);
        
        $this->teamRepositoryMock->shouldReceive('getAll')
            ->once()
            ->andReturn($teamsCollectionMock);
        
        $this->teamRepositoryMock->shouldReceive('update')
            ->with(1, Mockery::subset([
                'points' => 0,
                'goals_for' => 0,
                'goals_against' => 0
            ]))
            ->once()
            ->andReturn(true);
            
        $this->teamRepositoryMock->shouldReceive('update')
            ->with(2, Mockery::subset([
                'points' => 0,
                'goals_for' => 0,
                'goals_against' => 0
            ]))
            ->once()
            ->andReturn(true);
            
        $this->matchRepositoryMock->shouldReceive('deleteAll')
            ->once();
        
        $this->leagueService->resetLeague();
        
        $this->assertTrue(true, "League reset completed successfully");
    }

    public function test_calculateWinProbabilities_returns_probabilities_when_points_exist()
    {
        $team1 = (object)[
            'id' => 1,
            'name' => 'Team A',
            'points' => 30
        ];
        
        $team2 = (object)[
            'id' => 2,
            'name' => 'Team B',
            'points' => 20
        ];
        
        $teams = [$team1, $team2];
        $teamsCollectionMock = $this->createEloquentCollectionMock($teams);
        
        $this->teamRepositoryMock->shouldReceive('getAll')
            ->once()
            ->andReturn($teamsCollectionMock);
        
        $result = $this->leagueService->calculateWinProbabilities();
        
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
        
        $this->assertEquals('Team A', $result[0]['name']);
        $this->assertEquals(60, $result[0]['win_probability']);
        
        $this->assertEquals('Team B', $result[1]['name']);
        $this->assertEquals(40, $result[1]['win_probability']);
    }
    
    public function test_calculateWinProbabilities_returns_message_when_no_points()
    {
        $team1 = (object)[
            'id' => 1,
            'name' => 'Team A',
            'points' => 0
        ];
        
        $team2 = (object)[
            'id' => 2,
            'name' => 'Team B',
            'points' => 0
        ];
        
        $teams = [$team1, $team2];
        $teamsCollectionMock = $this->createEloquentCollectionMock($teams);
        
        $this->teamRepositoryMock->shouldReceive('getAll')
            ->once()
            ->andReturn($teamsCollectionMock);
        
        $result = $this->leagueService->calculateWinProbabilities();
        
        $this->assertInstanceOf(Collection::class, $result);
        
        $resultArray = $result->toArray();
        $this->assertIsArray($resultArray);
        $this->assertArrayHasKey('message', $resultArray);
        $this->assertStringContainsString('No points', $resultArray['message']);
    }
} 