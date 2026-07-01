<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Str;

class TicketNumberService
{
    public function generate(): string
    {
        do {
            $ticketNumber = 'nusa-'.now()->format('dmy').Str::upper(Str::random(3));
        } while (Ticket::where('ticket_number', $ticketNumber)->exists());

        return $ticketNumber;
    }
}
