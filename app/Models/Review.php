<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
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
        'author_statement',
    ];

    protected function casts(): array
    {
        return [
            'review_date' => 'date',
            'originality_percentage' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
