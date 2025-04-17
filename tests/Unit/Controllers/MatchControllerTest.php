<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\MatchController;
use App\Repositories\MatchRepository;
use App\Services\MatchService;
use App\Services\MatchSimulationService;
use Illuminate\Http\JsonResponse; // Import JsonResponse
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface; // Import MockInterface
use Tests\TestCase;
use Illuminate\Support\Collection; // Correct namespace

class MatchControllerTest extends TestCase
{
    private MockInterface|MatchRepository $matchRepositoryMock; // Add type hint
    private MockInterface|MatchService $matchServiceMock; // Add type hint
    private MockInterface|MatchSimulationService $matchSimulationServiceMock; // Add type hint
    private MatchController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->matchRepositoryMock = Mockery::mock(MatchRepository::class);
        $this->matchServiceMock = Mockery::mock(MatchService::class);
        $this->matchSimulationServiceMock = Mockery::mock(MatchSimulationService::class);
        
        $this->controller = new MatchController(
            $this->matchRepositoryMock,
            $this->matchServiceMock,
            $this->matchSimulationServiceMock
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_returns_all_matches()
    {
        // Mock data as an array of stdClass objects (closer to JSON decode result)
        $matchesData = [
            (object)[
                'id' => 1,
                'home_team' => 'Team A',
                'away_team' => 'Team B',
                'home_team_score' => 2,
                'away_team_score' => 1,
                'week' => 1,
                'match_date' => '2023-05-15'
            ]
        ];
        $matchesCollection = collect($matchesData);
        
        // Setup expectations
        $this->matchRepositoryMock->shouldReceive('getAll')
            ->once()
            ->andReturn($matchesCollection);
        
        // Call the method
        $response = $this->controller->index();
        
        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        // Compare the decoded JSON data (which will be an array of objects)
        $this->assertEquals($matchesData, json_decode($response->getContent())); 
    }

    public function test_store_creates_new_match()
    {
        // Mock data
        $matchData = [
            'home_team_id' => 1,
            'away_team_id' => 2,
            'home_team_score' => 3,
            'away_team_score' => 1,
            'week' => 1,
            'match_date' => '2023-05-15'
        ];
        
        // Mock the created match
        $createdMatchMock = Mockery::mock(\App\Models\Matches::class);
        $createdMatchMock->shouldReceive('toJson')
            ->withAnyArgs() // Allow any arguments to toJson
            ->andReturn(json_encode((object)$matchData));
        
        // Create mock request
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('all')
            ->once()
            ->andReturn($matchData);
        
        // Setup repository mock to return the Matches mock
        $this->matchRepositoryMock->shouldReceive('create')
            ->with($matchData)
            ->once()
            ->andReturn($createdMatchMock);
        
        // Call the method
        $response = $this->controller->store($request);
        
        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        foreach ($matchData as $key => $value) {
            $this->assertArrayHasKey($key, $responseData);
            $this->assertEquals($value, $responseData[$key]);
        }
    }

    public function test_update_updates_match_result()
    {
        // Test data
        $matchId = 1;
        $newHomeScore = 3;
        $newAwayScore = 2;
        
        // Create mock request
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('input')
            ->with('home_team_score')
            ->once()
            ->andReturn($newHomeScore);
            
        $request->shouldReceive('input')
            ->with('away_team_score')
            ->once()
            ->andReturn($newAwayScore);
        
        // Use shouldReceive instead of allows
        $this->matchSimulationServiceMock->shouldReceive('updateMatchResult')
            ->with($matchId, $newHomeScore, $newAwayScore)
            ->once(); // Expect it to be called once
        
        // Call the method
        $response = $this->controller->update($request, $matchId);
        
        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Match updated successfully.', $responseData['message']);
    }

    public function test_update_catches_exceptions()
    {
        // Test data
        $matchId = 1;
        $newHomeScore = 3;
        $newAwayScore = 2;
        $errorMessage = 'Match not found';
        
        // Create mock request
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('input')
            ->with('home_team_score')
            ->once()
            ->andReturn($newHomeScore);
            
        $request->shouldReceive('input')
            ->with('away_team_score')
            ->once()
            ->andReturn($newAwayScore);
        
        // Use shouldReceive and expect it once
        $this->matchSimulationServiceMock->shouldReceive('updateMatchResult')
            ->with($matchId, $newHomeScore, $newAwayScore)
            ->once()
            ->andThrow(new \Exception($errorMessage));
        
        // Call the method
        $response = $this->controller->update($request, $matchId);
        
        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Failed to update match: ' . $errorMessage, $responseData['error']);
    }

    public function test_lastWeekMatches_returns_matches_from_last_week()
    {
        // Mock data as an array of arrays (or objects)
        $lastWeekMatchesData = [
            [
                'id' => 1,
                'home_team' => 'Team A',
                'away_team' => 'Team B',
                'home_team_score' => 2,
                'away_team_score' => 1,
                'week' => 5,
                'match_date' => '2023-05-15'
            ]
        ];
        $lastWeekMatchesCollection = collect($lastWeekMatchesData);
        
        // Setup service mock
        $this->matchServiceMock->shouldReceive('getLastWeekMatches')
            ->once()
            ->andReturn($lastWeekMatchesCollection);
        
        // Call the method
        $response = $this->controller->lastWeekMatches();
        
        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response); // Expect a JsonResponse
        $this->assertEquals(200, $response->getStatusCode());
        // Compare the decoded JSON data
        $this->assertEquals($lastWeekMatchesData, json_decode($response->getContent(), true)); 
    }
}