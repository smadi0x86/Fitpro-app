FROM php:8.1-cli

# Update package repository and install necessary packages
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    zip

# Install the PostgreSQL PDO extension
RUN docker-php-ext-install pdo_pgsql

# Install OpenSwoole
RUN pecl install openswoole \
    && docker-php-ext-enable openswoole

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory to /usr/src/app in the container
WORKDIR /usr/src/app

# Copy the application source code and other necessary files (like .env) into the container
COPY . /usr/src/app

# Install Composer dependencies
# --no-dev: Omit development dependencies
# --optimize-autoloader: Optimize the autoloader for performance
RUN composer install --no-dev --optimize-autoloader

# Expose the port that the app runs on. Adjust if your application uses a different port
EXPOSE 9501

# Define the command to start the Swoole server
CMD ["php", "src/server.php"]
