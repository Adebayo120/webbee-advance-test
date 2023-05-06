<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PlannedOffSeeder extends Seeder
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

        DB::table('planned_offs')->insert($data);
    }

    private function getdata(Service $service): array
    {
        return [
            [
                'start_date' => now()->addDays(3)->startOfDay(),
                'end_date' => now()->addDays(3)->endOfDay(),
                'service_id' => $service->id,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
    }
}
