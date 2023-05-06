<?php

use App\Models\Slot;
use App\Models\BookableCalender;

test('request require service id', function () {
    $response = $this->getJson('api/v1/available-slots');

    $response->assertInvalid('service_id');
});

test('request validate service exists on database', function () {
    $serviceId = fake()->randomDigitNotNull();

    $response = $this->getJson("api/v1/available-slots?service_id={$serviceId}");

    $response->assertInvalid(['service_id' => 'id is invalid']);
});

test('request require date range', function () {
    $response = $this->getJson('api/v1/available-slots');

    $response->assertInvalid('date_range');
});

test('request require date range start date', function () {
    $response = $this->getJson('api/v1/available-slots');

    $response->assertInvalid('date_range.start_date_in_unix_timestamp');
});

test('request require date range end date', function () {
    $response = $this->getJson('api/v1/available-slots');

    $response->assertInvalid('date_range.end_date_in_unix_timestamp');
});

test('request validates date range is an array', function () {
    $response = $this->getJson('api/v1/available-slots?date_range=dddd');

    $response->assertInvalid(['date_range' => 'must be an array']);
});

test('request returns successful response', function () {

    $calender = BookableCalender::factory()->create([
        'opening_hour_in_minutes' => 480,
        'closing_hour_in_minutes' => 1320
    ]);
    
    Slot::factory()->for($calender)->create([
        'start_date' => now()->startOfDay()->addMinutes(480)
    ]);

    $startOfMonthInUnixTimestamp = now()->startOfMonth()->timestamp;

    $endOfMonthInUnixTimestamp = now()->startOfMonth()->timestamp;

    $response = $this->getJson(
        "api/v1/available-slots?date_range[start_date_in_unix_timestamp]={$startOfMonthInUnixTimestamp}&date_range[end_date_in_unix_timestamp]={$endOfMonthInUnixTimestamp}&service_id={$calender->service_id}"
    );

    $response->assertStatus(200);
});

test('request returns appropriate json response', function () {

    $slot = Slot::factory()->create();

    $startOfMonthInUnixTimestamp = now()->startOfMonth()->timestamp;

    $endOfMonthInUnixTimestamp = now()->endOfMonth()->timestamp;
    
    $response = $this->getJson(
        "api/v1/available-slots?date_range[start_date_in_unix_timestamp]={$startOfMonthInUnixTimestamp}&date_range[end_date_in_unix_timestamp]={$endOfMonthInUnixTimestamp}&service_id={$slot->bookableCalender->service_id}"
    );

    $response->assertJsonPath('data.available_dates.0', $slot->start_date_without_time_in_unix_timestamp);
    $response->assertJsonPath("data.available_slots.{$slot->start_date_in_unix_timestamp}.id", $slot->id);
});

