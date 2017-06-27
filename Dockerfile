FROM php:7.1-apache

RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng12-dev \
        libicu-dev \
        g++ \
    && docker-php-ext-install -j$(nproc) iconv mcrypt intl \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

RUN pecl install APCu \
    && docker-php-ext-enable apcu

RUN docker-php-ext-install -j$(nproc) mysqli
RUN docker-php-ext-install -j$(nproc) opcache && docker-php-ext-enable opcache
RUN docker-php-ext-install -j$(nproc) calendar
RUN docker-php-ext-install -j$(nproc) dba

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.remote_enable=1" >> /usr/local/etc/php/php.ini \
    && echo "xdebug.remote_connect_back=1" >> /usr/local/etc/php/php.ini

RUN apt-get update && apt-get install -y openssh-server \
    && sed -i -e"s/^#PasswordAuthentication yes/PasswordAuthentication yes/" /etc/ssh/sshd_config \
    && usermod --password $(echo www-data | openssl passwd -1 -stdin) --shell /bin/bash www-data \
    && chown www-data:www-data /var/www \
    && chmod a+rw /var/www

# for composer
RUN apt-get update && apt-get install -y unzip

RUN curl https://getcomposer.org/composer.phar > /usr/local/bin/composer && chmod +x /usr/local/bin/composer

RUN apt-get update && apt-get install -y git

RUN a2enmod expires headers

COPY run.sh /run.sh

CMD ["/bin/bash", "/run.sh"]
