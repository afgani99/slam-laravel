<?php

namespace App\Http\Controllers;

use App\Http\Requests\CloseTicketRequest;
use App\Http\Requests\PendingTicketRequest;
use App\Http\Requests\ResumeTicketRequest;
use App\Models\Ticket;
use App\Support\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class TicketStatusController extends Controller
{
    use LogsActivity;
    public function pending(PendingTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        DB::transaction(function () use ($request, $ticket): void {
            $ticket->pendingIntervals()->create($request->validated());

            $ticket->update([
                'status' => Ticket::STATUS_PENDING,
            ]);
        });

        $this->logActivity('pending', 'Mengubah ticket '.$ticket->ticket_number.' ke pending', $ticket);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket berhasil diubah ke pending.');
    }

    public function resume(ResumeTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        DB::transaction(function () use ($request, $ticket): void {
            $ticket->pendingIntervals()
                ->whereNull('ended_at')
                ->latest('started_at')
                ->firstOrFail()
                ->update($request->validated());

            $ticket->update([
                'status' => Ticket::STATUS_OPEN,
            ]);
        });

        $this->logActivity('resume', 'Melanjutkan ticket '.$ticket->ticket_number, $ticket);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket berhasil dilanjutkan.');
    }

    public function close(CloseTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validated();

        $ticket->update([
            ...$validated,
            'status' => Ticket::STATUS_CLOSED,
            'closed_at' => $validated['finished_at'],
        ]);

        $this->logActivity('close', 'Menutup ticket '.$ticket->ticket_number, $ticket);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket berhasil ditutup.');
    }
}
