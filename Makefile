# Executables (local)
DOCKER_COMP = docker compose

# Docker containers
PHP_CONT = $(DOCKER_COMP) exec php

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer
SYMFONY  = $(PHP) bin/console

# Misc
.DEFAULT_GOAL = help
.PHONY        : help build up start down logs sh composer vendor sf cc test

## —— 🎵 🐳 The Symfony Docker Makefile 🐳 🎵 ——————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9\./_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
buildc: ## Builds the Docker images
	@$(DOCKER_COMP) -f compose.yaml -f compose.override.yaml build --pull --no-cache

build: ## Builds the Docker images
	@$(DOCKER_COMP) -f compose.yaml -f compose.override.yaml build --pull

up: ## Start the docker hub in detached mode (no logs)
	@$(DOCKER_COMP) -f compose.yaml -f compose.override.yaml up --detach
	@$(PHP_CONT) bin/console sass:build
	
start: build up ## Build and start the containers

down: ## Stop the docker hub
	@$(DOCKER_COMP) -f compose.yaml -f compose.override.yaml down --remove-orphans

logs: ## Show live logs
	@$(DOCKER_COMP) -f compose.yaml -f compose.override.yaml logs --tail=0 --follow

sh: ## Connect to the FrankenPHP container
	@$(PHP_CONT) sh

bash: ## Connect to the FrankenPHP container via bash so up and down arrows go to previous commands
	@$(PHP_CONT) bash

test: ## Start tests with phpunit, pass the parameter "c=" to add options to phpunit, example: make test c="--group e2e --stop-on-failure"
	@$(eval c ?=)
	@$(DOCKER_COMP) -f compose.yaml -f compose.override.yaml exec -e APP_ENV=test php bin/phpunit $(c)


## —— Composer 🧙 ——————————————————————————————————————————————————————————————
composer: ## Run composer, pass the parameter "c=" to run a given command, example: make composer c='req symfony/orm-pack'
	@$(eval c ?=)
	@$(COMPOSER) $(c)

vendor: ## Install vendors according to the current composer.lock file
vendor: c=install --prefer-dist --no-dev --no-progress --no-scripts --no-interaction
vendor: composer

## —— Symfony 🎵 ———————————————————————————————————————————————————————————————
sf: ## List all Symfony commands or pass the parameter "c=" to run a given command, example: make sf c=about
	@$(eval c ?=)
	@$(SYMFONY) $(c)

cc: c=c:c ## Clear the cache
cc: sf

fixtures: ## Load fixtures data
	@$(PHP_CONT) bin/console doctrine:fixtures:load --no-interaction

fixtures-append: ## Append fixtures data (without purging)
	@$(PHP_CONT) bin/console doctrine:fixtures:load --no-interaction --append

ps: ## List all running containers
	@$(DOCKER_COMP) -f compose.yaml -f compose.override.yaml ps

images: ## List all images
	@$(DOCKER_COMP) -f compose.yaml -f compose.override.yaml images

volumes: ## List all volumes
	@$(DOCKER_COMP) -f compose.yaml -f compose.override.yaml volumes

rm: ## Remove all containers, images and volumes
	@$(DOCKER_COMP) -f compose.yaml -f compose.override.yaml down --remove-orphans --volumes --rmi all

migrate: ## Run migrations
	@$(PHP_CONT) bin/console doctrine:migrations:migrate --no-interaction

sass: ## Compile SASS
	@$(PHP_CONT) bin/console sass:build --watch