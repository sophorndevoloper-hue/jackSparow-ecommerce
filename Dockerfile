FROM php:8.4-fpm-alpine

# Install Nginx, PostgreSQL, tools, and NodeJS/NPM
RUN apk add --no-cache \
    nginx \
    postgresql-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    nodejs \
    npm \
    oniguruma-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip opcache

# Configure OPcache
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.revalidate_freq=0'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.save_comments=1'; \
    echo 'opcache.fast_shutdown=1'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini

# Ensure PHP-FPM listens on 127.0.0.1:9000 and has sufficient workers
RUN sed -i 's|^listen = .*|listen = 127.0.0.1:9000|' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/pm.max_children = 5/pm.max_children = 20/' /usr/local/etc/php-fpm.d/www.conf

# Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

# Build Vite frontend assets
RUN npm install && npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/public/build \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Create Nginx run directory
RUN mkdir -p /run/nginx /var/log/nginx

# Configure Nginx virtual host with FastCGI pointing to 127.0.0.1:9000
RUN rm -f /etc/nginx/http.d/default.conf && \
    echo 'server {' > /etc/nginx/http.d/laravel.conf && \
    echo '    listen 10000 default_server;' >> /etc/nginx/http.d/laravel.conf && \
    echo '    server_name _;' >> /etc/nginx/http.d/laravel.conf && \
    echo '    root /var/www/public;' >> /etc/nginx/http.d/laravel.conf && \
    echo '    index index.php index.html;' >> /etc/nginx/http.d/laravel.conf && \
    echo '    location / {' >> /etc/nginx/http.d/laravel.conf && \
    echo '        try_files $uri $uri/ /index.php?$query_string;' >> /etc/nginx/http.d/laravel.conf && \
    echo '    }' >> /etc/nginx/http.d/laravel.conf && \
    echo '    location ^~ /storage/ {' >> /etc/nginx/http.d/laravel.conf && \
    echo '        expires 7d;' >> /etc/nginx/http.d/laravel.conf && \
    echo '        try_files $uri /assets/images/placeholder-product.svg;' >> /etc/nginx/http.d/laravel.conf && \
    echo '    }' >> /etc/nginx/http.d/laravel.conf && \
    echo '    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|webp|avif|woff|woff2|ttf)$ {' >> /etc/nginx/http.d/laravel.conf && \
    echo '        expires max;' >> /etc/nginx/http.d/laravel.conf && \
    echo '        log_not_found off;' >> /etc/nginx/http.d/laravel.conf && \
    echo '        try_files $uri =404;' >> /etc/nginx/http.d/laravel.conf && \
    echo '    }' >> /etc/nginx/http.d/laravel.conf && \
    echo '    location ~ \.php$ {' >> /etc/nginx/http.d/laravel.conf && \
    echo '        fastcgi_pass 127.0.0.1:9000;' >> /etc/nginx/http.d/laravel.conf && \
    echo '        fastcgi_index index.php;' >> /etc/nginx/http.d/laravel.conf && \
    echo '        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;' >> /etc/nginx/http.d/laravel.conf && \
    echo '        include fastcgi_params;' >> /etc/nginx/http.d/laravel.conf && \
    echo '        fastcgi_read_timeout 300;' >> /etc/nginx/http.d/laravel.conf && \
    echo '    }' >> /etc/nginx/http.d/laravel.conf && \
    echo '}' >> /etc/nginx/http.d/laravel.conf

# Setup startup entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 10000

ENTRYPOINT ["docker-entrypoint.sh"]