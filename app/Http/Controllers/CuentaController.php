<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\User;
use App\Services\UserDataExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class CuentaController extends Controller
{
    /**
     * Muestra la vista de Mi Cuenta y Privacidad con los datos del usuario y validación de estado.
     */
    public function datos(): View
    {
        $usuario = auth()->user();
        $bloqueosCuenta = $usuario->validarBloqueosEliminacionCuenta();

        $planesOrganizados = $usuario->planes()->latest()->get();
        $planesParticipados = $usuario->planesParticipados()->latest()->get();

        $resumenActividad = [
            'activos' => $planesOrganizados->whereIn('estado', ['pendiente', 'propuesta', 'votando'])->count(),
            'completados' => $planesOrganizados->where('estado', 'confirmado')->count(),
            'participaciones' => $planesParticipados->count(),
        ];

        return view('perfil.cuenta', compact(
            'usuario',
            'bloqueosCuenta',
            'resumenActividad'
        ));
    }

    /**
     * Actualiza los datos de identificación personal del usuario (nombre y correo).
     */
    public function actualizarPerfil(Request $request): RedirectResponse
    {
        /** @var User $usuario */
        $usuario = auth()->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$usuario->id],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no debe superar los 255 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'Este correo electrónico ya está registrado por otro usuario.',
        ]);

        $usuario->update([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
        ]);

        return back()->with('exito', 'Tus datos personales se han actualizado correctamente.');
    }

    /**
     * Actualiza la contraseña del usuario tras verificar su credencial actual.
     */
    public function actualizarContrasena(Request $request): RedirectResponse
    {
        $request->validateWithBag('cambioContrasena', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Debes ingresar tu contraseña actual.',
            'current_password.current_password' => 'La contraseña actual no es correcta.',
            'password.required' => 'Debes ingresar la nueva contraseña.',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        /** @var User $usuario */
        $usuario = auth()->user();
        $usuario->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return back()->with('exito', 'Tu contraseña ha sido actualizada exitosamente.');
    }

    /**
     * Genera la descarga del extracto estructurado de datos en formato JSON o CSV.
     */
    public function exportar(Request $request, UserDataExportService $exportService): Response
    {
        $usuario = auth()->user();
        $formato = strtolower((string) $request->query('formato', 'json'));

        if ($formato === 'csv') {
            $csvContent = $exportService->exportarCsv($usuario);

            return response($csvContent, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="mis-datos-planazo.csv"',
            ]);
        }

        $jsonData = $exportService->exportarJson($usuario);

        return response(json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="mis-datos-planazo.json"',
        ]);
    }

    /**
     * Procesa la eliminación honesta de la cuenta tras verificar la contraseña actual y las reglas de dominio.
     */
    public function eliminarCuenta(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
        ], [
            'current_password.required' => 'Debes ingresar tu contraseña actual para confirmar la baja.',
            'current_password.current_password' => 'La contraseña ingresada no es correcta.',
        ]);

        $usuario = auth()->user();
        $bloqueos = $usuario->validarBloqueosEliminacionCuenta();

        if (! empty($bloqueos)) {
            // Telemetría sin PII (Clase 21/22)
            Evento::registrar('cuenta_eliminacion_bloqueada', [
                'motivos' => array_keys($bloqueos),
                'total_planes_abiertos' => count($bloqueos['planes_abiertos_organizados']['planes'] ?? []),
                'total_planes_con_deuda' => count($bloqueos['deudas_pendientes_gastos']['planes'] ?? []),
            ]);

            return redirect()->route('perfil.datos')
                ->with('error', 'No puedes eliminar tu cuenta mientras tengas planes abiertos organizados o deudas pendientes.')
                ->with('bloqueos_cuenta', $bloqueos);
        }

        // Telemetría de éxito sin PII
        Evento::registrar('cuenta_eliminada', [
            'planes_organizados_total' => $usuario->planes()->count(),
            'participaciones_total' => $usuario->planesParticipados()->count(),
        ]);

        $usuario->eliminarCuenta();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('exito', 'Tu cuenta ha sido eliminada correctamente de Planazo.');
    }
}
