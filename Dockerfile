FROM php:8.2-apache

RUN apt update && apt upgrade -y

RUN apt-get install -y \
         libzip-dev \
         && docker-php-ext-install zip

RUN docker-php-ext-install pdo pdo_mysql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY conf.ini /usr/local/etc/php/conf.d/

RUN a2enmod rewrite

WORKDIR /var/www/html

COPY src/composer.json ./

RUN composer update

COPY src/ ./src/

RUN sed -i 's|AllowOverride None|AllowOverride All|' /etc/apache2/apache2.conf

RUN sed -i 's|/var/www/html|/var/www/html/src/public|g' /etc/apache2/sites-available/000-default.conf

RUN chown -R www-data:www-data /var/www/html

RUN composer dump-autoload 

RUN mkdir -p /var/uploads
