#!/bin/bash

set -eo

# output command to log
set -x

#########################################
# SETUP DEFAULTS #
#########################################
VERSION="${VERSION:-${GITHUB_REF#refs/tags/}}"; VERSION="${VERSION#v}"
SLUG="${SLUG:-${GITHUB_REPOSITORY#*/}}"

# If the version is not set, check if package.json exists and get the version from there otherwise exit.
if [[ -z "$VERSION" && -f "$GITHUB_WORKSPACE/package.json" ]]; then
  VERSION=$(node -p "require('./package.json').version")
fi

#########################################
# CHECK IF EVERYTHING IS SET CORRECTLY #
#########################################
for var in USERNAME PASSWORD SLUG VERSION; do
  if [ -z "${!var}" ]; then
    echo "x︎ $var is not set. Exiting..."
    exit 1
  fi
done

# Log the slug.
echo "ℹ︎ SLUG is $SLUG"

# Log the version.
echo "ℹ︎ VERSION is $VERSION"
