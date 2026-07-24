<?php

declare(strict_types=1);

$titulo_pagina = 'Compartir reporte';

require_once __DIR__ . '/../includes/utilidades.php';

if (session_status() === PHP_SESSION_NONE) {
    $directorio_sesiones = __DIR__ . '/../storage/sessions';

    if (!is_dir($directorio_sesiones)) {
        mkdir($directorio_sesiones, 0775, true);
    }

    session_save_path($directorio_sesiones);
    session_start();
}

$contenido_reporte = $_POST['contenido_reporte']
    ?? ($_SESSION['reporte_para_enviar'] ?? '');

$asunto_reporte = $_POST['asunto_reporte']
    ?? ($_SESSION['asunto_para_enviar']
        ?? 'Reporte de planilla — Planilla Prospera');

// Guarda el reporte en sesión cuando se llega desde una pantalla de reporte.
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['contenido_reporte'])
) {
    $_SESSION['reporte_para_enviar'] = $contenido_reporte;
    $_SESSION['asunto_para_enviar'] = $asunto_reporte;
}

require __DIR__ . '/../includes/header.php';
?>

<div class="encabezado-pagina">
    <div>
        <h1>Compartir reporte</h1>
        <p>
            Genera el reporte en PDF y compártelo desde tu dispositivo.
        </p>
    </div>
</div>

<?php if ($contenido_reporte === ''): ?>
    <div class="mensaje mensaje-info">
        Genere un reporte y use su botón “Compartir reporte”.
    </div>
<?php endif; ?>

<div class="formulario formulario-correo">
    <div class="campo">
        <label for="correo_destino">
            Correo del destinatario
        </label>

        <input
            type="email"
            id="correo_destino"
            name="correo_destino"
            placeholder="profesora@utp.ac.pa"
        >
    </div>

    <div class="campo">
        <label for="asunto_reporte">
            Asunto
        </label>

        <input
            type="text"
            id="asunto_reporte"
            name="asunto_reporte"
            value="<?= escapar($asunto_reporte) ?>"
        >
    </div>

    <div class="acciones-formulario">
        <button
            type="button"
            id="btnCompartirReporte"
            class="boton boton-dorado"
            <?= $contenido_reporte === '' ? 'disabled' : '' ?>
        >
            Compartir PDF
        </button>

        <a
            href="generar_pdf.php"
            id="btnDescargarReporte"
            class="boton"
            <?= $contenido_reporte === ''
                ? 'aria-disabled="true" tabindex="-1"'
                : 'download' ?>
        >
            Descargar PDF
        </a>
    </div>

    <p
        id="estadoCompartir"
        class="texto-ayuda"
        aria-live="polite"
    ></p>
</div>

<script>
(() => {
    const botonCompartir = document.getElementById(
        'btnCompartirReporte'
    );

    const botonDescargar = document.getElementById(
        'btnDescargarReporte'
    );

    const estado = document.getElementById('estadoCompartir');
    const campoAsunto = document.getElementById('asunto_reporte');
    const campoDestinatario = document.getElementById(
        'correo_destino'
    );

    botonDescargar?.addEventListener('click', (evento) => {
        if (
            botonDescargar.getAttribute('aria-disabled') === 'true'
        ) {
            evento.preventDefault();

            estado.textContent =
                'Primero debe generar un reporte.';
        }
    });

    botonCompartir?.addEventListener('click', async () => {
        const asunto = campoAsunto?.value.trim()
            || 'Reporte de Planilla Prospera';

        const destinatario = campoDestinatario?.value.trim() || '';

        if (
            destinatario !== ''
            && !esCorreoValido(destinatario)
        ) {
            estado.textContent =
                'El correo del destinatario no es válido.';

            campoDestinatario.focus();
            return;
        }

        botonCompartir.disabled = true;
        estado.textContent = 'Generando el PDF...';

        try {
            const respuesta = await fetch('generar_pdf.php', {
                method: 'GET',
                credentials: 'same-origin',
                cache: 'no-store',
                headers: {
                    Accept: 'application/pdf'
                }
            });

            if (!respuesta.ok) {
                const mensaje = await respuesta.text();

                throw new Error(
                    mensaje || 'No fue posible generar el PDF.'
                );
            }

            const tipoContenido =
                respuesta.headers.get('Content-Type') || '';

            if (!tipoContenido.includes('application/pdf')) {
                const mensaje = await respuesta.text();

                throw new Error(
                    mensaje || 'El servidor no devolvió un PDF válido.'
                );
            }

            const blob = await respuesta.blob();

            if (blob.size === 0) {
                throw new Error(
                    'El PDF generado está vacío.'
                );
            }

            const nombreArchivo = obtenerNombreArchivo(
                respuesta.headers.get('Content-Disposition')
            );

            const archivo = new File(
                [blob],
                nombreArchivo,
                {
                    type: 'application/pdf',
                    lastModified: Date.now()
                }
            );

            const datosCompartir = {
                title: asunto,
                text: destinatario
                    ? `Reporte preparado para ${destinatario}`
                    : 'Reporte generado por Planilla Prospera',
                files: [archivo]
            };

            const puedeCompartirArchivo =
                typeof navigator.share === 'function'
                && typeof navigator.canShare === 'function'
                && navigator.canShare({
                    files: [archivo]
                });

            if (puedeCompartirArchivo) {
                await navigator.share(datosCompartir);

                estado.textContent =
                    'El reporte fue entregado al menú de compartir.';
            } else {
                descargarArchivo(blob, archivo.name);

                estado.textContent =
                    'Tu navegador no permite compartir archivos. '
                    + 'El PDF fue descargado.';
            }
        } catch (error) {
            if (
                error instanceof DOMException
                && error.name === 'AbortError'
            ) {
                estado.textContent = 'Se canceló el proceso.';
            } else {
                console.error(error);

                estado.textContent =
                    error instanceof Error
                        ? error.message
                        : 'No fue posible compartir el reporte.';
            }
        } finally {
            botonCompartir.disabled = false;
        }
    });

    function esCorreoValido(correo) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo);
    }

    function obtenerNombreArchivo(disposicion) {
        if (!disposicion) {
            return 'reporte-planilla.pdf';
        }

        const coincidencia = disposicion.match(
            /filename="?([^"]+)"?/i
        );

        return coincidencia?.[1]
            || 'reporte-planilla.pdf';
    }

    function descargarArchivo(blob, nombre) {
        const url = URL.createObjectURL(blob);
        const enlace = document.createElement('a');

        enlace.href = url;
        enlace.download = nombre;

        document.body.appendChild(enlace);
        enlace.click();
        enlace.remove();

        setTimeout(() => {
            URL.revokeObjectURL(url);
        }, 1000);
    }
})();
</script>

<?php
require __DIR__ . '/../includes/footer.php';
?>
