FROM php:8.4-apache

RUN pecl install pcov \
 && docker-php-ext-enable pcov

RUN a2enmod rewrite

RUN sed -i 's|DocumentRoot .*|DocumentRoot /var/www/html/public|' \
    /etc/apache2/sites-available/000-default.conf

RUN echo '<Directory /var/www/html>' \
    > /etc/apache2/conf-enabled/allow-override.conf \
 && echo 'AllowOverride All' >> /etc/apache2/conf-enabled/allow-override.conf \
 && echo '</Directory>' >> /etc/apache2/conf-enabled/allow-override.conf

WORKDIR /var/www/html
