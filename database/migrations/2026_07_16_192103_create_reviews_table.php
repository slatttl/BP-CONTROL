<?php
use App\Models\User;
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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->string('academic_year', 20);
            $table->string('student_name');
            $table->string('study_program');
            $table->text('thesis_title');
            $table->string('supervisor_name');
            $table->string('place');
            $table->date('review_date');
            $table->decimal('originality_percentage', 5, 2);
            $table->string('originality_status', 30);
            $table->string('activity_independence', 2);
            $table->string('activity_creativity', 2);
            $table->text('activity_comment')->nullable();
            $table->string('quality_overall_concept', 2);
            $table->string('quality_topic_completeness', 2);
            $table->string('quality_topic_quality', 2);
            $table->string('quality_methods', 2);
            $table->string('quality_complexity', 2);
            $table->string('quality_practicality', 2);
            $table->text('quality_comment')->nullable();
            $table->string('literature_sorting', 2);
            $table->string('literature_usage', 2);
            $table->string('literature_conclusions', 2);
            $table->text('literature_comment')->nullable();
            $table->string('formal_logic', 2);
            $table->string('formal_style', 2);
            $table->string('formal_terminology', 2);
            $table->string('formal_graphics', 2);
            $table->text('formal_comment')->nullable();
            $table->string('final_recommendation', 20);
            $table->text('questions')->nullable();
            $table->text('originality_comment')->nullable();
            $table->string('final_grade', 2);
            $table->text('author_statement')->default('Praca je autorska.');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
