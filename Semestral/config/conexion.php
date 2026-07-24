<?php
declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host_bd = getenv('DB_HOST');
$puerto_env = getenv('DB_PORT');
$usuario_bd = getenv('DB_USER');
$clave_bd = getenv('DB_PASSWORD');
$nombre_bd = getenv('DB_NAME');

if (
    $host_bd === false || $host_bd === '' ||
    $puerto_env === false || $puerto_env === '' ||
    $usuario_bd === false || $usuario_bd === '' ||
    $clave_bd === false ||
    $nombre_bd === false || $nombre_bd === ''
) {
    throw new RuntimeException('Faltan variables de conexión DB_*.');
}

$puerto_bd = (int) $puerto_env;
$conexion = null;

try {
    $conexion = new mysqli(
        $host_bd,
        $usuario_bd,
        $clave_bd,
        $nombre_bd,
        $puerto_bd
    );

    $conexion->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    $conexion = null;
    error_log('No fue posible conectar con la base de datos: ' . $e->getMessage());
}
?>
