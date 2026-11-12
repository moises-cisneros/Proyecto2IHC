<?php

namespace App\Http\Requests;

use App\Models\Plan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePagoRequest extends FormRequest
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
            'receptor_id' => ['required', 'integer', 'exists:users,id'],
            'monto' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'comprobante' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
        ];
    }

    /**
     * Validador adicional de pertenencia y no auto-pago.
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

            $receptorId = (int) $this->input('receptor_id');
            $authId = (int) auth()->id();

            if ($receptorId === $authId) {
                $validator->errors()->add('receptor_id', 'No puedes registrar un pago hacia ti mismo.');
            }

            $participantesValidos = collect([$plan->user_id])
                ->merge($plan->participantes()->pluck('users.id'))
                ->merge(
                    $plan->invitaciones()->where('estado', 'aceptada')->pluck('user_id')
                )
                ->filter()
                ->unique()
                ->values();

            if (! $participantesValidos->contains($receptorId)) {
                $validator->errors()->add('receptor_id', 'El destinatario del pago no pertenece a este plan.');
            }

            // Validar que el monto no exceda la deuda neta calculada
            $monto = round((float) $this->input('monto', 0), 2);
            $saldos = $plan->calcularSaldosNetos();
            $deuda = collect($saldos['deudas'] ?? [])->first(function ($d) use ($authId, $receptorId) {
                return (int) $d['deudor_id'] === $authId && (int) $d['acreedor_id'] === $receptorId;
            });

            if ($deuda && $monto > ((float) $deuda['monto'] + 0.009)) {
                $validator->errors()->add(
                    'monto',
                    "El monto ingresado (Bs {$monto}) excede la deuda exigible de Bs {$deuda['monto']}."
                );
            }
        });
    }

    /**
     * Mensajes descriptivos en español.
     */
    public function messages(): array
    {
        return [
            'receptor_id.required' => 'Debes seleccionar a quién va dirigido el pago.',
            'receptor_id.exists' => 'El usuario seleccionado no es válido.',
            'monto.required' => 'Debes ingresar el monto a pagar.',
            'monto.min' => 'El monto debe ser mayor a cero.',
            'comprobante.image' => 'El comprobante debe ser una imagen válida.',
            'comprobante.mimes' => 'El comprobante debe estar en formato JPEG, PNG o WebP.',
            'comprobante.max' => 'El comprobante no debe superar los 4 MB.',
        ];
    }
}
