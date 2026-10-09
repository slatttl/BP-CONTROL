<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'thesis_type',
        'review_role',
        'opponent_name',
        'academic_year',
        'student_name',
        'study_program',
        'thesis_title',
        'supervisor_name',
        'place',
        'review_date',
        'originality_percentage',
        'originality_status',
        'activity_independence',
        'activity_creativity',
        'activity_comment',
        'quality_overall_concept',
        'quality_topic_completeness',
        'quality_topic_quality',
        'quality_methods',
        'quality_complexity',
        'quality_practicality',
        'quality_comment',
        'literature_sorting',
        'literature_usage',
        'literature_conclusions',
        'literature_comment',
        'formal_logic',
        'formal_style',
        'formal_terminology',
        'formal_graphics',
        'formal_comment',
        'final_recommendation',
        'questions',
        'originality_comment',
        'final_grade',
        'final_score',
        'author_statement',
    ];

    protected function casts(): array
    {
        return [
            'review_date' => 'date',
            'originality_percentage' => 'decimal:2',
            'final_score' => 'decimal:3',
        ];
    }

    /** Academic year ("2025/2026") the given date belongs to; the year starts in September. */
    public static function academicYearFor(CarbonInterface $date): string
    {
        $startYear = $date->month >= config('review.academic_year_start_month') ? $date->year : $date->year - 1;

        return $startYear.'/'.($startYear + 1);
    }

    public function isOpponent(): bool
    {
        return $this->review_role === 'opponent';
    }

    public function thesisConfig(): array
    {
        return config('review.thesis_types.'.($this->thesis_type ?: 'bachelor'));
    }

    public function roleConfig(): array
    {
        return config('review.roles.'.($this->review_role ?: 'supervisor'));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
