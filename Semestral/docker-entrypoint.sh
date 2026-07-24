#!/bin/sh
set -e

# Desactivar todos los MPM conocidos.
a2dismod mpm_event 2>/dev/null || true
a2dismod mpm_worker 2>/dev/null || true
a2dismod mpm_prefork 2>/dev/null || true

# Habilitar únicamente el requerido por mod_php.
a2enmod mpm_prefork

PORT="${PORT:-8080}"

sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost [^>]+>/<VirtualHost *:${PORT}>/" \
    /etc/apache2/sites-available/000-default.conf

echo "Apache iniciando en puerto ${PORT}"

exec apache2-foreground
