<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'category_id' => 'required|exists:categories,id',
        ];

        if (request()->hasFile('files')) {
            $rules['files'] = 'required|array';
            $rules['files.*'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:20480';
        } else {
            $rules['title'] = 'required|string|max:255';
            $rules['file'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:20480';
        }

        return $rules;
    }
}
