<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'project_name' => 'required|string|max:255',
            'program' => 'nullable|string|max:255',
            'domain' => 'nullable|string|max:255',
            'subdomain' => 'nullable|string|max:255',
            'intervention' => 'nullable|string|max:255',
            'start_date_gregorian' => 'nullable|date',
            'start_date_hijri' => 'nullable|string|max:255',
            'end_date_gregorian' => 'nullable|date|after:start_date_gregorian',
            'end_date_hijri' => 'nullable|string|max:255',
            'number_of_beneficiaries' => 'nullable|integer|min:0',
        ];
    }
}
