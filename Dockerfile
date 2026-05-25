FROM php:8.4-fpm

# Installer les extensions système nécessaires à Laravel + MySQL
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip xml

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# Dossier de travail dans le conteneur
WORKDIR /var/www

# Copier seulement les fichiers de dépendances d'abord (optimisation)
COPY composer.json composer.lock ./

# Installer les dépendances PHP (en mode production plus tard on ajoutera des options)
RUN composer install --no-interaction --prefer-dist --no-scripts --no-dev

# Copier le reste de l'application
COPY . .

# Donner les bons droits au dossier storage et bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache

# Exposer le port PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
