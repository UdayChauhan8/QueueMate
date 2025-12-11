# Multi-service container: nginx + php-fpm + supervisor + composer
FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
    nginx \
    php8.1-fpm php8.1-cli php8.1-pgsql php8.1-redis php8.1-mbstring php8.1-xml php8.1-curl php8.1-zip php8.1-bcmath \
    curl git unzip supervisor rsync && \
    curl -sS https://getcomposer.org/installer -o composer-setup.php && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    rm -f composer-setup.php && \
    mkdir -p /run/php /var/www/html

# Nginx config
RUN rm -f /etc/nginx/sites-enabled/default
COPY nginx.conf /etc/nginx/sites-available/laravel
RUN ln -s /etc/nginx/sites-available/laravel /etc/nginx/sites-enabled/laravel

# Supervisor config
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

WORKDIR /var/www/html

# Pre-copy overrides (routes, migrations, etc.)
# We'll create Laravel during build, then apply overrides again
COPY src/ /tmp/backend-src/

# Create Laravel app
RUN composer create-project --prefer-dist laravel/laravel:^10.0 app && \
    rsync -a app/ ./ && rm -rf app && \
    composer require predis/predis pusher/pusher-php-server twilio/sdk && \
    php artisan key:generate && \
    php artisan storage:link

# Apply overrides (controllers, models, migrations, routes, etc.)
RUN rsync -a /tmp/backend-src/ /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
