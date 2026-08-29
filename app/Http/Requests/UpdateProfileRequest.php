<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'interests' => ['nullable', 'array'],
            'favorite_topics' => ['nullable', 'array'],
            'preferred_categories' => ['nullable', 'array'],
            'skills' => ['nullable', 'array'],
            'learning_goals' => ['nullable', 'string'],
        ];
    }
}
