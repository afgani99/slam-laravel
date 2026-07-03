<?php

namespace App\Http\Controllers;

use App\Models\TicketPendingInterval;
use Illuminate\Http\RedirectResponse;

class TicketPendingIntervalController extends Controller
{
    public function destroy(TicketPendingInterval $interval): RedirectResponse
    {
        $ticket = $interval->ticket;
        $interval->delete();

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Interval pending berhasil dihapus.');
    }
}
