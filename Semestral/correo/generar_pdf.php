<?php

declare(strict_types=1);

use Dompdf\Dompdf;
use Dompdf\Options;

if (session_status() === PHP_SESSION_NONE) {
    $directorioSesiones = __DIR__ . '/../storage/sessions';

    if (!is_dir($directorioSesiones)) {
        mkdir($directorioSesiones, 0775, true);
    }

    session_save_path($directorioSesiones);
    session_start();
}

$autoload = __DIR__ . '/../vendor/autoload.php';

if (!is_file($autoload)) {
    http_response_code(500);
    exit('No se encontró vendor/autoload.php.');
}

require_once $autoload;

$contenidoReporte = $_SESSION['reporte_para_enviar'] ?? '';
$asuntoReporte = $_SESSION['asunto_para_enviar']
    ?? 'Reporte de planilla — Planilla Prospera';

if (trim($contenidoReporte) === '') {
    http_response_code(400);
    exit('No existe un reporte disponible para generar.');
}

/*
 * Agregamos una estructura HTML completa y estilos básicos compatibles
 * con Dompdf. El contenido del reporte permanece dentro del body.
 */
$htmlCompleto = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <style>
        @page {
            margin: 28px 32px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #17233f;
            line-height: 1.45;
        }

        h1, h2, h3 {
            color: #14213d;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid #d6dbe5;
            padding: 7px;
            text-align: left;
        }

        th {
            background: #14213d;
            color: #ffffff;
        }

        .no-imprimir,
        button,
        form,
        nav {
            display: none !important;
        }
    </style>
</head>

<body>
    {$contenidoReporte}
</body>
</html>
HTML;

$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($htmlCompleto, 'UTF-8');
$dompdf->setPaper('letter', 'portrait');
$dompdf->render();

$pdf = $dompdf->output();

$nombreArchivo = 'reporte-planilla-'
    . date('Y-m-d-His')
    . '.pdf';

header('Content-Type: application/pdf');
header(
    'Content-Disposition: attachment; filename="' .
    $nombreArchivo .
    '"'
);
header('Content-Length: ' . strlen($pdf));
header('Cache-Control: no-store, no-cache, must-revalidate');

echo $pdf;
?>
