<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdateSaleRequest extends FormRequest
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
        // Get current sale items to calculate stock differences
        $sale = $this->route('sale');
        $currentItemQuantities = [];
        foreach ($sale->items as $item) {
            $currentItemQuantities[$item->product_id] = $item->quantity;
        }

        // Aggregate quantities by product from new items
        $newItemQuantities = [];
        foreach ($this->items ?? [] as $item) {
            $productId = $item['product_id'] ?? null;
            if ($productId) {
                if (!isset($newItemQuantities[$productId])) {
                    $newItemQuantities[$productId] = 0;
                }
                $newItemQuantities[$productId] += $item['quantity'] ?? 0;
            }
        }

        // Get all product IDs involved
        $allProductIds = array_unique(array_merge(
            array_keys($currentItemQuantities),
            array_keys($newItemQuantities)
        ));

        // Validate stock availability for each product
        foreach ($allProductIds as $productId) {
            $currentQty = $currentItemQuantities[$productId] ?? 0;
            $newQty = $newItemQuantities[$productId] ?? 0;
            $stockDiff = $newQty - $currentQty;

            // If we're reducing quantity, we need to check if we have enough "extra" stock
            // If we're increasing quantity, we need to check if we have enough total stock
            $product = \App\Models\Product::find($productId);
            if ($product && $product->stock < $newQty) {
                $validator->errors()->add(
                    'items',
                    "Stok produk '{$product->name}' hanya {$product->stock}, tidak cukup untuk {$newQty} yang diminta."
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