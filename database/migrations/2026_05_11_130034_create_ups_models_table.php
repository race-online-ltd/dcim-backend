<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ups_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('protocol');
            $table->timestamps();

            // Indexes
            $table->index('name');
            $table->index('protocol');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ups_models');
    }
};