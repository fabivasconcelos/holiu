FROM php:8.2-apache

RUN docker-php-ext-install mysqli

# Configura o Apache para servir a pasta public como raiz do site
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

COPY ./public /var/www/html/public
COPY ./src /var/www/html/src
COPY ./webhook /var/www/html/webhook
COPY .env /var/www/html/.env