FROM php:8.3-cli

# Install ALL system libraries needed by PHP extensions in one layer
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libpq-dev \
    libonig-dev \
    libsqlite3-dev \
    git \
    unzip \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Configure gd before installing it
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Install each extension individually so a single failure is isolated
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_pgsql
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install pdo_sqlite
RUN docker-php-ext-install zip
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install bcmath
RUN docker-php-ext-install gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install Node.js 20
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copy full source
COPY . .

# Install PHP dependencies (production only)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Build frontend assets then remove node_modules to keep image slim
RUN npm install && npm run build && rm -rf node_modules

# Ensure SQLite file and writable directories exist
RUN touch database/database.sqlite \
    && chmod -R 777 storage bootstrap/cache database

# Make start script executable
RUN chmod +x render-start.sh

EXPOSE 10000

CMD ["/bin/sh", "render-start.sh"]
