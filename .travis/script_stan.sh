#!/usr/bin/env sh
set -ev

export PATH="$PATH:$HOME/.config/composer/vendor/bin"
phpstan analyse src -l 7
