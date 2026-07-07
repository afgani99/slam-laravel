<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCidRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'cid' => ['required', 'string', 'max:255', 'unique:cids,cid'],
            'cid_is' => ['nullable', 'string', 'max:255'],
            'vendor_name' => ['required', 'string', 'max:255'],
            'customer_name' => ['required', 'string', 'max:255'],
            'service' => ['required', 'string', 'max:255'],
            'sla_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_dismantled' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'cid' => 'CID',
            'vendor_name' => 'nama vendor',
            'customer_name' => 'nama pelanggan',
            'service' => 'service',
            'sla_percentage' => 'SLA target',
        ];
    }
}
