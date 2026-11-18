import { test, expect } from '@playwright/test';

test.describe('TF2 — Creación de Planes (Happy path y alternativos)', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/ingresar');
        await page.getByLabel('Correo electrónico').fill('test@example.com');
        await page.getByLabel('Contraseña').fill('password');
        await page.getByRole('button', { name: 'Entrar' }).click();
        await expect(page).toHaveURL(/.*mis-planes/);
    });

    test('Test User crea un plan exitosamente con fecha futura válida (Happy Path)', async ({ page }) => {
        const nombrePlan = `Parrillada Fin de Año ${Date.now()}`;

        // 1. Abrir modal
        await page.getByRole('button', { name: '+ Crear plan' }).click();
        await expect(page.getByRole('heading', { name: 'Nuevo plan' })).toBeVisible();

        // 2. Llenar campos con fecha futura válida
        await page.getByLabel('Nombre del plan').fill(nombrePlan);
        await page.getByLabel('Fecha límite').fill('2026-12-31');
        await page.getByRole('button', { name: 'Guardar' }).click();

        // 3. Verificación de feedback y presencia en tabla
        await expect(page.getByText('¡Plan creado!')).toBeVisible();
        await expect(page.getByRole('link', { name: nombrePlan }).first()).toBeVisible();
    });

    test('Usuario cancela la creación del plan y el modal se cierra sin persistir datos (Camino alternativo)', async ({
        page,
    }) => {
        const nombreDescartado = `Plan Descartado ${Date.now()}`;

        await page.getByRole('button', { name: '+ Crear plan' }).click();
        const dialog = page.locator('#modal-crear');
        await expect(dialog).toBeVisible();

        await page.getByLabel('Nombre del plan').fill(nombreDescartado);
        await dialog.getByRole('button', { name: 'Cancelar' }).click();

        await expect(dialog).not.toBeVisible();
        await expect(page.getByRole('link', { name: nombreDescartado })).toHaveCount(0);
    });
});
