#!/usr/bin/env bash

projectRoot=$( cd "$(dirname "${BASH_SOURCE[0]}")/../.." || { echo "determining project root failed"; exit 1; }; pwd -P )

docker network create web
docker compose -f docker-compose.dev.yml build
docker compose -f docker-compose.dev.yml run php composer install
docker compose -f docker-compose.dev.yml run php bin/console doctrine:schema:update --force
docker compose -f docker-compose.dev.yml run php bin/console doctrine:migrations:migrate --no-interaction --query-time --allow-no-migration
docker compose -f docker-compose.dev.yml run php bin/console app:load-users users.dist.json
$projectRoot/syncSchema.sh
