<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResumeTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        return $ticket?->isPending()
            && $ticket->pendingIntervals()->whereNull('ended_at')->exists();
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        $activePending = $this->route('ticket')
            ->pendingIntervals()
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        return [
            'ended_at' => ['required', 'date', 'after_or_equal:'.$activePending->started_at->format('Y-m-d H:i:s')],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'ended_at' => 'waktu lanjut',
        ];
    }
}
