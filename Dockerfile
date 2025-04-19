# Use official PHP image as the base
FROM php:8.0-cli

# Install dependencies
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev zip git

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

# Install Composer (if you're using it)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy your PHP code into the container
COPY . .

# Install dependencies (if you have any)
RUN composer install

# Expose port and run PHP built-in server (you can adjust the command if you use a different server)
EXPOSE 80
CMD ["php", "-S", "0.0.0.0:80", "-t", "public"]
