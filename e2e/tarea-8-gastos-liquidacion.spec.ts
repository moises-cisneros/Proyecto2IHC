import { test, expect } from '@playwright/test';
import fs from 'node:fs';

test.describe('Tarea 8 — Proceso de Gastos y Liquidación con Estados y Consecuencias', () => {
    test('Como grupo de 3 participantes, registramos 2 gastos con división personalizada y liquidamos un pago pendiente confirmándolo', async ({
        browser,
    }) => {
        test.setTimeout(90000);
        fs.mkdirSync('docs/evidencias/tarea-8', { recursive: true });

        // -------------------------------------------------------------
        // FASE 1: Lucas (test@example.com) crea el plan e invita a Ana y Carlos
        // -------------------------------------------------------------
        const ctxLucas = await browser.newContext();
        const pageLucas = await ctxLucas.newPage();

        await pageLucas.goto('/ingresar');
        await pageLucas.getByLabel('Correo electrónico').fill('test@example.com');
        await pageLucas.getByLabel('Contraseña').fill('password');
        await pageLucas.getByRole('button', { name: 'Entrar' }).click();
        await expect(pageLucas).toHaveURL(/.*mis-planes/);

        const nombrePlan = `Plan Gastos Vacaciones ${Date.now()}`;
        await pageLucas.getByRole('button', { name: '+ Crear plan' }).click();
        await pageLucas.getByLabel('Nombre del plan').fill(nombrePlan);
        await pageLucas.getByLabel('Fecha límite').fill('2026-12-31');
        await pageLucas.getByRole('button', { name: 'Guardar' }).click();

        await pageLucas.getByRole('link', { name: nombrePlan }).first().click();

        // Invitar a Ana (usuario1@example.com)
        await pageLucas.getByRole('button', { name: '+ Invitar colaborador' }).click();
        await pageLucas.getByLabel('Correo electrónico del invitado').fill('usuario1@example.com');
        await pageLucas.getByRole('button', { name: 'Enviar invitación' }).click();

        // Invitar a Carlos (usuario2@example.com)
        await pageLucas.getByRole('button', { name: '+ Invitar colaborador' }).click();
        await pageLucas.getByLabel('Correo electrónico del invitado').fill('usuario2@example.com');
        await pageLucas.getByRole('button', { name: 'Enviar invitación' }).click();

        // -------------------------------------------------------------
        // FASE 2: Ana y Carlos aceptan las invitaciones
        // -------------------------------------------------------------
        const ctxAna = await browser.newContext();
        const pageAna = await ctxAna.newPage();
        await pageAna.goto('/ingresar');
        await pageAna.getByLabel('Correo electrónico').fill('usuario1@example.com');
        await pageAna.getByLabel('Contraseña').fill('password123');
        await pageAna.getByRole('button', { name: 'Entrar' }).click();

        // Ana va al plan y acepta la invitación
        await pageAna.goto(pageLucas.url());
        const btnAceptarAna = pageAna.getByRole('button', { name: /Aceptar invitación/i }).first();
        if (await btnAceptarAna.isVisible()) {
            await btnAceptarAna.click();
        }

        const ctxCarlos = await browser.newContext();
        const pageCarlos = await ctxCarlos.newPage();
        await pageCarlos.goto('/ingresar');
        await pageCarlos.getByLabel('Correo electrónico').fill('usuario2@example.com');
        await pageCarlos.getByLabel('Contraseña').fill('password123');
        await pageCarlos.getByRole('button', { name: 'Entrar' }).click();

        // Carlos va al plan y acepta la invitación
        await pageCarlos.goto(pageLucas.url());
        const btnAceptarCarlos = pageCarlos.getByRole('button', { name: /Aceptar invitación/i }).first();
        if (await btnAceptarCarlos.isVisible()) {
            await btnAceptarCarlos.click();
        }

        // -------------------------------------------------------------
        // FASE 3: Lucas registra Gasto 1: Cena de bienvenida ($60 equitativo)
        // -------------------------------------------------------------
        await pageLucas.reload();
        await pageLucas.getByRole('tab', { name: /Gastos y Liquidación/i }).click();

        await pageLucas.getByRole('button', { name: /Registrar (nuevo )?gasto/i }).click();
        await pageLucas.getByLabel('Concepto o descripción').fill('Cena de bienvenida');
        await pageLucas.getByLabel(/Monto total/i).fill('60.00');
        await pageLucas.getByRole('button', { name: 'Guardar Gasto' }).click();

        await expect(pageLucas.getByText('Cena de bienvenida')).toBeVisible();
        await pageLucas.screenshot({ path: 'docs/evidencias/tarea-8/01-registro-gasto.png', fullPage: true });

        // -------------------------------------------------------------
        // FASE 4: Ana registra Gasto 2: Transporte compartido ($45 división personalizada)
        // Lucas: $15, Ana: $10, Carlos: $20 (Suma = 45)
        // -------------------------------------------------------------
        await pageAna.reload();
        await pageAna.getByRole('tab', { name: /Gastos y Liquidación/i }).click();

        await pageAna.getByRole('button', { name: /Registrar (nuevo )?gasto/i }).click();
        await pageAna.getByLabel('Concepto o descripción').fill('Transporte compartido');
        await pageAna.getByLabel(/Monto total/i).fill('45.00');
        await pageAna.locator('#select-tipo-division').selectOption('personalizada');

        // Llenar montos individuales personalizados
        const inputsPersonalizados = pageAna.locator('.input-monto-indiv');
        const count = await inputsPersonalizados.count();
        if (count >= 3) {
            await inputsPersonalizados.nth(0).fill('15.00');
            await inputsPersonalizados.nth(1).fill('10.00');
            await inputsPersonalizados.nth(2).fill('20.00');
        }

        await pageAna.screenshot({ path: 'docs/evidencias/tarea-8/02-division-personalizada.png' });
        await pageAna.getByRole('button', { name: 'Guardar Gasto' }).click();

        await expect(pageAna.getByText('Transporte compartido')).toBeVisible();

        // Ana configura sus datos de cobro personales (Alias y CBU)
        await pageAna.getByRole('button', { name: /Mis Datos de Cobro/i }).click();
        await pageAna.locator('#input-perfil-alias').fill('ana.bcp.qr');
        await pageAna.locator('#input-perfil-cbu').fill('10000045678912');
        await pageAna.getByRole('button', { name: 'Guardar Datos' }).click();
        await expect(pageAna.getByText(/¡Tus datos de cobro han sido actualizados/i)).toBeVisible();

        // -------------------------------------------------------------
        // FASE 5: Carlos registra un pago hacia Ana ($20) con comprobante bancario real
        // -------------------------------------------------------------
        await pageCarlos.reload();
        await pageCarlos.getByRole('tab', { name: /Gastos y Liquidación/i }).click();

        await pageCarlos
            .getByRole('button', { name: /Registrar Pago/i })
            .first()
            .click();
        await pageCarlos.locator('#select-pago-receptor').selectOption({ label: 'Usuario 1' });

        // Verifica que se visualizan reactivamente los datos de cobro de Ana
        await expect(pageCarlos.locator('#contenedor-datos-receptor')).toBeVisible();
        await expect(pageCarlos.locator('#texto-alias-receptor')).toContainText('ana.bcp.qr');

        await pageCarlos.locator('#input-pago-monto').fill('20.00');

        // Adjuntar comprobante bancario sintetico limpio (BNB 20 Bs)
        const comprobantePath = 'tests/fixtures/comprobantes/comprobante_bnb_20bs.png';
        if (fs.existsSync(comprobantePath)) {
            await pageCarlos.locator('#input-pago-comprobante').setInputFiles(comprobantePath);
        }

        await pageCarlos.getByRole('button', { name: 'Enviar Pago' }).click();

        await expect(pageCarlos.locator('.badge-estado-pago', { hasText: 'pendiente' }).first()).toBeVisible();
        await expect(pageCarlos.locator('.badge-escaneo', { hasText: /Verificado/i }).first()).toBeVisible();
        await pageCarlos.screenshot({ path: 'docs/evidencias/tarea-8/03-pago-pendiente.png', fullPage: true });

        // Carlos abre el modal para revisar su comprobante
        const btnVerComprobante = pageCarlos.getByRole('button', { name: /Ver comprobante/i }).first();
        if (await btnVerComprobante.isVisible()) {
            await btnVerComprobante.click();
            await pageCarlos.screenshot({ path: 'docs/evidencias/tarea-8/05-modal-inspeccion-comprobante.png' });
            await pageCarlos.locator('dialog[id^="modal-comprobante-"] button:has-text("Cerrar")').first().click();
        }

        // -------------------------------------------------------------
        // FASE 6: Ana (receptora) confirma el pago de Carlos -> Saldo actualizado
        // -------------------------------------------------------------
        await pageAna.reload();
        await pageAna.getByRole('tab', { name: /Gastos y Liquidación/i }).click();

        const btnConfirmar = pageAna.getByRole('button', { name: /Confirmar pago/i }).first();
        await expect(btnConfirmar).toBeVisible();
        await btnConfirmar.click();

        await expect(pageAna.locator('.badge-estado-pago', { hasText: 'confirmado' }).first()).toBeVisible();
        await expect(pageAna.getByText('¡Pago confirmado!')).toBeVisible();

        await pageAna.screenshot({ path: 'docs/evidencias/tarea-8/04-pago-confirmado-saldos.png', fullPage: true });

        await ctxLucas.close();
        await ctxAna.close();
        await ctxCarlos.close();
    });

    test('Usuario sin permisos o ajeno al plan no puede ver ni modificar los gastos del grupo', async ({ browser }) => {
        const ctxLucas = await browser.newContext();
        const pageLucas = await ctxLucas.newPage();

        await pageLucas.goto('/ingresar');
        await pageLucas.getByLabel('Correo electrónico').fill('test@example.com');
        await pageLucas.getByLabel('Contraseña').fill('password');
        await pageLucas.getByRole('button', { name: 'Entrar' }).click();

        const planPrivado = `Plan Privado IHC ${Date.now()}`;
        await pageLucas.getByRole('button', { name: '+ Crear plan' }).click();
        await pageLucas.getByLabel('Nombre del plan').fill(planPrivado);
        await pageLucas.getByLabel('Fecha límite').fill('2026-12-31');
        await pageLucas.getByRole('button', { name: 'Guardar' }).click();
        await pageLucas.getByRole('link', { name: planPrivado }).first().click();
        const urlPrivada = pageLucas.url();

        // Usuario ajeno intenta entrar directamente a la URL de la sala privada
        const ctxAjeno = await browser.newContext();
        const pageAjeno = await ctxAjeno.newPage();
        await pageAjeno.goto('/ingresar');
        await pageAjeno.getByLabel('Correo electrónico').fill('usuario2@example.com');
        await pageAjeno.getByLabel('Contraseña').fill('password123');
        await pageAjeno.getByRole('button', { name: 'Entrar' }).click();

        const resp = await pageAjeno.goto(urlPrivada);
        expect([403, 302]).toContain(resp?.status() ?? 403);

        await ctxLucas.close();
        await ctxAjeno.close();
    });
});
