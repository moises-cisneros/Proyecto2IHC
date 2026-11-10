import { chromium } from '@playwright/test';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const outputDir = path.resolve(__dirname, '../tests/fixtures/comprobantes');

if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
}

/**
 * Plantillas HTML/SVG con diseño verosímil y datos limpios/sintéticos
 * de comprobantes bancarios móviles bolivianos.
 */
const fixtures = [
    {
        filename: 'comprobante_bnb_20bs.png',
        title: 'BNB - Transferencia QR 20 Bs',
        html: `
        <div style="font-family: system-ui, -apple-system, sans-serif; background: #007a3d; padding: 24px 16px; border-radius: 20px; color: white; width: 380px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <div style="text-align: center; margin-bottom: 16px;">
                <div style="font-size: 13px; letter-spacing: 1.5px; opacity: 0.9; font-weight: 700;">BANCO NACIONAL DE BOLIVIA</div>
                <div style="font-size: 11px; opacity: 0.8; margin-top: 2px;">Comprobante de Transferencia QR</div>
            </div>
            <div style="background: white; border-radius: 16px; padding: 20px; color: #1e293b; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
                <div style="text-align: center; padding-bottom: 14px; border-bottom: 1px dashed #cbd5e1;">
                    <div style="width: 48px; height: 48px; background: #dcfce7; border-radius: 50%; margin: 0 auto 10px; display: flex; align-items: center; justify-content: center; color: #15803d; font-size: 26px; font-weight: bold;">✓</div>
                    <div style="font-size: 12px; color: #15803d; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Transferencia Exitosa</div>
                    <div style="font-size: 32px; font-weight: 800; color: #0f172a; margin-top: 6px;">Bs 20.00</div>
                    <div style="font-size: 11px; color: #64748b;">Veinte 00/100 Bolivianos</div>
                </div>
                <div style="margin-top: 14px; font-size: 12px; line-height: 1.7;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                        <span style="color: #64748b;">Destinatario:</span>
                        <strong style="color: #0f172a;">Ana Martinez</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                        <span style="color: #64748b;">Entidad destino:</span>
                        <strong style="color: #0f172a;">Banco Nacional de Bolivia</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                        <span style="color: #64748b;">Glosa / Concepto:</span>
                        <span style="color: #0f172a;">Aporte Cena Planazo</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                        <span style="color: #64748b;">Fecha y hora:</span>
                        <span style="color: #0f172a;">10/11/2026 10:45:12</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Nro. Referencia:</span>
                        <strong style="color: #007a3d; font-family: monospace;">BNB-98432170</strong>
                    </div>
                </div>
            </div>
            <div style="text-align: center; margin-top: 14px; font-size: 10px; opacity: 0.8;">
                Documento generado electrónicamente - BNB Móvil
            </div>
        </div>
        `,
    },
    {
        filename: 'comprobante_banco_union_100bs.png',
        title: 'Banco Unión - Pago Simple 100 Bs',
        html: `
        <div style="font-family: system-ui, -apple-system, sans-serif; background: #003366; padding: 24px 16px; border-radius: 20px; color: white; width: 380px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 10px;">
                <div>
                    <div style="font-size: 14px; font-weight: 800; color: #ffcc00; letter-spacing: 1px;">BANCO UNIÓN</div>
                    <div style="font-size: 10px; opacity: 0.9;">Unión Móvil Bolivia</div>
                </div>
                <div style="background: rgba(255,204,0,0.2); color: #ffcc00; font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 10px;">PAGO QR</div>
            </div>
            <div style="background: white; border-radius: 14px; padding: 18px; color: #1e293b;">
                <div style="text-align: center; margin-bottom: 14px;">
                    <div style="font-size: 11px; color: #64748b; font-weight: 600;">IMPORTE TOTAL TRANSFERIDO</div>
                    <div style="font-size: 34px; font-weight: 800; color: #003366; margin: 4px 0;">Bs 100.00</div>
                    <div style="display: inline-block; background: #e0f2fe; color: #0369a1; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 12px;">OPERACIÓN APROBADA</div>
                </div>
                <div style="font-size: 12px; border-top: 1px solid #f1f5f9; padding-top: 12px; line-height: 1.8;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Titular de destino:</span>
                        <strong style="color: #0f172a;">Carlos Gomez</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Entidad bancaria:</span>
                        <strong style="color: #0f172a;">Banco Unión S.A.</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Fecha de emisión:</span>
                        <span style="color: #0f172a;">10/11/2026 11:15:00</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Cód. Autorización:</span>
                        <strong style="color: #003366; font-family: monospace;">BU-202611-77889</strong>
                    </div>
                </div>
            </div>
        </div>
        `,
    },
    {
        filename: 'comprobante_yape_25bs.png',
        title: 'Yape Bolivia - Transferencia 25 Bs',
        html: `
        <div style="font-family: system-ui, -apple-system, sans-serif; background: #742284; padding: 24px 16px; border-radius: 24px; color: white; width: 380px; box-shadow: 0 10px 25px rgba(0,0,0,0.25);">
            <div style="text-align: center; margin-bottom: 12px;">
                <div style="font-size: 26px; font-weight: 900; letter-spacing: -0.5px; color: #00d2c6;">yape</div>
                <div style="font-size: 13px; font-weight: 700; margin-top: 2px;">¡Yapeaste con éxito!</div>
            </div>
            <div style="background: white; border-radius: 18px; padding: 20px; color: #1e293b; text-align: center;">
                <div style="font-size: 36px; font-weight: 800; color: #742284; margin-bottom: 2px;">Bs 25.00</div>
                <div style="font-size: 12px; color: #64748b; margin-bottom: 16px;">Para: <strong style="color: #0f172a;">Ana Martinez</strong></div>
                <div style="border-top: 1px dashed #e2e8f0; padding-top: 14px; font-size: 11px; text-align: left; line-height: 1.8;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Fecha y hora:</span>
                        <strong style="color: #0f172a;">10 nov 2026 - 11:20 am</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Banco origen:</span>
                        <strong style="color: #0f172a;">BCP Bolivia</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Número de operación:</span>
                        <strong style="color: #742284; font-family: monospace;">YAPE-482910</strong>
                    </div>
                </div>
            </div>
            <div style="text-align: center; margin-top: 14px; font-size: 10px; opacity: 0.85;">
                Comprobante oficial Yape Bolivia - Banco de Crédito
            </div>
        </div>
        `,
    },
    {
        filename: 'comprobante_yasta_35bs.png',
        title: 'Yastá - Pago Simple 35.50 Bs',
        html: `
        <div style="font-family: system-ui, -apple-system, sans-serif; background: #008080; padding: 24px 16px; border-radius: 20px; color: white; width: 380px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <div style="font-size: 16px; font-weight: 800; letter-spacing: 0.5px;">Yastá QR</div>
                <div style="font-size: 10px; background: rgba(255,255,255,0.2); padding: 3px 8px; border-radius: 10px;">Red Enlace</div>
            </div>
            <div style="background: white; border-radius: 16px; padding: 20px; color: #1e293b;">
                <div style="text-align: center; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">
                    <div style="font-size: 11px; color: #10b981; font-weight: 700;">TRANSACCIÓN COMPLETADA</div>
                    <div style="font-size: 32px; font-weight: 800; color: #008080; margin: 4px 0;">Bs 35.50</div>
                </div>
                <div style="margin-top: 14px; font-size: 12px; line-height: 1.8;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Comercio / Destino:</span>
                        <strong style="color: #0f172a;">Planazo Eventos</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Fecha:</span>
                        <span style="color: #0f172a;">10/11/2026 11:22:10</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Referencia:</span>
                        <strong style="color: #008080; font-family: monospace;">YST-55992</strong>
                    </div>
                </div>
            </div>
        </div>
        `,
    },
    {
        filename: 'comprobante_discrepante.png',
        title: 'Comprobante Discrepante - 35.50 Bs',
        html: `
        <div style="font-family: system-ui, -apple-system, sans-serif; background: #1e293b; padding: 24px 16px; border-radius: 20px; color: white; width: 380px;">
            <div style="text-align: center; margin-bottom: 14px;">
                <div style="font-size: 13px; font-weight: 700; color: #38bdf8;">BANCO MERCANTIL SANTA CRUZ</div>
                <div style="font-size: 10px; opacity: 0.8;">Transferencia Móvil QR</div>
            </div>
            <div style="background: white; border-radius: 16px; padding: 20px; color: #1e293b;">
                <div style="text-align: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;">
                    <div style="font-size: 11px; color: #0284c7; font-weight: 700;">MONTO ENVIADO</div>
                    <div style="font-size: 32px; font-weight: 800; color: #0369a1; margin: 4px 0;">Bs 35.50</div>
                </div>
                <div style="margin-top: 14px; font-size: 12px; line-height: 1.8;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Beneficiario:</span>
                        <strong style="color: #0f172a;">Carlos Gomez</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Nro. Referencia:</span>
                        <strong style="color: #0369a1; font-family: monospace;">TRX-DISCREP-7721</strong>
                    </div>
                </div>
            </div>
        </div>
        `,
    },
    {
        filename: 'comprobante_invalido.png',
        title: 'Ticket Inválido - No Bancario',
        html: `
        <div style="font-family: monospace; background: #fffbe8; border: 2px dashed #ca8a04; padding: 20px; border-radius: 8px; color: #451a03; width: 340px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
            <div style="text-align: center; border-bottom: 1px dashed #ca8a04; padding-bottom: 10px; margin-bottom: 12px;">
                <div style="font-size: 15px; font-weight: bold;">ESTACIONAMIENTO CENTRAL</div>
                <div style="font-size: 11px;">Ticket de Control Vehicular</div>
            </div>
            <div style="font-size: 12px; line-height: 1.6;">
                <div>Placa: 4022-LKP</div>
                <div>Hora Entrada: 08:30</div>
                <div>Hora Salida: 10:15</div>
                <div style="margin-top: 8px; font-weight: bold; font-size: 14px;">TOTAL COBRADO: Bs 15.00</div>
            </div>
            <div style="text-align: center; margin-top: 14px; font-size: 10px; color: #78350f;">
                NO VÁLIDO COMO CRÉDITO FISCAL NI COMPROBANTE BANCARIO
            </div>
        </div>
        `,
    },
];

