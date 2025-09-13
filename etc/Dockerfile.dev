FROM php:8.3-apache

RUN apt-get update && apt-get install -y libzip-dev libpng-dev libjpeg-dev libfreetype6-dev zip unzip git curl libonig-dev

RUN docker-php-ext-install pdo pdo_mysql zip mbstring exif pcntl bcmath gd

RUN a2enmod rewrite && a2enmod actions

WORKDIR /var/www

COPY --from=composer:2.2.7 /usr/bin/composer /usr/bin/composer

COPY ./etc/vhost.conf /etc/apache2/sites-available/000-default.conf

COPY . .

RUN composer install

EXPOSE 9000

CMD ["apache2-foreground"]