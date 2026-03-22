FROM php:8.1-apache

RUN docker-php-ext-install mysqli

RUN apt-get update && apt-get install -y \
    && rm -rf /var/lib/apt/lists/*

RUN phpdismod -v ALL -s ALL mpm_event mpm_worker 2>/dev/null || true \
    && a2dismod mpm_event mpm_worker 2>/dev/null || true \
    && a2enmod mpm_prefork 2>/dev/null || true

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
