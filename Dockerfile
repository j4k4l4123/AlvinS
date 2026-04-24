FROM php:8.4-cli

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libpq-dev \
    libzip-dev \
    zip \
    nodejs \
    npm \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-dev --optimize-autoloader \
    && npm install \
    && npm run build

EXPOSE 10000

CMD sh -c 'if [ ! -f .env ]; then cp .env.example .env; fi && php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan key:generate --force && php artisan serve --host 0.0.0.0 --port ${PORT:-10000}'
