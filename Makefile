.PHONY: help build up down restart logs shell mysql composer composer-install install clean

# Default target
help: ## Show this help message
	@echo "Available commands:"
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

# Docker commands
build: ## Build all containers
	docker-compose build --no-cache

up: ## Start all services
	docker-compose up -d

down: ## Stop all services
	docker-compose down

restart: ## Restart all services
	docker-compose restart

logs: ## Show logs for all services
	docker-compose logs -f

# Development commands
shell: ## Access PHP container shell
	docker-compose exec php bash

mysql: ## Access MySQL shell
	docker-compose exec mysql mysql -u$(shell grep DB_USER .env | cut -d '=' -f2) -p$(shell grep DB_PASSWORD .env | cut -d '=' -f2) $(shell grep DB_NAME .env | cut -d '=' -f2)

composer: ## Run composer install
	docker-compose exec php composer install

composer-install: ## Force install composer dependencies
	docker-compose exec php bash -c "cd /var/www/html && composer install --optimize-autoloader --no-interaction --prefer-dist"

composer-update: ## Update composer dependencies
	docker-compose exec php bash -c "cd /var/www/html && composer update"

# Check if vendor exists
check-vendor: ## Check if vendor directory exists
	@docker-compose exec php bash -c "if [ -d '/var/www/html/vendor' ]; then echo 'Vendor directory exists'; ls -la /var/www/html/vendor | head -10; else echo 'Vendor directory does not exist'; fi"

# Utility commands
clean: ## Clean up containers and volumes
	docker-compose down -v --remove-orphans
	docker system prune -f

start: ## Start development environment  
	@if [ ! -f .env ]; then echo "Error: .env file not found!"; exit 1; fi
	@make build
	@make up
	@echo "Waiting for services to start..."
	@sleep 10
	@make check-vendor
	@echo ""
	@echo "Yii2 application is ready!"
	@echo "http://localhost:$(shell grep NGINX_PORT .env | cut -d '=' -f2 | head -1)/orders"
	@echo ""
	@echo "If vendor directory is missing, run: make composer-install"

# Fix permissions
fix-permissions: ## Fix file permissions
	docker-compose exec php bash -c "chown -R www-data:www-data /var/www/html && chmod -R 775 /var/www/html/runtime /var/www/html/web/assets"