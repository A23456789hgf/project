<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return app('App\Http\Services\Project\ProjectValidator')->getUpdateValidationRules($this);
    }

    public function messages()
    {
        return app('App\Http\Services\Project\ProjectValidator')->getValidationMessages();
    }
}
