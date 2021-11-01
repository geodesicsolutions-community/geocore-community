
FROM php:7.4-apache

RUN apt-get update && apt-get install -y \
      git \
      libcurl4-openssl-dev \
      libicu-dev \
      libmcrypt-dev \
      libpng-dev \
      libpq-dev \
      libxml2-dev \
      libzip-dev \
      mariadb-client \
      pkg-config \
      unzip \
      zip \
      zlib1g-dev


RUN rm -r /var/lib/apt/lists/*
RUN pecl install xdebug-3.1.1
RUN docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd
RUN docker-php-ext-install \
      curl \
      gd \
      json \
      xml \
      xmlrpc \
      zip \
      intl \
      pcntl \
      pdo_mysql \
      mysqli \
      opcache
RUN docker-php-ext-enable xdebug

# install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

# change uid and gid of apache to docker user uid/gid
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

# change the web_root to cakephp /var/www/html/src folder
RUN sed -i -e "s/html/html\/src/g" /etc/apache2/sites-enabled/000-default.conf

# enable apache module rewrite
RUN a2enmod rewrite
