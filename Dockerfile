FROM php:8.3.13-apache

RUN apt-get update \
    && apt-get install -y libicu-dev libxml2-dev libzip-dev libpng-dev libonig-dev libjpeg62-turbo libjpeg62-turbo-dev libfreetype6-dev \
    && docker-php-ext-install intl xml xmlwriter gettext mbstring zip mysqli pdo_mysql exif \
    && docker-php-ext-enable intl xml xmlwriter gettext mbstring zip mysqli pdo_mysql exif \
    && docker-php-ext-install -j$(nproc) iconv \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-enable gd

RUN a2enmod rewrite

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

COPY . /var/www/html

RUN composer install

# Copy sample config files
RUN if [ -f config/csp.sample.php ] && [ ! -f config/csp.php ]; then \
        cp config/csp.sample.php config/csp.php; \
    fi

# Backup membercard assets to a location outside the volume mount
# so they can be restored at runtime by entrypoint.sh
RUN cp -r /var/www/html/files/membercard /opt/slims-assets-membercard

RUN chown -R www-data:www-data /var/www/html/files
RUN chown -R www-data:www-data /var/www/html/images
RUN chown -R www-data:www-data /var/www/html/repository
RUN chown -R www-data:www-data /var/www/html/config

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]

CMD ["apache2-foreground"]
