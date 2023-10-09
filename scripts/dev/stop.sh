#!/usr/bin/env bash

## include variables and functions from util.inc.sh
source "$(dirname "$0")/util.inc.sh"

docker compose $dockerComposeFiles down