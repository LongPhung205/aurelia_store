<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.analytics.index');
    }

    public function export(Request $request)
    {
        return response()->json(['status' => 'ok']);
    }
}
