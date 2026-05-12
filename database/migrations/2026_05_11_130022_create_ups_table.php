<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('ip');
            $table->unsignedInteger('slave_id');
            $table->timestamps();

            // Indexes
            $table->index('name');
            $table->index('ip');
            $table->index('slave_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ups');
    }
};