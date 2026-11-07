<?php

namespace App\Http\Requests;

use App\Models\Opcion;
use App\Models\Plan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ConfirmarPlanRequest extends FormRequest
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
        /** @var Plan|null $plan */
        $plan = $this->route('plan');
        if (is_numeric($plan)) {
            $plan = Plan::find($plan);
        }

        $tieneEmpate = $plan && ($plan->tieneEmpateFechas() || $plan->tieneEmpateLugares());

        return [
            'propuesta_fecha_id' => ['required', 'integer', 'exists:opciones,id'],
            'propuesta_lugar_id' => ['nullable', 'integer', 'exists:opciones,id'],
            'motivo_desempate' => $tieneEmpate
                ? ['required', 'string', 'min:5', 'max:1000']
                : ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Custom validation logic to ensure selected options belong to the plan
     * and match the required election (specifically within tied options if applicable).
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            /** @var Plan|null $plan */
            $plan = $this->route('plan');
            if (is_numeric($plan)) {
                $plan = Plan::find($plan);
            }

            if (! $plan) {
                return;
            }

            if ($this->filled('propuesta_fecha_id')) {
                $opcionFecha = Opcion::find($this->propuesta_fecha_id);
                if (! $opcionFecha || ! $plan->validarPropuestaPertenece($opcionFecha, 'fecha')) {
                    $validator->errors()->add('propuesta_fecha_id', 'La fecha seleccionada no pertenece a este plan.');
                } elseif ($plan->tieneEmpateFechas()) {
                    $empatadas = $plan->obtenerPropuestasEmpatadas('fecha')->pluck('id');
                    if (! $empatadas->contains($opcionFecha->id)) {
                        $validator->errors()->add('propuesta_fecha_id', 'Debes seleccionar una de las fechas empatadas con mayor votación.');
                    }
                }
            }

            if ($this->filled('propuesta_lugar_id')) {
                $opcionLugar = Opcion::find($this->propuesta_lugar_id);
                if (! $opcionLugar || ! $plan->validarPropuestaPertenece($opcionLugar, 'lugar')) {
                    $validator->errors()->add('propuesta_lugar_id', 'El lugar seleccionado no pertenece a este plan.');
                } elseif ($plan->tieneEmpateLugares()) {
                    $empatadas = $plan->obtenerPropuestasEmpatadas('lugar')->pluck('id');
                    if (! $empatadas->contains($opcionLugar->id)) {
                        $validator->errors()->add('propuesta_lugar_id', 'Debes seleccionar uno de los lugares empatados con mayor votación.');
                    }
                }
            }
        });
    }

    /**
     * Mensajes descriptivos en español para prevención de errores (Nielsen H5 / H9).
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'propuesta_fecha_id.required' => 'Debes seleccionar una fecha confirmada para el plan.',
            'propuesta_fecha_id.exists' => 'La opción de fecha seleccionada no es válida.',
            'propuesta_lugar_id.exists' => 'La opción de lugar seleccionada no es válida.',
            'motivo_desempate.required' => 'Al existir un empate, es obligatorio registrar el motivo de tu decisión.',
            'motivo_desempate.min' => 'El motivo de desempate debe contener al menos 5 caracteres.',
        ];
    }
}
