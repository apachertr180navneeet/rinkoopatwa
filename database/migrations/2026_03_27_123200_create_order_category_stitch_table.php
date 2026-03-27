<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_category_stitch', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('stitch_master_id')->constrained('users')->cascadeOnDelete();

            $table->enum('stitch_status', ['trial_ready', 'pending', 'complete'])->default('pending');
            $table->timestamps();

            $table->unique(['order_id', 'category_id', 'stitch_master_id']);
        });

        // Ensure we have deterministic enum values in case DB collation/settings differ.
        DB::statement('SET foreign_key_checks=1');
    }

    public function down(): void
    {
        Schema::dropIfExists('order_category_stitch');
    }
};

