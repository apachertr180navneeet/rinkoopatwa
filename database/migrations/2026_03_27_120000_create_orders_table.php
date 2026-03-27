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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->unique();
            $table->string('user_name')->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('stitch_for_name')->nullable();
            $table->string('phone_no', 20)->nullable();
            $table->string('height')->nullable();
            $table->string('body_weight')->nullable();
            $table->string('shoes_size')->nullable();
            $table->string('front_photo')->nullable();
            $table->string('side_photo')->nullable();
            $table->string('back_photo')->nullable();
            $table->string('neck')->nullable();
            $table->string('chest')->nullable();
            $table->string('shoulder')->nullable();
            $table->string('sleeve_length')->nullable();
            $table->string('waist')->nullable();
            $table->text('additional_requirement')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('stitch_master_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('stitch_status', ['trial_ready', 'pending', 'complete'])->default('pending');
            $table->enum('status', ['pending', 'complete'])->default('pending');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
