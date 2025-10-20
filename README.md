Abas ERP Lastenheftgenerator - API
==================================

[docs]: ./docs/index.md
[dotenv]: ./.env
[laravel-passport]: https://laravel.com/docs/5.6/passport
[docker]: https://www.docker.com/
[vagrant]: https://www.vagrantup.com/
[ide-helper]: https://github.com/barryvdh/laravel-ide-helper
[ps-laravel-plugin]: https://plugins.jetbrains.com/plugin/7532-laravel-plugin

## Setup

The project uses docker for development environment.

### First Install

Clone repository

```bash
git clone git@github.com:digitalist-se/abas-specificationsconfigurator-api-web.git
cd abas-specificationsconfigurator-api-web
```

and initialize project 

```bash
make init-env
make init
```

Adapt your [.env][dotenv]:
* add your ssh key to DOCKER_GIT_SSH_KEY
* add mailtrap credentials MAIL_USERNAME, MAIL_PASSWORD 

To access the API from your host system via `https://erpplanner.test`
add the following to your `hosts` file

```
127.0.0.1 erpplanner.test
```

### Use the development environment

## Testing

### Running Tests

```bash
./vendor/bin/phpunit
#or
make test
``` 

## Development

### Code Style

#### PHP 

For a consistent php code style, there is an local `php-cs-fixer` and `php_cs`config file.

```bash
./vendor/bin/php-cs-fixer --verbose --config=./.php_cs fix
#or
make csfix
``` 

### Languages

Automatic update lang files of laravel 
``` 
php artisan lang:update
```

### IDE helper

The [barryvdh/laravel-ide-helper][ide-helper] is used to provide accurate auto-completion 
and class/method/property/helper/... recognition of laravel magic.

The artisan commands

```bash
    php artisan ide-helper:generate
    php artisan ide-helper:meta
```

are part of the composer `post-update-cmd` script, but could be run manually if necessary.

If you create new Eloquent models or update existent ones, run

```bash
 php artisan ide-helper:model
``` 

This will generate/update required phpDocs of the model files. 
Be aware that this overwrites the existent docs. 
If you have added custom comments, run

```bash
 php artisan ide-helper:model --nowrite
``` 

and copy required phpDocs from generated `_ide_helper_models.php` manually.
Remove this file afterwards to avoid duplicate declaration warnings.

#### phpStorm

Install [Laravel Plugin][ps-laravel-plugin] for phpStorm and enable auto-completion.

##### Note

Due to the generated `_ide_helper.php` phpStorm will generate a lot of "Multiple definitions exists for class ..." warnings.
Yet there is no real solution for this issue, without loosing the advantages of the `_ide_helper.php`, see
(github issues)[https://github.com/barryvdh/laravel-ide-helper/issues?utf8=%E2%9C%93&q=is%3Aissue+multiple+definition].

A obviously not satisfying circumvention is to enable phpStorm `Don't report multiple class declaration potential problems`
under `Settings -> Editor -> Inspections -> PHP -> Undefined -> Undefined class`.

## Documentation

Your find a separate application documentation under [./docs][docs].
