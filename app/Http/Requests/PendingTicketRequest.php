<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PendingTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('ticket')?->isOpen() ?? false;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'started_at' => ['required', 'date', 'after_or_equal:'.$this->route('ticket')->started_at->format('Y-m-d H:i:s')],
            'note' => ['nullable', 'string'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'started_at' => 'waktu mulai pending',
            'note' => 'catatan',
        ];
    }
}
