<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserOrderRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'spa_services_id' => 'required|exists:spa_services,id',
            'time_service' => 'required|string',
            'date_service' => 'required',
            'notes' => 'nullable|string|max:1000'
        ];
    }

    /**
     * Get custom validation error messages.
     */
    public function messages(): array
    {
        return [
            'spa_services_id.required' => 'Spa service selection is required',
            'spa_services_id.exists' => 'Selected spa service does not exist',
            'time_service.required' => 'Service time is required',
            'time_service.date_format' => 'Service time must be in HH:MM format (e.g., 14:30)',
            'date_service.required' => 'Service date is required',
            'date_service.after_or_equal' => 'Service date cannot be in the past',
            'notes.max' => 'Notes cannot exceed 1000 characters'
        ];
    }
}
