FROM registry.gitlab.com/jitesoft/dockerfiles/phpunit:8.0

RUN apt update
 && apt install -y wget
 && wget https://raw.githubusercontent.com/php-opencv/php-opencv-packages/master/opencv_4.5.0_amd64.deb
 && dpkg -i opencv_4.5.0_amd64.deb
 && rm opencv_4.5.0_amd64.deb
 && apt update
 && apt install -y pkg-config cmake git php-dev
 && git clone https://github.com/php-opencv/php-opencv.git
 && cd php-opencv
 && phpize
 && ./configure --with-php-config=/usr/bin/php-config
 && make
 && make install
 && rm -rf php-opencv
 && echo "extension=opencv.so" > /etc/php/8.0/cli/conf.d/opencv.ini
 && php -v

ENTRYPOINT ["entrypoint"]
CMD ["php"]