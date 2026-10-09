<?php

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Review::class)->nullable()->constrained()->nullOnDelete();
            $table->string('draft_key', 64);
            $table->json('payload');
            $table->timestamps();
            $table->unique(['user_id', 'draft_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_drafts');
    }
};
