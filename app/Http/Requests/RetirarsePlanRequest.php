<?php

namespace App\Http\Requests;

use App\Models\Plan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RetirarsePlanRequest extends FormRequest
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
            'motivo' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            /** @var Plan|null $plan */
            $plan = $this->route('plan');
            if (is_numeric($plan)) {
                $plan = Plan::find($plan);
            }

            if ($plan && $plan->estado !== 'pendiente') {
                $validator->errors()->add('plan', 'Solo puedes retirarte de un plan que se encuentre en estado pendiente.');
            }
        });
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'motivo.string' => 'El motivo debe ser una cadena de texto.',
            'motivo.max' => 'El motivo no puede exceder los 500 caracteres.',
        ];
    }
}
