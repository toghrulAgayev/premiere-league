<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\MatchSimulationController;
use App\Services\MatchSimulationService;
use Mockery;
use Tests\TestCase;

class MatchSimulationControllerTest extends TestCase
{
    private $matchSimulationServiceMock;
    private MatchSimulationController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->matchSimulationServiceMock = Mockery::mock(MatchSimulationService::class);
        $this->controller = new MatchSimulationController($this->matchSimulationServiceMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_simulateNextWeek_returns_appropriate_message()
    {
        // Mock response from service
        $serviceResponse = 'Next week simulated successfully';
        
        // Setup expectations
        $this->matchSimulationServiceMock->shouldReceive('playNextWeek')
            ->once()
            ->andReturn($serviceResponse);
        
        // Call the method
        $response = $this->controller->simulateNextWeek();
        
        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals($serviceResponse, $responseData['message']);
    }
    
    public function test_simulateNextWeek_returns_no_more_games_message()
    {
        // Mock response from service
        $serviceResponse = 'No more games to simulate';
        
        // Setup expectations
        $this->matchSimulationServiceMock->shouldReceive('playNextWeek')
            ->once()
            ->andReturn($serviceResponse);
        
        // Call the method
        $response = $this->controller->simulateNextWeek();
        
        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals($serviceResponse, $responseData['message']);
    }

    public function test_simulateAllWeeks_returns_success_message()
    {
        // Setup expectations
        $this->matchSimulationServiceMock->shouldReceive('playAllWeeks')
            ->once();
        
        // Call the method
        $response = $this->controller->simulateAllWeeks();
        
        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('All weeks simulated successfully', $responseData['message']);
    }
} 