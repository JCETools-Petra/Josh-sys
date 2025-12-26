<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetRequest extends FormRequest
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
            'entries' => 'required|array|min:1',
            'entries.*.category_id' => 'required|exists:financial_categories,id',
            'entries.*.budget_value' => 'required|numeric|min:0',
            'mode' => 'nullable|in:replace,update',
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
            'entries.required' => 'Data budget harus diisi.',
            'entries.*.category_id.required' => 'Kategori harus dipilih.',
            'entries.*.category_id.exists' => 'Kategori tidak ditemukan.',
            'entries.*.budget_value.required' => 'Nilai budget harus diisi.',
            'entries.*.budget_value.numeric' => 'Nilai budget harus berupa angka.',
            'entries.*.budget_value.min' => 'Nilai budget tidak boleh negatif.',
        ];
    }
}
