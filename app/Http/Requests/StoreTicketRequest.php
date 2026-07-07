<?php

namespace App\Http\Requests;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'cid_id' => [
                'required',
                Rule::exists('cids', 'id')->where(function ($query) {
                    $query->where('is_dismantled', false);
                }),
            ],
            'vendor_ticket_number' => ['nullable', 'string', 'max:255'],
            'case_type' => ['required', Rule::in(Ticket::CASE_TYPES)],
            'started_at' => ['required', 'date'],
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
        ];
    }
}
