ARGS = $(filter-out $@,$(MAKECMDGOALS))

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

init: ## initialize Homestead files
	bash -c "./init.sh"

up: ## boot homestead
	vagrant up

halt: ## shutdown homestead
	vagrant halt

ssh: ## go into homestead vm with ssh
	vagrant ssh

reset: ## run install and reset db
	make install
	make db-reset

install: ## Install all dependencies. composer, yarn. frontend and backend
	vagrant ssh -c "cd /home/vagrant/api && composer install"
	bash -c "yarn"

webpack: ## build assets in
	bash -c "yarn run dev"

webpack-watch: ## build and start watch. restart build after files has been changed
	bash -c "yarn run watch"

webpack-hot: ## TBD. do we need this?
	bash -c "yarn run hot"

webpack-production: ## build production assets
	bash -c "yarn run production"

db-reset: ## migrate fresh and seed database. create client token
	vagrant ssh -c "cd /home/vagrant/api/ && php artisan migrate:fresh"
	vagrant ssh -c "cd /home/vagrant/api/ && php artisan db:seed"
	vagrant ssh -c "cd /home/vagrant/api/ && php artisan passport:install"

db-migrate: ## migrate database
	vagrant ssh -c "cd /home/vagrant/api && php artisan migrate"

db-seed: ## seed all data into database
	vagrant ssh -c "cd /home/vagrant/api && php artisan db:seed"

deploy-stage: ## deploy to staging server
	bash -c "./vendor/bin/dep deploy stage"

test: ## Run phpunit tests
	bash -c "./vendor/bin/phpunit"

csfix: ## Run php-cs-fixer fix with local config
	bash -c "./vendor/bin/php-cs-fixer --config=./.php_cs --verbose fix"
