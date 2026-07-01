<?php

namespace App\Http\Requests;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ! $this->route('ticket')?->isClosed();
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'cid_id' => ['required', 'exists:cids,id'],
            'vendor_ticket_number' => ['nullable', 'string', 'max:255'],
            'case_type' => ['required', Rule::in(Ticket::CASE_TYPES)],
            'started_at' => ['required', 'date'],
            'finished_at' => ['nullable', 'date', 'after:started_at'],
            'rfo_action' => ['nullable', 'string'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'cid_id' => 'CID',
            'vendor_ticket_number' => 'ticket vendor',
            'case_type' => 'kasus',
            'started_at' => 'waktu mulai',
            'finished_at' => 'waktu selesai',
            'rfo_action' => 'RFO/action',
        ];
    }
}
