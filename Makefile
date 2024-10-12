ROOT_DIR:=$(shell dirname $(realpath $(lastword $(MAKEFILE_LIST))))
include $(ROOT_DIR)/.mk-lib/common.mk

include .env
-include .env.local
export

DC_CMD := $(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE)

bash: ## bash php-fpm
	@$(DC_CMD) exec fpm /bin/bash

bash-db: ## bash php-db
	@$(DC_CMD) exec db /bin/bash

bash-nginx: ## bash php-nginx
	@$(DC_CMD) exec nginx /bin/bash

db: ## run mysql console
	@$(DC_CMD) exec db mysql -u root -p${MYSQL_ROOT_PASSWORD} ${MYSQL_DATABASE}

up: ## Start all or c=<name> containers in foreground
	@$(DC_CMD) up $(c)
	@$(DC_CMD) exec -T fpm /var/www/html/bin/console doctrine:migrations:migrate --no-interaction

start: ## Start all or c=<name> containers in background
	@$(DC_CMD) up -d $(c)

stop: ## Stop all or c=<name> containers
	@$(DC_CMD) stop $(c)

restart: ## Restart all or c=<name> containers
	@$(DC_CMD) stop $(c)
	@$(DC_CMD) up -d $(c)

ps:	## Show status of containers
	@$(DC_CMD) ps

clean: ## Clean all data
	@$(DC_CMD) down

build: ## Build php-fpm
	@$(DC_CMD) up --build -d

logs: ## Show all or c=<name> logs of containers
	@$(DC_CMD) logs $(c)

DB_CONTAINER_ID := `$(DC_CMD) ps -q db`

install: pull-images start restart #update-database ## Run containers

# composer-install clear-cache restart

pull-images: ## Pull new images
	@echo 'Updating images ... '
	@$(DC_CMD) pull
	@echo 'Success'

update-database: ## Execute 'diff' and 'migrate'
	@$(DC_CMD) exec -T fpm /var/www/html/bin/console make:migration --no-interaction
	@$(DC_CMD) exec -T fpm /var/www/html/bin/console doctrine:migrations:migrate --no-interaction

# composer-install: ## Run composer install
# 	@echo 'Installing dependencies ...'
# 	@$(DC_CMD) exec -T fpm bash -c "cd /var/www/html && composer install"
# 	@echo 'Success'

# cache-clear: clear-cache
# clear-cache: ## Clear cache
# 	@echo 'Clearing cache ...'
# 	@$(DC_CMD) exec -T fpm /var/www/html/bin/clear_cache
# 	@echo 'Success'