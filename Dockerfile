FROM php:8.2-apache

# Enable mysqli
RUN docker-php-ext-install mysqli

# Copy app code
COPY . /var/www/html/

# Open port 80
EXPOSE 80
