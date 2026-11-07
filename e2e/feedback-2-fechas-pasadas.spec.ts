import { test, expect } from '@playwright/test';
import fs from 'fs';

test.describe('Feedback Mascota al Día — Bloqueo de fechas pasadas en creación y edición', () => {
    test('Como usuario Choque Benavides, al intentar crear un plan con fecha pasada, la interfaz lo restringe y el backend rechaza con mensaje visible', async ({
        page,
    }) => {
        // 1. Acceso a la aplicación
        await page.goto('/ingresar');
        await page.getByLabel('Correo electrónico').fill('usuario1@example.com');
        await page.getByLabel('Contraseña').fill('password123');
        await page.getByRole('button', { name: 'Entrar' }).click();

        await expect(page).toHaveURL(/.*mis-planes/);

        // 2. Apertura del modal de creación
        await page.getByRole('button', { name: '+ Crear plan' }).click();
        await expect(page.getByRole('heading', { name: 'Nuevo plan' })).toBeVisible();

        // 3. Verificación de la restricción del lado del cliente (atributo min en input de fecha)
        const inputFecha = page.locator('#fecha_limite');
        const hoyStr = new Date().toISOString().split('T')[0];
        await expect(inputFecha).toHaveAttribute('min', hoyStr);

        // 4. Intento deliberado de ingresar una fecha pasada omitiendo restricción del cliente
        await page.getByLabel('Nombre del plan').fill('Plan Canino Pasado');
        await inputFecha.evaluate((el: HTMLInputElement) => {
            el.removeAttribute('min');
            el.value = '2020-01-01';
        });

        // 5. Envío del formulario
        await page.getByRole('button', { name: 'Guardar' }).click();

        // 6. El backend intercepta la petición y responde con error visible (Nielsen H9)
        const mensajeError = page
            .locator('.campo-error')
            .filter({ hasText: 'La fecha del plan debe ser igual o posterior a la fecha actual.' });
        await expect(mensajeError).toBeVisible();

        // 7. Captura de evidencia para la entrega
        fs.mkdirSync('docs/evidencias/tarea-7', { recursive: true });
        await page.screenshot({ path: 'docs/evidencias/tarea-7/01-feedback-fechas-bloqueadas.png' });
    });
});
