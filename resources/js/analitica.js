/**
 * Analítica de Interacción - Planazo
 * Captura intentos de usuario (User Intent) desacoplados de la interfaz.
 */
(function (window) {
    'use strict';

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta && meta.content) {
            return meta.content;
        }
        const input = document.querySelector('input[name="_token"]');
        return input ? input.value : '';
    }

    async function guardarEvento(registro) {
        try {
            const token = getCsrfToken();
            await fetch('/eventos', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    evento: registro.evento,
                    props: registro.props || {},
                }),
            });
        } catch (err) {
            // Falla silenciosa para no degradar la experiencia de navegación del usuario
            console.warn('[analitica] Error al registrar evento:', err);
        }
    }

    function track(evento, props = {}) {
        const registro = {
            evento: evento,
            props: props,
            fecha: new Date().toISOString(),
        };

        guardarEvento(registro);
        console.log('[evento]', registro);
    }

    window.track = track;
})(window);
