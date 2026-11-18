import type { Page } from '@playwright/test';

/**
 * El modal de gasto no preselecciona participantes:
 * la persona debe marcar a quienes comparten el gasto.
 */
export async function marcarTodosLosParticipantes(page: Page): Promise<void> {
    const checks = page.getByRole('dialog').getByRole('checkbox');
    const total = await checks.count();
    for (let i = 0; i < total; i++) {
        await checks.nth(i).check();
    }
}
