Stanjan/Sudoku
=======

This project uses logic to generate and solve sudoku's. It also uses OpenCV and OCR to read sudoku's from photo's.

[![Version](https://img.shields.io/badge/version-v0.1.0-informational)](https://packagist.org/packages/stanjan/sudoku)
[![MIT License](https://img.shields.io/badge/license-MIT-yellow)](https://gitlab.com/Stanjan/sudoku/-/blob/master/LICENSE)
[![PHP dependency](https://img.shields.io/badge/php-%3E%3D8.0-8892BF)](https://gitlab.com/Stanjan/sudoku/-/blob/master/composer.json)
[![OpenCV](https://img.shields.io/badge/opencv-%5E4.5-1e7dff)](https://github.com/php-opencv/php-opencv)

[![Pipeline](https://gitlab.com/Stanjan/sudoku/badges/master/pipeline.svg)](https://gitlab.com/Stanjan/sudoku/-/commits/master)
[![Coverage](https://gitlab.com/Stanjan/sudoku/badges/master/coverage.svg)](https://gitlab.com/Stanjan/sudoku/-/commits/master)
[![PHPStan](https://user-content.gitlab-static.net/d4ae0e0efb3625dfb95247271afa58a0060027cf/68747470733a2f2f696d672e736869656c64732e696f2f62616467652f5048505374616e2d6c6576656c253230372d627269676874677265656e2e7376673f7374796c653d666c6174)](https://gitlab.com/Stanjan/sudoku/-/commits/master)

## Installation

### Composer
```bash
$ composer require stanjan/sudoku
```

### OpenCV
This library requires OpenCV to process images before applying OCR on them. Please follow the installation guide on https://github.com/php-opencv/php-opencv.

## Information

### OCR
The image sent to the OCR implementation gets preprocessed with OpenCV. For example:

![OpenCV before image](https://gitlab.com/Stanjan/sudoku/-/raw/master/tests/images/sudoku-photo-2.jpg)
![OpenCV after image](https://gitlab.com/Stanjan/sudoku/-/raw/master/tests/images/sudoku-photo-2-ocr.jpg)

Supported OCR implementations:
* [OCR Space](https://ocr.space/ocrapi)

## Dev tools
You can use the [Dockerfile](https://gitlab.com/Stanjan/sudoku/-/blob/master/Dockerfile) in this repository for development.

### PHPStan
```bash
$ vendor/bin/phpstan analyse
```

### PHP-CS-fixer
#### Installation
```bash
$ composer install --working-dir=tools/php-cs-fixer
```

#### Usage
```bash
$ tools/php-cs-fixer/vendor/bin/php-cs-fixer fix ./ --dry-run --diff
```