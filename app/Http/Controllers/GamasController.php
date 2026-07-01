<?php

namespace App\Http\Controllers;

use App\Models\Cid;
use App\Models\Gamas;
use App\Models\Ticket;
use App\Models\GamasLog;
use App\Services\TicketNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GamasController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $perPage = (int) $request->query('per_page', 15);
        $perPage = in_array($perPage, [10, 25, 50], true) ? $perPage : 15;

        $gamasList = Gamas::withCount(['tickets'])
            ->with(['tickets' => fn ($q) => $q->with('cid')->take(1)])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('gamas_number', 'like', "%{$search}%")
                        ->orWhere('vendor_ticket_number', 'like', "%{$search}%")
                        ->orWhere('case_type', 'like', "%{$search}%")
                        ->orWhereHas('tickets.cid', function ($cq) use ($search) {
                            $cq->where('cid', 'like', "%{$search}%")
                                ->orWhere('customer_name', 'like', "%{$search}%")
                                ->orWhere('cid_is', 'like', "%{$search}%")
                                ->orWhere('vendor_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('gamas.index', compact('gamasList', 'search', 'status', 'perPage'));
    }

    public function create(): View
    {
        $cids = Cid::orderBy('cid')->get();
        $caseTypes = Ticket::CASE_TYPES;

        return view('gamas.create', compact('cids', 'caseTypes'));
    }

    public function store(Request $request, TicketNumberService $ticketNumberService): RedirectResponse
    {
        $validated = $request->validate([
            'vendor_ticket_number' => ['nullable', 'string', 'max:255'],
            'case_type' => ['required', 'string'],
            'started_at' => ['required', 'date'],
            'cid_ids' => ['required', 'array', 'min:1'],
            'cid_ids.*' => ['required', 'exists:cids,id'],
        ]);

        $gamas = DB::transaction(function () use ($validated, $ticketNumberService): Gamas {
            $gamas = Gamas::create([
                'gamas_number' => Gamas::generateNumber(),
                'vendor_ticket_number' => $validated['vendor_ticket_number'],
                'case_type' => $validated['case_type'],
                'started_at' => $validated['started_at'],
                'status' => Gamas::STATUS_OPEN,
            ]);

            foreach ($validated['cid_ids'] as $cidId) {
                Ticket::create([
                    'gamas_id' => $gamas->id,
                    'cid_id' => $cidId,
                    'ticket_number' => $ticketNumberService->generate(),
                    'vendor_ticket_number' => $validated['vendor_ticket_number'],
                    'case_type' => $validated['case_type'],
                    'started_at' => $validated['started_at'],
                    'status' => Ticket::STATUS_OPEN,
                ]);
            }

            return $gamas;
        });

        return redirect()
            ->route('gamas.show', $gamas)
            ->with('success', 'GAMAS berhasil dibuat dengan '.count($validated['cid_ids']).' tiket.');
    }

    public function show(Gamas $gamas): View
    {
        $gamas->load([
            'tickets' => fn ($q) => $q->with('cid')->latest('started_at'),
            'logs',
        ]);

        $activeTickets = $gamas->tickets->filter(fn (Ticket $t) => ! $t->isClosed());
        $closedTickets = $gamas->tickets->filter(fn (Ticket $t) => $t->isClosed());
        $caseTypes = Ticket::CASE_TYPES;

        // Kelompokkan logs menjadi interval pending-resume
        $sortedLogs = $gamas->logs->sortBy('started_at')->values();
        $intervalLogs = [];
        $pendingBuffer = null;
        foreach ($sortedLogs as $log) {
            if ($log->type === GamasLog::TYPE_PENDING) {
                $pendingBuffer = $log;
            } elseif ($log->type === GamasLog::TYPE_RESUME && $pendingBuffer) {
                $intervalLogs[] = ['pending' => $pendingBuffer, 'resume' => $log];
                $pendingBuffer = null;
            }
        }
        // Pending yang masih aktif (belum diresume)
        if ($pendingBuffer) {
            $intervalLogs[] = ['pending' => $pendingBuffer, 'resume' => null];
        }
        $intervalLogs = array_reverse($intervalLogs); // terbaru di atas

        // Durasi kendala hanya dihitung jika waktu selesai sudah diisi
        $durasiFormatted = null;
        $tiketDurasiFormatted = null;
        $pendingFormatted = null;
        $durasiMinutes = 0;
        $pendingMinutes = 0;

        // Hitung total durasi pending (hanya yang sudah ada waktu lanjutkan/selesai)
        $pendingSeconds = 0;
        foreach ($gamas->logs as $log) {
            if ($log->type !== GamasLog::TYPE_PENDING) {
                continue;
            }
            if ($log->ended_at) {
                $pendingSeconds += (int) abs($log->started_at->diffInSeconds($log->ended_at));
            }
        }

        $pendingMinutes = (int) floor($pendingSeconds / 60);

        // Format: Xh Yj Zm Wd (hari/jam/menit/detik)
        $formatDurasi = function (int $sec): string {
            $d = intdiv($sec, 86400);
            $h = intdiv($sec % 86400, 3600);
            $m = intdiv($sec % 3600, 60);
            $s = $sec % 60;

            $parts = [];
            if ($d > 0) $parts[] = $d . 'h';
            if ($h > 0 || $d > 0) $parts[] = $h . 'j';
            $parts[] = $m . 'm';
            $parts[] = $s . 'd';

            return implode(' ', $parts);
        };

        if ($pendingSeconds > 0) {
            $pendingFormatted = $formatDurasi($pendingSeconds);
        }

        if ($gamas->finished_at) {
            $totalSeconds = (int) abs($gamas->started_at->diffInSeconds($gamas->finished_at));
            $durasiSeconds = max(0, $totalSeconds - $pendingSeconds);
            $durasiMinutes = (int) floor($durasiSeconds / 60);
            $durasiFormatted = $formatDurasi($durasiSeconds);
            $tiketDurasiFormatted = $formatDurasi($totalSeconds);
        }

        return view('gamas.show', compact('gamas', 'activeTickets', 'closedTickets', 'caseTypes', 'durasiFormatted', 'durasiMinutes', 'pendingMinutes', 'pendingFormatted', 'tiketDurasiFormatted', 'intervalLogs'));
    }

    public function edit(Gamas $gamas): View
    {
        $caseTypes = Ticket::CASE_TYPES;

        return view('gamas.edit', compact('gamas', 'caseTypes'));
    }

    public function update(Request $request, Gamas $gamas): RedirectResponse
    {
        $action = $request->input('form_action', 'update');

        if ($action === 'close') {
            return $this->close($request, $gamas);
        }

        if ($action === 'pending') {
            return $this->pending($request, $gamas);
        }

        if ($action === 'resume') {
            return $this->resume($gamas);
        }

        $validated = $request->validate([
            'vendor_ticket_number' => ['nullable', 'string', 'max:255'],
            'case_type' => ['required', 'string'],
            'started_at' => ['required', 'date'],
            'finished_at' => ['nullable', 'date'],
            'rfo_action' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated, $gamas): void {
            $gamas->update($validated);

            $gamas->tickets()
                ->whereNot('status', Ticket::STATUS_CLOSED)
                ->update([
                    'vendor_ticket_number' => $validated['vendor_ticket_number'],
                    'case_type' => $validated['case_type'],
                    'started_at' => $validated['started_at'],
                ]);
        });

        return redirect()->route('gamas.show', $gamas)->with('success', 'GAMAS berhasil diperbarui.');
    }

    public function close(Request $request, Gamas $gamas): RedirectResponse
    {
        $validated = $request->validate([
            'finished_at' => ['required', 'date'],
            'rfo_action' => ['required', 'string'],
        ]);

        DB::transaction(function () use ($validated, $gamas): void {
            // Close pending interval yang masih aktif (jika ada)
            $gamas->logs()
                ->where('type', GamasLog::TYPE_PENDING)
                ->whereNull('ended_at')
                ->update(['ended_at' => $validated['finished_at']]);

            $gamas->update([
                'finished_at' => $validated['finished_at'],
                'rfo_action' => $validated['rfo_action'],
                'status' => Gamas::STATUS_CLOSED,
            ]);

            // Close semua tiket yang belum closed
            $gamas->tickets()
                ->whereNot('status', Ticket::STATUS_CLOSED)
                ->update([
                    'finished_at' => $validated['finished_at'],
                    'rfo_action' => $validated['rfo_action'],
                    'status' => Ticket::STATUS_CLOSED,
                    'closed_at' => $validated['finished_at'],
                ]);
        });

        return redirect()
            ->route('gamas.show', $gamas)
            ->with('success', 'GAMAS berhasil ditutup.');
    }

    public function pending(Request $request, Gamas $gamas): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
            'pending_at' => ['required', 'date'],
        ]);

        DB::transaction(function () use ($validated, $gamas): void {
            $gamas->update([
                'status' => Gamas::STATUS_PENDING,
            ]);

            $gamas->logs()->create([
                'type' => GamasLog::TYPE_PENDING,
                'reason' => $validated['reason'],
                'started_at' => $validated['pending_at'],
            ]);

            $gamas->tickets()
                ->whereNot('status', Ticket::STATUS_CLOSED)
                ->update(['status' => Ticket::STATUS_PENDING]);
        });

        return redirect()->route('gamas.show', $gamas)->with('success', 'GAMAS di-pending.');
    }

    public function resume(Request $request, Gamas $gamas): RedirectResponse
    {
        $validated = $request->validate([
            'resume_at' => ['required', 'date'],
        ]);

        DB::transaction(function () use ($validated, $gamas): void {
            $lastPending = $gamas->logs()
                ->where('type', GamasLog::TYPE_PENDING)
                ->whereNull('ended_at')
                ->latest()
                ->first();

            if ($lastPending) {
                $lastPending->update(['ended_at' => $validated['resume_at']]);
            }

            $gamas->update(['status' => Gamas::STATUS_OPEN]);

            $gamas->logs()->create([
                'type' => GamasLog::TYPE_RESUME,
                'started_at' => $validated['resume_at'],
            ]);

            $gamas->tickets()
                ->where('status', Ticket::STATUS_PENDING)
                ->update(['status' => Ticket::STATUS_OPEN]);
        });

        return redirect()->route('gamas.show', $gamas)->with('success', 'GAMAS dilanjutkan.');
    }

    public function deleteLog(GamasLog $log): RedirectResponse
    {
        $gamas = $log->gamas;
        
        DB::transaction(function () use ($log): void {
            // Jika ini log pending, cari resume yang berpasangan
            if ($log->type === GamasLog::TYPE_PENDING) {
                $relatedResume = GamasLog::where('gamas_id', $log->gamas_id)
                    ->where('type', GamasLog::TYPE_RESUME)
                    ->where('started_at', '>', $log->started_at)
                    ->orderBy('started_at')
                    ->first();
                
                if ($relatedResume) {
                    $relatedResume->delete();
                }
            }
            
            // Jika ini log resume, cari pending yang berpasangan
            if ($log->type === GamasLog::TYPE_RESUME) {
                $relatedPending = GamasLog::where('gamas_id', $log->gamas_id)
                    ->where('type', GamasLog::TYPE_PENDING)
                    ->where('started_at', '<', $log->started_at)
                    ->orderBy('started_at', 'desc')
                    ->first();
                
                if ($relatedPending) {
                    $relatedPending->delete();
                }
            }
            
            $log->delete();
        });

        return redirect()->route('gamas.show', $gamas)->with('success', 'Interval pending berhasil dihapus.');
    }

    public function removeTicket(Gamas $gamas, Ticket $ticket): RedirectResponse
    {
        if ($gamas->status === Gamas::STATUS_CLOSED) {
            return back()->with('error', 'Tidak dapat menghapus tiket pada GAMAS yang sudah ditutup.');
        }

        DB::transaction(function () use ($ticket) {
            $ticket->delete();
        });

        return back()->with('success', 'Tiket berhasil dihapus dari GAMAS.');
    }

    public function destroy(Gamas $gamas): RedirectResponse
    {
        DB::transaction(function () use ($gamas) {
            $gamas->tickets()->delete();
            $gamas->logs()->delete();
            $gamas->delete();
        });

        return redirect()->route('gamas.index')->with('success', 'GAMAS berhasil dihapus.');
    }
}
