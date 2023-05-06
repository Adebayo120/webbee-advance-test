<?php

use App\Models\Slot;
use App\Actions\CreateMultipleAppointmentsAction;

test('handle method creates multiple appointments', function () {
    $slot = Slot::factory()->create();
    
    $data = [
        'slot_id' => $slot->id,
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