<?php

namespace App\Http\Controllers;

use App\Services\SlaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SlaRestitutionController extends Controller
{
    public function __invoke(Request $request, SlaService $slaService): View
    {
        $year = (int) $request->query('tahun', date('Y'));
        $month = (int) $request->query('bulan', date('n'));

        $startDate = now()->year($year)->month($month)->startOfMonth();
        $endDate = now()->year($year)->month($month)->endOfMonth();

        $results = $slaService->getRestitutionCidsForPeriod($startDate, $endDate);

        $search = $request->query('search');
        if ($search) {
            $results = array_filter($results, function ($item) use ($search) {
                return stripos($item['cid'], $search) !== false ||
                    stripos($item['vendor_name'], $search) !== false ||
                    stripos($item['customer_name'], $search) !== false ||
                    stripos($item['service'], $search) !== false;
            });
        }

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return view('sla.restitution', compact('results', 'year', 'month', 'months'));
    }
}
