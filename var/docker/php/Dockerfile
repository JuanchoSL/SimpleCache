FROM php:8.2-fpm

RUN apt-get update && apt-get install -y git zip libmemcached-dev zlib1g-dev libssl-dev

RUN docker-php-ext-install pdo_mysql

COPY ./src ./src
COPY ./tests ./tests
COPY composer.json .
COPY ./vendor ./vendor

RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer
#RUN composer update

RUN git config --global user.email \'JuanchoSL@hotmail.com\'
RUN git config --global user.name \'Juan Sánchez\'

#COPY var/docker/php/conf.d/php.ini /usr/local/etc/php/php.ini

RUN pecl install xdebug
#COPY var/docker/php/conf.d/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini


# install the memcached extension
RUN yes '' | pecl install -f memcached && docker-php-ext-enable memcached

# install the memcache extension
RUN yes '' | pecl install -f memcache && docker-php-ext-enable memcache

# install the redis extension
RUN pecl install redis
RUN docker-php-ext-enable redis