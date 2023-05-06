<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            BusinessAdministratorSeeder::class,
            ServiceSeeder::class,
            BookableCalenderSeeder::class,
            ConfiguredBreakSeeder::class,
            PlannedOffSeeder::class,
            SlotSeeder::class
        ]);
    }
}
