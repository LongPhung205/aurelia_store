<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function index(Request $request)
    {
        $range = $request->input('range', 'last_7_days');
        $from = $request->input('from_date');
        $to = $request->input('to_date');

        $data = $this->analyticsService->getAnalyticsData($range, $from, $to);

        return view('admin.analytics.index', compact('data'));
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
