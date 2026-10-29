/**
 * ui.js — Confirmaciones modales nativas (<dialog>) y ergonomía de interacción
 * ==============================================================================
 * Cero dependencias externas (sin SweetAlert2 ni NProgress) y sin demoras artificiales.
 *
 * CÓMO USARLO:
 * 1) CONFIRMACIÓN en un botón de formulario:
 *    <button type="submit"
 *            data-confirm="¿Seguro que deseas eliminar el plan?"
 *            data-title="Eliminar plan"
 *            data-btn-ok="Sí, eliminar"
 *            data-btn-cancel="Volver"
 *            data-type="danger">Eliminar</button>
 *
 * 2) CONFIRMACIÓN en un enlace:
 *    <a href="/ruta"
 *       data-confirm="¿Deseas continuar?"
 *       data-btn-ok="Sí, continuar">Ir a la ruta</a>
 *
 * 3) PROGRAMÁTICAMENTE:
 *    const confirmado = await window.mostrarConfirmacion({
 *        titulo: 'Confirmar acción',
 *        mensaje: '¿Estás seguro?',
 *        textoAceptar: 'Sí, continuar',
 *        textoCancelar: 'Cancelar',
 *        destructivo: false
 *    });
 */

(function () {
    'use strict';

    /**
     * Muestra el modal <dialog> de confirmación y retorna una Promesa con la elección.
     * @param {Object} opciones
     * @returns {Promise<boolean>}
     */
    function mostrarConfirmacion({
        titulo = 'Confirmar acción',
        mensaje = '¿Estás seguro de continuar?',
        textoAceptar = 'Confirmar',
        textoCancelar = 'Cancelar',
        destructivo = false,
    } = {}) {
        return new Promise((resolve) => {
            const dialog = document.getElementById('dialog-confirm');

            // Si por alguna razón no existe el dialog en el DOM, fallback seguro nativo
            if (!dialog || typeof dialog.showModal !== 'function') {
                resolve(window.confirm(mensaje));
                return;
            }

            const elTitulo   = document.getElementById('dialog-confirm-titulo');
            const elMensaje  = document.getElementById('dialog-confirm-mensaje');
            const btnAceptar = document.getElementById('dialog-confirm-aceptar');
            const btnCancelar = document.getElementById('dialog-confirm-cancelar');

            if (elTitulo) elTitulo.textContent = titulo;
            if (elMensaje) elMensaje.textContent = mensaje;
            if (btnAceptar) btnAceptar.textContent = textoAceptar;
            if (btnCancelar) btnCancelar.textContent = textoCancelar;

            if (btnAceptar) {
                if (destructivo) {
                    btnAceptar.className = 'boton boton-rojo';
                } else {
                    btnAceptar.className = 'boton';
                }
            }

            let cerrado = false;

            function limpiar() {
                cerrado = true;
                btnAceptar?.removeEventListener('click', onAceptar);
                btnCancelar?.removeEventListener('click', onCancelar);
                dialog.removeEventListener('close', onClose);
                dialog.removeEventListener('click', onBackdropClick);
            }

            function onAceptar() {
                if (cerrado) return;
                limpiar();
                dialog.close();
                resolve(true);
            }

            function onCancelar() {
                if (cerrado) return;
                limpiar();
                dialog.close();
                resolve(false);
            }

            function onClose() {
                if (cerrado) return;
                limpiar();
                resolve(false);
            }

            function onBackdropClick(e) {
                const rect = dialog.getBoundingClientRect();
                const clicFuera = (
                    e.clientX < rect.left ||
                    e.clientX > rect.right ||
                    e.clientY < rect.top ||
                    e.clientY > rect.bottom
                );
                if (clicFuera) {
                    onCancelar();
                }
            }

            btnAceptar?.addEventListener('click', onAceptar);
            btnCancelar?.addEventListener('click', onCancelar);
            dialog.addEventListener('close', onClose);
            dialog.addEventListener('click', onBackdropClick);

            dialog.showModal();
        });
    }

    /**
     * Muestra una notificación Toast flotante auto-dismissable (3s) con botón de cierre manual (Nielsen H3).
     * @param {string|{mensaje: string, tipo?: string}} mensaje Texto o configuración de la notificación
     * @param {string} [tipo='exito'] 'exito' | 'error'
     * @returns {HTMLElement|undefined}
     */
    function mostrarToast(mensaje, tipo = 'exito') {
        let texto = mensaje;
        let estilo = tipo;
        if (typeof mensaje === 'object' && mensaje !== null) {
            texto = mensaje.mensaje || '';
            estilo = mensaje.tipo || 'exito';
        }

        if (!texto) return;

        let contenedor = document.getElementById('toast-contenedor');
        if (!contenedor) {
            contenedor = document.createElement('div');
            contenedor.id = 'toast-contenedor';
            contenedor.className = 'toast-contenedor fixed top-5 right-5 z-50 flex flex-col gap-2 pointer-events-none';
            contenedor.setAttribute('aria-live', 'polite');
            document.body.appendChild(contenedor);
        }

        const toast = document.createElement('div');
        toast.className = `toast-alerta toast-${estilo} pointer-events-auto flex items-center justify-between gap-3 p-4 rounded-xl shadow-lg border transition-all duration-300 ease-out`;
        toast.setAttribute('role', estilo === 'error' ? 'alert' : 'status');

        const iconoSvg = estilo === 'error'
            ? '<svg class="toast-icono w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>'
            : '<svg class="toast-icono w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';

        toast.innerHTML = `
            <div class="toast-contenido flex items-center gap-3">
                ${iconoSvg}
                <span class="toast-mensaje text-sm font-medium">${texto}</span>
            </div>
            <button type="button" class="toast-cerrar min-w-[44px] min-h-[44px] flex items-center justify-center text-xl leading-none rounded-lg cursor-pointer" aria-label="Cerrar notificación">
                <span aria-hidden="true">&times;</span>
            </button>
        `;

        contenedor.appendChild(toast);

        let descartado = false;
        function descartar() {
            if (descartado) return;
            descartado = true;
            toast.classList.add('toast-ocultando');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 300);
        }

        const temporizador = setTimeout(descartar, 3000);

        const btnCerrar = toast.querySelector('.toast-cerrar');
        btnCerrar?.addEventListener('click', () => {
            clearTimeout(temporizador);
            descartar();
        });

        return toast;
    }

    // Exponer funciones para uso global o programático
    window.mostrarConfirmacion = mostrarConfirmacion;
    window.mostrarToast = mostrarToast;

    /* ─── Interceptar clics en elementos con data-confirm ─────────────── */
    document.addEventListener('click', async function (e) {
        const trigger = e.target.closest('[data-confirm]');
        if (!trigger) return;

        e.preventDefault();
        e.stopPropagation();

        const form = trigger.closest('form');
        const metodo = (form?.querySelector('input[name="_method"]')?.value || form?.method || '').toUpperCase();

        const esDestructivo = (
            trigger.dataset.type === 'danger' ||
            trigger.classList.contains('boton-rojo') ||
            metodo === 'DELETE'
        );

        const confirmado = await mostrarConfirmacion({
            titulo: trigger.dataset.title || 'Confirmar acción',
            mensaje: trigger.dataset.confirm || '¿Estás seguro?',
            textoAceptar: trigger.dataset.btnOk || 'Sí, continuar',
            textoCancelar: trigger.dataset.btnCancel || 'Cancelar',
            destructivo: esDestructivo,
        });

        if (!confirmado) return;

        // Si pertenece a un formulario: submit inmediato (sin retrasos artificiales)
        if (form) {
            form.dataset.enviando = '1';
            form.submit();
            return;
        }

        // Si es un enlace de navegación: redirigir inmediatamente
        if (trigger.tagName === 'A' && trigger.href) {
            window.location.href = trigger.href;
        }
    });

    /* ─── Prevención de doble envío síncrona para formularios generales ── */
    document.addEventListener('submit', function (e) {
        const form = e.target;

        if (e.defaultPrevented) return;
        if ((form.method || 'get').toLowerCase() === 'get') return;
        if (form.closest('[data-no-spinner]')) return;

        if (form.dataset.enviando) {
            e.preventDefault();
            return;
        }

        form.dataset.enviando = '1';
    });

    /* ─── Restauración de estado si el usuario navega atrás (bfcache) ──── */
    window.addEventListener('pageshow', (e) => {
        if (e.persisted) {
            document.querySelectorAll('form[data-enviando]').forEach((f) => delete f.dataset.enviando);
            const dialog = document.getElementById('dialog-confirm');
            if (dialog && dialog.open) {
                dialog.close();
            }
        }
    });
})();
