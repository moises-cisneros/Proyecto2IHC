import { test, expect } from '@playwright/test';
import fs from 'node:fs';

test.describe('Tarea 7 — Confirmación de Plan, Resolución de Empates con Motivo y Veredicto Público', () => {

    test('Como organizador y participante, completamos el ciclo de desempate con motivo obligatorio y congelamiento de sala', async ({ browser }) => {
        test.setTimeout(60000);
        fs.mkdirSync('docs/evidencias/tarea-7', { recursive: true });

        // -------------------------------------------------------------
        // FASE 1: Organizador (Anita) crea plan, propone opciones e invita
        // -------------------------------------------------------------
        const contextoOrganizador = await browser.newContext();
        const pageOrganizador = await contextoOrganizador.newPage();

        await pageOrganizador.goto('/ingresar');
        await pageOrganizador.getByLabel('Correo electrónico').fill('usuario1@example.com');
        await pageOrganizador.getByLabel('Contraseña').fill('password123');
        await pageOrganizador.getByRole('button', { name: 'Entrar' }).click();
        await expect(pageOrganizador).toHaveURL(/.*mis-planes/);

        const nombrePlan = `Plan Fin de Año IHC ${Date.now()}`;
        await pageOrganizador.getByRole('button', { name: '+ Crear plan' }).click();
        await pageOrganizador.getByLabel('Nombre del plan').fill(nombrePlan);
        await pageOrganizador.getByLabel('Fecha límite').fill('2026-12-31');
        await pageOrganizador.getByRole('button', { name: 'Guardar' }).click();

        await pageOrganizador.getByRole('link', { name: nombrePlan }).first().click();
        await expect(pageOrganizador.getByRole('heading', { name: 'Sala de Votación y Propuestas' })).toBeVisible();

        // Invitar al colaborador Carlos (usuario2)
        await pageOrganizador.getByRole('button', { name: '+ Invitar colaborador' }).click();
        await pageOrganizador.getByLabel('Correo electrónico del invitado').fill('usuario2@example.com');
        await pageOrganizador.getByRole('button', { name: 'Enviar invitación' }).click();
        await expect(pageOrganizador.getByText('Invitación enviada')).toBeVisible();

        // Proponer Fecha A
        await pageOrganizador.getByRole('button', { name: /Proponer/ }).first().click();
        await pageOrganizador.locator('#input-fecha-opcion').fill('2026-12-20T12:00');
        await pageOrganizador.getByRole('button', { name: 'Guardar propuesta' }).click();
        await expect(pageOrganizador.getByText('Opción propuesta correctamente.')).toBeVisible();

        // Proponer Fecha B
        await pageOrganizador.getByRole('button', { name: /Proponer/ }).first().click();
        await pageOrganizador.locator('#input-fecha-opcion').fill('2026-12-21T12:00');
        await pageOrganizador.getByRole('button', { name: 'Guardar propuesta' }).click();
        await expect(pageOrganizador.getByText('Opción propuesta correctamente.')).toBeVisible();

        // Anita vota por Fecha A
        await pageOrganizador.getByRole('button', { name: /Votar por 20\/12\/2026 12:00/ }).click();
        await expect(pageOrganizador.getByRole('button', { name: /Retirar voto de 20\/12\/2026 12:00/ })).toBeVisible();

        // Proponer Lugares
        await pageOrganizador.getByRole('tab', { name: /Lugares propuestos/ }).click();
        await pageOrganizador.getByRole('button', { name: /Proponer/ }).first().click();
        await pageOrganizador.getByLabel('Tipo de sugerencia').selectOption('lugar');
        await pageOrganizador.getByLabel('Lugar o destino propuesto').fill('Pizzería Napoli');
        await pageOrganizador.getByRole('button', { name: 'Guardar propuesta' }).click();

        await pageOrganizador.getByRole('button', { name: /Proponer/ }).first().click();
        await pageOrganizador.getByLabel('Tipo de sugerencia').selectOption('lugar');
        await pageOrganizador.getByLabel('Lugar o destino propuesto').fill('Tacos del Rey');
        await pageOrganizador.getByRole('button', { name: 'Guardar propuesta' }).click();

        // Anita vota por Pizzería Napoli
        await pageOrganizador.getByRole('button', { name: /Votar por Pizzería Napoli/ }).click();
        await expect(pageOrganizador.getByRole('button', { name: /Retirar voto de Pizzería Napoli/ })).toBeVisible();

        // -------------------------------------------------------------
        // FASE 2: Colaborador (Carlos) vota generando empate
        // -------------------------------------------------------------
        const contextoParticipante = await browser.newContext();
        const pageParticipante = await contextoParticipante.newPage();

        await pageParticipante.goto('/ingresar');
        await pageParticipante.getByLabel('Correo electrónico').fill('usuario2@example.com');
        await pageParticipante.getByLabel('Contraseña').fill('password123');
        await pageParticipante.getByRole('button', { name: 'Entrar' }).click();

        await pageParticipante.getByRole('link', { name: nombrePlan }).first().click();

        // Acepta la invitación pendiente para habilitar la votación colaborativa
        const btnAceptarInvitacion = pageParticipante.getByRole('button', { name: /Aceptar invitación/ }).first();
        if (await btnAceptarInvitacion.isVisible()) {
            await btnAceptarInvitacion.click();
        }

        // Carlos vota por Fecha B
        await pageParticipante.getByRole('button', { name: /Votar por 21\/12\/2026 12:00/ }).click();
        await expect(pageParticipante.getByRole('button', { name: /Retirar voto de 21\/12\/2026 12:00/ })).toBeVisible();

        // Carlos vota por Tacos del Rey
        await pageParticipante.getByRole('tab', { name: /Lugares propuestos/ }).click();
        await pageParticipante.getByRole('button', { name: /Votar por Tacos del Rey/ }).click();
        await expect(pageParticipante.getByRole('button', { name: /Retirar voto de Tacos del Rey/ })).toBeVisible();

        // Evidencia 03: Votación en empate
        await pageParticipante.screenshot({ path: 'docs/evidencias/tarea-7/03-votacion-empate.png' });

        // -------------------------------------------------------------
        // FASE 3: Organizador resuelve el empate con motivo obligatorio
        // -------------------------------------------------------------
        await pageOrganizador.reload();
        const btnCerrar = pageOrganizador.getByRole('button', { name: '✓ Cerrar votación y confirmar' });
        await expect(btnCerrar).toBeVisible();
        await btnCerrar.click();

        const modalConfirmar = pageOrganizador.locator('#modal-confirmar-plan');
        await expect(modalConfirmar).toBeVisible();

        // Selección en selectores de opciones empatadas (Ley de Hick)
        await modalConfirmar.locator('select[name="propuesta_fecha_id"]').selectOption({ index: 1 });
        await modalConfirmar.locator('select[name="propuesta_lugar_id"]').selectOption({ index: 1 });

        // Redacción de justificación pública obligatoria
        await modalConfirmar.locator('#motivo_desempate').fill('Mayor facilidad de estacionamiento y cercanía para la mayoría del equipo.');

        // Evidencia 04: Modal de desempate con motivo
        await pageOrganizador.screenshot({ path: 'docs/evidencias/tarea-7/04-modal-desempate-motivo.png' });

        // Envío de la confirmación
        await modalConfirmar.getByRole('button', { name: 'Confirmar y cerrar votación' }).click();

        // Verificación de veredicto público en vista del organizador
        await expect(pageOrganizador.getByText('¡Plan confirmado! Deliberación y votación finalizadas.')).toBeVisible();
        await expect(pageOrganizador.getByText('Mayor facilidad de estacionamiento y cercanía para la mayoría del equipo.')).toBeVisible();

        // Evidencia 05: Sala confirmada vista por el organizador
        await pageOrganizador.screenshot({ path: 'docs/evidencias/tarea-7/05-sala-confirmada-organizador.png' });

        // -------------------------------------------------------------
        // FASE 4: Participante verifica inmutabilidad y veredicto
        // -------------------------------------------------------------
        await pageParticipante.reload();
        await expect(pageParticipante.getByText('¡Plan confirmado! Deliberación y votación finalizadas.')).toBeVisible();
        await expect(pageParticipante.getByText('Mayor facilidad de estacionamiento y cercanía para la mayoría del equipo.')).toBeVisible();
        await expect(pageParticipante.getByText('Votación cerrada (plan confirmado)').first()).toBeVisible();
        await expect(pageParticipante.getByRole('button', { name: '+ Proponer opción' })).toHaveCount(0);

        // Evidencia 06: Sala confirmada vista por el participante
        await pageParticipante.screenshot({ path: 'docs/evidencias/tarea-7/06-vista-participante-inmutable.png' });

        await contextoOrganizador.close();
        await contextoParticipante.close();
    });

});
