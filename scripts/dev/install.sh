#!/usr/bin/env bash

docker network create web
docker compose -f docker-compose.dev.yml build
docker compose -f docker-compose.dev.yml run php composer install
docker compose -f docker-compose.dev.yml run php bin/console doctrine:schema:update --force
docker compose -f docker-compose.dev.yml run php bin/console doctrine:migrations:migrate --no-interaction --query-time --allow-no-migration
docker compose -f docker-compose.dev.yml run php bin/console app:load-users users.dist.json

