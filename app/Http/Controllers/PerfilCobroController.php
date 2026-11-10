<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateDatosCobroRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class PerfilCobroController extends Controller
{
    /**
     * Actualiza la información bancaria y QR de cobro del usuario autenticado.
     */
    public function update(UpdateDatosCobroRequest $request): RedirectResponse
    {
        /** @var User $usuario */
        $usuario = auth()->user();

        $datos = [
            'alias_pago' => $request->input('alias_pago'),
            'cbu_pago' => $request->input('cbu_pago'),
        ];

        if ($request->hasFile('qr_pago')) {
            if ($usuario->qr_pago_path && Storage::disk('public')->exists($usuario->qr_pago_path)) {
                Storage::disk('public')->delete($usuario->qr_pago_path);
            }
            $datos['qr_pago_path'] = $request->file('qr_pago')->store('qrs', 'public');
        }

        $usuario->update($datos);

        return back()->with('exito', '¡Tus datos de cobro han sido actualizados exitosamente!');
    }
}
