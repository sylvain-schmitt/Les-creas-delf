FROM php:8.3-fpm

# Installe les dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    zip \
    libonig-dev \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql intl zip opcache gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installe Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Définit le dossier de travail
WORKDIR /var/www/html

# Copie des fichiers du projet
COPY . .

# Création des dossiers var et permissions (style user)
RUN mkdir -p var/cache var/logs public/uploads \
    && chmod -R 777 var/ public/

# Installation des dépendances PHP
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction \
    && composer dump-autoload --optimize

# ===== BUILDS D'ASSETS OGAN =====
# 1. Télécharge le binaire Tailwind (avec mémoire illimitée pour le téléchargement)
RUN php -d memory_limit=-1 bin/console tailwind:init
# 2. Compile le CSS en mode prod
RUN php -d memory_limit=-1 bin/console tailwind:build --minify
# 3. Installe les assets JS
RUN php -d memory_limit=-1 bin/console assets:install
# 4. Compile les routes
RUN php -d memory_limit=-1 bin/console cache:routes

# Configuration Opcache
RUN echo "opcache.enable=1\n\
    opcache.memory_consumption=128\n\
    opcache.max_accelerated_files=10000\n\
    opcache.validate_timestamps=0" > /usr/local/etc/php/conf.d/opcache.ini

# Variables d'environnement de base
ENV APP_ENV=prod

# Exposition du port 8002
EXPOSE 8002

# Démarrage avec le serveur interne PHP (Comme demandé)
CMD ["php", "-S", "0.0.0.0:8002", "-t", "public"]
