<?php

namespace App\Actions;

use App\Models\Appointment;
use Illuminate\Support\Collection;

class CreateMultipleAppointmentsAction
{
    public function handle(array $data): Collection
    {
        $data = collect($data['profiles'])->map(function (array $profile) use($data) {
            return Appointment::create([...$profile, ...['slot_id' => $data['slot_id']]]);
        });

        return $data;
    }
}