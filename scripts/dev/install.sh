#!/usr/bin/env bash

## include variables and functions from util.inc.sh
source $(dirname $0)/util.inc.sh

## use all env variables from .env in this script
set -o allexport
source .env
set +o allexport

docker network create web
docker compose $dockerComposeFiles build
docker compose $dockerComposeFiles run php composer install
docker compose $dockerComposeFiles run php bin/console doctrine:schema:update --force
docker compose $dockerComposeFiles run php bin/console doctrine:migrations:migrate --no-interaction --query-time --allow-no-migration
docker compose $dockerComposeFiles run php bin/console app:load-users users.dist.json

ssh-keygen -t rsa -b 4096 -m PEM -P "$JWT_PASSPHRASE" -f $projectRoot/config/jwt/private.pem
openssl rsa -passin pass:$JWT_PASSPHRASE -in $projectRoot/config/jwt/private.pem -pubout -outform PEM -out $projectRoot/config/jwt/public.pem
rm $projectRoot/config/jwt/private.pem.pub
# for dev only
chmod a+r $projectRoot/config/jwt/private.pem

$projectRoot/syncSchema.sh