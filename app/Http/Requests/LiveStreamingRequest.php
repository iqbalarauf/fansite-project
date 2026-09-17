<?php

namespace App\Http\Requests;

use App\Enums\LiveStreamingPlatform;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LiveStreamingRequest extends FormRequest
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
            'live_id' => ['nullable', 'string', 'max:255', Rule::unique('live_streaming', 'live_id')->ignore($this->route('liveStreaming')?->id)],
            'platform' => ['required', Rule::enum(LiveStreamingPlatform::class)],
            'live_date' => ['required', 'date'],
            'duration' => ['nullable', 'integer', 'min:0'],
            'additional_info' => ['nullable', 'string'],
        ];
    }
}
