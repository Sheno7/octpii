#!/bin/bash

# Check if the subdomain.txt file exists
if [ ! -f "/var/www/html/Jinni/app/core/public/subdomain.txt" ]; then
    echo "Error: subdomain.txt file not found."
    exit 1
fi

# Read FRONTEND_DOMAIN and BACKEND_DOMAIN from the file
FRONTEND_DOMAIN=$(head -n 1 /var/www/html/Jinni/app/core/public/subdomain.txt)
BACKEND_DOMAIN=$(tail -n 1 /var/www/html/Jinni/app/core/public/subdomain.txt)

if [ -z "$FRONTEND_DOMAIN" ] || [ -z "$BACKEND_DOMAIN" ]; then
    echo "Error: Subdomains not specified in subdomain.txt."
    exit 1
fi

FRONTEND_PATH="/var/www/html/vendor-dashboard/dist"
URL_REDIRECT="try_files \$uri \$uri/ /index.html;"
BACKEND_PATH="/var/www/html/core/public"

cat <<EOL | sudo tee /etc/nginx/sites-available/$FRONTEND_DOMAIN > /dev/null
server {
    listen 80;
    server_name $FRONTEND_DOMAIN;

    location / {
        root $FRONTEND_PATH;
        index index.html;
        $URL_REDIRECT
    }

    # Add additional configuration for SSL if needed
}
EOL

cat <<EOL | sudo tee /etc/nginx/sites-available/$BACKEND_DOMAIN > /dev/null
server {
    listen 80;
    server_name $BACKEND_DOMAIN;
    root $BACKEND_PATH;
    index index.php;

    location / {
        try_files "\$uri" "\$uri/" "/index.php?\$query_string";
        sendfile on;
        sendfile_max_chunk 256k;
    }

    location ~ \.php$ {
        include fastcgi.conf;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }

    # Add additional configuration for SSL if needed
}
EOL

sudo ln -s /etc/nginx/sites-available/$FRONTEND_DOMAIN /etc/nginx/sites-enabled/ > /dev/null
sudo ln -s /etc/nginx/sites-available/$BACKEND_DOMAIN /etc/nginx/sites-enabled/ > /dev/null

sudo certbot --nginx -d $FRONTEND_DOMAIN -d $BACKEND_DOMAIN > /dev/null

# Clear the subdomain.txt file
#> subdomain.txt

echo "#!/bin/bash" | sudo tee /etc/cron.daily/certbot-renew > /dev/null
echo "certbot renew --quiet" | sudo tee -a /etc/cron.daily/certbot-renew > /dev/null
sudo chmod +x /etc/cron.daily/certbot-renew > /dev/null
(crontab -l 2>/dev/null; echo "0 0 * * * /etc/cron.daily/certbot-renew") | crontab - > /dev/null
sudo systemctl restart nginx > /dev/null
echo > /var/www/html/Jinni/app/core/public/subdomain.txt
echo "Successfully configured domains: $FRONTEND_DOMAIN and $BACKEND_DOMAIN"
