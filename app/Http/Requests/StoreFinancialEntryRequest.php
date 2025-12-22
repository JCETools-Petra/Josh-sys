<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFinancialEntryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('manage-data');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'year' => [
                'required',
                'integer',
                'min:' . config('hotelier.validation.year_range.min'),
                'max:' . config('hotelier.validation.year_range.max'),
            ],
            'month' => [
                'required',
                'integer',
                'min:' . config('hotelier.validation.month_range.min'),
                'max:' . config('hotelier.validation.month_range.max'),
            ],
            'entries' => 'required|array|min:1',
            'entries.*.category_id' => 'required|exists:financial_categories,id',
            'entries.*.actual_value' => 'required|numeric|min:0',
            'entries.*.budget_value' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'year.required' => 'Tahun harus diisi.',
            'year.min' => 'Tahun tidak valid.',
            'year.max' => 'Tahun tidak valid.',
            'month.required' => 'Bulan harus diisi.',
            'month.min' => 'Bulan harus antara 1-12.',
            'month.max' => 'Bulan harus antara 1-12.',
            'entries.required' => 'Data entries harus diisi.',
            'entries.*.category_id.required' => 'Kategori harus dipilih.',
            'entries.*.category_id.exists' => 'Kategori tidak ditemukan.',
            'entries.*.actual_value.required' => 'Nilai actual harus diisi.',
            'entries.*.actual_value.numeric' => 'Nilai actual harus berupa angka.',
            'entries.*.actual_value.min' => 'Nilai actual tidak boleh negatif.',
        ];
    }
}
