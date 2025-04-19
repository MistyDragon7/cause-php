# Use the official PHP image as the base image
FROM php:8.0-cli

# Install dependencies if needed
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev zip git

# Install PHP extensions if needed (e.g., for GD, PDO, etc.)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd pdo pdo_mysql

# Install Composer for PHP dependencies if you use it
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory to the root of your project
WORKDIR /var/www/html

# Copy the entire project into the Docker container
COPY . .

# Expose port 80 (the standard port for HTTP)
EXPOSE 80

# Run PHP built-in server to serve static files and handle PHP requests
CMD ["php", "-S", "0.0.0.0:80", "-t", "."]
