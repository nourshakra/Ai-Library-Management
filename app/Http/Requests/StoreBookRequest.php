<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'isbn' => ['nullable', 'string', 'unique:books,isbn'],
            'publication_date' => ['nullable', 'date'],
            'available_copies' => ['required', 'integer', 'min:0'],
            'cover' => ['nullable', 'image', 'max:2048'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
