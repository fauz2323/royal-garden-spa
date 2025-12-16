<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $missions = [
            [
                'title' => 'First Mission',
                'description' => 'Complete your first task',
                'points' => 100,
                'goal' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Daily Challenge',
                'description' => 'Complete 5 daily tasks',
                'points' => 250,
                'goal' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Weekly Goal',
                'description' => 'Complete 20 tasks in a week',
                'points' => 500,
                'goal' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        \App\Models\Mission::insert($missions);
    }
}
