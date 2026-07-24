<?php
declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host_bd = getenv('DB_HOST') ?: 'localhost';
$puerto_bd = (int) (getenv('DB_PORT') ?: 3306);
$usuario_bd = getenv('DB_USER') ?: 'root';
$clave_bd = getenv('DB_PASSWORD') ?: '';
$nombre_bd = getenv('DB_NAME') ?: 'planilla_prospera';

$conexion = null;

try {
    $conexion = new mysqli($host_bd, $usuario_bd, $clave_bd, $nombre_bd, $puerto_bd);
    $conexion->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    $conexion = null;
    error_log('No fue posible conectar con la base de datos: ' . $e->getMessage());
}
?>
