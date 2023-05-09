<?php

use App\Models\Service;
use App\Helpers\Models\SlotHelper;
use App\Actions\CreateMultipleAppointmentsAction;
use App\Models\BookableCalender;

test('handle method creates multiple appointments', function () {
    $service = Service::factory()->create();

    $date = now();

    BookableCalender::factory()->for($service)->create([
        'day' => now()->dayOfWeek
    ]);

    $slot = (new SlotHelper)->forService($service)->forSlot($date);
    
    $data = [
        'service_id' => $service->id,
        'start_date' => now(),
        'slot' => $slot,
        'profiles' => [[
            'first_name' => $firstName = fake()->firstName(),
            'last_name' => $lastName = fake()->lastName(),
            'email' => $email = fake()->safeEmail()
        ]]
    ];

    (new CreateMultipleAppointmentsAction())->handle($data);
  
    $this->assertDatabaseHas('appointments', [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email
    ]);
});