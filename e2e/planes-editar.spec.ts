import { test, expect } from '@playwright/test';

test.describe('Planes — Edición de un plan desde Mis planes', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/ingresar');
        await page.getByLabel('Correo electrónico').fill('usuario1@example.com');
        await page.getByLabel('Contraseña').fill('password123');
        await page.getByRole('button', { name: 'Entrar' }).click();
        await expect(page).toHaveURL(/.*mis-planes/);
    });

    test('Como organizador, edito el nombre de mi plan y veo el cambio reflejado en el listado', async ({ page }) => {
        const nombre = `Plan Editable ${Date.now()}`;
        const nombreNuevo = `${nombre} (renombrado)`;

        await page.getByRole('button', { name: '+ Crear plan' }).click();
        await page.getByLabel('Nombre del plan').fill(nombre);
        await page.getByLabel('Fecha límite').fill('2026-12-31');
        await page.getByRole('button', { name: 'Guardar' }).click();
        await expect(page.getByText('¡Plan creado!')).toBeVisible();

        await page.getByRole('link', { name: `Editar plan ${nombre}` }).click();
        await expect(page.getByRole('heading', { name: 'Editar plan' })).toBeVisible();

        await page.getByLabel('Nombre del plan').fill(nombreNuevo);
        await page.getByRole('button', { name: 'Actualizar' }).click();

        await expect(page.getByText('¡Plan actualizado!')).toBeVisible();
        await expect(page.getByText(nombreNuevo).first()).toBeVisible();
    });

    test('Como organizador, cancelar la edición vuelve al listado sin modificar el plan', async ({ page }) => {
        const nombre = `Plan Sin Cambios ${Date.now()}`;

        await page.getByRole('button', { name: '+ Crear plan' }).click();
        await page.getByLabel('Nombre del plan').fill(nombre);
        await page.getByLabel('Fecha límite').fill('2026-12-31');
        await page.getByRole('button', { name: 'Guardar' }).click();
        await expect(page.getByText('¡Plan creado!')).toBeVisible();

        await page.getByRole('link', { name: `Editar plan ${nombre}` }).click();
        await page.getByLabel('Nombre del plan').fill('Texto descartado');
        await page.getByRole('link', { name: 'Cancelar' }).click();

        await expect(page).toHaveURL(/.*mis-planes/);
        await expect(page.getByText(nombre).first()).toBeVisible();
        await expect(page.getByText('Texto descartado')).toHaveCount(0);
    });

    test('Como organizador, cancelo un plan confirmado mediante el diálogo destructivo y pasa a estado cancelado irreversible', async ({
        page,
    }) => {
        const nombre = `Plan Para Cancelar ${Date.now()}`;

        // 1. Crear plan
        await page.getByRole('button', { name: '+ Crear plan' }).click();
        await page.getByLabel('Nombre del plan').fill(nombre);
        await page.getByLabel('Fecha límite').fill('2026-12-31');
        await page.getByRole('button', { name: 'Guardar' }).click();
        await expect(page.getByText('¡Plan creado!')).toBeVisible();

        // 2. Ir a la sala y confirmar el plan
        await page.getByRole('link', { name: nombre }).first().click();
        await page.getByRole('button', { name: /Cerrar votación y confirmar/i }).click();
        const dialogConfirmar = page.locator('#modal-confirmar-plan');
        await expect(dialogConfirmar).toBeVisible();
        await dialogConfirmar.getByRole('button', { name: 'Confirmar y cerrar votación' }).click();
        await expect(page.getByText(/¡Plan confirmado!/i)).toBeVisible();

        // 3. Volver a mis-planes
        await page.goto('/mis-planes');

        // 4. Ubicar la fila y hacer clic en el botón Cancelar de la botonera de acciones
        const fila = page.locator('tr', { hasText: nombre });
        await fila.locator('.acciones-grupo').getByRole('button', { name: 'Cancelar', exact: true }).click();

        // 5. El diálogo destructivo se abre
        const dialog = page.locator('#dialog-confirm');
        await expect(dialog).toBeVisible();
        await expect(dialog.getByText(/¿Cancelar el plan/i)).toBeVisible();

        // 6. Confirmar la cancelación
        await dialog.getByRole('button', { name: 'Sí, cancelar' }).click();

        // 7. El plan ahora tiene chip Cancelado y no ofrece Confirmar ni Editar
        await expect(page.getByText('El plan ahora está cancelado.')).toBeVisible();
        const filaCancelada = page.locator('tr', { hasText: nombre });
        await expect(filaCancelada.getByText('Cancelado')).toBeVisible();
        await expect(filaCancelada.getByRole('button', { name: 'Confirmar' })).toHaveCount(0);
        await expect(filaCancelada.getByRole('link', { name: `Editar plan ${nombre}` })).toHaveCount(0);
    });
});
