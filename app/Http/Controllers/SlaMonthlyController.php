<?php

namespace App\Http\Controllers;

use App\Services\SlaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SlaMonthlyController extends Controller
{
    public function __invoke(Request $request, SlaService $slaService): View
    {
        $year = (int) $request->query('tahun', date('Y'));
        $month = (int) $request->query('bulan', date('n'));

        $startDate = now()->year($year)->month($month)->startOfMonth();
        $endDate = now()->year($year)->month($month)->endOfMonth();

        $results = $slaService->calculateSlaForPeriod($startDate, $endDate);

        $search = $request->query('search');
        if ($search) {
            $results = array_values(array_filter($results, function ($item) use ($search) {
                return stripos($item['cid'], $search) !== false ||
                    stripos($item['vendor_name'], $search) !== false ||
                    stripos($item['customer_name'], $search) !== false ||
                    stripos($item['service'], $search) !== false;
            }));
        }

        $months = __('sla_monthly.months');

        return view('sla.monthly', compact('results', 'year', 'month', 'months'));
    }
}
