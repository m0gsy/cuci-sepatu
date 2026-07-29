#!/usr/bin/env bash

set -Eeuo pipefail

cd "$(dirname "$0")/.."

exec 9>/tmp/step-shine-deploy.lock
flock -n 9 || {
    echo "Another deployment is already running."
    exit 1
}

if [[ -z "${COMPOSER_AUTH:-}" ]] && ! composer config --global --auth github-oauth.github.com >/dev/null 2>&1; then
    echo "Configure COMPOSER_AUTH or a global GitHub OAuth token before deploying."
    exit 1
fi

php artisan down --retry=60
trap 'echo "Deployment failed; application remains in maintenance mode."' ERR

git pull --ff-only origin main

composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
npm ci --include=dev
npm run build
npm prune --omit=dev

php artisan backup:database
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan app:production-check

sudo supervisorctl restart step-shine-queue:*
sudo supervisorctl restart step-shine-scheduler

php artisan up
trap - ERR

echo "Deployment complete."
