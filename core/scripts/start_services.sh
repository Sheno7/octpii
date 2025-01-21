#!/bin/bash
# Restart Nginx to apply changes
systemctl restart nginx
# Restart PHP-FPM; adjust the version number as necessary
systemctl restart php8.2-fpm

php /var/www/html/testing/autodeploy/artisan cache:clear
php /var/www/html/testing/autodeploy/artisan config:cache
php /var/www/html/testing/autodeploy/artisan route:cache

