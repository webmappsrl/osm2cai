FROM wodby/php:7.4-dev

USER root

WORKDIR /var/www/html/osm2cai
COPY www.conf /usr/local/etc/php-fpm.d/www.conf


RUN docker-php-ext-enable xdebug
# RUN  apk update && apk add --no-cache \
#         mysql-client \
#         vim \
#         libmcrypt-dev \
#         libpng-dev \
#         g++ \
#         imagemagick \
#         git \
#         curl \
#         curl-dev \
#         icu-dev \
#         libzip-dev \
#         libxml2-dev \
#         && rm -rf /var/cache/apk/* \
#         #&& docker-php-ext-install -j$(nproc) iconv \
#         #&& docker-php-ext-install -j$(nproc) gd \
#         #&& docker-php-ext-configure gd \
#         && docker-php-ext-configure intl \
#         && docker-php-ext-configure curl \
#         #&& docker-php-ext-install xml \
#         #&& docker-php-ext-install -j$(nproc) intl \
#         #&& docker-php-ext-install -j$(nproc) zip \
#         #&& docker-php-ext-install -j$(nproc) pdo \
#         #&& docker-php-ext-install -j$(nproc) pdo_mysql \
#         #&& docker-php-ext-install mysqli \
#         && docker-php-ext-install -j$(nproc) opcache \
#         && pecl install xdebug-3 \
#         && pecl install imagick \
#         && docker-php-ext-enable xdebug \
#         && docker-php-ext-enable imagick

COPY xdebug.ini /usr/local/etc/php/conf.d/.
