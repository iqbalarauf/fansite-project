<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MagazineUpdateRequest extends FormRequest
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
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('magazines', 'slug')->ignore($this->route('magazine')),
            ],
            'description' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array{slug: string, description: string|null}
     */
    public function magazinePayload(): array
    {
        $validated = $this->validated();

        return [
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
        ];
    }
}
