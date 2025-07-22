.PHONY: help build up down restart logs shell mysql composer install clean

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

# Utility commands
clean: ## Clean up containers and volumes
	docker-compose down -v --remove-orphans
	docker system prune -f

start: ## Start development environment  
	@make build
	@make up
	@echo ""
	@echo "Yii2 application is ready!"
	@echo "http://localhost:$(shell grep NGINX_PORT .env | cut -d '=' -f2 | head -1)"
	@echo ""