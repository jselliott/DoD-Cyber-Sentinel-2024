FROM php:8.1-apache


# Copy the current directory to /var/www/html in the container
COPY challenge /var/www/html
COPY flag.txt /flag.txt

# Install necessary dependencies and enable PHP extensions
RUN apt-get update && \
    apt-get install -y libxml2-dev libbz2-dev zlib1g-dev curl sqlite3 libsqlite3-dev git && \
    docker-php-ext-install intl pdo_sqlite && \
    a2enmod rewrite && \
    apt-get clean && rm -r /var/lib/apt/lists/*



# Composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    && chmod +x /usr/local/bin/composer \
    && composer self-update


# Set up Apache configuration
ADD config/apache.conf /etc/apache2/sites-available/000-default.conf


COPY entrypoint.sh /usr/local/bin/

RUN chmod +x /usr/local/bin/entrypoint.sh
RUN chmod -R 777 /var/www/html

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

CMD ["apache2-foreground"]


