#!/usr/bin/env bash
set -e
composer install --no-interaction --prefer-dist
composer run setup
