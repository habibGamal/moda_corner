<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestOrderReturnRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware and controller logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason' => 'required|string|min:10|max:500',
        ];
    }

    /**
     * Get the validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reason.required' => 'سبب الإرجاع مطلوب.',
            'reason.string' => 'سبب الإرجاع يجب أن يكون نص.',
            'reason.min' => 'سبب الإرجاع يجب أن يكون على الأقل 10 أحرف.',
            'reason.max' => 'سبب الإرجاع يجب ألا يتجاوز 500 حرف.',
        ];
    }
}
