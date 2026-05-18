IMAGE ?= sun-app
NETWORK ?= sun-net
DATABASE_URL ?= postgresql://symfony:symfony@sun-postgres:5432/sun?serverVersion=18&charset=utf8
RABBITMQ_DSN ?= amqp://symfony:symfony@sun-rabbitmq:5672/%2f/messages

DOCKER_ENV = --network $(NETWORK) \
	-e DATABASE_URL='$(DATABASE_URL)' \
	-e RABBITMQ_DSN='$(RABBITMQ_DSN)'

.PHONY: help install test phpunit phpstan phpcs cs-fix openapi openapi-validate openapi-yaml openapi-json docker-build docker-phpunit docker-phpstan docker-phpcs docker-openapi qa docker-qa

help:
	@echo "Available targets:"
	@echo "  install          Install Composer and npm dependencies"
	@echo "  phpunit          Run PHPUnit locally"
	@echo "  phpstan          Run PHPStan locally"
	@echo "  phpcs            Check code style locally with php-cs-fixer"
	@echo "  cs-fix           Fix code style locally with php-cs-fixer"
	@echo "  openapi          Validate and build public OpenAPI files locally"
	@echo "  qa               Run local phpunit, phpstan, phpcs and openapi"
	@echo "  docker-build     Build Docker image"
	@echo "  docker-phpunit   Run PHPUnit in Docker image"
	@echo "  docker-phpstan   Run PHPStan in Docker image"
	@echo "  docker-phpcs     Check code style in Docker image"
	@echo "  docker-openapi   Validate and build OpenAPI in Docker image"
	@echo "  docker-qa        Run Docker phpunit, phpstan, phpcs and openapi"

install:
	composer install
	npm ci

test: phpunit

phpunit:
	php bin/phpunit

phpstan:
	vendor/bin/phpstan analyse --memory-limit=512M

phpcs:
	vendor/bin/php-cs-fixer check --diff --config=.php-cs-fixer.dist.php

cs-fix:
	vendor/bin/php-cs-fixer fix --diff --config=.php-cs-fixer.dist.php

openapi: openapi-validate openapi-yaml openapi-json

openapi-validate:
	npm run openapi:validate

openapi-yaml:
	npm run openapi:yaml

openapi-json:
	npm run openapi:json

qa: phpunit phpstan phpcs openapi

docker-build:
	docker build -t $(IMAGE) .

docker-phpunit:
	docker run --rm $(DOCKER_ENV) --entrypoint php $(IMAGE) bin/phpunit

docker-phpstan:
	docker run --rm --entrypoint php $(IMAGE) vendor/bin/phpstan analyse --memory-limit=512M

docker-phpcs:
	docker run --rm --entrypoint php $(IMAGE) vendor/bin/php-cs-fixer check --diff --config=.php-cs-fixer.dist.php

docker-openapi:
	docker run --rm --entrypoint npm $(IMAGE) run openapi:build

docker-qa: docker-phpunit docker-phpstan docker-phpcs docker-openapi
