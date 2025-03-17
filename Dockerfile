# FROM alpine:3.18
FROM alpine:latest

# Install dependencies
RUN apk add --no-cache \
    php82 \
    php82-fpm \
    php82-pdo \
    php82-pdo_mysql \
    php82-mbstring \
    php82-openssl \
    php82-json \
    php82-tokenizer \
    php82-xml \
    php82-dom \
    php82-xmlwriter \
    php82-fileinfo \
    php82-ctype \
    php82-phar \
    php82-session \
    php82-zlib \
    php82-curl \
    php82-zip \
    php82-gd \
    php82-bcmath \
    php82-simplexml \
    php82-xmlreader \
    php82-opcache \
    php82-pecl-xdebug \
    nginx \
    curl \
    bash \
    git \
    supervisor
    # npm

# Configure PHP-FPM
RUN rm -f /usr/bin/php && \ 
    ln -s /usr/bin/php82 /usr/bin/php && \
    sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/g' /etc/php82/php.ini && \
    sed -i 's/user = nobody/user = nginx/g' /etc/php82/php-fpm.d/www.conf && \
    sed -i 's/group = nobody/group = nginx/g' /etc/php82/php-fpm.d/www.conf && \
    sed -i 's/listen = 127.0.0.1:9000/listen = \/var\/run\/php-fpm.sock/g' /etc/php82/php-fpm.d/www.conf && \
    sed -i 's/;listen.owner = nobody/listen.owner = nginx/g' /etc/php82/php-fpm.d/www.conf && \
    sed -i 's/;listen.group = nobody/listen.group = nginx/g' /etc/php82/php-fpm.d/www.conf && \
    sed -i 's/;listen.mode = 0660/listen.mode = 0660/g' /etc/php82/php-fpm.d/www.conf

# Configure Xdebug
RUN echo "xdebug.mode=develop,debug" >> /etc/php82/conf.d/50_xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /etc/php82/conf.d/50_xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /etc/php82/conf.d/50_xdebug.ini \
    && echo "xdebug.discover_client_host=0" >> /etc/php82/conf.d/50_xdebug.ini \
    && echo "xdebug.client_port=9003" >> /etc/php82/conf.d/50_xdebug.ini \
    && echo "xdebug.log=/var/www/html/storage/logs/xdebug.log" >> /etc/php82/conf.d/50_xdebug.ini

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configure Nginx
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
RUN mkdir -p /run/nginx

# Configure supervisord
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY . /var/www/html

# Set permissions
RUN chown -R nginx:nginx /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && mkdir -p /var/log/supervisor \
    && chown nginx:nginx /var/log/supervisor

# Expose ports
EXPOSE 80

# Start services using supervisord
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]