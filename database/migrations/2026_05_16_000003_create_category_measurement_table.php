<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_measurement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('measurement_id')->constrained('measurements')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['category_id', 'measurement_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_measurement');
    }
};
