<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'fecha_limite' => ['required', 'date', 'after_or_equal:today'],
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
            'nombre.required' => 'Escribe el nombre del plan.',
            'nombre.max' => 'El nombre no puede superar los 255 caracteres.',
            'fecha_limite.required' => 'Elige la fecha límite.',
            'fecha_limite.date' => 'La fecha no es válida.',
            'fecha_limite.after_or_equal' => 'La fecha del plan debe ser igual o posterior a la fecha actual.',
        ];
    }
}
