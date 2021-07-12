# Laravel Mixins

Laravel mixins collection.

## Installation

In your `composer.json`, add this repository:
```
"repositories": [
    {
        "type": "git",
        "url": "https://github.com/tenantcloud/laravel-mixins"
    }
],
```
Then do `composer require tenantcloud/laravel-mixins` to install the package.

### Commands
Install dependencies:
`docker run -it --rm -v $PWD:/app -w /app composer install`

Run tests:
`docker run -it --rm -v $PWD:/app -w /app php:7.4-cli vendor/bin/phpunit`

Run php-cs-fixer on self:
`docker run -it --rm -v $PWD:/app -w /app composer cs-fix`
