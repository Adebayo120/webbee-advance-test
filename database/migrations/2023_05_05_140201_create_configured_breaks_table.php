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
        Schema::create('configured_breaks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('start_hour_in_minutes');
            $table->unsignedInteger('end_hour_in_minutes');
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
        Schema::dropIfExists('configured_breaks');
    }
};
