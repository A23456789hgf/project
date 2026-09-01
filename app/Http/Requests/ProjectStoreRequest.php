<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return app('App\Http\Services\Project\ProjectValidator')->getStoreValidationRules($this);
    }

    public function messages()
    {
        return app('App\Http\Services\Project\ProjectValidator')->getValidationMessages();
    }
}
