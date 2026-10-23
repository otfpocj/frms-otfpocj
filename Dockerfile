FROM php:8.2-apache

# ติดตั้ง Driver สำหรับ MySQL (mysqli และ pdo_mysql)
RUN docker-php-ext-install mysqli pdo pdo_mysql

COPY . /var/www/html/
EXPOSE 80
