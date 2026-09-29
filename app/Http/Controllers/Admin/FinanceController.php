<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Models\Import;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $range = $request->input('range', 'this_month');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        // Resolve date boundaries if not provided
        if (!$fromDate || !$toDate) {
            match ($range) {
                'today' => [
                    $fromDate = Carbon::today()->toDateString(),
                    $toDate = Carbon::today()->toDateString(),
                ],
                'last_7_days' => [
                    $fromDate = Carbon::now()->subDays(6)->toDateString(),
                    $toDate = Carbon::now()->toDateString(),
                ],
                'this_year' => [
                    $fromDate = Carbon::now()->startOfYear()->toDateString(),
                    $toDate = Carbon::now()->endOfYear()->toDateString(),
                ],
                default => [ // this_month
                    $fromDate = Carbon::now()->startOfMonth()->toDateString(),
                    $toDate = Carbon::now()->endOfMonth()->toDateString(),
                ],
            };
        }

        // 1. KPI Metrics
        $collectedRevenue = (float) PaymentTransaction::where('status', 'success')
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->sum('amount');

        $pendingRevenue = (float) PaymentTransaction::where('status', 'pending')
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->sum('amount');

        $costOfImports = (float) Import::where('status', 'completed')
            ->where(function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('completed_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
                  ->orWhere(function ($q2) use ($fromDate, $toDate) {
                      $q2->whereNull('completed_at')
                         ->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59']);
                  });
            })
            ->sum('total_amount');

        $grossProfit = $collectedRevenue - $costOfImports;
        $profitMargin = $collectedRevenue > 0 ? round(($grossProfit / $collectedRevenue) * 100, 1) : 0;

        $kpi = [
            'collected_revenue' => $collectedRevenue,
            'pending_revenue'   => $pendingRevenue,
            'cost_of_imports'   => $costOfImports,
            'gross_profit'      => $grossProfit,
            'profit_margin'     => $profitMargin,
        ];

        // 2. Timeline Revenue Chart Data (group by date)
        $dailyRevenues = PaymentTransaction::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as total')
            )
            ->where('status', 'success')
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $chartLabels = [];
        $chartValues = [];
        $startDate = Carbon::parse($fromDate);
        $endDate = Carbon::parse($toDate);
        
        // Prevent huge iteration if range is broad
        if ($startDate->diffInDays($endDate) > 90) {
            // Group by month
            $monthlyRevenues = PaymentTransaction::select(
                    DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                    DB::raw('SUM(amount) as total')
                )
                ->where('status', 'success')
                ->whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('total', 'month')
                ->all();

            $current = $startDate->copy()->startOfMonth();
            while ($current->lte($endDate)) {
                $mKey = $current->format('Y-m');
                $chartLabels[] = 'T' . $current->format('m/Y');
                $chartValues[] = (float) ($monthlyRevenues[$mKey] ?? 0);
                $current->addMonth();
            }
        } else {
            $mappedRevenues = $dailyRevenues->pluck('total', 'date')->all();
            $period = $startDate->daysUntil($endDate);
            foreach ($period as $date) {
                $dateStr = $date->toDateString();
                $chartLabels[] = $date->format('d/m');
                $chartValues[] = (float) ($mappedRevenues[$dateStr] ?? 0);
            }
        }

        // 3. Payment Methods Breakdown
        $methods = ['cod' => 'Tiền mặt (COD)', 'payos' => 'PayOS (QR)', 'momo' => 'Ví MoMo'];
        $methodStats = PaymentTransaction::select(
                'payment_method',
                DB::raw('COUNT(*) as total_tx'),
                DB::raw('SUM(CASE WHEN status = "success" THEN amount ELSE 0 END) as collected_amount'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as pending_amount')
            )
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        $donutLabels = [];
        $donutValues = [];
        $methodSummary = [];

        foreach ($methods as $key => $label) {
            $stat = $methodStats->get($key);
            $collected = (float) ($stat ? $stat->collected_amount : 0);
            $pending = (float) ($stat ? $stat->pending_amount : 0);
            $count = (int) ($stat ? $stat->total_tx : 0);

            $donutLabels[] = $label;
            $donutValues[] = $collected;

            $methodSummary[] = [
                'key' => $key,
                'name' => $label,
                'count' => $count,
                'collected' => $collected,
                'pending' => $pending,
            ];
        }

        $chartData = [
            'timeline_labels' => $chartLabels,
            'timeline_values' => $chartValues,
            'donut_labels'    => $donutLabels,
            'donut_values'    => $donutValues,
        ];

        return view('admin.finance.index', compact('kpi', 'chartData', 'methodSummary', 'fromDate', 'toDate', 'range'));
    }
}
