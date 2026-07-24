FROM php:8.2-apache

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
    && docker-php-ext-install \
        mysqli \
        pdo \
        pdo_mysql \
        zip \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN if [ -f composer.json ]; then \
        composer install \
            --no-dev \
            --optimize-autoloader \
            --no-interaction \
            --prefer-dist; \
    fi

RUN mkdir -p /var/www/html/storage/sessions \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage

CMD ["sh", "-c", "\
PORT=${PORT:-8080}; \
sed -ri \"s!Listen 80!Listen 0.0.0.0:${PORT}!\" /etc/apache2/ports.conf; \
sed -ri \"s!<VirtualHost \\*:80>!<VirtualHost 0.0.0.0:${PORT}>!\" /etc/apache2/sites-available/000-default.conf; \
echo \"Apache escuchando en 0.0.0.0:${PORT}\"; \
apache2-foreground"]
