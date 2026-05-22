<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BomUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // We can delegate authorization check to standard mechanisms
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'project_id' => 'required|integer|exists:projects,id',
            'version' => [
                'required',
                'string',
                'max:50',
                Rule::unique('bom_headers')->where(function ($query) {
                    return $query->where('project_id', $this->project_id);
                }),
            ],
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ];
    }

    /**
     * Get custom error messages for validator failures.
     */
    public function messages(): array
    {
        return [
            'version.unique' => 'This version has already been uploaded for the selected project.',
            'file.mimes' => 'The uploaded file must be of type: .xlsx, .xls, or .csv.',
        ];
    }
}
