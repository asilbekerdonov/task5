<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MovieIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'locale' => ['sometimes', 'string', 'in:ru_RU,en_US,uz_UZ'],
            'seed' => ['sometimes', 'integer', 'min:0', 'max:281474976710655'],
            'likes' => ['sometimes', 'numeric', 'min:0', 'max:10'],
            'reviews' => ['sometimes', 'numeric', 'min:0', 'max:10'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function toParameters(): array
    {
        return [
            'locale' => $this->input('locale', 'en_US'),
            'seed' => (int) $this->input('seed', 0),
            'likes' => (float) $this->input('likes', 0.0),
            'reviews' => (float) $this->input('reviews', 0.0),
            'page' => (int) $this->input('page', 1),
            'per_page' => (int) $this->input('per_page', 20),
        ];
    }
}