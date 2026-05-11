<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('model_wise_address_mapping', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('model_id');
            $table->unsignedBigInteger('address_id');
            $table->timestamps();

            // Indexes
            $table->index('model_id');
            $table->index('address_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_wise_address_mapping');
    }
};