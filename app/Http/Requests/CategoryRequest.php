<?php

namespace App\Http\Requests;

use App\Enums\ContentSection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
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
        $type = $this->input('type', $this->route('type'));

        return [
            'type' => ['required', Rule::enum(ContentSection::class)],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('categories', 'slug')->where('type', $type)->ignore($this->route('id')),
            ],
        ];
    }
}
