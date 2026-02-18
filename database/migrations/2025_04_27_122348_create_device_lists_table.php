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
        Schema::create('device_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->integer('data_center_id')->length(11);
            $table->string('location', 255);
            $table->string('secret_key', 255);
            $table->tinyInteger('status')->length(2);
            $table->integer('is_active')->length(2)->default(1);
            $table->timestamps();

            // Optional foreign key constraint
//            $table->foreign('data_center_id')->references('id')->on('data_center_creations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_lists');
    }
};
