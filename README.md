# Step Shine Works

Laravel application for shoe-care order tracking, payments, loyalty points,
vouchers, inventory, reports, and WhatsApp notifications.

## Requirements

- PHP 8.3 with PDO MySQL, mbstring, openssl, tokenizer, XML, ctype, JSON, BCMath, and ZIP
- MySQL 8 or MariaDB 10.6+
- Composer 2
- Node.js 20+
- Supervisor on production hosts

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
npm ci
npm run build
php artisan migrate --force
```

Set these production values before the first migration or seed:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://example.com
DB_CONNECTION=mysql
SESSION_SECURE_COOKIE=true
SEED_ADMIN_PASSWORD=use-a-unique-secret
TWILIO_SID=
TWILIO_AUTH_TOKEN=
TWILIO_WHATSAPP_FROM=
MAIL_ADMIN_ADDRESS=
```

`SEED_ADMIN_PASSWORD` is required by `DatabaseSeeder`; no default password is
stored in the repository.

## Workers

Install [deploy/supervisor.conf](deploy/supervisor.conf) and adjust its project
path and operating-system user. It runs both:

- `php artisan queue:work database`
- `php artisan schedule:work`

The scheduler executes database backups and overdue-order notifications.

## Release

Run `bash deploy/deploy.sh` from the project root, or execute the same steps
manually. The script requires Composer GitHub authentication, locks concurrent
deployments, creates a database backup before migration, and leaves the
application in maintenance mode on failure. Before routing traffic, verify:

```bash
php artisan about --only=environment
php artisan migrate:status
php artisan route:cache
php artisan view:cache
php artisan schedule:list
php artisan app:production-check
php artisan test
npm run build
composer audit --locked
npm audit
```

The application health endpoint is `/up`.
