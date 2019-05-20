FROM php:5.5-alpine AS composer

# Install Git for Composer
RUN apk add --no-cache git

# Install Composer
RUN  cd /tmp && curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer
RUN  composer self-update

WORKDIR /app

# Install composer dependencies
COPY composer.json .
COPY composer.lock .
RUN composer install --prefer-dist --no-interaction --no-scripts



FROM php:5.5-fpm-alpine

# Install postgresql drivers
RUN apk add --no-cache postgresql-dev postgresql-client \
  && docker-php-ext-install pdo pdo_pgsql

# Enable Redis driver
RUN apk add --no-cache autoconf g++ make \
  && pecl install redis \
  && docker-php-ext-enable redis

# Add NGINX
RUN apk add --no-cache nginx

# Install openssl for wget and certificate generation
RUN apk add --update openssl

# Add Confd to configure parameters on start
ENV CONFD_VERSION="0.16.0"
RUN wget -q -O /usr/local/bin/confd "https://github.com/kelseyhightower/confd/releases/download/v${CONFD_VERSION}/confd-${CONFD_VERSION}-linux-amd64" \
  && chmod +x /usr/local/bin/confd

# Add Waitforit to wait on db starting
ENV WAITFORIT_VERSION="v2.4.1"
RUN wget -q -O /usr/local/bin/waitforit https://github.com/maxcnunes/waitforit/releases/download/$WAITFORIT_VERSION/waitforit-linux_amd64 \
  && chmod +x /usr/local/bin/waitforit

WORKDIR /var/www

# Generate certificate
RUN mkdir -p /etc/nginx/certs
RUN openssl req -newkey rsa:4096 -x509 -nodes -keyout /etc/nginx/certs/app.key -new -out /etc/nginx/certs/app.crt -subj "/C=GB/ST=GB/L=London/O=OPG/OU=Digital/CN=default" -sha256 -days "3650"

EXPOSE 80
EXPOSE 443

# See this page for directories required
# https://symfony.com/doc/3.4/quick_tour/the_architecture.html
COPY --from=composer /app/vendor vendor
COPY src src
COPY app app
COPY scripts scripts
COPY tests tests
COPY web web
COPY docker/confd /etc/confd
ENV TIMEOUT=20
CMD confd -onetime -backend env \
  && waitforit -address=tcp://$API_DATABASE_HOSTNAME:$API_DATABASE_PORT -timeout=$TIMEOUT \
  && php app/console doctrine:migrations:migrate --no-interaction \
  && php app/console doctrine:fixtures:load --no-interaction \
  && mkdir -p var/cache \
  && mkdir -p var/logs \
  && chown -R www-data var \
  && php-fpm -D \
  && nginx
