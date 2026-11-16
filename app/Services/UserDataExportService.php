<?php

namespace App\Services;

use App\Models\User;

class UserDataExportService
{
    /**
     * Construye la estructura normalizada de datos del usuario para exportación JSON.
     *
     * @return array<string, mixed>
     */
    public function exportarJson(User $user): array
    {
        $user->loadMissing([
            'planes',
            'planesParticipados',
            'invitaciones.plan',
            'votos.opcion.plan',
            'gastosPagados.plan',
            'gastoDivisiones.gasto.plan',
            'pagosRealizados.plan',
            'pagosRealizados.receptor',
            'pagosRecibidos.plan',
            'pagosRecibidos.deudor',
        ]);

        return [
            'version' => '1.0',
            'fecha_exportacion' => now()->toIso8601String(),
            'perfil' => [
                'id' => $user->id,
                'nombre' => $user->name,
                'email' => $user->email,
                'alias_pago' => $user->alias_pago,
                'cbu_pago' => $user->cbu_pago,
                'tiene_qr_pago' => ! empty($user->qr_pago_path),
                'creado_en' => $user->created_at?->toIso8601String(),
            ],
            'planes_organizados' => $user->planes->map(fn ($p) => [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'estado' => $p->estado,
                'fecha_limite' => $p->fecha_limite?->toDateString(),
                'fecha_confirmacion' => $p->fecha_confirmacion?->toIso8601String(),
                'creado_en' => $p->created_at?->toIso8601String(),
            ])->values()->all(),
            'planes_participados' => $user->planesParticipados->map(fn ($p) => [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'estado' => $p->estado,
                'organizador' => $p->organizador,
                'fecha_limite' => $p->fecha_limite?->toDateString(),
            ])->values()->all(),
            'invitaciones' => $user->invitaciones->map(fn ($i) => [
                'id' => $i->id,
                'plan_id' => $i->plan_id,
                'plan_nombre' => $i->plan?->nombre,
                'email' => $i->email,
                'estado' => $i->estado,
                'creado_en' => $i->created_at?->toIso8601String(),
            ])->values()->all(),
            'votos_emitidos' => $user->votos->map(fn ($v) => [
                'id' => $v->id,
                'plan_id' => $v->opcion?->plan_id,
                'plan_nombre' => $v->opcion?->plan?->nombre,
                'opcion_id' => $v->opcion_id,
                'tipo_opcion' => $v->opcion?->tipo,
                'valor_opcion' => $v->opcion?->valor,
                'creado_en' => $v->created_at?->toIso8601String(),
            ])->values()->all(),
            'liquidacion_gastos' => [
                'gastos_pagados' => $user->gastosPagados->map(fn ($g) => [
                    'id' => $g->id,
                    'plan_id' => $g->plan_id,
                    'plan_nombre' => $g->plan?->nombre,
                    'concepto' => $g->concepto,
                    'monto_total' => (float) $g->monto_total,
                    'tipo_division' => $g->tipo_division,
                    'creado_en' => $g->created_at?->toIso8601String(),
                ])->values()->all(),
                'divisiones_asignadas' => $user->gastoDivisiones->map(fn ($d) => [
                    'gasto_id' => $d->gasto_id,
                    'plan_id' => $d->gasto?->plan_id,
                    'plan_nombre' => $d->gasto?->plan?->nombre,
                    'concepto' => $d->gasto?->concepto,
                    'monto_asignado' => (float) $d->monto_asignado,
                ])->values()->all(),
                'pagos_realizados' => $user->pagosRealizados->map(fn ($p) => [
                    'id' => $p->id,
                    'plan_id' => $p->plan_id,
                    'plan_nombre' => $p->plan?->nombre,
                    'receptor' => $p->receptor?->name ?? 'Usuario',
                    'monto' => (float) $p->monto,
                    'estado' => $p->estado,
                    'creado_en' => $p->created_at?->toIso8601String(),
                ])->values()->all(),
                'pagos_recibidos' => $user->pagosRecibidos->map(fn ($p) => [
                    'id' => $p->id,
                    'plan_id' => $p->plan_id,
                    'plan_nombre' => $p->plan?->nombre,
                    'deudor' => $p->deudor?->name ?? 'Usuario',
                    'monto' => (float) $p->monto,
                    'estado' => $p->estado,
                    'creado_en' => $p->created_at?->toIso8601String(),
                ])->values()->all(),
            ],
        ];
    }

