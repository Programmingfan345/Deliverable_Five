FROM php:8.2-apache

# Install and enable mysqli extension
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Set working directory to Apache document root
WORKDIR /var/www/html

# Copy everything from the project into the container
COPY . /var/www/html

# Make sure Apache can read the files
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
