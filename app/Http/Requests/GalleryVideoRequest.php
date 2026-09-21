<?php

namespace App\Http\Requests;

use App\Support\GalleryVideoEmbed;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class GalleryVideoRequest extends FormRequest
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
            'url' => ['required', 'url', 'max:500'],
            'title' => ['nullable', 'string', 'max:255'],
            'credit_account' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $url = (string) $this->input('url');

            if ($url !== '' && GalleryVideoEmbed::detect($url) === null) {
                $validator->errors()->add('url', 'URL harus dari YouTube, Twitter/X, atau TikTok.');
            }
        });
    }
}
