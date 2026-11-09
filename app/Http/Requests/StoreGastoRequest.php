<?php

namespace App\Http\Requests;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreGastoRequest extends FormRequest
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
            'concepto'        => ['required', 'string', 'max:255'],
            'monto_total'     => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'tipo_division'   => ['required', 'in:equitativa,personalizada'],
            'participantes'   => ['required', 'array', 'min:1'],
            'participantes.*' => ['integer', 'exists:users,id'],
            'divisiones'      => ['nullable', 'array'],
            'divisiones.*'    => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Configuración del validador con lógica de coherencia de división y seguridad anti-IDOR.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            /** @var Plan|null $plan */
            $plan = $this->route('plan');
            if (is_numeric($plan)) {
                $plan = Plan::find($plan);
            }

            if (!$plan) {
                return;
            }

            // Obtener IDs de usuarios válidos del plan (creador + participantes aceptados)
            $participantesValidos = collect([$plan->user_id])
                ->merge($plan->participantes()->pluck('users.id'))
                ->merge(
                    $plan->invitaciones()->where('estado', 'aceptada')->pluck('user_id')
                )
                ->filter()
                ->unique()
                ->values();

            $participantesSeleccionados = collect($this->input('participantes', []))->map(fn ($id) => (int) $id);

            // Validar anti-IDOR: cada participante debe pertenecer al plan
            foreach ($participantesSeleccionados as $uid) {
                if (!$participantesValidos->contains($uid)) {
                    $validator->errors()->add('participantes', 'Uno o más participantes seleccionados no pertenecen a este plan.');
                    break;
                }
            }

            // Validación de división personalizada
            if ($this->input('tipo_division') === 'personalizada') {
                $montoTotal = round((float) $this->input('monto_total', 0), 2);
                $divisiones = $this->input('divisiones', []);

                $sumaDivisiones = 0.0;
                foreach ($participantesSeleccionados as $uid) {
                    $montoUser = isset($divisiones[$uid]) ? round((float) $divisiones[$uid], 2) : 0.0;
                    $sumaDivisiones += $montoUser;
                }
                $sumaDivisiones = round($sumaDivisiones, 2);

                if (abs($sumaDivisiones - $montoTotal) > 0.01) {
                    $validator->errors()->add(
                        'divisiones',
                        "La suma de los montos asignados (\${$sumaDivisiones}) debe coincidir exactamente con el monto total (\${$montoTotal})."
                    );
                }
            }
        });
    }

    /**
     * Mensajes descriptivos en español (Nielsen H5/H9).
     */
    public function messages(): array
    {
        return [
            'concepto.required'      => 'Debes ingresar el concepto o descripción del gasto.',
            'monto_total.required'   => 'El monto total es obligatorio.',
            'monto_total.min'        => 'El monto total debe ser mayor a cero.',
            'tipo_division.required' => 'Debes seleccionar el tipo de división (equitativa o personalizada).',
            'participantes.required' => 'Debes seleccionar al menos un participante para dividir el gasto.',
            'participantes.min'      => 'Debes seleccionar al menos un participante para dividir el gasto.',
        ];
    }
}
