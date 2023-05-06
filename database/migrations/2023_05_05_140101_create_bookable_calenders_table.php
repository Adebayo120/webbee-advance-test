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
        Schema::create('bookable_calenders', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('day');
            $table->unsignedInteger('opening_hour_in_minutes')->nullable();
            $table->unsignedInteger('closing_hour_in_minutes')->nullable();
            $table->boolean('available')->default(true);
            $table->foreignId('service_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookable_calenders');
    }
};
