#!/usr/bin/env sh
set -ev

export PATH="$PATH:$HOME/.composer/vendor/bin"
phpstan analyse src -l 7
