FROM php:8.3-cli

# Install system dependencies (including all libs needed by PHP extensions)
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libpq-dev \
    libonig-dev \
    git \
    unzip \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Configure gd with jpeg + freetype support before installing
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Install PHP extensions (gd separate because it needs configuring above)
RUN docker-php-ext-install pdo pdo_pgsql pdo_mysql pdo_sqlite zip mbstring bcmath
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
