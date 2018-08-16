# PHP Env Builder
[![Latest Version](https://img.shields.io/github/tag/dbtlr/php-env-builder.svg?style=flat&label=release)](https://github.com/dbtlr/php-env-builder/tags)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/dbtlr/php-env-builder/master.svg?style=flat)](https://travis-ci.org/dbtlr/php-env-builder)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/dbtlr/php-env-builder.svg?style=flat)](https://scrutinizer-ci.com/g/dbtlr/php-env-builder/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/dbtlr/php-env-builder.svg?style=flat)](https://scrutinizer-ci.com/g/dbtlr/php-env-builder)
[![Total Downloads](https://img.shields.io/packagist/dt/dbtlr/php-env-builder.svg?style=flat)](https://packagist.org/packages/dbtlr/php-env-builder)

Makes building .env files from the command-line simple.

## Installation

The recommended method of installing this library is via [Composer](https://getcomposer.org/).

Run the following command from your project root:

```bash
$ composer require dbtlr/php-env-builder
```


## Usage

```php
require_once __DIR__ . "/vendor/autoload.php";

$config = [
    'verbose' => true,
    'loadEnv' => true,
];

$builder = new \Dbtlr\PHPEnvBuilder\Builder('/path/to/.env', $config);

$builder->ask(
    'name',              // ENV variable name
    'What is your name?' // Command prompt
    '',                  // Default answer
    true                 // Is required?
);

$builder->run(); // Run the builder and return the answers.
$builder->write(); // Write the answers to the file.
```
