<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Team;

class TeamsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $teams = [
            ['name' => 'Real Madrid', 'strength' => 90],
            ['name' => 'Barcelona', 'strength' => 85],
            ['name' => 'Manchester City', 'strength' => 80],
            ['name' => 'Bayern Munich', 'strength' => 75],
        ];

        foreach ($teams as $team) {
            Team::create($team);
        }
    }
}
