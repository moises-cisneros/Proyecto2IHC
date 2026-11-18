import { test, expect } from '@playwright/test';

test.describe('Historial — Navegación y filtros por categoría', () => {
    test('Como usuario, llego al historial desde el menú y filtro por categoría con un estado activo visible', async ({
        page,
    }) => {
        await page.goto('/ingresar');
        await page.getByLabel('Correo electrónico').fill('usuario1@example.com');
        await page.getByLabel('Contraseña').fill('password123');
        await page.getByRole('button', { name: 'Entrar' }).click();
        await expect(page).toHaveURL(/.*mis-planes/);

        await page.getByRole('menuitem', { name: /Historial/ }).click();
        await expect(page).toHaveURL(/.*historial/);
        await expect(page.getByRole('heading', { name: 'Historial de actividad' })).toBeVisible();

        const filtros = page.getByRole('group', { name: 'Filtrar historial por categoría temática' });
        await expect(filtros.getByRole('link', { name: 'Todos' })).toHaveAttribute('aria-current', 'page');

        await filtros.getByRole('link', { name: 'Gastos' }).click();
        await expect(page).toHaveURL(/categoria=gastos/);
        await expect(
            page
                .getByRole('group', { name: 'Filtrar historial por categoría temática' })
                .getByRole('link', { name: 'Gastos' })
        ).toHaveAttribute('aria-current', 'page');
    });

    test('Como usuario, siempre dispongo de una salida hacia Mis planes desde el historial', async ({ page }) => {
        await page.goto('/ingresar');
        await page.getByLabel('Correo electrónico').fill('usuario1@example.com');
        await page.getByLabel('Contraseña').fill('password123');
        await page.getByRole('button', { name: 'Entrar' }).click();
        await page.goto('/historial');

        await page.getByRole('link', { name: 'Volver a Mis Planes' }).click();
        await expect(page).toHaveURL(/.*mis-planes/);
    });
});
