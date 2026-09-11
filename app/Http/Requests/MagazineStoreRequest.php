<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MagazineStoreRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:magazines,slug'],
            'description' => ['nullable', 'string'],
            'cover' => ['nullable', 'image', 'max:3072'],
            'file' => ['required', 'mimes:pdf', 'max:50000'],
            'is_main' => ['boolean'],
        ];
    }
}
