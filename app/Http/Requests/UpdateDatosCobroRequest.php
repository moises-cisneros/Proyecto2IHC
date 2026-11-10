<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDatosCobroRequest extends FormRequest
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
            'alias_pago' => ['nullable', 'string', 'max:100'],
            'cbu_pago' => ['nullable', 'string', 'max:50'],
            'qr_pago' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }

    /**
     * Mensajes descriptivos en español.
     */
    public function messages(): array
    {
        return [
            'alias_pago.max' => 'El alias no debe exceder los 100 caracteres.',
            'cbu_pago.max' => 'El CBU o número de cuenta no debe exceder los 50 caracteres.',
            'qr_pago.image' => 'El archivo de código QR debe ser una imagen válida.',
            'qr_pago.mimes' => 'El código QR debe estar en formato JPEG, PNG o WebP.',
            'qr_pago.max' => 'La imagen del código QR no debe superar los 2 MB.',
        ];
    }
}
