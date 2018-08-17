# PHP Env Builder
[![Latest Version](https://img.shields.io/github/tag/dbtlr/php-env-builder.svg?style=flat&label=release)](https://github.com/dbtlr/php-env-builder/tags)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/dbtlr/php-env-builder/master.svg?style=flat)](https://travis-ci.org/dbtlr/php-env-builder)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/dbtlr/php-env-builder.svg?style=flat)](https://scrutinizer-ci.com/g/dbtlr/php-env-builder/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/dbtlr/php-env-builder.svg?style=flat)](https://scrutinizer-ci.com/g/dbtlr/php-env-builder)
[![Total Downloads](https://img.shields.io/packagist/dt/dbtlr/php-env-builder.svg?style=flat)](https://packagist.org/packages/dbtlr/php-env-builder)

Makes building .env files from the command-line simple.

For loading and using .env files in your development workflow, I highly suggest looking to the [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) package, which helps abstract out the process of loading environmental variables. This library is designed to help you build a local .env library that you can add to your .gitignore file, without the extra .env.example file that most people add to document how to build it.

[Why .env?](https://github.com/vlucas/phpdotenv#why-env)

## Installation

The recommended method of installing this library is via [Composer](https://getcomposer.org/).

Run the following command from your project root:

```bash
$ composer require --dev dbtlr/php-env-builder
```


## Usage as a Composer Script

Because Composer provides its own way of working with the console IO, the `extra.php-env-builder` config is the proper way of setting up your questions and variable names in this use case.

In order to have your .env file built when you run `composer install` or `composer update` you should provide config similar to this:

```json
{
    "scripts": {
        "build-env": "Dbtlr\\PHPEnvBuilder\\ComposerScriptRunner::build",
        "post-install-cmd": "@build-env",
        "post-update-cmd": "@build-env"
    },
    "extra": {
        "php-env-builder": {
            "envFile": ".env",
            "questions": [
                {
                    "name": "MYSQL_HOST",
                    "prompt": "What is the hostname for the MySQL server?",
                    "default": "127.0.0.1",
                    "required": true
                },
                {
                    "name": "MYSQL_PORT",
                    "prompt": "The port for the MySQL server?",
                    "default": "3306",
                    "required": true
                },
                {
                    "name": "MYSQL_USER",
                    "prompt": "What is the MySQL user?",
                    "default": "app",
                    "required": true
                },
                {
                    "name": "MYSQL_PASSWORD",
                    "prompt": "What is the MySQL password?",
                    "default": "app-password",
                    "required": true
                }
            ]
        }
    }
}

```

### All `extra.php-env-builder` options

- questions - default: [] `(required)` // An array of questions that contain at least the `name` and `prompt` elements.
- envFile - default: `.env` // Either the absolute location or one that is relative to your `package.json` file.
- clobber - default: `false` // Will an existing .env file be overwritten on build?
- loadEnv - default: `false` // If an existing file is being clobbered, will it be loaded to provide defaults?
- verbose - default: `false` // Extra output 

## General Usage

If you would like to roll your own script, the syntax itself is fairly straightforward.

Note: This is a less expected use case, since the console IO needs to be provided by Composer, in order for it to accept input from inside a Composer script. This may be useful if you want to run this instead from a Makefile or an `npm run postinstall`

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

## Running tests?

All tests are run using PHPUnit. Make sure you have at least PHP 7.1 installed, as well as [Composer](https://getcomposer.org).

To run tests, simply run:

```bash
composer install
composer test
```

## Want to contribute?

[Read more here](https://github.com/dbtlr/php-env-builder/blob/master/CONTRIBUTING.md)
