Stanjan/Sudoku
=======

This project uses logic to generate and solve sudoku's. It also uses OpenCV and OCR to read sudoku's from photo's.

[![Latest version](https://img.shields.io/github/v/tag/stanjansen/sudoku?label=version&sort=semver)](//packagist.org/packages/stanjan/sudoku)
[![License](https://img.shields.io/github/license/stanjansen/sudoku)](//packagist.org/packages/stanjan/sudoku)
[![PHP dependency](https://img.shields.io/badge/php-%3E%3D8.0-8892BF)](https://gitlab.com/Stanjan/sudoku/-/blob/master/composer.json)
[![Pipeline](https://gitlab.com/Stanjan/sudoku/badges/master/pipeline.svg)](https://gitlab.com/Stanjan/sudoku/-/commits/master)
[![Coverage](https://gitlab.com/Stanjan/sudoku/badges/master/coverage.svg)](https://gitlab.com/Stanjan/sudoku/-/commits/master)

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