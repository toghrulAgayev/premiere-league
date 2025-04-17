<?php

namespace Tests\Unit\Services;

use App\Models\Team;
use App\Repositories\TeamRepository;
use App\Services\TeamService;
use Mockery;
use Tests\TestCase;

class TeamServiceTest extends TestCase
{
    private $teamRepositoryMock;
    private TeamService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->teamRepositoryMock = Mockery::mock(TeamRepository::class);
        $this->service = new TeamService($this->teamRepositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Helper method to create a mock Team object.
     */
    private function createTeam(int $id, string $name, int $played, int $won, int $drawn, int $lost, int $gf, int $ga, int $points): Team
    {
        $team = Mockery::mock(Team::class)->makePartial();
        $team->id = $id;
        $team->name = $name;
        $team->matches_played = $played;
        $team->matches_won = $won;
        $team->matches_drawn = $drawn;
        $team->matches_lost = $lost;
        $team->goals_for = $gf;
        $team->goals_against = $ga;
        $team->points = $points;
        $team->goal_difference = $gf - $ga;
        return $team;
    }

    public function test_updateTeamStats_when_home_team_wins()
    {
        // Create mock teams
        $homeTeam = $this->createTeam(1, 'Home Team', 3, 1, 1, 1, 5, 3, 3);
        $awayTeam = $this->createTeam(2, 'Away Team', 4, 2, 0, 2, 7, 6, 6);
        
        // Test scores
        $homeScore = 3;
        $awayScore = 1;
        
        // Setup repository expectations
        $this->teamRepositoryMock->shouldReceive('update')
            ->with(1, Mockery::on(function ($data) {
                return $data['matches_played'] === 4 &&
                       $data['matches_won'] === 2 &&
                       $data['matches_drawn'] === 1 &&
                       $data['matches_lost'] === 1 &&
                       $data['points'] === 6 &&
                       $data['goals_for'] === 8 &&
                       $data['goals_against'] === 4 &&
                       $data['goal_difference'] === 4;
            }))
            ->once();
        $this->teamRepositoryMock->shouldReceive('update')
             ->with(2, Mockery::on(function ($data) {
                 return $data['matches_played'] === 5 &&
                        $data['matches_won'] === 2 &&
                        $data['matches_drawn'] === 0 &&
                        $data['matches_lost'] === 3 &&
                        $data['points'] === 6 && // Points don't change for loser
                        $data['goals_for'] === 8 &&
                        $data['goals_against'] === 9 &&
                        $data['goal_difference'] === -1;
             }))
            ->once();
        
        // Call the method
        $this->service->updateTeamStats($homeTeam, $awayTeam, $homeScore, $awayScore);
        
        // Assertions are implicitly covered by the Mockery expectations above
        // If direct state assertion is needed (e.g., if service modifies objects before repo call):
        // $this->assertEquals(4, $homeTeam->matches_played);
        // ... etc ...
        $this->assertTrue(true, "Method executed successfully");
    }
    
    public function test_updateTeamStats_when_away_team_wins()
    {
        // Create mock teams
        $homeTeam = $this->createTeam(1, 'Home Team', 3, 1, 1, 1, 5, 3, 3);
        $awayTeam = $this->createTeam(2, 'Away Team', 4, 2, 0, 2, 7, 6, 6);
        
        // Test scores
        $homeScore = 1;
        $awayScore = 3;
        
        // Setup repository expectations
        $this->teamRepositoryMock->shouldReceive('update')
            ->with(1, Mockery::on(function ($data) {
                return $data['matches_played'] === 4 &&
                       $data['matches_won'] === 1 &&
                       $data['matches_drawn'] === 1 &&
                       $data['matches_lost'] === 2 &&
                       $data['points'] === 3 && // Points don't change for loser
                       $data['goals_for'] === 6 &&
                       $data['goals_against'] === 6 &&
                       $data['goal_difference'] === 0;
            }))
            ->once();
        $this->teamRepositoryMock->shouldReceive('update')
             ->with(2, Mockery::on(function ($data) {
                 return $data['matches_played'] === 5 &&
                        $data['matches_won'] === 3 &&
                        $data['matches_drawn'] === 0 &&
                        $data['matches_lost'] === 2 &&
                        $data['points'] === 9 && // 6 + 3 points
                        $data['goals_for'] === 10 &&
                        $data['goals_against'] === 7 &&
                        $data['goal_difference'] === 3;
             }))
            ->once();
        
        // Call the method
        $this->service->updateTeamStats($homeTeam, $awayTeam, $homeScore, $awayScore);
        
        // Assertions implicitly covered by Mockery expectations
        $this->assertTrue(true, "Method executed successfully");
    }
    
    public function test_updateTeamStats_when_draw()
    {
        // Create mock teams
        $homeTeam = $this->createTeam(1, 'Home Team', 3, 1, 1, 1, 5, 3, 3);
        $awayTeam = $this->createTeam(2, 'Away Team', 4, 2, 0, 2, 7, 6, 6);
        
        // Test scores
        $homeScore = 2;
        $awayScore = 2;
        
        // Setup repository expectations
        $this->teamRepositoryMock->shouldReceive('update')
            ->with(1, Mockery::on(function ($data) {
                return $data['matches_played'] === 4 &&
                       $data['matches_won'] === 1 &&
                       $data['matches_drawn'] === 2 &&
                       $data['matches_lost'] === 1 &&
                       $data['points'] === 4 && // 3 + 1 point
                       $data['goals_for'] === 7 &&
                       $data['goals_against'] === 5 &&
                       $data['goal_difference'] === 2;
            }))
            ->once();
        $this->teamRepositoryMock->shouldReceive('update')
             ->with(2, Mockery::on(function ($data) {
                 return $data['matches_played'] === 5 &&
                        $data['matches_won'] === 2 &&
                        $data['matches_drawn'] === 1 &&
                        $data['matches_lost'] === 2 &&
                        $data['points'] === 7 && // 6 + 1 point
                        $data['goals_for'] === 9 &&
                        $data['goals_against'] === 8 &&
                        $data['goal_difference'] === 1;
             }))
            ->once();
        
        // Call the method
        $this->service->updateTeamStats($homeTeam, $awayTeam, $homeScore, $awayScore);
        
        // Assertions implicitly covered by Mockery expectations
        $this->assertTrue(true, "Method executed successfully");
    }
    
    public function test_revertTeamStats_when_home_team_won()
    {
        // Create mock teams
        $homeTeam = $this->createTeam(1, 'Home Team', 10, 3, 1, 6, 15, 5, 10);
        $awayTeam = $this->createTeam(2, 'Away Team', 10, 2, 4, 4, 12, 8, 10);
        
        // Match score to revert
        $homeScore = 3;
        $awayScore = 1;
        
        // Setup repository update expectations
        $this->teamRepositoryMock->shouldReceive('update')
            ->with(Mockery::any(), Mockery::any())
            ->times(2)
            ->andReturn(true);
            
        // Call the service method
        $this->service->revertTeamStats($homeTeam, $awayTeam, $homeScore, $awayScore);
        
        // Assert stats after the service call
        $this->assertEquals(9, $homeTeam->matches_played);
        $this->assertEquals(2, $homeTeam->matches_won);
        $this->assertEquals(1, $homeTeam->matches_drawn);
        $this->assertEquals(6, $homeTeam->matches_lost);
        $this->assertEquals(7, $homeTeam->points);
        $this->assertEquals(12, $homeTeam->goals_for);
        $this->assertEquals(4, $homeTeam->goals_against);
        $this->assertEquals(8, $homeTeam->goal_difference);
        
        $this->assertEquals(9, $awayTeam->matches_played);
        $this->assertEquals(2, $awayTeam->matches_won);
        $this->assertEquals(4, $awayTeam->matches_drawn);
        $this->assertEquals(3, $awayTeam->matches_lost);
        $this->assertEquals(10, $awayTeam->points);
        $this->assertEquals(11, $awayTeam->goals_for);
        $this->assertEquals(5, $awayTeam->goals_against);
        $this->assertEquals(6, $awayTeam->goal_difference);
    }

    public function test_revertTeamStats_when_away_team_won()
    {
        // Create mock teams
        $homeTeam = $this->createTeam(1, 'Home Team', 10, 3, 1, 6, 15, 5, 10);
        $awayTeam = $this->createTeam(2, 'Away Team', 10, 2, 4, 4, 12, 8, 10);
        
        // Match score to revert
        $homeScore = 1;
        $awayScore = 3;
        
        // Setup repository update expectations
        $this->teamRepositoryMock->shouldReceive('update')
            ->with(Mockery::any(), Mockery::any())
            ->times(2)
            ->andReturn(true);
            
        // Call the service method
        $this->service->revertTeamStats($homeTeam, $awayTeam, $homeScore, $awayScore);
        
        // Assert home team stats after the service call
        $this->assertEquals(9, $homeTeam->matches_played);
        $this->assertEquals(3, $homeTeam->matches_won);
        $this->assertEquals(1, $homeTeam->matches_drawn);
        $this->assertEquals(5, $homeTeam->matches_lost);
        $this->assertEquals(10, $homeTeam->points);
        $this->assertEquals(14, $homeTeam->goals_for);
        $this->assertEquals(2, $homeTeam->goals_against);
        $this->assertEquals(12, $homeTeam->goal_difference);
        
        // Assert away team stats
        $this->assertEquals(9, $awayTeam->matches_played);
        $this->assertEquals(1, $awayTeam->matches_won);
        $this->assertEquals(4, $awayTeam->matches_drawn);
        $this->assertEquals(4, $awayTeam->matches_lost); 
        $this->assertEquals(7, $awayTeam->points);
        $this->assertEquals(9, $awayTeam->goals_for);
        $this->assertEquals(7, $awayTeam->goals_against);
        $this->assertEquals(2, $awayTeam->goal_difference);
    }

    public function test_revertTeamStats_when_draw()
    {
        // Create mock teams
        $homeTeam = $this->createTeam(1, 'Home Team', 10, 3, 1, 6, 15, 5, 10);
        $awayTeam = $this->createTeam(2, 'Away Team', 10, 2, 4, 4, 12, 8, 10);
        
        // Match score to revert
        $homeScore = 2;
        $awayScore = 2;
        
        // Setup repository update expectations
        $this->teamRepositoryMock->shouldReceive('update')
            ->with(Mockery::any(), Mockery::any())
            ->times(2)
            ->andReturn(true);
            
        // Call the service method
        $this->service->revertTeamStats($homeTeam, $awayTeam, $homeScore, $awayScore);
        
        // Assert home team stats after the service call
        $this->assertEquals(9, $homeTeam->matches_played);
        $this->assertEquals(3, $homeTeam->matches_won);
        $this->assertEquals(0, $homeTeam->matches_drawn);
        $this->assertEquals(6, $homeTeam->matches_lost); 
        $this->assertEquals(9, $homeTeam->points);
        $this->assertEquals(13, $homeTeam->goals_for);
        $this->assertEquals(3, $homeTeam->goals_against);
        $this->assertEquals(10, $homeTeam->goal_difference);
        
        // Assert away team stats
        $this->assertEquals(9, $awayTeam->matches_played);
        $this->assertEquals(2, $awayTeam->matches_won); 
        $this->assertEquals(3, $awayTeam->matches_drawn);
        $this->assertEquals(4, $awayTeam->matches_lost); 
        $this->assertEquals(9, $awayTeam->points);
        $this->assertEquals(10, $awayTeam->goals_for);
        $this->assertEquals(6, $awayTeam->goals_against);
        $this->assertEquals(4, $awayTeam->goal_difference);
    }
    

}