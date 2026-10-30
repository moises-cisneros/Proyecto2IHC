import { test, expect } from '@playwright/test';

test('Test User inicia sesión y crea un plan para el fin de semana', async ({ page }) => {
  // 1. Login con datos del seeder
  await page.goto('/ingresar');
  await page.getByLabel('Correo electrónico').fill('test@example.com');
  await page.getByLabel('Contraseña').fill('password');
  await page.getByRole('button', { name: 'Entrar' }).click();

  // 2. Crear el plan desde Mis planes
  await page.getByRole('button', { name: '+ Crear plan' }).click();
  await page.getByLabel('Nombre del plan').fill('Parrillada de fin de semana');
  await page.getByLabel('Fecha límite').fill('2026-11-15');
  await page.getByRole('button', { name: 'Guardar' }).click();

  // 3. Lo ve en pantalla
  await expect(page.getByText('Parrillada de fin de semana').first()).toBeVisible();
});
