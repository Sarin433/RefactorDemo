<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'product_number'   => ['required', 'array', 'min:1'],
            'product_number.*' => ['required', 'string', 'exists:products,product_number'],
            'quantity'         => ['required', 'array', 'min:1'],
            'quantity.*'       => ['required', 'integer', 'min:1'],
        ];
    }
}
