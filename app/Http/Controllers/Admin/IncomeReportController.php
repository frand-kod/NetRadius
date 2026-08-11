<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class IncomeReportController extends Controller
{
    public function show(Request $request, ReportService $reportService): Response
    {
        $from = $request->input('from', Carbon::now()->startOfMonth()->toDateString());
        $to = $request->input('to', Carbon::now()->endOfMonth()->toDateString());

        $fromDate = Carbon::parse($from)->startOfDay();
        $toDate = Carbon::parse($to)->endOfDay();

        $rows = $reportService->incomeByDay($fromDate, $toDate);
        $total = $reportService->totalIncome($fromDate, $toDate) ?? 0;

        return Inertia::render('Admin/IncomeReport', [
            'rows' => $rows,
            'total' => $total,
            'from' => $from,
            'to' => $to,
        ]);
    }
}
