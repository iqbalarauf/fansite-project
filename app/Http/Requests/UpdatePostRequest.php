<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    public function authorize()
    {
        $post = $this->route('post');
        return $post && $this->user()->can('update', $post);
    }

    public function rules()
    {
        $postId = $this->route('post')?->id;
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
            'meta_title' => ['nullable','string','max:255'],
            'meta_description' => ['nullable','string','max:500'],
            'meta_keywords' => ['nullable','string','max:255'],
        ];
    }
}
