<?php

namespace Tests\Unit\Repositories;

use App\Models\Team;
use App\Repositories\TeamRepository;
use Mockery;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Mockery\MockInterface;
use Illuminate\Database\Eloquent\Builder;

class TeamRepositoryTest extends TestCase
{
    private MockInterface $teamModelMock;
    private TeamRepository $repository;
    private MockInterface|Builder $queryBuilderMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->teamModelMock = Mockery::mock(Team::class);
        $this->queryBuilderMock = Mockery::mock(Builder::class);
        
        $this->repository = new TeamRepository($this->teamModelMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_getAll_returns_ordered_teams()
    {
        $team1 = Mockery::mock(Team::class);
        $team1->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $team1->shouldReceive('getAttribute')->with('name')->andReturn('Arsenal');
        $team1->shouldReceive('getAttribute')->with('points')->andReturn(10);
        $team1->shouldReceive('getAttribute')->with('played')->andReturn(5);
        $team1->shouldReceive('getAttribute')->with('won')->andReturn(3);
        $team1->shouldReceive('getAttribute')->with('drawn')->andReturn(1);
        $team1->shouldReceive('getAttribute')->with('lost')->andReturn(1);
        $team1->shouldReceive('getAttribute')->with('goal_for')->andReturn(7);
        $team1->shouldReceive('getAttribute')->with('goal_against')->andReturn(5);
        $team1->shouldReceive('getAttribute')->with('goal_difference')->andReturn(2);
        
        $team2 = Mockery::mock(Team::class);
        $team2->shouldReceive('getAttribute')->with('id')->andReturn(2);
        $team2->shouldReceive('getAttribute')->with('name')->andReturn('Liverpool');
        $team2->shouldReceive('getAttribute')->with('points')->andReturn(20);
        $team2->shouldReceive('getAttribute')->with('played')->andReturn(5);
        $team2->shouldReceive('getAttribute')->with('won')->andReturn(5);
        $team2->shouldReceive('getAttribute')->with('drawn')->andReturn(0);
        $team2->shouldReceive('getAttribute')->with('lost')->andReturn(0);
        $team2->shouldReceive('getAttribute')->with('goal_for')->andReturn(12);
        $team2->shouldReceive('getAttribute')->with('goal_against')->andReturn(2);
        $team2->shouldReceive('getAttribute')->with('goal_difference')->andReturn(10);
        
        // Mock the array access behavior
        $team1->shouldReceive('offsetGet')->with('points')->andReturn(10);
        $team2->shouldReceive('offsetGet')->with('points')->andReturn(20);
        
        $teams = new EloquentCollection([$team2, $team1]); // Order by points desc
        
        $this->teamModelMock->shouldReceive('orderBy')
            ->with('points', 'desc')
            ->once()
            ->andReturn($this->queryBuilderMock);
        
        $this->queryBuilderMock->shouldReceive('get')
            ->once()
            ->andReturn($teams);
            
        $result = $this->repository->getAll();
        
        // Set up the result to use array offsets for testing
        // Use offsetGet directly instead of direct property access
        $this->assertEquals(20, $result[0]->offsetGet('points'));
        $this->assertEquals(10, $result[1]->offsetGet('points'));
    }

    public function test_create_team()
    {
        $teamData = ['name' => 'Manchester United', 'strength' => 85];
        
        $createdTeam = Mockery::mock(Team::class);
        $this->teamModelMock->shouldReceive('create')->with($teamData)->once()->andReturn($createdTeam);
        
        $result = $this->repository->create($teamData);
        
        $this->assertSame($createdTeam, $result);
    }

    public function test_delete_team()
    {
        $teamId = 1;
        $team = Mockery::mock(Team::class);
        
        $this->teamModelMock->shouldReceive('where')
            ->with('id', $teamId)
            ->once()
            ->andReturn($this->queryBuilderMock);
            
        $this->queryBuilderMock->shouldReceive('first')
            ->once()
            ->andReturn($team);
        
        $team->shouldReceive('delete')->once()->andReturn(true);
        
        $result = $this->repository->delete($teamId);
        
        $this->assertTrue($result);
    }
}
