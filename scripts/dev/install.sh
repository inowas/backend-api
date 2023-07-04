#!/usr/bin/env bash

## define project root
projectRoot=$( cd "$(dirname "${BASH_SOURCE[0]}")/../.." || { echo "determining project root failed"; exit 1; }; pwd -P )

## use all env variables from .env in this script
set -o allexport
source conf-file
set +o allexport

docker network create web
docker compose -f docker-compose.dev.yml build
docker compose -f docker-compose.dev.yml run php composer install
docker compose -f docker-compose.dev.yml run php bin/console doctrine:schema:update --force
docker compose -f docker-compose.dev.yml run php bin/console doctrine:migrations:migrate --no-interaction --query-time --allow-no-migration
docker compose -f docker-compose.dev.yml run php bin/console app:load-users users.dist.json

ssh-keygen -t rsa -b 4096 -m PEM -P "$JWT_PASSPHRASE" -f $projectRoot/config/jwt/private.pem
openssl rsa -passin pass:$JWT_PASSPHRASE -in $projectRoot/config/jwt/private.pem -pubout -outform PEM -out $projectRoot/config/jwt/public.pem
rm $projectRoot/config/jwt/private.pem.pub
# for dev only
chmod a+r $projectRoot/config/jwt/private.pem

$projectRoot/syncSchema.sh