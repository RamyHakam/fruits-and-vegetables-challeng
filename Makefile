
# Docker container names
APP_CONTAINER=produce_app
REDIS_CONTAINER=produce_redis

DOCKER_APP_EXEC = docker exec -it $(APP_CONTAINER)
DOCKER_REDIS_EXEC = docker exec -it $(REDIS_CONTAINER)

up:
	docker-compose up -d --build

data-import:
	@echo "Running data import from request.json..."
	$(DOCKER_APP_EXEC) php bin/console app:import-from-file request.json

# Redis maintenance
flush-redis:
	@echo "Flushing all Redis data..."
	$(DOCKER_REDIS_EXEC) redis-cli FLUSHDB


test:
	@echo "Running PHPUnit tests..."
	$(DOCKER_APP_EXEC) php bin/phpunit --colors=always

