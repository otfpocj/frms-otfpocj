FROM php:8.2-apache

# ติดตั้งไลบรารีและ Driver สำหรับ PostgreSQL (pdo_pgsql)
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql

COPY . /var/www/html/
EXPOSE 80
