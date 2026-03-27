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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('full_name');
            $table->string('slug');
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->enum('role',['admin','user'])->default('user');
            $table->string('address')->default('');
            $table->string('area')->default('');
            $table->string('city')->default('');
            $table->string('state')->default('');
            $table->string('country');
            $table->integer('country_code')->default(61);
            $table->string('zipcode')->default('');
            $table->string('latitude')->default('');
            $table->string('longitude')->default('');
            $table->string('timezone')->default('Australia/Sydney');
            $table->text('avatar')->default('');
            $table->text('bio')->default('');
            $table->string('device_token')->default('');
            $table->enum('device_type',['android','ios'])->default('ios');
            $table->enum('status',['active','inactive'])->default('active');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
