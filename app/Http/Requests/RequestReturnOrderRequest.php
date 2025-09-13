<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestReturnOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:1000'],
            'return_items' => ['required', 'array', 'min:1'],
            'return_items.*.order_item_id' => ['required', 'integer', 'exists:order_items,id'],
            'return_items.*.quantity' => ['required', 'integer', 'min:1'],
            'return_items.*.reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'reason.required' => 'Please provide a reason for the return.',
            'reason.max' => 'The return reason cannot exceed 1000 characters.',
            'return_items.required' => 'Please select at least one item to return.',
            'return_items.min' => 'Please select at least one item to return.',
            'return_items.*.order_item_id.required' => 'Order item ID is required.',
            'return_items.*.order_item_id.exists' => 'The selected order item does not exist.',
            'return_items.*.quantity.required' => 'Quantity is required for each item.',
            'return_items.*.quantity.min' => 'Quantity must be at least 1.',
            'return_items.*.reason.max' => 'Item return reason cannot exceed 500 characters.',
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'reason' => 'return reason',
            'return_items' => 'return items',
            'return_items.*.order_item_id' => 'order item',
            'return_items.*.quantity' => 'quantity',
            'return_items.*.reason' => 'item return reason',
        ];
    }
}
