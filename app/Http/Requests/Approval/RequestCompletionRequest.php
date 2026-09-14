<?php

namespace App\Http\Requests\Approval;

use App\Models\Project;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RequestCompletionRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->has('reason') && $this->filled('notes')) {
            $this->merge(['reason' => $this->input('notes')]);
        }
        if (! $this->has('notes') && $this->filled('reason')) {
            $this->merge(['notes' => $this->input('reason')]);
        }

        // Default to creator_entity if not provided, for backwards compatibility
        if (! $this->filled('return_target')) {
            $this->merge(['return_target' => 'creator_entity']);
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

        return $this->user()?->can('requestAction', $project) ?? false;
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
            'return_target' => 'required|string|in:creator_entity,previous_step',
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
            'reason.required' => 'سبب طلب الاستكمال إلزامي.',
            'reason.min' => 'يجب أن يكون سبب طلب الاستكمال 10 أحرف على الأقل.',
            'reason.max' => 'يجب ألا تتجاوز الملاحظات 2000 حرف.',
            'return_target.required' => 'جهة الإرجاع إلزامية.',
            'return_target.in' => 'جهة الإرجاع يجب أن تكون إما creator_entity أو previous_step.',
            'attachment.max' => 'يجب ألا يتجاوز حجم المرفق 20 ميجابايت.',
            'attachment.mimes' => 'نوع الملف المرفق غير مدعوم.',
        ];
    }
}
