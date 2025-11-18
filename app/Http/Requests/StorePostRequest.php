<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'title' => ['required','string','max:255'],
            'excerpt' => ['nullable','string','max:500'],
            'body' => ['required','string'],
            'featured_image' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
            'category' => ['nullable','string','max:100'],
            'tags' => ['nullable'],
            'tags.*' => ['string','max:50'],
            'seo_title' => ['nullable','string','max:255'],
            'seo_description' => ['nullable','string'],
            'status' => ['required','in:draft,published'],
            'published_at' => ['nullable','date'],
        ];
    }
}
