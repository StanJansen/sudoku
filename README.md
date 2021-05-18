# Stanjan/Sudoku ![PHP dependency](https://img.shields.io/badge/php-%3E%3D8.0-blue) ![OpenCV](https://img.shields.io/badge/opencv-%5E4.5-blue)

## Information
This project uses logic to generate and solve sudoku's. It also uses OpenCV and OCR to read sudoku's from photo's.

### OCR
The image sent to the OCR implementation gets preprocessed with OpenCV. For example:

![OpenCV before image](https://gitlab.com/Stanjan/sudoku/-/raw/master/tests/images/sudoku-photo-2.jpg)
![OpenCV after image](https://gitlab.com/Stanjan/sudoku/-/raw/master/tests/images/sudoku-photo-2-ocr.jpg)

Supported OCR implementations:
* [OCR Space](https://ocr.space/ocrapi)

## Installation

### Composer
```
composer install
```

### OpenCV
This library requires OpenCV to process images before applying OCR on them. Please follow the installation guide on https://github.com/php-opencv/php-opencv.
```
apk update && apk install -y wget pkg-config cmake git php-dev
wget https://raw.githubusercontent.com/php-opencv/php-opencv-packages/master/opencv_4.5.0_amd64.deb
dpkg -i opencv_4.5.0_amd64.deb
rm opencv_4.5.0_amd64.deb
git clone https://github.com/php-opencv/php-opencv.git
cd php-opencv
phpize
./configure --with-php-config=/usr/bin/php-config
make && make install
cd ../
rm -rf php-opencv
echo "extension=opencv.so" > /etc/php/8.0/cli/conf.d/opencv.ini
```

## Dev tools

### PHPStan
```
vendor/bin/phpstan analyse
```

### PHP-CS-fixer
#### Installation
```
composer install --working-dir=tools/php-cs-fixer
```

#### Usage
```
tools/php-cs-fixer/vendor/bin/php-cs-fixer fix ./ --dry-run --diff
```