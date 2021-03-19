ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
$(eval $(RUN_ARGS):;@:)

WEBPACK_ROOT := ./
.PHONY: help

include .env

## Common
#################################

help: ## Show Help
	@echo -e "$$(grep -hE '(^\S+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | sed -e 's/:.*##\s*/:/' -e 's/^\(.\+\):\(.*\)/\x1b[36m\1\x1b[m:\2/' | column -c2 -t -s :)"

init-env:
	@if [ ! -f ./.env ]; then echo "cp .env.example .env" && cp .env.example .env; else echo ".env already exits"; fi

require-env:
	@if [ ! -f ./.env ]; then echo "missing .env run 'make init-env'"; exit 1; fi

copy-git-key:
	cp ${DOCKER_GIT_SSH_KEY} ./.docker/web/ssh/git_id_rsa

generate-ssl-key: ## generate ssl for localhost
	@bash -c "cd ./.docker/web/ssl-keys && mkcert -cert-file localhost.crt -key-file localhost.key localhost 127.0.0.1 ::1 ${DOCKER_DOMAIN}"

init: build boot vendor-composer vendor-yarn ## initializes docker machine
	@make -s art key:generate
	sleep 10 # wait that db is ready
	@make -s art migrate
	@make -s art db:seed
	@make -s art passport:install

build: require-env generate-ssl-key copy-git-key ## build all docker containers
	bash -c "docker-compose build"

clear-volumes: ## deletes all volumes of this project
	docker-compose down -v

reset: clear-volumes init ## clear volumes and run init command

vendor-composer: ## installs composer dependencies
	@make -s composer install

vendor-yarn: ## install node modules
	@make -s yarn

## Docker booting
#################################

boot: require-env
	bash -c "docker-compose up -d"

up: boot vendor-composer ## start docker

down: ## stop  all docker containers
	bash -c "docker-compose down"

restart: down up ## restart all docker containers

## Docker login to containers
#################################

bash: ## open docker bash via ssh
	bash -c "docker-compose exec -u www-data web bash"

bash-root: ## open docker bash via ssh as root
	bash -c "docker-compose exec -u root web bash"

bash-db: ## open docker bash via ssh as root
	bash -c "docker-compose exec db bash"

zsh: ## open docker zsh with oh-my-zsh via ssh
	bash -c "docker-compose exec -u www-data web zsh"

zsh-root: ## open docker zsh with oh-my-zsh via ssh as root
	bash -c "docker-compose exec -u root web zsh"

## Forwarding commands
#################################
php: ## forward php command to container
	docker-compose exec -u www-data web bash -c 'php ${ARGS}'

composer: ## forward composer command to container
	docker-compose exec -u www-data web bash -c 'composer ${ARGS}'

yarn: ## forward yarn command to container
	docker-compose exec -u www-data web bash -c '. /usr/local/nvm/nvm.sh && yarn ${ARGS}'

art: ## forward artisan command to container
	docker-compose exec -T -u www-data web bash -c 'php artisan ${ARGS}'

laravel: ## forward laravel installer command to container
	docker-compose exec -T -u www-data web bash -c '. /usr/local/nvm/nvm.sh && laravel ${ARGS}'

## TODO: add if you have also vapor
# vapor: ## forward vapor command to container
# 	docker-compose exec -u www-data web bash -c 'php ./vendor/bin/vapor ${ARGS}'

## TODO: add if you use laravel echo server
# echo: ## forward laravel-echo-server command to container
# 	docker-compose exec -T echo laravel-echo-server ${ARGS}

php-cs-fixer: ## forward csfixer command to container, pass file path relative to project root dir
	docker-compose exec -T -u www-data web bash -c 'php /var/www/html/vendor/bin/php-cs-fixer fix /var/www/html/${ARGS} --config "/var/www/html/.php_cs"'

## IDE Helper
#################################
ide-helper: ## Runs all php artisan ide-helper tasks
	@make -s art "clear-compiled"
	@make -s composer "dumpauto -o"
	@make -s art "ide-helper:eloquent -n"
	@make -s art "ide-helper:generate -n"
	@make -s art "ide-helper:meta -n"
	@make -s art "ide-helper:models -nM"

## Builds Assets / Node commands
#################################

webpack: ## build assets
	@make -s yarn "dev"
webpack-watch: ## build assets and start watching for file changes
	@make -s yarn "watch-poll"
webpack-production: ## build assets and start watching for file changes
	@make -s yarn "production"

## Migration helpers
#################################

migrate-test: ## migrates one step back and forward
	@make -s art "migrate:refresh --step=1"

## PHPUNIT
#################################

test: ## run phpunit tests
	@make -s php './vendor/bin/phpunit -v --colors=never --stderr'

# match all unkown tasks
%:
	@:
