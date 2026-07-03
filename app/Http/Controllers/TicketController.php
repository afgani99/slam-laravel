<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Cid;
use App\Models\Ticket;
use App\Services\TicketNumberService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status'); // Bisa null
        $search = trim((string) $request->query('search'));
        $caseType = $request->query('case_type');
        $perPage = (int) $request->query('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50], true) ? $perPage : 10;

        $filter = $request->string('filter')->toString();
        $filter = in_array($filter, ['day', 'month', 'year'], true) ? $filter : 'month';

        [$startDate, $endDate] = match ($filter) {
            'day' => [now()->startOfDay(), now()->endOfDay()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };

        $tickets = Ticket::query()
            ->with('cid', 'gamas')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($status, function ($query) use ($status): void {
                $query->where('status', $status);
            })
            ->when($caseType, function ($query) use ($caseType): void {
                $query->where('case_type', $caseType);
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('ticket_number', 'like', "%{$search}%")
                        ->orWhere('vendor_ticket_number', 'like', "%{$search}%")
                        ->orWhere('case_type', 'like', "%{$search}%")
                        ->orWhereHas('cid', function ($query) use ($search): void {
                            $query->where('cid', 'like', "%{$search}%")
                                ->orWhere('vendor_name', 'like', "%{$search}%")
                                ->orWhere('customer_name', 'like', "%{$search}%")
                                ->orWhere('service', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('started_at')
            ->paginate($perPage)
            ->withQueryString();

        $cids      = Cid::orderBy('cid')->get();
        $caseTypes = Ticket::CASE_TYPES;

        return view('tickets.index', compact('tickets', 'status', 'search', 'perPage', 'filter', 'caseType', 'cids', 'caseTypes'));
    }

    public function create(Request $request): View
    {
        $cids = Cid::orderBy('cid')->get();
        $selectedCid = $request->integer('cid_id') ?: null;

        return view('tickets.create', [
            'ticket' => new Ticket(['cid_id' => $selectedCid]),
            'cids' => $cids,
            'caseTypes' => Ticket::CASE_TYPES,
        ]);
    }

    public function store(StoreTicketRequest $request, TicketNumberService $ticketNumberService): RedirectResponse
    {
        $ticket = Ticket::create([
            ...$request->validated(),
            'ticket_number' => $ticketNumberService->generate(),
            'status' => Ticket::STATUS_OPEN,
        ]);

        if ($request->has('_from_cid')) {
            return redirect()
                ->route('cids.index')
                ->with('success', 'Ticket berhasil dibuat.');
        }

        return redirect()
            ->route('tickets.index', ['status' => 'open'])
            ->with('success', 'Ticket berhasil dibuat.');
    }

    public function show(Ticket $ticket): View
    {
        $ticket->load(['cid', 'pendingIntervals' => fn ($query) => $query->latest('started_at')]);

        $formatDurasi = fn ($seconds) => sprintf('%d jam, %d menit, %d detik', floor($seconds / 3600), floor(($seconds % 3600) / 60), $seconds % 60);

        // Total Durasi Pending: jumlah durasi interval yang sudah selesai (memiliki ended_at)
        $pendingFinished = $ticket->pendingIntervals->whereNotNull('ended_at');
        
        if ($pendingFinished->isEmpty()) {
            $totalPendingFormatted = '-';
            $totalPendingSeconds = 0;
        } else {
            $totalPendingSeconds = $pendingFinished->sum(fn ($interval) => $interval->started_at->diffInSeconds($interval->ended_at));
            $totalPendingFormatted = $formatDurasi($totalPendingSeconds);
        }

        // Durasi Kendala & Efektif: hanya hitung jika sudah ada finished_at, kosong jika belum
        if ($ticket->finished_at) {
            $durasiTiketSeconds = $ticket->started_at->diffInSeconds($ticket->finished_at);
            $durasiEfektifSeconds = max(0, $durasiTiketSeconds - $totalPendingSeconds);
            $durasiKendalaFormatted = $formatDurasi($durasiTiketSeconds);
            $durasiEfektifFormatted = $formatDurasi($durasiEfektifSeconds);
        } else {
            $durasiKendalaFormatted = '-';
            $durasiEfektifFormatted = '-';
        }

        return view('tickets.show', compact('ticket', 'durasiKendalaFormatted', 'totalPendingFormatted', 'durasiEfektifFormatted'));
    }

    public function edit(Ticket $ticket): View|RedirectResponse
    {
        if ($ticket->isClosed()) {
            return redirect()
                ->route('tickets.show', $ticket)
                ->with('error', 'Ticket yang sudah closed tidak dapat diedit.');
        }

        $cids = Cid::orderBy('cid')->get();

        return view('tickets.edit', [
            'ticket' => $ticket,
            'cids' => $cids,
            'caseTypes' => Ticket::CASE_TYPES,
        ]);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $ticket->update($request->validated());

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket berhasil diperbarui.');
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        if ($ticket->isClosed()) {
            return back()->with('error', 'Ticket closed tidak dapat dihapus.');
        }

        $ticket->delete();

        return redirect()
            ->route('tickets.index')
            ->with('success', 'Ticket berhasil dihapus.');
    }
}
