Abas ERP Lastenheftgenerator - API
==================================

[homestead-config]: ./Homestead.yaml
[docs]: ./docs/index.md
[laravel-homestead]: (https://laravel.com/docs/5.6/homestead)
[laravel-passport]: (https://laravel.com/docs/5.6/passport)
[virtualbox]: (https://www.virtualbox.org/)
[vagrant]: (https://www.vagrantup.com/)
[ide-helper]: (https://github.com/barryvdh/laravel-ide-helper)
[ps-laravel-plugin]: (https://plugins.jetbrains.com/plugin/7532-laravel-plugin)

## Setup

The project uses [Laravel/Homestead][laravel-homestead] as the development environment, 
therefore you have to install [virtualbox][virtualbox] and [vagrant][vagrant].

### First Install

Clone repository

```bash
git clone git@bitbucket.org:GALDigital/abas-specificationsconfigurator-api-web.git
cd abas-specificationsconfigurator-api-web
```

and initialize project 

```bash
# init will run composer install and 
# copy after.sh and
# copy Homestead.yaml from Homestead.yaml.example and
# copy .env from .env.example
make init
```


Adapt your [homestead configuration][homestead-config]

* add the the paths to your vagrant ssh keys:
    
    ```
        authorize: path/to/homestead/public/key.pub` 
        keys:     
            - path/to/homestead/private/key
     ```
             
* if you use `macos` see additional hints under Troubleshooting 


To access the API from your host system via `http://lastenheft.api`
add the following to your `hosts` file

```
192.168.10.10 lastenheft.api
```

Finally generate an app-key, run the migrations and generate oauth2 encryption keys (see [Laravel/Passport][laravel-passport])

```bash
php artisan key:generate
php artisan migrate
php artisan passport:install
```

### Use the development environment

Bring up vagrant/homestead

```bash
# vagrant up
make up
```

Enter environment via ssh
```bash
# vagrant ssh
make ssh
```

Pause you machine
```bash
# vagrant halt
make halt
```

### Troubleshooting

#### macos

If you have problems to bring up vagrant/homestead with nfs under `macos` try the following

* Ensure to have the following vagrant plugins installed
    * vagrant-bindfs 
    * vagrant-env 
    * vagrant-gatling
    * vagrant-share 
    * vagrant-vbguest 
* Add a private network to your [homestead configuration][homestead-config]
    ```
    network:
        - type: "private_network"
          ip: "192.168.10.10"
    ```

## Testing

### Running Tests

```bash
./vendor/bin/phpunit
#or
make test
#or (you just call your global phpunit, but there is no guarantee for same phpunit versions) 
phpunit
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
