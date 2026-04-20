FROM php:8.2-apache

# Extensiones necesarias
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Instalar Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Configuración de Xdebug
RUN echo "xdebug.mode=debug" > /usr/local/etc/php/conf.d/xdebug.ini \
 && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/xdebug.ini \
 && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/xdebug.ini \
 && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/xdebug.ini