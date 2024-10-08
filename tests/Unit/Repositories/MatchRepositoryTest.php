<?php

namespace Tests\Unit\Repositories;

use App\Models\Matches;
use App\Models\Team;
use App\Repositories\MatchRepository;
use App\Repositories\TeamRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class MatchRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Team::factory()->count(5)->create();
    }

    public function test_getAll_returns_all_matches_with_teams()
    {
        $match1 = Matches::factory()->create();
        $match2 = Matches::factory()->create();

        $repository = new MatchRepository();
        $result = $repository->getAll();

        $this->assertCount(2, $result);
    }

    public function test_create_match()
    {
        $repository = new MatchRepository();
        $teamRepository  = new TeamRepository();
        $teams = $teamRepository->getAll();
        $matchData = [
            'home_team_id' => $teams[0]->id,
            'away_team_id' => $teams[1]->id,
            'home_team_score' => 3,
            'away_team_score' => 1,
            'week' => 1,
            'match_date' => now()->toDateString(),
        ];

        $result = $repository->create($matchData);

        $this->assertDatabaseHas('matches', $matchData);
    }

    public function test_update_match()
    {
        $repository = new MatchRepository();
        $match = Matches::factory()->create(['home_team_score' => 2]);

        $repository->update($match->id, ['home_team_score' => 4]);

        $this->assertDatabaseHas('matches', [
            'id' => $match->id,
            'home_team_score' => 4,
        ]);
    }

    public function test_delete_match()
    {
        $repository = new MatchRepository();
        $match = Matches::factory()->create();

        $repository->delete($match->id);

        $this->assertDatabaseMissing('matches', ['id' => $match->id]);
    }
}

