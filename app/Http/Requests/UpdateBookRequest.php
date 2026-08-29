<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $bookId = $this->route('book')?->id;

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'author' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'isbn' => ['nullable', 'string', 'unique:books,isbn,' . $bookId],
            'publication_date' => ['nullable', 'date'],
            'available_copies' => ['sometimes', 'required', 'integer', 'min:0'],
            'cover' => ['nullable', 'image', 'max:2048'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
