<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('business_administrator_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unsignedInteger('bookable_duration_in_minutes');
            $table->unsignedInteger('break_between_slots_in_minutes')->default(0);
            $table->unsignedBigInteger('future_bookable_days')->default(config('app.default_future_bookable_days'));
            $table->unsignedBigInteger('bookable_appointments_per_slot_count')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
