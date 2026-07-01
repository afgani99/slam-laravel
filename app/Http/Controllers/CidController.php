<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCidRequest;
use App\Http\Requests\UpdateCidRequest;
use App\Models\Cid;
use App\Models\Ticket;
use App\Services\SlaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CidController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $perPage = (int) $request->query('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50], true) ? $perPage : 10;

        $cids = Cid::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('cid', 'like', "%{$search}%")
                        ->orWhere('vendor_name', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('service', 'like', "%{$search}%");
                });
            })
            ->withCount('tickets')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $allCids = Cid::orderBy('cid')->get();
        $caseTypes = Ticket::CASE_TYPES;

        return view('cids.index', compact('cids', 'search', 'perPage', 'allCids', 'caseTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('cids.create', [
            'cid' => new Cid(['sla_percentage' => 99.00]),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCidRequest $request): RedirectResponse
    {
        $cid = Cid::create($request->validated());

        if ($request->has('_modal')) {
            return redirect()
                ->route('cids.index')
                ->with('success', 'CID berhasil ditambahkan.');
        }

        return redirect()
            ->route('cids.show', $cid)
            ->with('success', 'CID berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Cid $cid, SlaService $slaService): View
    {
        $cid->loadCount([
            'tickets',
            'tickets as open_tickets_count' => fn ($query) => $query->where('status', 'open'),
            'tickets as pending_tickets_count' => fn ($query) => $query->where('status', 'pending'),
            'tickets as closed_tickets_count' => fn ($query) => $query->where('status', 'closed'),
        ]);

        $recentTickets = $cid->tickets()
            ->with('cid')
            ->latest('started_at')
            ->limit(10)
            ->get();

        $slaHistory = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonthsNoOverflow($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();
            $monthResult = collect($slaService->calculateSlaForPeriod($startOfMonth, $endOfMonth))
                ->firstWhere('cid', $cid->cid);

            $slaHistory[] = [
                'label' => $date->format('M Y'),
                'sla_achieved' => (float) ($monthResult['sla_achieved'] ?? 100.00),
                'status' => $monthResult['status'] ?? 'Aman',
            ];
        }

        return view('cids.show', compact('cid', 'recentTickets', 'slaHistory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cid $cid): View
    {
        return view('cids.edit', compact('cid'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCidRequest $request, Cid $cid): RedirectResponse
    {
        $cid->update($request->validated());

        if ($request->has('_modal')) {
            return redirect()
                ->route('cids.index')
                ->with('success', 'CID berhasil diperbarui.');
        }

        return redirect()
            ->route('cids.show', $cid)
            ->with('success', 'CID berhasil diperbarui.');
    }

    public function destroy(Cid $cid): RedirectResponse
    {
        DB::transaction(function () use ($cid): void {
            // Hapus semua interval pending dari tiket-tiket milik CID ini
            \App\Models\TicketPendingInterval::whereIn('ticket_id', function ($query) use ($cid) {
                $query->select('id')->from('tickets')->where('cid_id', $cid->id);
            })->delete();

            // Hapus semua tiket milik CID ini
            $cid->tickets()->delete();

            // Hapus CID
            $cid->delete();
        });

        return redirect()
            ->route('cids.index')
            ->with('success', 'CID dan semua tiket terkait berhasil dihapus.');
    }
}
