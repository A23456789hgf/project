<?php

namespace App\Http\Requests\Approval;

use App\Models\Project;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ApproveStepRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $project = $this->route('project');
        if (! $project instanceof Project) {
            $project = Project::find($project);
        }

        if (! $project) {
            return false;
        }

        return $this->user()?->can('approve', $project) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'notes' => 'nullable|string|max:2000',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,ppt,pptx,xls,xlsx,txt,csv|max:20480',
            'drop' => 'nullable|string',
            'entity_id' => 'nullable|integer|exists:internal_entities,id',
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'notes.max' => 'يجب ألا تتجاوز الملاحظات 2000 حرف.',
            'attachment.max' => 'يجب ألا يتجاوز حجم المرفق 20 ميجابايت.',
            'attachment.mimes' => 'نوع الملف المرفق غير مدعوم.',
        ];
    }
}
