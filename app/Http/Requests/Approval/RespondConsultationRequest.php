<?php

namespace App\Http\Requests\Approval;

use App\Models\ProjectReferral;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RespondConsultationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $referral = $this->route('referral');
        if (! $referral instanceof ProjectReferral) {
            $referral = ProjectReferral::find($referral);
        }

        if (! $referral) {
            return false;
        }

        return $this->user()?->can('respond', $referral) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'response_text' => 'required|string|min:10|max:3000',
            'status' => 'required|in:responded,returned',
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,ppt,pptx,xls,xlsx,txt,csv|max:20480',
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
            'response_text.required' => 'نص الرد أو الإفادة إلزامي.',
            'response_text.min' => 'يجب أن يكون نص الرد 10 أحرف على الأقل.',
            'response_text.max' => 'يجب ألا يتجاوز نص الرد 3000 حرف.',
            'status.required' => 'حالة الإجراء المتخذ إلزامية.',
            'status.in' => 'الحالة المحددة غير صالحة.',
            'attachments.*.max' => 'يجب ألا يتجاوز حجم المرفق 20 ميجابايت.',
            'attachments.*.mimes' => 'نوع الملف المرفق غير مدعوم.',
        ];
    }
}
