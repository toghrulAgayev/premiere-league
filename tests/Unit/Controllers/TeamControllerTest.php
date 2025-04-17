<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\TeamController;
use App\Repositories\TeamRepository;
use Illuminate\Http\JsonResponse; // Import JsonResponse
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface; // Import MockInterface
use Tests\TestCase;
use Illuminate\Database\Eloquent\Collection as EloquentCollection; // Import Eloquent Collection
use App\Models\Team; // Import Team model

class TeamControllerTest extends TestCase
{
    private MockInterface|TeamRepository $teamRepositoryMock; // Add type hint
    private TeamController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->teamRepositoryMock = Mockery::mock(TeamRepository::class);
        $this->controller = new TeamController($this->teamRepositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_returns_all_teams()
    {
        // Mock data as an array of stdClass objects
        $teamsData = [
            [
                'id' => 1,
                'name' => 'Team A',
                'points' => 10,
                'played' => 5,
                'won' => 3,
                'drawn' => 1,
                'lost' => 1
            ]
        ];
        
        // Use Eloquent Collection mock
        $teamsCollection = Mockery::mock(EloquentCollection::class);
        $teamsCollection->shouldReceive('toJson')
            ->withAnyArgs()
            ->andReturn(json_encode($teamsData));
        
        // Setup expectations to return Eloquent Collection mock
        $this->teamRepositoryMock->shouldReceive('getAll')
            ->once()
            ->andReturn($teamsCollection);
        
        // Call the method
        $response = $this->controller->index();
        
        // Assert response format
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        // Verify response data contains our teams data
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($teamsData, $responseData);
    }

    public function test_store_creates_new_team()
    {
        // Mock data
        $teamData = [
            'name' => 'New Team',
            'strength' => 80
        ];
        
        // Mock the created team
        $createdTeamMock = Mockery::mock(Team::class);
        $createdTeamMock->shouldReceive('toJson')
            ->withAnyArgs()
            ->andReturn(json_encode((object)$teamData));
        
        // Create mock request
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('all')
            ->once()
            ->andReturn($teamData);
        
        // Setup repository mock to return the Team mock
        $this->teamRepositoryMock->shouldReceive('create')
            ->with($teamData)
            ->once()
            ->andReturn($createdTeamMock);
        
        // Call the method
        $response = $this->controller->store($request);
        
        // Assert response format
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        
        // Verify response data contains our team data
        $responseData = json_decode($response->getContent(), true);
        foreach ($teamData as $key => $value) {
            $this->assertArrayHasKey($key, $responseData);
            $this->assertEquals($value, $responseData[$key]);
        }
    }

    public function test_update_updates_team()
    {
        // Test data
        $teamId = 1;
        $updateData = [
            'name' => 'Updated Team',
            'strength' => 85
        ];
        
        // Create mock request
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('all')
            ->once()
            ->andReturn($updateData);
        
        // Setup repository mock to return bool
        $this->teamRepositoryMock->shouldReceive('update')
            ->with($teamId, $updateData)
            ->once()
            ->andReturn(true); // Return bool
        
        // Call the method
        $response = $this->controller->update($request, $teamId);
        
        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        // Controller returns the *result* of update (bool), json encoded.
        // We need to check the JSON response content. Assuming it returns the bool `true`.
        $this->assertEquals('true', $response->getContent()); 
    }

    public function test_destroy_deletes_team()
    {
        // Test data
        $teamId = 1;
        
        // Setup repository mock - delete returns bool
        $this->teamRepositoryMock->shouldReceive('delete')
            ->with($teamId)
            ->once()
            ->andReturn(true); // Assume delete returns true on success
        
        // Call the method
        $response = $this->controller->destroy($teamId);
        
        // Assert response format
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals('{}', $response->getContent());
    }
}