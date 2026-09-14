<?php

namespace App\Http\Requests\Approval;

use App\Models\Project;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RecordConsultationRequest extends FormRequest
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

        return $this->user()?->can('refer', $project) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'referred_entity_id' => 'required|integer|exists:internal_entities,id',
            'referral_text' => 'required|string|min:10|max:3000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,ppt,pptx,xls,xlsx,txt,csv|max:20480',
            'drop' => 'nullable|string',
            'stage_id' => 'nullable|integer',
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
            'referred_entity_id.required' => 'يجب اختيار الجهة المحال إليها للاستشارة.',
            'referred_entity_id.exists' => 'الجهة المختارة غير موجودة.',
            'referral_text.required' => 'نص الاستشارة / الإحالة إلزامي.',
            'referral_text.min' => 'يجب أن يكون نص الاستشارة 10 أحرف على الأقل.',
            'referral_text.max' => 'يجب ألا يتجاوز نص الاستشارة 3000 حرف.',
            'attachments.*.max' => 'يجب ألا يتجاوز حجم المرفق 20 ميجابايت.',
            'attachments.*.mimes' => 'نوع الملف المرفق غير مدعوم.',
        ];
    }
}
