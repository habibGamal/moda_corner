<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only authenticated users can create orders
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'address_id' => [
                'required',
                'integer',
                // Ensure the address exists and belongs to the current user
                Rule::exists('addresses', 'id')->where(function ($query) {
                    $query->where('user_id', Auth::id());
                }),
            ],
            'payment_method' => [
                'required',
                'string',
                // Allow cash_on_delivery, card, and wallet payment methods
                Rule::in(['cash_on_delivery', 'card', 'wallet']),
            ],
            'coupon_code' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];

        // Add conditional validation based on payment method
        $paymentMethod = $this->input('payment_method');

        if ($paymentMethod === 'card') {
            $rules['card_number'] = [
                'required',
                'string',
                'min:13',
                'max:19',
                'regex:/^\d{13,19}$/',
            ];
            $rules['expiry_month'] = [
                'required',
                'string',
                'regex:/^(0[1-9]|1[0-2])$/',
            ];
            $rules['expiry_year'] = [
                'required',
                'string',
                'regex:/^\d{2}$/',
            ];
            $rules['security_code'] = [
                'required',
                'string',
                'min:3',
                'max:4',
                'regex:/^\d{3,4}$/',
            ];
            $rules['name_on_card'] = [
                'required',
                'string',
                'min:2',
                'max:50',
                'regex:/^[a-zA-Z\s]+$/',
            ];
        }

        if ($paymentMethod === 'wallet') {
            $rules['mobile_phone'] = 'required|string|regex:/^01[0-9]{9}$/';
        }

        return $rules;
    }

    /**
     * Get custom error messages for validation failures.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'card_number.required' => 'Card number is required for card payments.',
            'card_number.regex' => 'Card number must contain only digits and be 13-19 characters long.',
            'expiry_month.required' => 'Expiry month is required for card payments.',
            'expiry_month.regex' => 'Expiry month must be in MM format (01-12).',
            'expiry_year.required' => 'Expiry year is required for card payments.',
            'expiry_year.regex' => 'Expiry year must be in YY format.',
            'security_code.required' => 'Security code (CVV) is required for card payments.',
            'security_code.regex' => 'Security code must be 3 or 4 digits.',
            'name_on_card.required' => 'Name on card is required for card payments.',
            'name_on_card.regex' => 'Name on card must contain only letters and spaces.',
            'mobile_phone.required' => 'Mobile phone is required for wallet payments.',
            'mobile_phone.regex' => 'Mobile phone must be a valid Egyptian number (01xxxxxxxxx).',
        ];
    }
}
