<?php

namespace App\Http\Requests\Approval;

use App\Models\Project;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RejectStepRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->has('reason') && $this->filled('rejection_reason')) {
            $this->merge(['reason' => $this->input('rejection_reason')]);
        }
        if (! $this->has('reason') && $this->filled('notes')) {
            $this->merge(['reason' => $this->input('notes')]);
        }
        if (! $this->has('notes') && $this->filled('reason')) {
            $this->merge(['notes' => $this->input('reason')]);
        }
    }

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

        return $this->user()?->can('reject', $project) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason' => 'required|string|min:10|max:2000',
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
            'reason.required' => 'سبب الرفض إلزامي.',
            'reason.min' => 'يجب أن يكون سبب الرفض 10 أحرف على الأقل.',
            'reason.max' => 'يجب ألا يتجاوز سبب الرفض 2000 حرف.',
            'attachment.max' => 'يجب ألا يتجاوز حجم المرفق 20 ميجابايت.',
            'attachment.mimes' => 'نوع الملف المرفق غير مدعوم.',
        ];
    }
}
