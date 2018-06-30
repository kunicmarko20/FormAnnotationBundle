#!/usr/bin/env sh
set -ev

mkdir --parents "${HOME}/bin"

composer global require "phpstan/phpstan 0.9.2"
composer install --dev --prefer-dist
