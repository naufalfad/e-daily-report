FROM php:8.2-fpm

# 1. Install dependencies sistem
# [FIX] Tambahkan 'libmagic-dev' (library wajib untuk deteksi Mime Type PDF akurat)
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
    libwebp-dev \
    libmagic-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Konfigurasi & Install Ekstensi PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath zip fileinfo

# 3. Atur batasan upload PHP
RUN echo "upload_max_filesize=100M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size=100M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit=512M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo 'max_file_uploads=50' >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo 'max_execution_time=300' >> /usr/local/etc/php/conf.d/uploads.ini

# 4. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Set working dir
WORKDIR /var/www
RUN chown -R www-data:www-data /var/www
