<?php

namespace App\Actions;

use App\Models\Appointment;
use Illuminate\Support\Collection;

class CreateMultipleAppointmentsAction
{
    public function handle(array $data): Collection
    {
        $data = collect($data['profiles'])->map(function (array $profile) use($data) {
            return Appointment::create([...$profile, ...[
                'service_id' => $data['service_id'],
                'start_date' => $data['slot']->getStartDate(),
                'end_date' => $data['slot']->getEndDate()
            ]]);
        });

        return $data;
    }
}