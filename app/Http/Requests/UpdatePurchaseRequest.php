<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|mixed|string>
     */
    public function rules(): array
    {
        return [
            'invoice_number' => ['nullable', 'string', 'max:100', 'unique:purchases,invoice_number,'.$this->route('purchase')->id],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.buy_price' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'items.min' => 'Setidaknya harus ada satu item dalam transaksi.',
            'items.*.quantity.min' => 'Quantity harus minimal 1.',
            'items.*.quantity.integer' => 'Quantity harus berupa bilangan bulat.',
            'items.*.buy_price.min' => 'Harga beli tidak boleh negatif.',
        ];
    }
}