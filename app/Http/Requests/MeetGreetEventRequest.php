<?php

namespace App\Http\Requests;

use App\Enums\MeetGreetEventType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MeetGreetEventRequest extends FormRequest
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
            'event_type' => ['required', Rule::enum(MeetGreetEventType::class)],
            'event_date' => ['required', 'date'],
            'event_date_2' => ['nullable', 'date', 'after_or_equal:event_date'],
            'ticket_sale_datetime' => ['nullable', 'date'],
            'purchase_link' => ['nullable', 'url', 'max:500'],
            'location' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function eventPayload(): array
    {
        $validated = $this->validated();

        if ($validated['event_type'] !== MeetGreetEventType::VideoCall->value) {
            $validated['event_date_2'] = null;
        }

        return $validated;
    }
}
