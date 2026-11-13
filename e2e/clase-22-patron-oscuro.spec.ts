import { test, expect } from '@playwright/test';
import fs from 'node:fs';

test.describe('Clase 22 — Patrón Oscuro: Preselección por Defecto en Gastos', () => {
    test('Captura estado DESPUÉS con casillas desmarcadas por omisión (opt-in consciente)', async ({ browser }) => {
        fs.mkdirSync('docs/evidencias/clase-22', { recursive: true });

        // 1. Lucas crea el plan
        const ctxLucas = await browser.newContext();
        const pageLucas = await ctxLucas.newPage();

        await pageLucas.goto('/ingresar');
        await pageLucas.getByLabel('Correo electrónico').fill('test@example.com');
        await pageLucas.getByLabel('Contraseña').fill('password');
        await pageLucas.getByRole('button', { name: 'Entrar' }).click();
        await expect(pageLucas).toHaveURL(/.*mis-planes/);

        const nombrePlan = `Plan Ética Corregido ${Date.now()}`;
        await pageLucas.getByRole('button', { name: '+ Crear plan' }).click();
        await pageLucas.getByLabel('Nombre del plan').fill(nombrePlan);
        await pageLucas.getByLabel('Fecha límite').fill('2026-12-31');
        await pageLucas.getByRole('button', { name: 'Guardar' }).click();

        await pageLucas.getByRole('link', { name: nombrePlan }).first().click();

        // 2. Invitar a Ana
        await pageLucas.getByRole('button', { name: '+ Invitar colaborador' }).click();
        await pageLucas.getByLabel('Correo electrónico del invitado').fill('usuario1@example.com');
        await pageLucas.getByRole('button', { name: 'Enviar invitación' }).click();

        // 3. Ana acepta la invitación
        const ctxAna = await browser.newContext();
        const pageAna = await ctxAna.newPage();
        await pageAna.goto('/ingresar');
        await pageAna.getByLabel('Correo electrónico').fill('usuario1@example.com');
        await pageAna.getByLabel('Contraseña').fill('password123');
        await pageAna.getByRole('button', { name: 'Entrar' }).click();

        await pageAna.goto(pageLucas.url());
        const btnAceptarAna = pageAna.getByRole('button', { name: /Aceptar invitación/i }).first();
        if (await btnAceptarAna.isVisible()) {
            await btnAceptarAna.click();
        }

        // 4. Lucas entra a Gastos y Liquidación
        await pageLucas.goto(`${pageLucas.url()}?tab=gastos`);

        const btnRegistrarGasto = pageLucas.getByRole('button', { name: /Registrar (nuevo )?gasto/i });
        await expect(btnRegistrarGasto).toBeVisible();
        await btnRegistrarGasto.click();

        const modalGasto = pageLucas.locator('#modal-crear-gasto');
        await expect(modalGasto).toBeVisible();

        // Verificar que los checkboxes vienen DESMARCADOS (checked === false)
        const checks = modalGasto.locator('input.check-participante');
        const count = await checks.count();
        expect(count).toBeGreaterThanOrEqual(2);

        for (let i = 0; i < count; i++) {
            const isChecked = await checks.nth(i).isChecked();
            console.log(`Participante ${i} marcado por omisión tras corrección: ${isChecked}`);
            expect(isChecked).toBe(false);
        }

        // Capturar evidencia DESPUÉS con casillas desmarcadas
        await pageLucas.waitForTimeout(600);
        await modalGasto.screenshot({ path: 'docs/evidencias/clase-22/02-despues-modal-desmarcado.png' });
        await pageLucas.screenshot({ path: 'docs/evidencias/clase-22/02-despues-pantalla-completa.png', fullPage: false });

        // Demostrar selección consciente deliberada de un solo participante
        await checks.nth(0).check();
        const contador = modalGasto.locator('#contador-seleccionados');
        await expect(contador).toHaveText('1');

        await modalGasto.screenshot({ path: 'docs/evidencias/clase-22/03-despues-seleccion-consciente.png' });
    });
});
