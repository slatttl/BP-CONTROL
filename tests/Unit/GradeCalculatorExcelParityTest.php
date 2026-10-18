<?php

namespace Tests\Unit;

use App\Services\GradeCalculator;
use App\Support\CriterionLabel;
use Tests\TestCase;

/**
 * Parity with the official evaluation spreadsheets (BP/DP x vedúci/oponent).
 *
 * The expected values come from a deliberately independent re-implementation of the
 * spreadsheet formulas (sheet "Hárok3": AVERAGE per block, weighted sum, ROUND at the end,
 * T23/T28 critical-criteria override) and from the criteria lists printed in the sheets.
 */
class GradeCalculatorExcelParityTest extends TestCase
{
    private const LETTERS = ['A', 'B', 'C', 'D', 'E', 'FX'];

    /** Criteria per block in the exact order of the spreadsheets, per thesis type and role. */
    private const SHEETS = [
        'bachelor:supervisor' => [
            'weights' => [0.10, 0.15, 0.65, 0.10],
            'blocks' => [
                ['activity_independence' => 'Samostatnosť', 'activity_creativity' => 'Tvorivosť'],
                ['literature_sorting' => 'Triedenie a hodnotenie prameňov', 'literature_usage' => 'Využitie poznatkov z literatúry a praxe', 'literature_conclusions' => 'Vyvodzovanie vlastných záverov z literárnych prameňov'],
                ['quality_overall_concept' => 'Celková koncepcia práce', 'quality_topic_completeness' => 'Úplnosť spracovania témy', 'quality_topic_quality' => 'Kvalita spracovania témy', 'quality_methods' => 'Použité metódy riešenia', 'quality_complexity' => 'Algoritmická náročnosť, prácnosť riešenia', 'quality_practicality' => 'Praktická aplikovateľnosť práce'],
                ['formal_logic' => 'Logika usporiadania práce', 'formal_style' => 'Štylizácia textu', 'formal_terminology' => 'Použitá terminológia', 'formal_graphics' => 'Grafická realizácia'],
            ],
        ],
        'bachelor:opponent' => [
            'weights' => [0.20, 0.65, 0.15],
            'blocks' => [
                ['literature_sorting' => 'Triedenie a hodnotenie prameňov', 'literature_usage' => 'Využitie poznatkov z literatúry a praxe', 'literature_conclusions' => 'Vyvodzovanie vlastných záverov z literárnych prameňov'],
                ['quality_overall_concept' => 'Celková koncepcia práce', 'quality_topic_completeness' => 'Úplnosť spracovania témy', 'quality_topic_quality' => 'Kvalita spracovania témy', 'quality_methods' => 'Použité metódy riešenia', 'quality_complexity' => 'Algoritmická náročnosť, prácnosť riešenia', 'quality_practicality' => 'Praktická aplikovateľnosť práce'],
                ['formal_logic' => 'Logika usporiadania práce', 'formal_style' => 'Štylizácia textu', 'formal_terminology' => 'Použitá terminológia', 'formal_graphics' => 'Grafická realizácia'],
            ],
        ],
        'master:supervisor' => [
            'weights' => [0.10, 0.15, 0.65, 0.10],
            'blocks' => [
                ['activity_independence' => 'Samostatnosť', 'activity_creativity' => 'Tvorivosť'],
                ['literature_sorting' => 'Triedenie a hodnotenie prameňov', 'literature_usage' => 'Využitie poznatkov z literatúry a praxe', 'literature_conclusions' => 'Vyvodzovanie vlastných záverov z literárnych prameňov'],
                ['quality_overall_concept' => 'Celková koncepcia práce', 'quality_topic_completeness' => 'Úplnosť spracovania témy', 'quality_topic_quality' => 'Kvalita spracovania témy', 'quality_methods' => 'Použité metódy riešenia', 'quality_complexity' => 'Aplikácia inžinierskych metód riešenia', 'quality_practicality' => 'Praktická aplikovateľnosť práce'],
                ['formal_logic' => 'Logika usporiadania práce', 'formal_style' => 'Štylizácia textu', 'formal_terminology' => 'Použitá terminológia', 'formal_graphics' => 'Grafická realizácia'],
            ],
        ],
        'master:opponent' => [
            'weights' => [0.20, 0.65, 0.15],
            'blocks' => [
                ['literature_sorting' => 'Triedenie a hodnotenie prameňov', 'literature_usage' => 'Využitie poznatkov z literatúry a praxe', 'literature_conclusions' => 'Vyvodzovanie vlastných záverov z literárnych prameňov'],
                ['quality_overall_concept' => 'Celková koncepcia práce', 'quality_topic_completeness' => 'Úplnosť spracovania témy', 'quality_topic_quality' => 'Kvalita spracovania témy', 'quality_methods' => 'Aplikácia inžinierskych metód riešenia', 'quality_complexity' => 'Algoritmická náročnosť, prácnosť riešenia', 'quality_practicality' => 'Praktická aplikovateľnosť práce'],
                ['formal_logic' => 'Logika usporiadania práce', 'formal_style' => 'Štylizácia textu', 'formal_terminology' => 'Použitá terminológia', 'formal_graphics' => 'Grafická realizácia'],
            ],
        ],
    ];

