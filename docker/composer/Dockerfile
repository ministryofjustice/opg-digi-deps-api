FROM php:5.5-alpine

RUN  cd /tmp && curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

RUN  composer self-update

WORKDIR /app

CMD ["composer"]
