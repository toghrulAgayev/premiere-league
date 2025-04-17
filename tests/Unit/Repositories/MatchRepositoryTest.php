<?php

namespace Tests\Unit\Repositories;

use App\Models\Matches;
use App\Models\Team;
use App\Repositories\MatchRepository;
use Mockery;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Mockery\MockInterface;

class MatchRepositoryTest extends TestCase
{
    private MockInterface $matchesMock;
    private MatchRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->matchesMock = Mockery::mock(Matches::class);
        $this->repository = new MatchRepository($this->matchesMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_getAll_returns_all_matches_with_teams()
    {
        $homeTeam = Mockery::mock(Team::class);
        $homeTeam->shouldReceive('getAttribute')->with('name')->andReturn('Liverpool');
        
        $awayTeam = Mockery::mock(Team::class);
        $awayTeam->shouldReceive('getAttribute')->with('name')->andReturn('Manchester United');
        
        $match1 = Mockery::mock(Matches::class);
        $match1->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $match1->shouldReceive('getAttribute')->with('home_team_score')->andReturn(2);
        $match1->shouldReceive('getAttribute')->with('away_team_score')->andReturn(1);
        $match1->shouldReceive('getAttribute')->with('week')->andReturn(1);
        $match1->shouldReceive('getAttribute')->with('match_date')->andReturn('2024-01-15');
        $match1->shouldReceive('getAttribute')->with('homeTeam')->andReturn($homeTeam);
        $match1->shouldReceive('getAttribute')->with('awayTeam')->andReturn($awayTeam);
        
        $matches = new Collection([$match1]);
        
        $this->matchesMock->shouldReceive('with')->with(['homeTeam', 'awayTeam'])->once()->andReturnSelf();
        $this->matchesMock->shouldReceive('get')->once()->andReturn($matches);
        
        $result = $this->repository->getAll();
        
        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]->id);
        $this->assertEquals('Liverpool', $result[0]->home_team);
        $this->assertEquals('Manchester United', $result[0]->away_team);
    }

    public function test_create_match()
    {
        $matchData = [
            'home_team_id' => 1,
            'away_team_id' => 2,
            'home_team_score' => 3,
            'away_team_score' => 1,
            'week' => 1,
            'match_date' => '2024-01-15',
        ];
        
        $createdMatch = Mockery::mock(Matches::class);
        $this->matchesMock->shouldReceive('create')->with($matchData)->once()->andReturn($createdMatch);
        
        $result = $this->repository->create($matchData);
        
        $this->assertSame($createdMatch, $result);
    }

    public function test_update_match()
    {
        $matchId = 1;
        $updateData = ['home_team_score' => 4];
        
        $match = Mockery::mock(Matches::class);
        
        $this->matchesMock->shouldReceive('where')->with('id', $matchId)->once()->andReturnSelf();
        $this->matchesMock->shouldReceive('first')->once()->andReturn($match);
        $match->shouldReceive('update')->with($updateData)->once()->andReturn(true);
        
        $result = $this->repository->update($matchId, $updateData);
        
        $this->assertTrue($result);
    }

    public function test_delete_match()
    {
        $matchId = 1;
        $match = Mockery::mock(Matches::class);
        
        $this->matchesMock->shouldReceive('where')->with('id', $matchId)->once()->andReturnSelf();
        $this->matchesMock->shouldReceive('first')->once()->andReturn($match);
        $match->shouldReceive('delete')->once()->andReturn(true);
        
        $result = $this->repository->delete($matchId);
        
        $this->assertTrue($result);
    }
    
    public function test_getMatchesByWeek()
    {
        $week = 1;
        
        $homeTeam = Mockery::mock(Team::class);
        $homeTeam->shouldReceive('getAttribute')->with('name')->andReturn('Arsenal');
        
        $awayTeam = Mockery::mock(Team::class);
        $awayTeam->shouldReceive('getAttribute')->with('name')->andReturn('Chelsea');
        
        $match = Mockery::mock(Matches::class);
        $match->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $match->shouldReceive('getAttribute')->with('home_team_score')->andReturn(2);
        $match->shouldReceive('getAttribute')->with('away_team_score')->andReturn(1);
        $match->shouldReceive('getAttribute')->with('week')->andReturn($week);
        $match->shouldReceive('getAttribute')->with('match_date')->andReturn('2024-01-15');
        $match->shouldReceive('getAttribute')->with('homeTeam')->andReturn($homeTeam);
        $match->shouldReceive('getAttribute')->with('awayTeam')->andReturn($awayTeam);
        
        $matches = new Collection([$match]);
        
        $this->matchesMock->shouldReceive('with')->with(['homeTeam', 'awayTeam'])->once()->andReturnSelf();
        $this->matchesMock->shouldReceive('where')->with('week', $week)->once()->andReturnSelf();
        $this->matchesMock->shouldReceive('get')->once()->andReturn($matches);
        
        $result = $this->repository->getMatchesByWeek($week);
        
        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals('Arsenal', $result[0]['home_team']);
        $this->assertEquals('Chelsea', $result[0]['away_team']);
    }
}

