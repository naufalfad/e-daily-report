FROM php:8.2-fpm

# 1. Install dependencies sistem
# libpq-dev: untuk driver PostgreSQL
# libfreetype6-dev, libjpeg62-turbo-dev, libpng-dev: untuk GD (Gambar/PDF)
# libzip-dev: untuk Zip
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Konfigurasi & Install Ekstensi PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath zip

# 3. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Set Permission Folder Kerja
WORKDIR /var/www
RUN chown -R www-data:www-data /var/www
