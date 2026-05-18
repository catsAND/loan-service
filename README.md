# loan-service
Test task for Sun Finance Using PHP 8.5, symfony 8, PostgreSQL 18 and RabbitMQ 4.

## How to run

```bash
make docker-build
docker network create sun-net

docker run -d --name sun-postgres --network sun-net \
  -e POSTGRES_USER=symfony \
  -e POSTGRES_PASSWORD=symfony \
  -e POSTGRES_DB=sun \
  postgres:18-alpine

docker run -d --name sun-rabbitmq --network sun-net \
  -e RABBITMQ_DEFAULT_USER=symfony \
  -e RABBITMQ_DEFAULT_PASS=symfony \
  -p 15672:15672 \
  rabbitmq:4-management-alpine

docker run --rm --name sun-app --network sun-net -p 8080:80 \
  -e DATABASE_URL='postgresql://symfony:symfony@sun-postgres:5432/sun?serverVersion=18&charset=utf8' \
  -e RABBITMQ_DSN='amqp://symfony:symfony@sun-rabbitmq:5672/%2f/messages' \
  sun-app
```

API docs: `http://localhost:8080/api/v1/docs`

## Quality checks

```bash
make docker-phpunit
make docker-phpstan
make docker-phpcs
```

Or all checks:

```bash
make docker-qa
```

## OpenAPI

```bash
make docker-openapi
```

## Stop containers

```bash
docker stop sun-app sun-postgres sun-rabbitmq || true
docker rm sun-app sun-postgres sun-rabbitmq || true
docker network rm sun-net || true
```
