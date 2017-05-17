FROM registry.service.opg.digital/opguk/php-fpm0x644:0.0.46-dev

RUN  apt update && apt install -y \
     nodejs dos2unix ruby && \
     apt-get clean && apt-get autoremove && \
     rm -rf /var/lib/cache/* /var/lib/log/* /tmp/* /var/tmp/*

RUN  cd /tmp && curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

RUN  gem install sass

# build app dependencies
COPY composer.json /app/
COPY composer.lock /app/
WORKDIR /app
USER app
ENV  HOME /app
RUN  composer install --prefer-source --no-interaction --no-scripts

# install remaining parts of app
ADD  . /app
USER root
RUN find . -not -user app -exec chown app:app {} \;
USER app
ENV  HOME /app
RUN  composer run-script post-install-cmd --no-interaction
RUN  composer dump-autoload --optimize

# cleanup
RUN  rm /app/app/config/parameters.yml
USER root
ENV  HOME /root

# app configuration
ADD docker/confd /etc/confd

# let's make sure they always work
RUN dos2unix /app/scripts/*

# copy init scripts
ADD  docker/my_init.d /etc/my_init.d
RUN  chmod a+x /etc/my_init.d/*

ENV  OPG_SERVICE api
