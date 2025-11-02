#!/bin/bash
set -e

# Install composer dependencies if vendor directory doesn't exist
if [ ! -d "/var/www/html/vendor" ]; then
    echo "Installing composer dependencies..."
    cd /var/www/html
    # Run composer as www-data user to maintain correct ownership
    su -s /bin/bash www-data -c "composer install --no-dev --optimize-autoloader"
    echo "Composer dependencies installed."
fi

# Ensure cache directories exist and are writable
mkdir -p /var/www/html/templates/_cache /var/www/html/templates/_compile
chown -R www-data:www-data /var/www/html/templates/_cache /var/www/html/templates/_compile
chmod -R 777 /var/www/html/templates/_cache /var/www/html/templates/_compile

# Execute the main container command (apache2-foreground)
exec "$@"
