FROM php:8.3-cli

# Dependências do sistema + extensões
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libsqlite3-dev sqlite3 \
    && docker-php-ext-install pdo pdo_sqlite \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copia o projeto
COPY . .

RUN mkdir -p /var/www/storage /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Instala deps
RUN composer install --no-interaction --prefer-dist

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
