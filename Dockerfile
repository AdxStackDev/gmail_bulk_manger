FROM php:8.2-apache

# Copy application files to Apache root
COPY . /var/www/html/

# Ensure Apache rewrite module is enabled
RUN a2enmod rewrite

EXPOSE 80
