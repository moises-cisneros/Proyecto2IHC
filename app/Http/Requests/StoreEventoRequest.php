<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreEventoRequest extends FormRequest
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
            'evento' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/'],
            'props' => ['nullable', 'array'],
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'evento.required' => 'El identificador del evento es obligatorio.',
            'evento.regex' => 'El evento debe estar en minúsculas y formato snake_case sin espacios.',
            'props.array' => 'Las propiedades deben ser un arreglo o diccionario asociativo válido.',
        ];
    }
}
