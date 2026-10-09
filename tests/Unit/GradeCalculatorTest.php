<?php

namespace Tests\Unit;

use App\Services\GradeCalculator;
use Tests\TestCase;

class GradeCalculatorTest extends TestCase
{
    private function grades(string $default, array $overrides = []): array
    {
        $fields = [];
        foreach (config('review.blocks') as $block) {
            foreach (array_keys($block['criteria']) as $field) {
                $fields[$field] = $default;
            }
        }

        return [...$fields, ...$overrides];
    }

    public function test_all_a_gives_a_for_both_roles(): void
    {
        foreach (['supervisor', 'opponent'] as $role) {
            $result = (new GradeCalculator)->calculate($role, $this->grades('A'));
            $this->assertSame('A', $result['letter']);
            $this->assertTrue($result['recommended']);
        }
    }

    public function test_weights_are_applied_for_supervisor(): void
    {
        // 0.10 * 5 + 0.90 * 1 = 1.4
        $grades = $this->grades('A', ['activity_independence' => 'E', 'activity_creativity' => 'E']);
        $result = (new GradeCalculator)->calculate('supervisor', $grades);
        $this->assertEqualsWithDelta(1.4, $result['score'], 0.0001);
        $this->assertSame('A', $result['letter']);
    }

    public function test_activity_is_ignored_for_opponent(): void
    {
        $grades = $this->grades('A', ['activity_independence' => 'FX', 'activity_creativity' => 'FX']);
        $this->assertSame('A', (new GradeCalculator)->calculate('opponent', $grades)['letter']);
    }

    public function test_opponent_weights(): void
    {
        $grades = $this->grades('A', [
            'literature_sorting' => 'D', 'literature_usage' => 'D', 'literature_conclusions' => 'D',
            'formal_logic' => 'D', 'formal_style' => 'D', 'formal_terminology' => 'D', 'formal_graphics' => 'D',
        ]);
        $result = (new GradeCalculator)->calculate('opponent', $grades);
        $this->assertEqualsWithDelta(0.65 + 0.35 * 4, $result['score'], 0.0001);
        $this->assertSame('B', $result['letter']);
    }

    public function test_fx_in_critical_criterion_forces_fx(): void
    {
        $result = (new GradeCalculator)->calculate('supervisor', $this->grades('A', ['quality_topic_quality' => 'FX']));
        $this->assertSame('FX', $result['letter']);
        $this->assertFalse($result['recommended']);
    }

    public function test_e_in_critical_criterion_caps_grade_at_e(): void
    {
        $result = (new GradeCalculator)->calculate('opponent', $this->grades('A', ['quality_topic_completeness' => 'E']));
        $this->assertSame('E', $result['letter']);
        $this->assertTrue($result['recommended']);
    }

    public function test_e_in_critical_criterion_does_not_hide_a_failing_average(): void
    {
        $result = (new GradeCalculator)->calculate('opponent', $this->grades('FX', ['quality_topic_completeness' => 'E']));
        $this->assertSame('FX', $result['letter']);
    }

    public function test_non_critical_fx_is_only_averaged(): void
    {
        $result = (new GradeCalculator)->calculate('supervisor', $this->grades('A', ['quality_methods' => 'FX']));
        // 0.65 * (11 / 6) + 0.35 = 1.54
        $this->assertSame('B', $result['letter']);
    }

    public function test_exact_half_rounds_up(): void
    {
        // opponent: 0.65 * 2 + 0.20 * 3 + 0.15 * 4 = 2.5
        $grades = [];
        foreach (['B' => 'quality', 'C' => 'literature', 'D' => 'formal'] as $letter => $block) {
            foreach (array_keys(config("review.blocks.{$block}.criteria")) as $field) {
                $grades[$field] = $letter;
            }
        }

        $result = (new GradeCalculator)->calculate('opponent', $grades);
        $this->assertSame('C', $result['letter']);
    }

    public function test_incomplete_grades_are_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new GradeCalculator)->calculate('supervisor', []);
    }

    public function test_mixed_opponent_marks_match_the_excel_formula(): void
    {
        // quality (1+3+4+4+4+3)/6, literature (3+4+3)/3, formal (2+3+4+3)/4
        $grades = $this->grades('A', [
            'quality_topic_completeness' => 'C', 'quality_topic_quality' => 'D', 'quality_methods' => 'D',
            'quality_complexity' => 'D', 'quality_practicality' => 'C',
            'literature_sorting' => 'C', 'literature_usage' => 'D', 'literature_conclusions' => 'C',
            'formal_logic' => 'B', 'formal_style' => 'C', 'formal_terminology' => 'D', 'formal_graphics' => 'C',
        ]);
        $result = (new GradeCalculator)->calculate('opponent', $grades);

        $this->assertEqualsWithDelta(0.65 * (19 / 6) + 0.20 * (10 / 3) + 0.15 * 3, $result['score'], 0.0001);
        $this->assertSame('C', $result['letter']);
    }

    public function test_mixed_supervisor_marks_match_the_excel_formula(): void
    {
        // activity 1, quality (1+2+2+1+1+1)/6, literature (2+1+1)/3, formal (1+2+1+1)/4
        $grades = $this->grades('A', [
            'quality_topic_completeness' => 'B', 'quality_topic_quality' => 'B',
            'literature_sorting' => 'B', 'formal_style' => 'B',
        ]);
        $result = (new GradeCalculator)->calculate('supervisor', $grades);

        $this->assertEqualsWithDelta(0.10 + 0.65 * (4 / 3) + 0.15 * (4 / 3) + 0.10 * 1.25, $result['score'], 0.0001);
        $this->assertSame('A', $result['letter']);
    }
}
