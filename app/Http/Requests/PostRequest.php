<?php

namespace App\Http\Requests;

use App\Enums\ContentSection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
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
        $section = $this->section();

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique($section->table(), 'slug')->ignore($this->route('id')),
            ],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where('type', $section->value),
            ],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'cover' => ['nullable', 'image', 'max:3072'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'is_featured' => ['boolean'],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'image', 'max:3072'],
        ];
    }

    public function section(): ContentSection
    {
        return ContentSection::from((string) $this->route('section'));
    }
}
