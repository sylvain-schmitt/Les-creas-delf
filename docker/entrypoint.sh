#!/bin/sh
set -e

echo "🚀 Ogan entrypoint starting..."

# Utilisateur PHP-FPM (adapter si besoin)
PHP_USER="www-data"
PHP_GROUP="www-data"

# Dossiers à préparer
DIRS="
/var/www/html/public/uploads
/var/www/html/var/cache
/var/www/html/var/log
"

for DIR in $DIRS; do
  echo "📂 Checking $DIR"

  mkdir -p "$DIR"
  chown -R $PHP_USER:$PHP_GROUP "$DIR"
  chmod -R 775 "$DIR"
done

echo "✅ Permissions OK"

# Lancer la commande originale (php-fpm)
exec "$@"