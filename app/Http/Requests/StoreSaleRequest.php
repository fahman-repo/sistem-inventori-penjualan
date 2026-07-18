<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreSaleRequest extends FormRequest
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
            'sale_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.sell_price' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     */
    protected function failedValidation(Validator $validator)
    {
        // Aggregate quantities by product (handle duplicate product entries)
        $productQuantities = [];
        foreach ($this->items ?? [] as $item) {
            $productId = $item['product_id'] ?? null;
            if ($productId) {
                if (!isset($productQuantities[$productId])) {
                    $productQuantities[$productId] = 0;
                }
                $productQuantities[$productId] += $item['quantity'] ?? 0;
            }
        }

        // Validate stock availability for each product
        foreach ($productQuantities as $productId => $totalQuantity) {
            $product = \App\Models\Product::find($productId);
            if ($product && $product->stock < $totalQuantity) {
                $validator->errors()->add(
                    'items',
                    "Stok produk '{$product->name}' hanya {$product->stock}, tidak cukup untuk {$totalQuantity} yang diminta."
                );
            }
        }

        throw new ValidationException($validator);
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
            'items.*.sell_price.min' => 'Harga jual tidak boleh negatif.',
        ];
    }
}