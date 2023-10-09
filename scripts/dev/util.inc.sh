#!/usr/bin/env bash

projectRoot=$( cd "$(dirname "${BASH_SOURCE[0]}")/../.." || { echo "determining project root failed"; exit 1; }; pwd -P )

if [[ "$(uname)" = "Linux" ]] ; then isLinux=true; else isLinux=false; fi

if [[ "$isLinux" = "true" ]] ;
then
  dockerComposeFiles="-f docker-compose.dev.yml -f docker-compose.dev.linux.override.yml"
else
  dockerComposeFiles="-f docker-compose.dev.yml"
fi

