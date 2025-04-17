<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\LeagueController;
use App\Services\LeagueService;
use Mockery;
use Tests\TestCase;
use Illuminate\Support\Collection;

class LeagueControllerTest extends TestCase
{
    private $leagueServiceMock;
    private LeagueController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->leagueServiceMock = Mockery::mock(LeagueService::class);
        $this->controller = new LeagueController($this->leagueServiceMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_resetLeague_calls_league_service_and_returns_success()
    {       
        $this->leagueServiceMock->shouldReceive('resetLeague')
            ->once();
        
        $response = $this->controller->resetLeague();
        
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('League reset successfully', $responseData['message']);
    }

    public function test_predictions_returns_win_probabilities()
    {
        // Mock data
        $winProbabilities = collect([
            [
                'name' => 'Team A',
                'win_probability' => 60
            ],
            [
                'name' => 'Team B',
                'win_probability' => 40
            ]
        ]);
        
        $this->leagueServiceMock->shouldReceive('calculateWinProbabilities')
            ->once()
            ->andReturn($winProbabilities);

        $response = $this->controller->predictions();
        
        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($winProbabilities, collect(json_decode($response->getContent(), true)));
    }
} 