<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ConfiguredBreakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [];

        Service::whereIn('name', ['Men Haircut', 'Women Haircut'])->each(function ($service) use(&$data) {
            $data = [...$data, ...$this->getData($service)];
        });

        DB::table('configured_breaks')->insert($data);
    }

    private function getData(Service $service): array
    {
        return [
            [
                'name' => 'lunch break',
                'start_hour_in_minutes' => 720,
                'end_hour_in_minutes' => 780,
                'service_id' => $service->id,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'cleaning break',
                'start_hour_in_minutes' => 900,
                'end_hour_in_minutes' => 960,
                'service_id' => $service->id,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
    }
}
