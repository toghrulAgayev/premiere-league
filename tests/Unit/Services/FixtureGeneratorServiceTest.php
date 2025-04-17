<?php

namespace Tests\Unit\Services;

use App\Models\Matches;
use App\Services\FixtureGeneratorService;
use Mockery;
use Tests\TestCase;
use Illuminate\Support\Collection;

class FixtureGeneratorServiceTest extends TestCase
{
    private FixtureGeneratorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FixtureGeneratorService();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_generateFixtures_with_no_matches_played()
    {
        // Create mock teams
        $team1 = (object)['id' => 1, 'name' => 'Team A'];
        $team2 = (object)['id' => 2, 'name' => 'Team B'];
        $team3 = (object)['id' => 3, 'name' => 'Team C'];
        $team4 = (object)['id' => 4, 'name' => 'Team D'];
        
        $teams = collect([$team1, $team2, $team3, $team4]);
        $matchesPlayed = collect([]);
        
        // Call the method
        $fixtures = $this->service->generateFixtures($teams, $matchesPlayed);
        
        // Assert
        $this->assertIsArray($fixtures);
        $this->assertCount(3, $fixtures); // 4 teams = 3 weeks of fixtures
        
        // Check each week has fixtures
        foreach ($fixtures as $weekFixtures) {
            $this->assertIsArray($weekFixtures);
            $this->assertNotEmpty($weekFixtures);
        }
        
        // Check all teams play exactly once per week
        foreach ($fixtures as $weekNumber => $weekFixtures) {
            $teamsPlaying = [];
            foreach ($weekFixtures as $fixture) {
                $this->assertArrayHasKey('home', $fixture);
                $this->assertArrayHasKey('away', $fixture);
                
                $teamsPlaying[] = $fixture['home'];
                $teamsPlaying[] = $fixture['away'];
            }
            
            // Each team should appear exactly once in each week
            $this->assertCount(4, $teamsPlaying);
            $this->assertContains(1, $teamsPlaying);
            $this->assertContains(2, $teamsPlaying);
            $this->assertContains(3, $teamsPlaying);
            $this->assertContains(4, $teamsPlaying);
        }
    }
    
    public function test_generateFixtures_with_some_matches_played()
    {
        // Create mock teams
        $team1 = (object)['id' => 1, 'name' => 'Team A'];
        $team2 = (object)['id' => 2, 'name' => 'Team B'];
        $team3 = (object)['id' => 3, 'name' => 'Team C'];
        $team4 = (object)['id' => 4, 'name' => 'Team D'];
        
        $teams = collect([$team1, $team2, $team3, $team4]);
        
        // Create a mock match that's already played
        $match1 = new \stdClass();
        $match1->home_team_id = 1;
        $match1->away_team_id = 2;
        
        $matchesPlayed = collect([$match1]);
        
        // Call the method
        $fixtures = $this->service->generateFixtures($teams, $matchesPlayed);
        
        // Assert
        $this->assertIsArray($fixtures);
        
        // Check team1 vs team2 is not in the fixtures (in either direction)
        $matchFound = false;
        foreach ($fixtures as $weekFixtures) {
            foreach ($weekFixtures as $fixture) {
                if (($fixture['home'] == 1 && $fixture['away'] == 2) || 
                    ($fixture['home'] == 2 && $fixture['away'] == 1)) {
                    $matchFound = true;
                    break 2;
                }
            }
        }
        $this->assertFalse($matchFound, 'Team 1 vs Team 2 (or Team 2 vs Team 1) should not be in fixtures as it was already played');
    }
} 