<?php

namespace App\Http\Controllers;

use App\Models\TicketPendingInterval;
use App\Support\LogsActivity;
use Illuminate\Http\RedirectResponse;

class TicketPendingIntervalController extends Controller
{
    use LogsActivity;

    public function destroy(TicketPendingInterval $interval): RedirectResponse
    {
        $ticket = $interval->ticket;
        $this->logActivity('delete', 'activity_logs.log_delete_ticket_interval', $ticket, ['number' => $ticket->ticket_number]);
        $interval->delete();

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', __('toasts.ticket_pending_interval_deleted'));
    }
}
