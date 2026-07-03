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
        $this->logActivity('delete', 'Menghapus interval pending ticket '.$ticket->ticket_number, $ticket);
        $interval->delete();

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Interval pending berhasil dihapus.');
    }
}
