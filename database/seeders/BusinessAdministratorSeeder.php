<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BusinessAdministrator;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class BusinessAdministratorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BusinessAdministrator::create(['name' => 'Hair Saloon']);
    }
}
