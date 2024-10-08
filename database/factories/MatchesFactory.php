<?php

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Matches>
 */
class MatchesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'home_team_id' => Team::factory(), // This will create a new Team instance
            'away_team_id' => Team::factory(), // This will create another Team instance
            'home_team_score' => $this->faker->numberBetween(0, 5),
            'away_team_score' => $this->faker->numberBetween(0, 5),
            'week' => $this->faker->numberBetween(1, 38), // Assuming a league with 38 weeks
            'match_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
