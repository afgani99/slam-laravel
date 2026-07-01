<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CloseTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        return $ticket && ! $ticket->isClosed() && ! $ticket->isPending();
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'finished_at' => ['required', 'date', 'after:'.$this->route('ticket')->started_at->format('Y-m-d H:i:s')],
            'rfo_action' => ['required', 'string'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'finished_at' => 'waktu selesai',
            'rfo_action' => 'RFO/action',
        ];
    }
}