    /** Spreadsheet formula: blocks averaged, weighted, ROUND, then the T23/T28 critical override. */
    private function excelGrade(string $sheet, array $grades): array
    {
        $points = array_flip(self::LETTERS);
        $score = 0.0;

        foreach (self::SHEETS[$sheet]['blocks'] as $index => $criteria) {
            $values = array_map(fn ($field) => $points[$grades[$field]] + 1, array_keys($criteria));
            $score += self::SHEETS[$sheet]['weights'][$index] * (array_sum($values) / count($values));
        }

        $completeness = $points[$grades['quality_topic_completeness']] + 1;
        $quality = $points[$grades['quality_topic_quality']] + 1;
        $rounded = (int) floor(round($score, 9) + 0.5);

        if ($completeness === 6 || $quality === 6) {
            $final = 6;
        } elseif (($completeness === 5 || $quality === 5) && $rounded !== 6) {
            $final = 5;
        } else {
            $final = $rounded;
        }

        return ['score' => $score, 'points' => $final, 'letter' => self::LETTERS[$final - 1]];
    }

    private function fields(string $sheet): array
    {
        return array_merge(...array_map('array_keys', self::SHEETS[$sheet]['blocks']));
    }

    public static function sheets(): array
    {
        return array_map(fn ($sheet) => [$sheet], array_keys(self::SHEETS));
    }

    /** @dataProvider sheets */
    public function test_random_marks_give_the_same_grade_as_the_spreadsheet(string $sheet): void
    {
        [, $role] = explode(':', $sheet);
        mt_srand(crc32($sheet));
        $calculator = new GradeCalculator;

        for ($i = 0; $i < 5000; $i++) {
            $grades = [];
            foreach ($this->fields($sheet) as $field) {
                $grades[$field] = self::LETTERS[mt_rand(0, 5)];
            }

            $expected = $this->excelGrade($sheet, $grades);
            $actual = $calculator->calculate($role, $grades);

            $this->assertSame($expected['points'], $actual['points'], "{$sheet}: ".json_encode($grades));
            $this->assertSame($expected['letter'], $actual['letter']);
            $this->assertSame($expected['points'] < 6, $actual['recommended']);
            $this->assertEqualsWithDelta($expected['score'], $actual['score'], 0.0001);
        }
    }

    /** @dataProvider sheets */
    public function test_every_critical_combination_with_the_rest_uniform_matches_the_spreadsheet(string $sheet): void
    {
        [, $role] = explode(':', $sheet);
        $calculator = new GradeCalculator;

        foreach (self::LETTERS as $rest) {
            foreach (self::LETTERS as $completeness) {
                foreach (self::LETTERS as $quality) {
                    $grades = array_fill_keys($this->fields($sheet), $rest);
                    $grades['quality_topic_completeness'] = $completeness;
                    $grades['quality_topic_quality'] = $quality;

                    $this->assertSame(
                        $this->excelGrade($sheet, $grades)['letter'],
                        $calculator->calculate($role, $grades)['letter'],
                        "{$sheet}: rest={$rest} completeness={$completeness} quality={$quality}"
                    );
                }
            }
        }
    }

    /** @dataProvider sheets */
    public function test_exact_half_scores_round_up_like_excel(string $sheet): void
    {
        [, $role] = explode(':', $sheet);
        $calculator = new GradeCalculator;
        $checked = 0;

        // Every uniform per-block mark combination, which produces many exact x.5 scores.
        foreach (self::LETTERS as $a) {
            foreach (self::LETTERS as $b) {
                foreach (self::LETTERS as $c) {
                    foreach (self::LETTERS as $d) {
                        $marks = [$a, $b, $c, $d];
                        $grades = [];
                        foreach (self::SHEETS[$sheet]['blocks'] as $index => $criteria) {
                            foreach (array_keys($criteria) as $field) {
                                $grades[$field] = $marks[$index];
                            }
                        }
                        $checked++;

                        $this->assertSame(
                            $this->excelGrade($sheet, $grades)['points'],
                            $calculator->calculate($role, $grades)['points'],
                            "{$sheet}: ".json_encode($marks)
                        );
                    }
                }
            }
        }

        $this->assertSame(1296, $checked);
    }

    /** @dataProvider sheets */
    public function test_criteria_labels_and_order_match_the_spreadsheet(string $sheet): void
    {
        [$type, $role] = explode(':', $sheet);
        $blocks = config('review.blocks');
        $roleBlocks = array_keys(config("review.role_blocks.{$role}"));

        $actual = [];
        foreach ($roleBlocks as $blockName) {
            $actual[$blockName] = collect($blocks[$blockName]['criteria'])
                ->map(fn ($label) => CriterionLabel::resolve($label, $type, $role))
                ->all();
        }

        $expected = array_combine(
            $roleBlocks === ['activity', 'quality', 'literature', 'formal']
                ? ['activity', 'literature', 'quality', 'formal']
                : ['literature', 'quality', 'formal'],
            self::SHEETS[$sheet]['blocks']
        );

        foreach ($expected as $blockName => $criteria) {
            $this->assertSame($criteria, $actual[$blockName], "{$sheet}: {$blockName}");
        }
    }

    /** @dataProvider sheets */
    public function test_block_weights_and_critical_criteria_match_the_spreadsheet(string $sheet): void
    {
        [, $role] = explode(':', $sheet);
        $weights = config("review.role_blocks.{$role}");

        // The spreadsheets list literature before quality; weights are compared per block name.
        $expectedWeights = $role === 'supervisor'
            ? ['activity' => 0.10, 'literature' => 0.15, 'quality' => 0.65, 'formal' => 0.10]
            : ['literature' => 0.20, 'quality' => 0.65, 'formal' => 0.15];

        $this->assertEqualsCanonicalizing($expectedWeights, $weights);
        foreach ($expectedWeights as $block => $weight) {
            $this->assertEqualsWithDelta($weight, $weights[$block], 1e-9, "{$sheet}: {$block}");
        }
        $this->assertEqualsWithDelta(1.0, array_sum($weights), 1e-9);
        $this->assertSame(
            ['quality_topic_completeness', 'quality_topic_quality'],
            config('review.blocks.quality.critical')
        );
    }
}
