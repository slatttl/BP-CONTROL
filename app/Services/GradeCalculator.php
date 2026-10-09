<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * Computes the final thesis grade exactly as the official evaluation spreadsheets do.
 *
 * Every block is the plain average of its criteria; the weighted sum of the blocks is the
 * score. A failing (FX) "critical" criterion forces FX, a sufficient (E) one caps the grade at E.
 */
class GradeCalculator
{
    private const FX = 6;

    private const E = 5;

    /**
     * @param  array<string, string|null>  $grades  criterion field => letter grade
     * @return array{score: float, points: int, letter: string, recommended: bool}
     */
    public function calculate(string $role, array $grades): array
    {
        $weights = config("review.role_blocks.{$role}")
            ?? throw new InvalidArgumentException("Unknown review role [{$role}].");
        $scale = config('review.grade_points');

        $score = 0.0;
        $critical = [];

        foreach ($weights as $block => $weight) {
            $criteria = array_keys(config("review.blocks.{$block}.criteria"));
            $points = array_map(fn (string $field) => $this->points($scale, $grades, $field), $criteria);

            $score += $weight * (array_sum($points) / count($points));

            foreach (config("review.blocks.{$block}.critical", []) as $field) {
                $critical[] = $this->points($scale, $grades, $field);
            }
        }

        $rounded = (int) round(round($score, 10), 0, PHP_ROUND_HALF_UP);

        if (in_array(self::FX, $critical, true)) {
            $final = self::FX;
        } elseif (in_array(self::E, $critical, true) && $rounded !== self::FX) {
            $final = self::E;
        } else {
            $final = $rounded;
        }

        $final = max(1, min(self::FX, $final));

        return [
            'score' => round($score, 4),
            'points' => $final,
            'letter' => (string) array_search($final, $scale, true),
            'recommended' => $final < self::FX,
        ];
    }

    private function points(array $scale, array $grades, string $field): int
    {
        $grade = $grades[$field] ?? null;

        return $scale[$grade] ?? throw new InvalidArgumentException("Missing or invalid grade for [{$field}].");
    }
}
