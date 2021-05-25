FROM phpdockerio/php80-cli:latest

LABEL description="Docker image for PHP 8 with xDebug, Composer and OpenCV."
LABEL maintainer="stanjan@live.nl"

RUN apt-get update && apt-get install --yes --quiet --no-install-recommends \
    libzip-dev \
    zip \
    unzip \
    wget \
    pkg-config \
    cmake \
    autoconf \
    build-essential \
    php-dev \
    php-xdebug \
    git && \
    wget https://raw.githubusercontent.com/php-opencv/php-opencv-packages/master/opencv_4.5.0_amd64.deb && \
    dpkg -i opencv_4.5.0_amd64.deb && \
    rm opencv_4.5.0_amd64.deb && \
    git clone https://github.com/php-opencv/php-opencv.git && \
    cd php-opencv && phpize && ./configure && make && make install && cd ../ && rm -rf php-opencv && \
    echo "extension=opencv.so" > /etc/php/8.0/cli/conf.d/opencv.ini

COPY --from=composer:2.0.13 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME /tmp
ENV COMPOSER_VERSION 2.0.13
ENV XDEBUG_MODE coverage