#!/usr/bin/env bash

set -e

# this script pulls the master from github with tags
# and pushes it to gitlab

git fetch --all --tags --verbose

# switch to the master branch
git checkout dev -q

# if there are uncommited changes, finish here
if [[ $(git diff --stat) != '' ]]; then
  echo 'Dev branch is dirty, please fix.'
  exit 1
fi

# get all changes from github (origin)
git pull origin dev

# and push it to gitlab
git push gitlab dev
