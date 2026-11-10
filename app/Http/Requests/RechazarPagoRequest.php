<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RechazarPagoRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'motivo_rechazo' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    /**
     * Mensajes descriptivos en español.
     */
    public function messages(): array
    {
        return [
            'motivo_rechazo.required' => 'Para rechazar un pago debes indicar obligatoriamente un motivo.',
            'motivo_rechazo.min' => 'El motivo de rechazo debe tener al menos 5 caracteres.',
            'motivo_rechazo.max' => 'El motivo de rechazo no puede exceder los 1000 caracteres.',
        ];
    }
}
