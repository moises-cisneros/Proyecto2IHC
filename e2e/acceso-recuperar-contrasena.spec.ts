import { test, expect } from '@playwright/test';

test.describe('Acceso — Recuperación y restablecimiento de contraseña', () => {
    test('Como usuario que olvidó su contraseña, llego a recuperar desde el ingreso y recibo una respuesta que no revela si el correo existe', async ({
        page,
    }) => {
        await page.goto('/ingresar');
        await page.getByRole('link', { name: '¿La olvidaste?' }).click();
        await expect(page).toHaveURL(/.*recuperar/);
        await expect(page.getByRole('heading', { name: 'Recuperar contraseña' })).toBeVisible();

        await page.getByLabel('Correo electrónico').fill('nadie-registrado@example.com');
        await page.getByRole('button', { name: 'Enviar enlace' }).click();

        // Anti-enumeración: el mensaje es el mismo exista o no el correo
        await expect(
            page.getByText(/Si el correo ingresado está registrado, te enviamos un enlace de recuperación/i)
        ).toBeVisible();
    });

    test('Como usuario, puedo volver a ingresar desde la pantalla de recuperación sin perder el camino de salida', async ({
        page,
    }) => {
        await page.goto('/recuperar');
        await page.getByRole('link', { name: /Volver a ingresar/ }).click();
        await expect(page).toHaveURL(/.*ingresar/);
        await expect(page.getByRole('heading', { name: 'Ingresar' })).toBeVisible();
    });

    test('Como usuario con un enlace vencido, el restablecimiento falla con un mensaje claro que indica pedir uno nuevo', async ({
        page,
    }) => {
        await page.goto('/restablecer/token-invalido?email=nadie-registrado@example.com');
        await expect(page.getByRole('heading', { name: 'Nueva contraseña' })).toBeVisible();

        await page.getByLabel('Nueva contraseña').fill('nuevaclave123');
        await page.getByLabel('Confirmar contraseña').fill('nuevaclave123');
        await page.getByRole('button', { name: 'Cambiar contraseña' }).click();

        await expect(page.getByText(/El enlace no es válido o ya expiró. Pide uno nuevo./i)).toBeVisible();
    });

    test('Como nuevo usuario, me registro con datos válidos y accedo directamente a Mis planes (Happy Path TF1)', async ({
        page,
    }) => {
        const timestamp = Date.now();
        const correo = `nuevo.usuario.${timestamp}@example.com`;

        await page.goto('/registro');
        await expect(page.getByRole('heading', { name: 'Crear cuenta' })).toBeVisible();

        await page.getByLabel('Nombre').fill('Usuario Nuevo Playwright');
        await page.getByLabel('Correo electrónico').fill(correo);
        await page.getByLabel('Contraseña', { exact: true }).fill('password123');
        await page.getByLabel('Confirmar contraseña').fill('password123');

        await page.getByRole('button', { name: 'Registrarme' }).click();

        await expect(page).toHaveURL(/.*mis-planes/);
        await expect(page.getByRole('heading', { name: 'Mis planes' })).toBeVisible();
    });

    test('Como usuario que comete un error al registrarse, la confirmación de contraseña fallida previene la creación de cuenta (Camino Alternativo TF1)', async ({
        page,
    }) => {
        await page.goto('/registro');

        await page.getByLabel('Nombre').fill('Usuario Error');
        await page.getByLabel('Correo electrónico').fill(`error.${Date.now()}@example.com`);
        await page.getByLabel('Contraseña', { exact: true }).fill('password123');
        await page.getByLabel('Confirmar contraseña').fill('otra-clave-distinta');

        await page.getByRole('button', { name: 'Registrarme' }).click();

        await expect(page).toHaveURL(/.*registro/);
        await expect(page.getByText('Las contraseñas no coinciden.')).toBeVisible();
    });
});
