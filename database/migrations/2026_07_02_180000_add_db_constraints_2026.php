<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // FK on sessions.user_id — SET NULL on user delete (nullable column)
        Schema::table('sessions', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        // CHECK constraint: reviews.rating must be 1–5
        DB::statement('ALTER TABLE reviews ADD CONSTRAINT chk_reviews_rating CHECK (rating BETWEEN 1 AND 5)');
    }

    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        DB::statement('ALTER TABLE reviews DROP CONSTRAINT chk_reviews_rating');
    }
};
