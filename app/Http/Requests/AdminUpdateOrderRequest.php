<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateOrderRequest extends FormRequest
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
            'user_id' => 'sometimes|exists:users,id',
            'spa_services_id' => 'sometimes|exists:spa_services,id',
            'time_service' => 'sometimes|string|date_format:H:i',
            'date_service' => 'sometimes|date|after_or_equal:today',
            'notes' => 'nullable|string|max:1000',
            'status' => 'sometimes|string|in:pending,confirmed,in_progress,completed,cancelled,rejected'
        ];
    }

    /**
     * Get custom validation error messages.
     */
    public function messages(): array
    {
        return [
            'user_id.exists' => 'Selected user does not exist',
            'spa_services_id.exists' => 'Selected spa service does not exist',
            'time_service.date_format' => 'Service time must be in HH:MM format (e.g., 14:30)',
            'date_service.after_or_equal' => 'Service date cannot be in the past',
            'notes.max' => 'Notes cannot exceed 1000 characters',
            'status.in' => 'Invalid status value'
        ];
    }
}
