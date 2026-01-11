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
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('check_in_event_id')->index();
            $table->unsignedBigInteger('check_out_event_id')->nullable()->index();
            $table->timestamp('check_in_at')->index();
            $table->timestamp('check_out_at')->nullable()->index();
            $table->decimal('check_in_lat', 10, 7)->nullable();
            $table->decimal('check_in_lng', 10, 7)->nullable();
            $table->decimal('check_out_lat', 10, 7)->nullable();
            $table->decimal('check_out_lng', 10, 7)->nullable();
            $table->string('check_in_location_label')->nullable();
            $table->string('check_out_location_label')->nullable();
            $table->decimal('hours_worked', 6, 2)->nullable();
            $table->enum('status', ['open', 'closed'])->default('open')->index();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('check_in_event_id')->references('id')->on('attendance_events');
            $table->foreign('check_out_event_id')->references('id')->on('attendance_events');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
