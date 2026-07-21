<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDebtPaymentRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Jumlah pembayaran wajib diisi.',
            'amount.numeric' => 'Jumlah pembayaran harus berupa angka.',
            'amount.min' => 'Jumlah pembayaran minimal 1.',
            'payment_date.required' => 'Tanggal pembayaran wajib diisi.',
            'payment_date.date' => 'Tanggal pembayaran tidak valid.',
        ];
    }
}