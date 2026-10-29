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

    // Exponer función para uso global o programático
    window.mostrarConfirmacion = mostrarConfirmacion;

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
