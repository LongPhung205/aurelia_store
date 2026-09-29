<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use App\Services\MarketBasketMiningService;
use App\Services\RfmAnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    protected AnalyticsService $analyticsService;
    protected RfmAnalyticsService $rfmService;
    protected MarketBasketMiningService $basketService;

    public function __construct(
        AnalyticsService $analyticsService,
        RfmAnalyticsService $rfmService,
        MarketBasketMiningService $basketService
    ) {
        $this->analyticsService = $analyticsService;
        $this->rfmService = $rfmService;
        $this->basketService = $basketService;
    }

    public function index(Request $request)
    {
        $activeTab = $request->input('tab', 'sales');
        $range = $request->input('range', 'last_7_days');
        $from = $request->input('from_date');
        $to = $request->input('to_date');

        $data = $this->analyticsService->getAnalyticsData($range, $from, $to);
        $rfmData = $activeTab === 'rfm' ? $this->rfmService->getRfmDashboardData() : null;
        $basketData = $activeTab === 'basket' ? $this->basketService->mineAssociationRules() : null;

        return view('admin.analytics.index', compact('activeTab', 'data', 'rfmData', 'basketData'));
    }

    public function export(Request $request)
    {
        $range = $request->input('range', 'last_7_days');
        $from = $request->input('from_date');
        $to = $request->input('to_date');

        $data = $this->analyticsService->getAnalyticsData($range, $from, $to);

        return $this->analyticsService->streamCsvReport($data);
    }
}
