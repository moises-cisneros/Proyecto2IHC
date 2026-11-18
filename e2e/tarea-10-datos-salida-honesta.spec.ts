import { test, expect } from '@playwright/test';
import fs from 'node:fs';

test.describe('Tarea 10 — Mis Datos, Salida Honesta y Eliminación de Cuenta sin Patrones Oscuros (Clase 22)', () => {
    test.beforeAll(() => {
        fs.mkdirSync('docs/evidencias/tarea-10', { recursive: true });
    });

    test('Flujo 1: Acceso a Mi Cuenta y ejercicio del derecho de portabilidad de datos (JSON y CSV)', async ({
        page,
    }) => {
        // 1. Iniciar sesión como usuario existente
        await page.goto('/ingresar');
        await page.getByLabel('Correo electrónico').fill('test@example.com');
        await page.getByLabel('Contraseña').fill('password');
        await page.getByRole('button', { name: 'Entrar' }).click();
        await expect(page).toHaveURL(/.*mis-planes/);

        // 2. Navegar a Mi Cuenta desde el navbar
        await page.getByRole('menuitem', { name: 'Mi Cuenta' }).click();
        await expect(page).toHaveURL(/.*perfil\/datos/);
        await expect(page.getByRole('heading', { name: 'Mi Cuenta y Privacidad' })).toBeVisible();

        // 3. Verificar sección de datos y botones de portabilidad
        await expect(page.getByRole('heading', { name: 'Mi Cuenta y Privacidad' })).toBeVisible();
        await expect(page.getByRole('heading', { name: 'Descarga de datos' })).toBeVisible();

        const btnJson = page.getByRole('link', { name: /Descargar.*JSON/i });
        const btnCsv = page.getByRole('link', { name: /Descargar.*CSV/i });
        await expect(btnJson).toBeVisible();
        await expect(btnCsv).toBeVisible();

        // 4. Capturar evidencia de la interfaz de portabilidad
        await page.screenshot({
            path: 'docs/evidencias/tarea-10/01-centro-privacidad-portabilidad.png',
            fullPage: false,
        });

        // 5. Simular y verificar descarga estructurada JSON
        const downloadPromiseJson = page.waitForEvent('download');
        await btnJson.click();
        const downloadJson = await downloadPromiseJson;
        expect(downloadJson.suggestedFilename()).toBe('mis-datos-planazo.json');

        // 6. Simular y verificar descarga estructurada CSV
        const downloadPromiseCsv = page.waitForEvent('download');
        await btnCsv.click();
        const downloadCsv = await downloadPromiseCsv;
        expect(downloadCsv.suggestedFilename()).toBe('mis-datos-planazo.csv');
    });

    test('Flujo 2: Intento de baja de cuenta bloqueado por organizar planes abiertos con feedback explicativo', async ({
        page,
    }) => {
        // 1. Iniciar sesión como organizador con planes activos
        await page.goto('/ingresar');
        await page.getByLabel('Correo electrónico').fill('test@example.com');
        await page.getByLabel('Contraseña').fill('password');
        await page.getByRole('button', { name: 'Entrar' }).click();

        // 2. Crear un plan abierto para forzar la regla de negocio
        const timestamp = Date.now();
        const nombrePlan = `Plan Salida Honesta ${timestamp}`;
        await page.getByRole('button', { name: '+ Crear plan' }).click();
        await page.getByLabel('Nombre del plan').fill(nombrePlan);
        await page.getByLabel('Fecha límite').fill('2026-12-31');
        await page.getByRole('button', { name: 'Guardar' }).click();
        await expect(page.getByText('¡Plan creado!')).toBeVisible();

        // 3. Ir a Mi Cuenta
        await page.goto('/perfil/datos');

        // 4. Al intentar eliminar la cuenta, el diálogo explica el bloqueo y ofrece la acción para resolverlo
        await page.getByRole('button', { name: 'Revisar requisitos para eliminar mi cuenta' }).click();
        const avisoBloqueo = page.getByRole('dialog', { name: 'No puedes dar de baja tu cuenta todavía' });
        await expect(avisoBloqueo).toBeVisible();
        await expect(avisoBloqueo.getByText(/Plan abierto · Pendiente/i).first()).toBeVisible();
        await expect(avisoBloqueo.getByRole('link', { name: /^Gestionar plan/ }).first()).toBeVisible();
        await expect(avisoBloqueo.getByRole('button', { name: /Cerrar diálogo y resolver pendientes/i })).toBeVisible();

        // 5. Capturar evidencia de la caja informativa (Por qué y Qué hacer)
        await page.screenshot({
            path: 'docs/evidencias/tarea-10/02-baja-cuenta-bloqueada-planes-abiertos.png',
            fullPage: false,
        });
    });

    test('Flujo 3: Modal de salida honesta al intentar eliminar un plan confirmado (sin botón disabled opaco)', async ({
        page,
    }) => {
        // 1. Iniciar sesión como organizador
        await page.goto('/ingresar');
        await page.getByLabel('Correo electrónico').fill('test@example.com');
        await page.getByLabel('Contraseña').fill('password');
        await page.getByRole('button', { name: 'Entrar' }).click();

        // 2. Crear y confirmar un plan para generar la condición de bloqueo
        const nombrePlan = `Plan Confirmado Salida ${Date.now()}`;
        await page.getByRole('button', { name: '+ Crear plan' }).click();
        await page.getByLabel('Nombre del plan').fill(nombrePlan);
        await page.getByLabel('Fecha límite').fill('2026-12-31');
        await page.getByRole('button', { name: 'Guardar' }).click();
        await expect(page.getByText('¡Plan creado!')).toBeVisible();

        // Ir a la sala y proponer una fecha
        await page.getByRole('link', { name: nombrePlan }).first().click();
        await page
            .getByRole('button', { name: /Proponer/ })
            .first()
            .click();
        await page.locator('#input-fecha-opcion').fill('2026-12-25T20:00');
        await page.getByRole('button', { name: 'Guardar propuesta' }).click();
        await expect(page.getByText('Opción propuesta correctamente.')).toBeVisible();

        // Votar la fecha
        await page
            .getByRole('button', { name: /Votar por/i })
            .first()
            .click();

        // Confirmar el plan
        await page.reload();
        const btnCerrar = page.getByRole('button', { name: /Cerrar votación y confirmar/i });
        await expect(btnCerrar).toBeVisible();
        await btnCerrar.click();

        const dialogConfirmar = page.locator('#modal-confirmar-plan');
        await expect(dialogConfirmar).toBeVisible();
        await dialogConfirmar.getByRole('button', { name: 'Confirmar y cerrar votación' }).click();
        await expect(page.getByText(/¡Plan confirmado!/i)).toBeVisible();

        // 3. Volver al listado Mis Planes
        await page.goto('/mis-planes');

        // 4. Localizar la fila del plan confirmado y hacer clic en el botón de eliminación
        const filaPlan = page.locator('tr', { hasText: nombrePlan });
        const btnEliminar = filaPlan.getByRole('button', { name: new RegExp(`Eliminar plan ${nombrePlan}`, 'i') });
        await expect(btnEliminar).toBeVisible();
        await btnEliminar.click();

        // 5. Verificar que se despliega el modal explicativo con .caja-salida-honesta (sin estar deshabilitado mudamente)
        const modalSalidaPlan = page.locator('dialog[open]');
        await expect(modalSalidaPlan.getByText('No es posible eliminar este plan')).toBeVisible();
        await expect(modalSalidaPlan.getByText(/Plan confirmado:/i)).toBeVisible();
        await expect(modalSalidaPlan.getByRole('button', { name: 'Cancelar plan formalmente' })).toBeVisible();

        // 6. Capturar evidencia gráfica del modal de salida honesta del plan
        await page.screenshot({
            path: 'docs/evidencias/tarea-10/03-modal-salida-honesta-plan-confirmado.png',
            fullPage: false,
        });

        // 7. Cerrar modal honestamente
        await modalSalidaPlan.getByRole('button', { name: /Cerrar modal de advertencia|Entendido/i }).click();
    });

    test('Flujo 4: Desvinculación simétrica de invitación pendiente (Rechazar vs Aceptar)', async ({ browser }) => {
        const ctxOrganizador = await browser.newContext();
        const pageOrganizador = await ctxOrganizador.newPage();

        // 1. Lucas crea un plan e invita a Carlos (usuario2@example.com)
        await pageOrganizador.goto('/ingresar');
        await pageOrganizador.getByLabel('Correo electrónico').fill('test@example.com');
        await pageOrganizador.getByLabel('Contraseña').fill('password');
        await pageOrganizador.getByRole('button', { name: 'Entrar' }).click();
        await expect(pageOrganizador).toHaveURL(/.*mis-planes/);
        await pageOrganizador.waitForLoadState('domcontentloaded');

        const nombrePlan = `Plan Invitación Simétrica ${Date.now()}`;
        await pageOrganizador.getByRole('button', { name: '+ Crear plan' }).click();
        await pageOrganizador.getByLabel('Nombre del plan').fill(nombrePlan);
        await pageOrganizador.getByLabel('Fecha límite').fill('2026-12-31');
        await pageOrganizador.getByRole('button', { name: 'Guardar' }).click();
        await expect(pageOrganizador.getByText('¡Plan creado!')).toBeVisible();

        await pageOrganizador.getByRole('link', { name: nombrePlan }).first().click();

        await pageOrganizador.getByRole('button', { name: '+ Invitar colaborador' }).click();
        await pageOrganizador.getByLabel('Correo electrónico del invitado').fill('usuario2@example.com');
        await pageOrganizador.getByRole('button', { name: 'Enviar invitación' }).click();
        await expect(pageOrganizador.getByText(/Invitación enviada/i)).toBeVisible();

        const urlSala = pageOrganizador.url();

        // 2. Carlos (invitado) inicia sesión y entra a la sala
        const ctxCarlos = await browser.newContext();
        const pageCarlos = await ctxCarlos.newPage();

        await pageCarlos.goto('/ingresar');
        await pageCarlos.getByLabel('Correo electrónico').fill('usuario2@example.com');
        await pageCarlos.getByLabel('Contraseña').fill('password123');
        await pageCarlos.getByRole('button', { name: 'Entrar' }).click();
        await expect(pageCarlos).toHaveURL(/.*mis-planes/);

        await pageCarlos.goto(urlSala);

        // 3. Verificar que tanto «Aceptar invitación» como «Rechazar invitación» están presentes simétricamente
        const btnAceptar = pageCarlos.getByRole('button', { name: 'Aceptar invitación' }).first();
        const btnRechazar = pageCarlos.getByRole('button', { name: 'Rechazar invitación' }).first();

        await expect(btnAceptar).toBeVisible();
        await expect(btnRechazar).toBeVisible();

        // 4. Capturar evidencia gráfica del rechazo simétrico (sin trampa de entrada/roach motel)
        await pageCarlos.screenshot({
            path: 'docs/evidencias/tarea-10/04-rechazo-simetrico-invitacion.png',
            fullPage: false,
        });

        // 5. Carlos rechaza la invitación limpiamente
        await btnRechazar.click();
        await expect(pageCarlos).toHaveURL(/.*mis-planes/);
        await expect(pageCarlos.getByText('Has rechazado la invitación al plan.')).toBeVisible();
    });

    test('Flujo 5: Baja definitiva exitosa con confirmación simétrica (sin confirmshaming ni chantaje)', async ({
        page,
    }) => {
        // 1. Registrar un usuario temporal libre de deudas y planes abiertos
        const uniqueEmail = `baja.libre.${Date.now()}@example.com`;
        await page.goto('/registro');
        await page.getByLabel('Nombre').fill('Usuario Baja');
        await page.getByLabel('Correo electrónico').fill(uniqueEmail);
        await page.getByLabel('Contraseña', { exact: true }).fill('temporal123');
        await page.getByLabel('Confirmar contraseña').fill('temporal123');
        await page.getByRole('button', { name: 'Registrarme' }).click();
        await expect(page).toHaveURL(/.*mis-planes/);

        // 2. Navegar a Mi Cuenta y Privacidad
        await page.goto('/perfil/datos');

        // 3. Como no tiene planes abiertos ni deudas, visualiza el botón habilitado de eliminación
        const btnEliminarCuenta = page.getByRole('button', { name: /Eliminar mi cuenta/i });
        await expect(btnEliminarCuenta).toBeVisible();
        await btnEliminarCuenta.click();

        // 4. Verificar modal con estricta simetría de Fitts y sin chantaje emocional
        const modal = page.locator('#modal-eliminar-cuenta');
        await expect(modal).toBeVisible();
        await expect(modal.getByRole('heading', { name: 'Confirmar eliminación de cuenta' })).toBeVisible();
        await expect(modal.getByRole('button', { name: /Cancelar y mantener mi cuenta/i })).toBeVisible();
        await expect(modal.getByRole('button', { name: /Confirmar y eliminar mi cuenta/i })).toBeVisible();

        // 5. Capturar evidencia del modal simétrico de salida
        await page.screenshot({
            path: 'docs/evidencias/tarea-10/05-baja-exitosa-cuenta-confirmacion.png',
            fullPage: false,
        });

        // 6. Ingresar contraseña actual y confirmar la eliminación
        await modal.locator('#input-current-password').fill('temporal123');
        await modal.getByRole('button', { name: /Confirmar y eliminar mi cuenta/i }).click();

        // 7. Verificar redirección y cierre de sesión con mensaje honesto de baja
        await expect(page).toHaveURL(/.*ingresar/);
        await expect(page.getByText(/Tu cuenta ha sido eliminada correctamente de Planazo/i)).toBeVisible();
    });
});
