<?php

namespace App\Http\Requests;

use App\Enums\ConcertStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConcertEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'event_name' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date'],
            'location' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::enum(ConcertStatus::class)],
            'purchase_link' => ['nullable', 'url', 'max:500'],
        ];
    }
}
