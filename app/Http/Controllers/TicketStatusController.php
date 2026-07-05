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

        $this->logActivity('pending', 'activity_logs.log_ticket_pending', $ticket, ['number' => $ticket->ticket_number]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', __('toasts.ticket_pending'));
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

        $this->logActivity('resume', 'activity_logs.log_ticket_resume', $ticket, ['number' => $ticket->ticket_number]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', __('toasts.ticket_resumed'));
    }

    public function close(CloseTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validated();

        $ticket->update([
            ...$validated,
            'status' => Ticket::STATUS_CLOSED,
            'closed_at' => $validated['finished_at'],
        ]);

        $this->logActivity('close', 'activity_logs.log_ticket_close', $ticket, ['number' => $ticket->ticket_number]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', __('toasts.ticket_closed'));
    }

    public function reopen(Ticket $ticket): RedirectResponse
    {
        if ($ticket->status !== Ticket::STATUS_CLOSED) {
            return back()->with('error', 'Only closed tickets can be reopened.');
        }

        $ticket->update([
            'status' => Ticket::STATUS_OPEN,
            'closed_at' => null,
            'finished_at' => null,
            'rfo_action' => null,
        ]);

        $this->logActivity('reopen', 'activity_logs.log_ticket_reopen', $ticket, ['number' => $ticket->ticket_number]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', __('toasts.ticket_reopened'));
    }
}
