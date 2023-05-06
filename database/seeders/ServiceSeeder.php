<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\BusinessAdministrator;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $administrator = BusinessAdministrator::where('name', 'Hair Saloon')->first();

        DB::table('services')->insert([[
            'name' => 'Men Haircut',
            'business_administrator_id' => $administrator->id,
            'bookable_duration_in_minutes' => 10,
            'break_between_slots_in_minutes' => 5,
            'future_bookable_days' => 7,
            'bookable_appointments_per_slot_count' => 3,
            'created_at' => now(),
            'updated_at' => now()
        ],[
            'name' => 'Women Haircut',
            'business_administrator_id' => $administrator->id,
            'bookable_duration_in_minutes' => 60,
            'break_between_slots_in_minutes' => 10,
            'future_bookable_days' => 7,
            'bookable_appointments_per_slot_count' => 3,
            'created_at' => now(),
            'updated_at' => now()
        ]
    ]);
    }
}
