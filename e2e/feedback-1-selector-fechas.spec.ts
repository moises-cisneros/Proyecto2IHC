import { test, expect } from '@playwright/test';
import fs from 'fs';
import path from 'path';

test.describe('Feedback UniSport — Selector de fechas con confirmación explícita', () => {

  test('Como participante Juan Carlos, al proponer una fecha utilizo el botón explícito «Aceptar fecha» y la selección se retiene sin pérdidas', async ({ page }) => {
    // 1. Acceso a la aplicación con cuenta válida
    await page.goto('/ingresar');
    await page.getByLabel('Correo electrónico').fill('usuario1@example.com');
    await page.getByLabel('Contraseña').fill('password123');
    await page.getByRole('button', { name: 'Entrar' }).click();

    await expect(page).toHaveURL(/.*mis-planes/);

    // 2. Creación de un plan para la prueba
    await page.getByRole('button', { name: '+ Crear plan' }).click();
    const nombrePlan = `Plan Torneo UniSport ${Date.now()}`;
    await page.getByLabel('Nombre del plan').fill(nombrePlan);
    await page.getByLabel('Fecha límite').fill('2026-12-31');
    await page.getByRole('button', { name: 'Guardar' }).click();

    // 3. Acceso a la sala de votación
    await page.getByRole('link', { name: nombrePlan }).first().click();
    await expect(page.getByRole('heading', { name: 'Sala de Votación y Propuestas' })).toBeVisible();

    // 4. Apertura del modal de propuesta
    await page.getByRole('button', { name: /Proponer/ }).first().click();
    await expect(page.getByRole('heading', { name: 'Proponer nueva opción' })).toBeVisible();

    // 5. Interacción con el selector de fecha y clic en «Aceptar fecha»
    const inputFecha = page.locator('#input-fecha-opcion');
    await inputFecha.fill('2026-12-28T16:30');

    const btnAceptarFecha = page.getByRole('button', { name: 'Aceptar fecha' });
    await expect(btnAceptarFecha).toBeVisible();
    await btnAceptarFecha.click();

    // 6. Verificación visual del feedback inmediato (Nielsen H1, H3 y H5)
    const feedbackConfirmado = page.locator('#feedback-fecha-confirmada');
    await expect(feedbackConfirmado).toBeVisible();
    await expect(feedbackConfirmado).toContainText('2026-12-28 16:30');

    // 7. Captura de evidencia para la entrega
    fs.mkdirSync('docs/evidencias/tarea-7', { recursive: true });
    await page.screenshot({ path: 'docs/evidencias/tarea-7/02-feedback-selector-fecha-aceptar.png' });

    // 8. Confirmación y persistencia de la propuesta
    await page.getByRole('button', { name: 'Guardar propuesta' }).click();
    await expect(page.getByText('Opción propuesta correctamente.')).toBeVisible();
    await expect(page.getByText('28/12/2026 16:30 hrs')).toBeVisible();
  });

});
