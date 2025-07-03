FROM dunglas/frankenphp

WORKDIR /app

RUN apt-get update \
    && apt-get install -y unzip git libzip-dev libpng-dev libonig-dev libxml2-dev pkg-config libssl-dev zlib1g-dev \
    && pecl install redis \
    && docker-php-ext-enable redis

COPY . /app

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install --no-interaction --no-dev --optimize-autoloader

EXPOSE  443
EXPOSE 80