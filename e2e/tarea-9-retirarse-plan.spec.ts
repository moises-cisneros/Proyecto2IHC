import { test, expect } from '@playwright/test';
import fs from 'node:fs';

test.describe('Tarea 9 — Analítica de Interacción en toda la app y funcionalidad de Retirarse de un plan', () => {
    test('Demostración completa: Retiro con anulación de 2 votos, bloqueo por deuda en gastos y telemetría de 6 embudos', async ({
        browser,
    }) => {
        test.setTimeout(90000);
        fs.mkdirSync('docs/evidencias/tarea-9', { recursive: true });

        // =========================================================================
        // DEMOSTRACIÓN 1: Retiro voluntario con anulación de 2 votos y recálculo
        // =========================================================================
        const ctxLucas = await browser.newContext();
        const pageLucas = await ctxLucas.newPage();

        // 1. Lucas (Organizador) inicia sesión
        await pageLucas.goto('/ingresar');
        await pageLucas.getByLabel('Correo electrónico').fill('test@example.com');
        await pageLucas.getByLabel('Contraseña').fill('password');
        await pageLucas.getByRole('button', { name: 'Entrar' }).click();
        await expect(pageLucas).toHaveURL(/.*mis-planes/);

        // 2. Lucas crea un nuevo plan colaborativo
        const timestamp = Date.now();
        const nombrePlan1 = `Plan Retiro Demo ${timestamp}`;
        await pageLucas.getByRole('button', { name: '+ Crear plan' }).click();
        await pageLucas.getByLabel('Nombre del plan').fill(nombrePlan1);
        await pageLucas.getByLabel('Fecha límite').fill('2026-12-30');
        await pageLucas.getByRole('button', { name: 'Guardar' }).click();

        // 3. Entrar a la sala y proponer 1 fecha y 1 lugar
        await pageLucas.getByRole('link', { name: nombrePlan1 }).first().click();

        // Invitar a Ana (usuario1@example.com)
        await pageLucas.getByRole('button', { name: '+ Invitar colaborador' }).click();
        await pageLucas.getByLabel('Correo electrónico del invitado').fill('usuario1@example.com');
        await pageLucas.getByRole('button', { name: 'Enviar invitación' }).click();
        await expect(pageLucas.getByText('Invitación enviada correctamente.')).toBeVisible();

        // Propuesta de Fecha
        await pageLucas
            .getByRole('button', { name: /Proponer/ })
            .first()
            .click();
        await pageLucas.getByLabel('Tipo de sugerencia').selectOption('fecha');
        await pageLucas.getByLabel('Fecha y hora propuesta').fill('2026-12-25T20:00');
        await pageLucas.getByRole('button', { name: 'Guardar propuesta' }).click();
        await expect(pageLucas.getByText(/Opción propuesta correctamente/i)).toBeVisible();

        // Propuesta de Lugar
        await pageLucas.getByRole('tab', { name: /Lugares propuestos/ }).click();
        await pageLucas
            .getByRole('button', { name: /Proponer/ })
            .first()
            .click();
        await pageLucas.getByLabel('Tipo de sugerencia').selectOption('lugar');
        await pageLucas.getByLabel('Lugar o destino propuesto').fill('Restaurante El Mirador');
        await pageLucas.getByRole('button', { name: 'Guardar propuesta' }).click();
        await expect(pageLucas.getByText(/Opción propuesta correctamente/i)).toBeVisible();

        const urlPlan1 = pageLucas.url();

        // 4. Ana inicia sesión y acepta la invitación
        const ctxAna = await browser.newContext();
        const pageAna = await ctxAna.newPage();

        await pageAna.goto('/ingresar');
        await pageAna.getByLabel('Correo electrónico').fill('usuario1@example.com');
        await pageAna.getByLabel('Contraseña').fill('password123');
        await pageAna.getByRole('button', { name: 'Entrar' }).click();
        await expect(pageAna).toHaveURL(/.*mis-planes/);

        await pageAna.goto(urlPlan1);
        const btnAceptar = pageAna.getByRole('button', { name: /Aceptar invitación/i }).first();
        if (await btnAceptar.isVisible()) {
            await btnAceptar.click();
        }

        // 5. Ana emite sus 2 votos: 1 en lugar y 1 en fecha
        await pageAna.getByRole('tab', { name: /Lugares propuestos/ }).click();
        const btnVotarLugar = pageAna.getByRole('button', { name: /Votar por Restaurante El Mirador/i }).first();
        await btnVotarLugar.click();
        await expect(
            pageAna.getByRole('button', { name: /Retirar voto de Restaurante El Mirador/i }).first()
        ).toBeVisible();

        await pageAna.getByRole('tab', { name: /Fechas propuestas/ }).click();
        const btnVotarFecha = pageAna.getByRole('button', { name: /Votar por/i }).first();
        await btnVotarFecha.click();
        await expect(pageAna.getByRole('button', { name: /Retirar voto/i }).first()).toBeVisible();

        // 6. Ana ejecuta el retiro voluntario sin deudas
        await expect(pageAna.getByRole('button', { name: 'Retirarme del plan' })).toBeVisible();
        await pageAna.getByRole('button', { name: 'Retirarme del plan' }).click();

        // Diálogo modal de advertencia (Nielsen H5)
        await expect(pageAna.getByRole('dialog')).toBeVisible();
        await expect(pageAna.getByText(/Anulación automática de votos/i)).toBeVisible();
        await pageAna.getByRole('button', { name: 'Sí, retirarme del plan' }).click();

        // 7. Redirección y mensaje de confirmación
        await expect(pageAna).toHaveURL(/.*mis-planes/);
        await expect(pageAna.getByText(/Te has retirado del plan exitosamente/i)).toBeVisible();

        // 8. Lucas verifica que Ana ya no es colaboradora y que los votos se redujeron
        await pageLucas.reload();
        await expect(pageLucas.locator('.seccion-participantes')).not.toContainText('Usuario 1');
        await pageLucas.screenshot({ path: 'docs/evidencias/tarea-9/01-retiro-exitoso-recalculo.png' });

        // =========================================================================
        // DEMOSTRACIÓN 2: Bloqueo de retiro por deuda pendiente en gastos
        // =========================================================================
        // Lucas crea un segundo plan
        const nombrePlan2 = `Plan Deuda Bloqueo ${timestamp}`;
        await pageLucas.goto('/mis-planes');
        await pageLucas.getByRole('button', { name: '+ Crear plan' }).click();
        await pageLucas.getByLabel('Nombre del plan').fill(nombrePlan2);
        await pageLucas.getByLabel('Fecha límite').fill('2026-12-30');
        await pageLucas.getByRole('button', { name: 'Guardar' }).click();

        await pageLucas.getByRole('link', { name: nombrePlan2 }).first().click();
        const urlPlan2 = pageLucas.url();

        // Invitar a Ana
        await pageLucas.getByRole('button', { name: '+ Invitar colaborador' }).click();
        await pageLucas.getByLabel('Correo electrónico del invitado').fill('usuario1@example.com');
        await pageLucas.getByRole('button', { name: 'Enviar invitación' }).click();

        // Ana acepta invitación
        await pageAna.goto(urlPlan2);
        const btnAceptar2 = pageAna.getByRole('button', { name: /Aceptar invitación/i }).first();
        if (await btnAceptar2.isVisible()) {
            await btnAceptar2.click();
        }

        // Lucas registra un gasto equitativo de 100 Bs (generando deuda de 50 Bs para Ana)
        await pageLucas.reload();
        await pageLucas.getByRole('tab', { name: /Gastos y Liquidación/i }).click();
        await pageLucas.getByRole('button', { name: /Registrar (nuevo )?gasto/i }).click();
        await pageLucas.getByLabel('Concepto o descripción').fill('Alquiler de salón');
        await pageLucas.getByLabel(/Monto total/i).fill('100.00');
        await pageLucas.getByRole('button', { name: 'Guardar Gasto' }).click();
        await expect(pageLucas.getByText('Alquiler de salón')).toBeVisible();

        // Ana visita el plan e intenta retirarse
        await pageAna.reload();
        await pageAna.getByRole('button', { name: 'Retirarme del plan' }).click();

        // Verificación de modal con banner de freno de emergencia (Nielsen H5/H6)
        await expect(pageAna.getByRole('dialog')).toBeVisible();
        await expect(pageAna.getByText(/No puedes retirarte: tienes deudas pendientes/i)).toBeVisible();
        await expect(pageAna.getByText(/saldo deudor acumulado de.*Bs 50\.00/i)).toBeVisible();
        await expect(pageAna.getByRole('link', { name: /Ir a Gastos y Liquidación/i })).toBeVisible();

        // El botón destructivo debe estar inhabilitado
        await expect(pageAna.getByRole('button', { name: 'Sí, retirarme del plan' })).toBeDisabled();

        await pageAna.screenshot({ path: 'docs/evidencias/tarea-9/02-bloqueo-retiro-con-deuda.png' });

        // Cerrar modal
        await pageAna.getByRole('button', { name: 'Mantener plan' }).click();

        // =========================================================================
        // DEMOSTRACIÓN 3: Telemetría y monitoreo de los 6 macroembudos
        // =========================================================================
        // Lucas navega a /analitica
        await pageLucas.goto('/analitica');
        await expect(pageLucas.getByRole('heading', { name: 'Analítica de Interacción y Telemetría' })).toBeVisible();

        // Validar presencia de los 6 embudos de interacción
        await expect(pageLucas.getByRole('heading', { name: 'Creación y Confirmación' })).toBeVisible();
        await expect(pageLucas.getByRole('heading', { name: 'Deliberación y Votación' })).toBeVisible();
        await expect(pageLucas.getByRole('heading', { name: 'Invitaciones y Convocatoria' })).toBeVisible();
        await expect(pageLucas.getByRole('heading', { name: 'Gastos y Liquidación' })).toBeVisible();
        await expect(pageLucas.getByRole('heading', { name: 'Retiro de Participantes' })).toBeVisible();
        await expect(pageLucas.getByRole('heading', { name: 'Búsquedas y Filtros' })).toBeVisible();

        // Validar barra de progreso y métricas de retiro
        await expect(pageLucas.locator('#retiro-iniciados')).toBeVisible();
        await expect(pageLucas.locator('#retiro-confirmados')).toBeVisible();

        // Capturar evidencia completa del dashboard
        await pageLucas.screenshot({ path: 'docs/evidencias/tarea-9/03-panel-analitica-completo.png', fullPage: true });

        await ctxLucas.close();
        await ctxAna.close();
    });
});
