<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Best-effort MySQL adjustment so Add/Edit can work with empty fields.
        // If your app uses another DB, adjust these statements accordingly.
        DB::statement("ALTER TABLE orders MODIFY user_name VARCHAR(255) NULL");
        DB::statement("ALTER TABLE orders MODIFY mobile VARCHAR(20) NULL");
    }

    public function down(): void
    {
        // Reverting to NOT NULL is risky if any NULL rows exist.
        // Keep as no-op to avoid migration failures on rollback.
    }
};

