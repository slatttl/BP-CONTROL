<?php

namespace App\Support;

class CriterionLabel
{
    /**
     * Resolve a criterion label for a thesis type and review role.
     * A label array may hold "type" entries and more specific "type:role" overrides.
     */
    public static function resolve(string|array $label, ?string $type, ?string $role): string
    {
        if (! is_array($label)) {
            return $label;
        }

        $type = $type ?: 'bachelor';

        return $label["{$type}:{$role}"] ?? $label[$type] ?? reset($label);
    }

    /** @return array<string, string> every type:role combination, for client-side switching */
    public static function variants(string|array $label): array
    {
        $variants = [];
        foreach (array_keys(config('review.thesis_types')) as $type) {
            foreach (array_keys(config('review.roles')) as $role) {
                $variants["{$type}:{$role}"] = self::resolve($label, $type, $role);
            }
        }

        return $variants;
    }
}