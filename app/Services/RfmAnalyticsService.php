<?php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;

class RfmAnalyticsService
{
    /**
     * Scores Recency on a 1-5 scale based on days since last order.
     */
    public function scoreRecency(int $days): int
    {
        return match (true) {
            $days <= 15 => 5,
            $days <= 30 => 4,
            $days <= 60 => 3,
            $days <= 90 => 2,
            default => 1,
        };
    }

    /**
     * Scores Frequency on a 1-5 scale based on total completed orders count.
     */
    public function scoreFrequency(int $orders): int
    {
        return match (true) {
            $orders >= 8 => 5,
            $orders >= 5 => 4,
            $orders >= 3 => 3,
            $orders === 2 => 2,
            default => 1,
        };
    }

    /**
     * Scores Monetary on a 1-5 scale based on cumulative spending in VNĐ.
     */
    public function scoreMonetary(float $amount): int
    {
        return match (true) {
            $amount >= 5000000 => 5,
            $amount >= 2500000 => 4,
            $amount >= 1000000 => 3,
            $amount >= 500000 => 2,
            default => 1,
        };
    }

    /**
     * Classifies customer into one of 6 cohorts based on R, F, M quintiles.
     */
    public function classifySegment(int $r, int $f, int $m): string
    {
        if ($r >= 4 && $f >= 4 && $m >= 4) {
            return 'Champions';
        }

        if ($r <= 2 && $f >= 3 && $m >= 3) {
            return 'At Risk';
        }

        if ($r >= 3 && $f >= 3) {
            return 'Loyal Customers';
        }

        if ($r >= 4 && $f <= 2 && $m >= 2) {
            return 'Potential Loyalists';
        }

        if ($r >= 4 && $f === 1) {
            return 'New Customers';
        }

        return 'Lost Customers';
    }

    /**
     * Computes individual RFM scores and segments for all purchasing customers.
     */
    public function calculateCustomerRfmScores(): array
    {
        $orders = Order::where(function($q) {
                $q->where('payment_status', 'paid')
                  ->orWhere('status', 'completed');
            })
            ->where('status', '!=', 'cancelled')
            ->select('id', 'user_id', 'customer_name', 'customer_phone', 'total_amount', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        $customers = [];
        $now = Carbon::now();

        foreach ($orders as $order) {
            $key = $order->user_id ? 'user_' . $order->user_id : 'phone_' . ($order->customer_phone ?: 'guest_' . $order->id);

            if (!isset($customers[$key])) {
                $customers[$key] = [
                    'key' => $key,
                    'user_id' => $order->user_id,
                    'name' => $order->customer_name ?: 'Khách vãng lai',
                    'phone' => $order->customer_phone ?: 'N/A',
                    'order_ids' => [],
                    'last_order_date' => $order->created_at,
                    'order_count' => 0,
                    'total_spend' => 0.0,
                ];
            }

            $customers[$key]['order_ids'][] = $order->id;
            $customers[$key]['order_count']++;
            $customers[$key]['total_spend'] += (float) $order->total_amount;

            if ($order->created_at->gt($customers[$key]['last_order_date'])) {
                $customers[$key]['last_order_date'] = $order->created_at;
            }
        }

        $results = [];

        foreach ($customers as $c) {
            $daysSinceLast = (int) $now->diffInDays($c['last_order_date']);
            $rScore = $this->scoreRecency($daysSinceLast);
            $fScore = $this->scoreFrequency($c['order_count']);
            $mScore = $this->scoreMonetary($c['total_spend']);
            $segment = $this->classifySegment($rScore, $fScore, $mScore);

            $results[] = [
                'key' => $c['key'],
                'user_id' => $c['user_id'],
                'name' => $c['name'],
                'phone' => $c['phone'],
                'order_ids' => $c['order_ids'],
                'recency_days' => $daysSinceLast,
                'last_order_date' => $c['last_order_date']->format('d/m/Y'),
                'frequency' => $c['order_count'],
                'monetary' => $c['total_spend'],
                'r_score' => $rScore,
                'f_score' => $fScore,
                'm_score' => $mScore,
                'segment' => $segment,
            ];
        }

        return $results;
    }
}
