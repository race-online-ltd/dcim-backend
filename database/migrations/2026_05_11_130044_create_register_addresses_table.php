<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('register_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();

            // Index
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('register_addresses');
    }
};