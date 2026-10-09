<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->string('thesis_type', 20)->default('bachelor')->after('user_id');
            $table->string('review_role', 20)->default('supervisor')->after('thesis_type');
            $table->string('opponent_name')->nullable()->after('supervisor_name');
            $table->decimal('final_score', 5, 3)->nullable()->after('final_grade');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->string('activity_independence', 2)->nullable()->change();
            $table->string('activity_creativity', 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['thesis_type', 'review_role', 'opponent_name', 'final_score']);
        });
    }
};
