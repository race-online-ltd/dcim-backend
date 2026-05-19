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
        Schema::create('sensor_lists', function (Blueprint $table) {
            $table->id();
            $table->integer('data_center_id');
            $table->integer('device_id');
            $table->integer('sensor_type_list_id');
            $table->integer('unique_id');
            $table->integer('trigger_type_id');
            $table->tinyInteger('sound_status')->default(0);
            $table->tinyInteger('blink_status')->default(0);
            $table->string('sensor_name', 255)->nullable();
            $table->string('location', 255)->nullable();
            $table->integer('model_id')->nullable();
            $table->string('register_address', 255)->nullable();
            $table->string('ip_address', 255)->nullable();
            $table->string('parameter_name', 255)->nullable();
            $table->float('multiplication_factor')->nullable();
            $table->string('unit', 50)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('timestamp');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_lists');
    }
};
