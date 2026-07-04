<?php

namespace App\Http\Controllers;

use App\Models\Cid;
use App\Models\Ticket;
use App\Services\SlaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, SlaService $slaService): View
    {
        $filter = $request->string('filter')->toString();
        $filter = in_array($filter, ['day', 'month', 'year'], true) ? $filter : 'month';

        [$startDate, $endDate] = match ($filter) {
            'day' => [now()->startOfDay(), now()->endOfDay()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };

        $stats = $slaService->getDashboardStats($startDate, $endDate);

        $recentTickets = Ticket::with('cid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $topCidIssues = Ticket::selectRaw('cid_id, COUNT(*) as total')
            ->with('cid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('cid_id')
            ->groupBy('cid_id')
            ->orderByDesc('total')
            ->take(2)
            ->get();

        $topCategoryIssues = Ticket::selectRaw('case_type, COUNT(*) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('case_type')
            ->orderByDesc('total')
            ->take(2)
            ->get();

        $monthlyRestitution = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthStart = now()->copy()->year(now()->year)->month($month)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            $monthlyRestitution[] = [
                'month' => $monthStart->format('M'),
                'total' => count($slaService->getRestitutionCidsForPeriod($monthStart, $monthEnd)),
            ];
        }

        $cids = Cid::orderBy('cid')->get();
        $caseTypes = Ticket::CASE_TYPES;

        return view('dashboard', compact(
            'stats',
            'filter',
            'recentTickets',
            'topCidIssues',
            'topCategoryIssues',
            'monthlyRestitution',
            'cids',
            'caseTypes'
        ));
    }
}
