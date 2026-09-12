<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GalleryPhotoRequest extends FormRequest
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
            'photo' => [$this->isMethod('post') ? 'required' : 'nullable', 'image', 'max:5120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'credit_photographer' => ['nullable', 'string', 'max:255'],
        ];
    }
}
