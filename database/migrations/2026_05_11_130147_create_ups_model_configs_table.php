<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ups_model_config', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ups_id');
            $table->unsignedBigInteger('model_id');
            $table->unsignedBigInteger('datacenter_id');
            $table->timestamps();

            // Indexes
            $table->index('ups_id');
            $table->index('model_id');
            $table->index('datacenter_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ups_model_config');
    }
};