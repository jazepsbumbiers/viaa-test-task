<?php

namespace App\Http\Requests\Book;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'title'                 => ['required', 'string', 'max:255', 'unique:books,title'],
            'type'                  => ['required', 'integer'],
            'category'              => ['required', 'integer'],
            'manufacturer'          => ['required', 'integer'],
            'summary'               => ['required', 'string', 'max:3000'],
            'price'                 => ['required', 'numeric'],
            'in_stock'              => ['required', 'boolean'],
            'photos'                => ['array'],
            'photos.*.name'         => ['required', 'string', 'max:255'],
            'photos.*.caption'      => ['nullable', 'string'],
            'photos.*.size'         => ['required', 'integer'],
        ];
    }
}
