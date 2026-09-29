<?php

namespace App\Services;

use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Resolves the current date period and an equal-duration previous baseline period.
     */
    public function resolvePeriods(string $range, ?string $fromDate = null, ?string $toDate = null): array
    {
        $now = Carbon::now();

        if ($range === 'custom' && $fromDate && $toDate) {
            $currentStart = Carbon::parse($fromDate)->startOfDay();
            $currentEnd = Carbon::parse($toDate)->endOfDay();
        } else {
            match ($range) {
                'today' => [
                    $currentStart = $now->copy()->startOfDay(),
                    $currentEnd = $now->copy()->endOfDay(),
                ],
                'last_30_days' => [
                    $currentStart = $now->copy()->subDays(29)->startOfDay(),
                    $currentEnd = $now->copy()->endOfDay(),
                ],
                'this_month' => [
                    $currentStart = $now->copy()->startOfMonth(),
                    $currentEnd = $now->copy()->endOfDay(),
                ],
                'this_year' => [
                    $currentStart = $now->copy()->startOfYear(),
                    $currentEnd = $now->copy()->endOfDay(),
                ],
                default => [ // last_7_days
                    $range = 'last_7_days',
                    $currentStart = $now->copy()->subDays(6)->startOfDay(),
                    $currentEnd = $now->copy()->endOfDay(),
                ],
            };
        }

        $durationDays = (int) $currentStart->copy()->startOfDay()->diffInDays($currentEnd->copy()->startOfDay()) + 1;
        $prevEnd = $currentStart->copy()->subDay()->endOfDay();
        $prevStart = $prevEnd->copy()->subDays($durationDays - 1)->startOfDay();

        return [
            'range' => $range,
            'days' => $durationDays,
            'current' => [
                'start' => $currentStart,
                'end' => $currentEnd,
            ],
            'previous' => [
                'start' => $prevStart,
                'end' => $prevEnd,
            ],
        ];
    }

    /**
     * Calculates period-over-period percentage growth.
     */
    public function calculateGrowth(float $current, float $previous): float
    {
        if ($previous == 0.0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
