# === STAGE 1 : Le constructeur (Build de l'application) ===
FROM composer:2.7 AS builder

WORKDIR /app

# Copier uniquement les fichiers nécessaires pour installer les dépendances
COPY database/ database/
COPY composer.json composer.lock ./

# Installer les dépendances PHP sans les outils de développement (optimisation)
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

# === STAGE 2 : L'image finale de production ===
FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

# Installer les extensions PHP nécessaires pour Laravel et les outils de base
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip

RUN docker-php-ext-install pdo_mysql bcmath gd

# Copier le code du projet depuis notre machine locale
COPY . .

# Remplacer le dossier vendor par celui optimisé du Stage 1 (Multi-stage)
COPY --from=builder /app/vendor ./vendor

# SÉCURITÉ : Créer un utilisateur non-root nommé "devopsuser"
RUN addgroup -g 1000 devopsuser && \
    adduser -u 1000 -G devopsuser -s /bin/sh -D devopsuser

# Donner les droits des dossiers de stockage à notre utilisateur
RUN chown -R devopsuser:devopsuser /var/www/html/storage /var/www/html/bootstrap/cache

# Dire à Docker d'utiliser cet utilisateur non-root pour lancer l'application
USER devopsuser

EXPOSE 9000

CMD ["php-fpm"]
