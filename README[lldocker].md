# Lightweight Laravel Docker

"Lightweight" Docker environment for local development that fulfill for most php project that based on the laravel framework.

## Features: 
* https using mkcert `DOCKER_DOMAIN` in your .env define local domain
* nginx as server
* php-fpm
* phpunit via ssh
* supervisor to start processes
    * queue worker / horizon
    * cron
* crontab to perform schedular tasks
* redis for caching and queue worker
* clone and composer install from private git. `DOCKER_GIT_SSH_KEY` in your .env is required.
* same node/npm and yarn version from container
* Makefile
* wkhtmltopdf

## Init

This project can be used as drop in. Point path to local dir and copy all files into your project.
merge content from `.env.example`.   
default: `DOCKER_APP_CODE_PATH_HOST=./`  
 
Or it can be used as parallel project. Then fork this project.   
`DOCKER_APP_CODE_PATH_HOST=../project`

Import .idea/* files for deployment configuration of intellij/phpstorm used for phpf


## Testing
### cli interpreter PHPSTORM
[Languages - PHP - CLI Interpreter](jetbrains://PhpStorm/settings?name=Languages+%26+Frameworks--PHP)
Add Remote interpreter via deployment configuration `docker`  
`/usr/local/bin/php`
Add phpunit remote interpreter

[Languages - PHP - Test Frameworks](jetbrains://PhpStorm/settings?name=Languages+%26+Frameworks--PHP--Test+Frameworks)
`/var/www/html/phpunit.xml` default config

### via ssh
User: `tester`  
Password: no password given. use key  
keyfile: ./.docker/web/ssh/insecure_id_rsa  

### db
The initial settings of phpunit in laravel project will perform faster tests (inmemory with sqlite).
But for some cases if sqlite is not enough there is a schema given `testing`. 
```xml
<php>
    <server name="DB_CONNECTION" value="mysql"/>
    <server name="DB_HOST" value="db"/>
    <server name="DB_DATABASE" value="testing"/>
</php>
```

## Feature can be enabled

* swagger editor. search for `TODO add swagger`
* supervisor for horizon. search for `TODO add horizon`

## Dev setup

copy and prepare .env (path to ssh key)
`cp .env.example .env`

init Docker environment
`make init`

## First start

You have to `make restart` the docker containers so the supervisor can start all necessary processes

## CS FIXER
required is php-cs-fixer in your project
`make composer "req --dev friendsofphp/php-cs-fixer"`  
You can run php-cs-fixer from Makefile.
`make php-cs-fixer $FilePathRelativeToProjectRoot$`

## QUEUE

If you are Working on code that will affect queue and broadcasting, then you need to restart queue. 
`make art queue:restart`

PHPStorm, just add a file watcher for php file to run `make art queue:restart`

## Assets
`make yarn` or `make vendor-yarn` install vendor  
`make webpack` run webpack in container  
`make webpack-watch` run webpack watch in container (using poll)