    /**
     * Construye un extracto estructurado y legible en formato CSV delimitado por comas.
     */
    public function exportarCsv(User $user): string
    {
        $data = $this->exportarJson($user);

        $fp = fopen('php://temp', 'r+');

        // Encabezado general
        fputcsv($fp, ['PLANAZO - PORTABILIDAD DE DATOS PERSONALES Y ACTIVIDAD']);
        fputcsv($fp, ['Fecha de exportacion', $data['fecha_exportacion']]);
        fputcsv($fp, []);

        // 1. Perfil
        fputcsv($fp, ['=== PERFIL DE USUARIO ===']);
        fputcsv($fp, ['ID', 'Nombre', 'Email', 'Alias de Pago', 'CBU de Pago', 'Tiene QR', 'Registrado En']);
        fputcsv($fp, [
            $data['perfil']['id'],
            $data['perfil']['nombre'],
            $data['perfil']['email'],
            $data['perfil']['alias_pago'] ?? 'Sin configurar',
            $data['perfil']['cbu_pago'] ?? 'Sin configurar',
            $data['perfil']['tiene_qr_pago'] ? 'Si' : 'No',
            $data['perfil']['creado_en'],
        ]);
        fputcsv($fp, []);

        // 2. Planes organizados
        fputcsv($fp, ['=== PLANES ORGANIZADOS ===']);
        fputcsv($fp, ['ID Plan', 'Nombre', 'Estado', 'Fecha Limite', 'Fecha Confirmacion', 'Creado En']);
        foreach ($data['planes_organizados'] as $p) {
            fputcsv($fp, [
                $p['id'],
                $p['nombre'],
                $p['estado'],
                $p['fecha_limite'] ?? 'Sin limite',
                $p['fecha_confirmacion'] ?? 'No confirmada',
                $p['creado_en'],
            ]);
        }
        if (empty($data['planes_organizados'])) {
            fputcsv($fp, ['(Sin planes organizados)']);
        }
        fputcsv($fp, []);

        // 3. Planes participados
        fputcsv($fp, ['=== PLANES EN LOS QUE PARTICIPA ===']);
        fputcsv($fp, ['ID Plan', 'Nombre', 'Estado', 'Organizador', 'Fecha Limite']);
        foreach ($data['planes_participados'] as $p) {
            fputcsv($fp, [
                $p['id'],
                $p['nombre'],
                $p['estado'],
                $p['organizador'],
                $p['fecha_limite'] ?? 'Sin limite',
            ]);
        }
        if (empty($data['planes_participados'])) {
            fputcsv($fp, ['(Sin participaciones en otros planes)']);
        }
        fputcsv($fp, []);

        // 4. Votos emitidos
        fputcsv($fp, ['=== VOTOS EMITIDOS ===']);
        fputcsv($fp, ['ID Voto', 'Plan', 'Tipo Opcion', 'Valor Votado', 'Emitido En']);
        foreach ($data['votos_emitidos'] as $v) {
            fputcsv($fp, [
                $v['id'],
                $v['plan_nombre'] ?? "Plan #{$v['plan_id']}",
                $v['tipo_opcion'],
                $v['valor_opcion'],
                $v['creado_en'],
            ]);
        }
        if (empty($data['votos_emitidos'])) {
            fputcsv($fp, ['(Sin votos registrados)']);
        }
        fputcsv($fp, []);

        // 5. Liquidación de gastos
        fputcsv($fp, ['=== GASTOS Y LIQUIDACION ===']);
        fputcsv($fp, ['Tipo Registro', 'Plan', 'Concepto / Contraparte', 'Monto (Bs)', 'Estado / Detalle', 'Fecha']);
        foreach ($data['liquidacion_gastos']['gastos_pagados'] as $g) {
            fputcsv($fp, [
                'Gasto Pagado',
                $g['plan_nombre'],
                $g['concepto'],
                number_format($g['monto_total'], 2),
                "Division: {$g['tipo_division']}",
                $g['creado_en'],
            ]);
        }
        foreach ($data['liquidacion_gastos']['divisiones_asignadas'] as $d) {
            fputcsv($fp, [
                'Consumo Asignado',
                $d['plan_nombre'],
                $d['concepto'],
                number_format($d['monto_asignado'], 2),
                'Porcion de gasto grupal',
                '-',
            ]);
        }
        foreach ($data['liquidacion_gastos']['pagos_realizados'] as $pr) {
            fputcsv($fp, [
                'Pago Enviado',
                $pr['plan_nombre'],
                "Acreedor: {$pr['receptor']}",
                number_format($pr['monto'], 2),
                "Estado: {$pr['estado']}",
                $pr['creado_en'],
            ]);
        }
        foreach ($data['liquidacion_gastos']['pagos_recibidos'] as $prec) {
            fputcsv($fp, [
                'Pago Recibido',
                $prec['plan_nombre'],
                "Deudor: {$prec['deudor']}",
                number_format($prec['monto'], 2),
                "Estado: {$prec['estado']}",
                $prec['creado_en'],
            ]);
        }

        rewind($fp);
        $csvContent = stream_get_contents($fp);
        fclose($fp);

        return $csvContent ?: '';
    }
}
