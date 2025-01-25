FROM php:8.4-fpm

RUN apt-get update && apt-get install -y git zip libmemcached-dev zlib1g-dev libssl-dev

RUN docker-php-ext-install pdo_mysql

COPY ./src ./src
COPY ./tests ./tests
COPY composer.json .
COPY ./vendor ./vendor

RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer

RUN pecl install xdebug

# install the memcached extension
RUN yes '' | pecl install -f memcached && docker-php-ext-enable memcached

# install the memcache extension
RUN yes '' | pecl install -f memcache && docker-php-ext-enable memcache

# install the redis extension
RUN pecl install redis
RUN docker-php-ext-enable redis