import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';
import fs from 'fs';
import path from 'path';

const authFile = path.resolve('storage/framework/cache/e2e-auth-anita.json');

test.describe.serial('Tarea 6 — Votos Múltiples, Cierre de Sala y Verificación Ergonómica', () => {

  test('Anita inicia sesión con credenciales sembradas y accede a la sala colaborativa', async ({ page }) => {
    // 1. Anita navega al formulario de acceso
    await page.goto('/ingresar');

    // 2. Introduce sus credenciales sembradas sin fricción cognitiva
    await page.getByLabel('Correo electrónico').fill('usuario1@example.com');
    await page.getByLabel('Contraseña').fill('password123');
    await page.getByRole('button', { name: 'Entrar' }).click();

    // 3. Verifica llegada al panel de planes y bienvenida de sesión
    await expect(page).toHaveURL(/.*mis-planes/);
    await expect(page.getByRole('heading', { name: 'Mis planes' })).toBeVisible();

    // Guarda la sesión autenticada para los siguientes pasos humanos sin saturar el throttle HTTP
    fs.mkdirSync(path.dirname(authFile), { recursive: true });
    await page.context().storageState({ path: authFile });
  });

  test.describe('Acciones colaborativas en sala', () => {
    test.use({ storageState: authFile });

    test('Anita vota por una fecha, vota por un lugar y el conteo se actualiza (voto múltiple)', async ({ page }) => {
      await page.goto('/mis-planes');

      // 1. Creación de un plan para la deliberación colaborativa
      await page.getByRole('button', { name: '+ Crear plan' }).click();
      const nombrePlan = `Plan Colaborativo ${Date.now()}`;
      await page.getByLabel('Nombre del plan').fill(nombrePlan);
      await page.getByLabel('Fecha límite').fill('2026-12-31');
      await page.getByRole('button', { name: 'Guardar' }).click();

      // 2. Acceso a la sala de votación del plan
      await page.getByRole('link', { name: nombrePlan }).first().click();
      await expect(page.getByRole('heading', { name: 'Sala de Votación y Propuestas' })).toBeVisible();

      // 3. Propuesta de una fecha estructurada (H5)
      await page.getByRole('button', { name: /Proponer/ }).first().click();
      await page.getByLabel('Tipo de sugerencia').selectOption('fecha');
      await page.getByLabel('Fecha y hora propuesta').fill('2026-12-24T20:00');
      await page.getByRole('button', { name: 'Guardar propuesta' }).click();
      await expect(page.getByText('Opción propuesta correctamente.')).toBeVisible();

      // 4. Propuesta de un lugar
      await page.getByRole('tab', { name: /Lugares propuestos/ }).click();
      await expect(page.getByRole('tab', { name: /Lugares propuestos/ })).toHaveAttribute('aria-selected', 'true');
      await page.getByRole('button', { name: /Proponer/ }).first().click();
      await page.getByLabel('Tipo de sugerencia').selectOption('lugar');
      await page.getByLabel('Lugar o destino propuesto').fill('Terraza Mirador');
      await page.getByRole('button', { name: 'Guardar propuesta' }).click();
      await expect(page.getByText('Opción propuesta correctamente.')).toBeVisible();

      // 5. Anita vota por el lugar
      const botonVotarLugar = page.getByRole('button', { name: /Votar por Terraza Mirador/ });
      await expect(botonVotarLugar).toBeVisible();
      await botonVotarLugar.click();
      await expect(page.getByRole('button', { name: /Retirar voto de Terraza Mirador/ })).toBeVisible();

      // 6. Anita cambia a la pestaña de fechas y vota por la fecha sugerida
      await page.getByRole('tab', { name: /Fechas propuestas/ }).click();
      await expect(page.getByRole('tab', { name: /Fechas propuestas/ })).toHaveAttribute('aria-selected', 'true');
      const botonVotarFecha = page.getByRole('button', { name: /Votar por/ }).first();
      await expect(botonVotarFecha).toBeVisible();
      await botonVotarFecha.click();

      // 7. Ambos votos coexisten independientemente (voto múltiple verificado)
      await expect(page.getByRole('button', { name: /Retirar voto/ }).first()).toBeVisible();
      await expect(page.getByText('1 voto').first()).toBeVisible();

      await page.getByRole('tab', { name: /Lugares propuestos/ }).click();
      await expect(page.getByRole('tab', { name: /Lugares propuestos/ })).toHaveAttribute('aria-selected', 'true');
      await expect(page.getByRole('button', { name: /Retirar voto de Terraza Mirador/ })).toBeVisible();
    });

    test('Anita retira su voto de una fecha y el conteo decrementa inmediatamente (toggle)', async ({ page }) => {
      await page.goto('/mis-planes');

      // 1. Creación de un plan de prueba rápido
      await page.getByRole('button', { name: '+ Crear plan' }).click();
      const nombrePlan = `Plan Retiro Voto ${Date.now()}`;
      await page.getByLabel('Nombre del plan').fill(nombrePlan);
      await page.getByLabel('Fecha límite').fill('2026-12-31');
      await page.getByRole('button', { name: 'Guardar' }).click();

      await page.getByRole('link', { name: nombrePlan }).first().click();

      // 2. Anita propone un lugar
      await page.getByRole('tab', { name: /Lugares propuestos/ }).click();
      await expect(page.getByRole('tab', { name: /Lugares propuestos/ })).toHaveAttribute('aria-selected', 'true');
      await page.getByRole('button', { name: /Proponer/ }).first().click();
      await page.getByLabel('Tipo de sugerencia').selectOption('lugar');
      await page.getByLabel('Lugar o destino propuesto').fill('Restaurante El Valle');
      await page.getByRole('button', { name: 'Guardar propuesta' }).click();

      // 3. Emite su voto: el contador sube a 1
      const botonVotar = page.getByRole('button', { name: /Votar por Restaurante El Valle/ });
      await botonVotar.click();
      await expect(page.getByRole('button', { name: /Retirar voto de Restaurante El Valle/ })).toBeVisible();
      await expect(page.getByText('1 voto')).toBeVisible();

      // 4. Retira su voto: el contador baja a 0 y el botón vuelve al estado inicial
      const botonRetirar = page.getByRole('button', { name: /Retirar voto de Restaurante El Valle/ });
      await botonRetirar.click();
      await expect(page.getByRole('button', { name: /Votar por Restaurante El Valle/ })).toBeVisible();
      await expect(page.getByText('0 votos')).toBeVisible();
    });

    test('Anita ingresa a un plan confirmado y comprueba que la votación está cerrada y el conteo no puede cambiar', async ({ page }) => {
      await page.goto('/mis-planes');

      // 1. Creación del plan
      const nombrePlan = `Plan Confirmado Cierre ${Date.now()}`;
      await page.getByRole('button', { name: '+ Crear plan' }).click();
      await page.getByLabel('Nombre del plan').fill(nombrePlan);
      await page.getByLabel('Fecha límite').fill('2026-12-31');
      await page.getByRole('button', { name: 'Guardar' }).click();

      // 2. Entra a la sala y crea una opción previa al cierre
      await page.getByRole('link', { name: nombrePlan }).first().click();
      await page.getByRole('button', { name: /Proponer/ }).first().click();
      await page.getByLabel('Tipo de sugerencia').selectOption('fecha');
      await page.getByLabel('Fecha y hora propuesta').fill('2026-12-30T19:00');
      await page.getByRole('button', { name: 'Guardar propuesta' }).click();

      // 3. Regresa al listado de planes y confirma el plan
      await page.goto('/mis-planes');
      const filaPlan = page.locator('tr', { hasText: nombrePlan });
      await filaPlan.getByRole('button', { name: 'Confirmar' }).click();

      // Confirma en el diálogo modal nativo
      const botonAceptarModal = page.locator('#dialog-confirm-aceptar');
      await expect(botonAceptarModal).toBeVisible();
      await botonAceptarModal.click();
      await expect(page.getByText('El plan ahora está confirmado.')).toBeVisible();

      // 4. Anita accede a la sala colaborativa del plan confirmado
      await page.getByRole('link', { name: nombrePlan }).first().click();

      // 5. Comprueba el banner accesible de cierre
      const bannerCierre = page.getByRole('status');
      await expect(bannerCierre).toBeVisible();
      await expect(bannerCierre).toContainText('Votación finalizada. Este plan ha sido confirmado y la deliberación se encuentra cerrada.');

      // 6. Comprueba que el botón de proponer opción está completamente ausente
      await expect(page.getByRole('button', { name: /Proponer/ })).toHaveCount(0);

      // 7. Comprueba que el control de voto está inhabilitado con texto explicativo
      await expect(page.getByText('Votación cerrada (plan confirmado)')).toBeVisible();
      await expect(page.getByRole('button', { name: /Votar por/ })).toHaveCount(0);
    });

    test('La sala colaborativa cumple con accesibilidad WCAG sin violaciones críticas', async ({ page }) => {
      await page.goto('/mis-planes');

      // Crear un plan para auditar su sala colaborativa
      await page.getByRole('button', { name: '+ Crear plan' }).click();
      const nombrePlan = `Plan Accesibilidad ${Date.now()}`;
      await page.getByLabel('Nombre del plan').fill(nombrePlan);
      await page.getByLabel('Fecha límite').fill('2026-12-31');
      await page.getByRole('button', { name: 'Guardar' }).click();

      await page.getByRole('link', { name: nombrePlan }).first().click();
      await expect(page.getByRole('heading', { name: 'Sala de Votación y Propuestas' })).toBeVisible();

      // Auditoría automatizada con AxeCore
      const resultadosAxe = await new AxeBuilder({ page })
        .withTags(['wcag2a', 'wcag2aa'])
        .analyze();

      const violacionesCriticas = resultadosAxe.violations.filter(
        (v) => v.impact === 'critical'
      );

      expect(violacionesCriticas).toHaveLength(0);
    });
  });

});
