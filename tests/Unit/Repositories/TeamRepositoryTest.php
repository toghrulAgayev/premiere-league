<?php

namespace Tests\Unit\Repositories;

use App\Models\Team;
use App\Repositories\TeamRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_getAll_returns_ordered_teams()
    {
        Team::factory()->create(['points' => 10]);
        Team::factory()->create(['points' => 20]);

        $repository = new TeamRepository();
        $teams = $repository->getAll();

        $this->assertEquals(20, $teams->first()->points);
    }

    public function test_create_team()
    {
        $repository = new TeamRepository();

        $teamData = ['name' => 'Team A', 'strength' => 80];
        $repository->create($teamData);

        $this->assertDatabaseHas('teams', $teamData);
    }

    public function test_delete_team()
    {
        $team = Team::factory()->create();
        $repository = new TeamRepository();

        $repository->delete($team->id);

        $this->assertDatabaseMissing('teams', ['id' => $team->id]);
    }
}