async function generate() {
    console.log('Iniciando generador de fixtures sintéticos de comprobantes bancarios...');
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 500, height: 700 },
        deviceScaleFactor: 2,
    });
    const page = await context.newPage();

    for (const fixture of fixtures) {
        const fullHtml = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <style>
                body {
                    margin: 0;
                    padding: 30px;
                    background: #f8fafc;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                    box-sizing: border-box;
                }
            </style>
        </head>
        <body>
            ${fixture.html}
        </body>
        </html>
        `;

        await page.setContent(fullHtml, { waitUntil: 'networkidle' });
        const cardLocator = page.locator('body > div').first();
        const destPath = path.join(outputDir, fixture.filename);

        await cardLocator.screenshot({ path: destPath });
        console.log(`✓ Generado: ${fixture.filename} (${fixture.title})`);

        // Si es el BNB 20bs, también crear copia como comprobante_coincidente.png para compatibilidad
        if (fixture.filename === 'comprobante_bnb_20bs.png') {
            const coincidentePath = path.join(outputDir, 'comprobante_coincidente.png');
            fs.copyFileSync(destPath, coincidentePath);
            console.log('✓ Copiado: comprobante_coincidente.png (espejo de BNB 20bs)');
        }
    }

    await browser.close();
    console.log('Todos los fixtures de comprobantes fueron generados exitosamente en tests/fixtures/comprobantes/');
}

generate().catch((err) => {
    console.error('Error generando fixtures:', err);
    process.exit(1);
});
