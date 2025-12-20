<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportBudgetRequest extends FormRequest
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
        $maxFileSize = config('hotelier.import.max_file_size');
        $allowedExtensions = implode(',', config('hotelier.import.allowed_extensions'));

        return [
            'year' => [
                'required',
                'integer',
                'min:' . config('hotelier.validation.year_range.min'),
                'max:' . config('hotelier.validation.year_range.max'),
            ],
            'file' => "required|file|mimes:{$allowedExtensions}|max:{$maxFileSize}",
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
            'file.required' => 'File Excel harus diupload.',
            'file.file' => 'File tidak valid.',
            'file.mimes' => 'File harus berformat Excel (.xlsx atau .xls).',
            'file.max' => 'Ukuran file maksimal ' . (config('hotelier.import.max_file_size') / 1024) . ' MB.',
        ];
    }
}
