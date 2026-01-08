FROM php:8.2-fpm-bookworm

# ---- SYSTEM DEPENDENCIES (OPTIMIZED) ----
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    libmagic-dev \
 && rm -rf /var/lib/apt/lists/*

# ---- PHP EXTENSIONS ----
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install -j$(nproc) \
    gd \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    zip \
    fileinfo

# ---- PHP LIMITS ----
RUN { \
    echo "upload_max_filesize=100M"; \
    echo "post_max_size=100M"; \
    echo "memory_limit=512M"; \
    echo "max_file_uploads=50"; \
    echo "max_execution_time=300"; \
} > /usr/local/etc/php/conf.d/uploads.ini

# ---- COMPOSER ----
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ---- PHP-FPM CONF ----
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# ---- WORKDIR ----
WORKDIR /var/www
RUN chown -R www-data:www-data /var/www
