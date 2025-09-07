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
        return [
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
                // Allow both cash_on_delivery and kashier payment methods
                Rule::in(['cash_on_delivery', 'kashier']),
            ],
            'coupon_code' => 'nullable|string|max:255', // Add more specific validation if coupons are implemented (e.g., exists:coupons,code)
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
