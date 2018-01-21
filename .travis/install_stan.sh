#!/usr/bin/env sh
set -ev

mkdir --parents "${HOME}/bin"

composer global require "phpstan/phpstan"
composer install --dev --prefer-dist
