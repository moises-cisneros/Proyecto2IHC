import { test, expect } from '@playwright/test';
import fs from 'node:fs';

test.describe('Clase 21 — Analítica de Interacción (Dos eventos, un panel, una decisión)', () => {
    test('Simula el flujo humano de creación, confirmación de plan y validación del embudo en el panel de analítica', async ({
        page,
    }) => {
        test.setTimeout(60000);
        fs.mkdirSync('docs/evidencias/clase-21', { recursive: true });

        // 1. Iniciar sesión como usuario organizador
        await page.goto('/ingresar');
        await page.getByLabel('Correo electrónico').fill('usuario1@example.com');
        await page.getByLabel('Contraseña').fill('password123');
        await page.getByRole('button', { name: 'Entrar' }).click();
        await expect(page).toHaveURL(/.*mis-planes/);

        // 2. Disparar inicio de creación desde Mis Planes
        const timestamp = Date.now();
        const nombrePlan = `Plan Analitica IHC ${timestamp}`;

        // Al hacer clic en "+ Crear plan", se abre el modal y se dispara track('plan_creacion_iniciada', ...)
        await page.getByRole('button', { name: '+ Crear plan' }).click();
        await expect(page.getByLabel('Nombre del plan')).toBeVisible();

        // Completar formulario y guardar (dispara backend: plan_guardado)
        await page.getByLabel('Nombre del plan').fill(nombrePlan);
        await page.getByLabel('Fecha límite').fill('2026-12-30');
        await page.getByRole('button', { name: 'Guardar' }).click();

        // Verificar que el plan aparece en la vista
        await expect(page.getByText(nombrePlan).first()).toBeVisible();

        // 3. Confirmar el plan desde la tabla de planes
        const filaPlan = page.locator('tr', { hasText: nombrePlan });
        await filaPlan.getByRole('button', { name: 'Confirmar' }).click();

        // Diálogo accesible de confirmación
        const modalConfirmacion = page.locator('#dialog-confirm');
        if (await modalConfirmacion.isVisible()) {
            await page.getByRole('button', { name: 'Sí, confirmar' }).click();
        }

        await expect(page.getByText(/El plan ahora está confirmado/i)).toBeVisible();
        await page.screenshot({ path: 'docs/evidencias/clase-21/plan-confirmado.png' });

        // 4. Navegar al panel de analítica
        await page.goto('/analitica');
        await expect(page.getByRole('heading', { name: 'Analítica de Interacción' })).toBeVisible();

        // 5. Verificar contadores del embudo
        const contadorIniciados = page.locator('#contador-iniciados');
        const contadorGuardados = page.locator('#contador-guardados');
        const contadorConfirmados = page.locator('#contador-confirmados');

        await expect(contadorIniciados).toBeVisible();
        await expect(contadorGuardados).toBeVisible();
        await expect(contadorConfirmados).toBeVisible();

        const numIniciados = parseInt((await contadorIniciados.textContent()) || '0', 10);
        const numGuardados = parseInt((await contadorGuardados.textContent()) || '0', 10);
        const numConfirmados = parseInt((await contadorConfirmados.textContent()) || '0', 10);

        expect(numIniciados).toBeGreaterThanOrEqual(1);
        expect(numGuardados).toBeGreaterThanOrEqual(1);
        expect(numConfirmados).toBeGreaterThanOrEqual(1);

        // 6. Verificar reflexión de diseño y tabla de eventos recientes
        await expect(
            page.getByRole('heading', { name: '¿Qué decisión de diseño cambiaría si supiera esto?' })
        ).toBeVisible();
        await expect(page.getByText('plan_creacion_iniciada').first()).toBeVisible();
        await expect(page.getByText('plan_guardado').first()).toBeVisible();
        await expect(page.getByText('plan_confirmado').first()).toBeVisible();

        // Capturar evidencia del panel
        await page.screenshot({ path: 'docs/evidencias/clase-21/panel-analitica.png' });
    });
});
