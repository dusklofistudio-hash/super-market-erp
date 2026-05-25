# Chapter 17 — Production deployment

The final step: get the app onto a server end users can reach over the
internet, with HTTPS, a real database, and the queue worker running.

Two deployment shapes are covered:

1. **Single VPS** (Ubuntu 24.04 + nginx + PHP-FPM + MySQL + Supervisor) —
   the simplest production setup.
2. **Containerized** (Docker Compose) — copy-pasteable example for
   teams that prefer images.

Both produce the same runtime contract.

## 1. Prepare the production environment file

Copy `.env.example` to `.env.production` and tune:

```ini
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...                # `php artisan key:generate --show`
APP_URL=https://erp.example.com

LOG_CHANNEL=daily
LOG_LEVEL=info

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=super_market_erp
DB_USERNAME=erp
DB_PASSWORD=<<rotate-this>>

SESSION_DRIVER=database
SESSION_LIFETIME=120

QUEUE_CONNECTION=database         # OR redis if available
CACHE_STORE=database              # OR redis

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="Super Market ERP"
```

Never commit `.env.production` to git. Store it in a secrets vault or
deploy it via your provisioner.

## 2. VPS path — Ubuntu 24.04

### 2.1 Install runtime

```bash
sudo apt update
sudo apt install -y nginx mysql-server git curl unzip \
    php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring \
    php8.3-xml php8.3-bcmath php8.3-zip php8.3-curl php8.3-gd \
    php8.3-intl
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### 2.2 Pull the code

```bash
sudo mkdir -p /var/www
sudo chown $USER:$USER /var/www
cd /var/www
git clone https://github.com/dusklofistudio-hash/super-market-erp.git
cd super-market-erp
cp .env.production .env
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan key:generate    # only if APP_KEY is empty
php artisan migrate --force
php artisan db:seed --force # OPTIONAL: only on a fresh install
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2.3 File permissions

The web server user (`www-data`) needs write access to `storage/`
and `bootstrap/cache/`:

```bash
sudo chown -R $USER:www-data .
sudo find . -type f -exec chmod 664 {} \;
sudo find . -type d -exec chmod 775 {} \;
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
```

### 2.4 nginx server block

`/etc/nginx/sites-available/super-market-erp`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name erp.example.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name erp.example.com;

    ssl_certificate     /etc/letsencrypt/live/erp.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/erp.example.com/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;

    root /var/www/super-market-erp/public;
    index index.php;
    charset utf-8;

    client_max_body_size 20M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* { deny all; }
}
```

Enable it and reload:

```bash
sudo ln -sf /etc/nginx/sites-available/super-market-erp /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

### 2.5 HTTPS with Let's Encrypt

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d erp.example.com
```

Certbot rewrites the nginx block to point at the new certs and sets up
automatic renewal.

### 2.6 Queue worker via Supervisor

The activity logger and PHPFlasher mailables benefit from queued
execution. Add `/etc/supervisor/conf.d/smk-worker.conf`:

```ini
[program:smk-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/super-market-erp/artisan queue:work --queue=default --tries=3 --sleep=3 --timeout=60
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/smk-worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start smk-worker:*
```

### 2.7 Scheduled tasks

The activity-log prune (Chapter 13) and any future schedule entries
need cron:

```bash
sudo crontab -e -u www-data
```

Add:

```cron
* * * * * cd /var/www/super-market-erp && php artisan schedule:run >> /dev/null 2>&1
```

## 3. Docker Compose path

Create `docker-compose.yml`:

```yaml
services:
  app:
    image: php:8.3-fpm
    working_dir: /var/www
    volumes:
      - .:/var/www
    depends_on:
      - db
    environment:
      DB_HOST: db
      DB_DATABASE: super_market_erp
      DB_USERNAME: erp
      DB_PASSWORD: secret
  nginx:
    image: nginx:alpine
    ports:
      - "8080:80"
    volumes:
      - .:/var/www
      - ./docker/nginx.conf:/etc/nginx/conf.d/default.conf:ro
    depends_on:
      - app
  db:
    image: mysql:8
    environment:
      MYSQL_DATABASE: super_market_erp
      MYSQL_USER: erp
      MYSQL_PASSWORD: secret
      MYSQL_ROOT_PASSWORD: rootsecret
    volumes:
      - db:/var/lib/mysql
  queue:
    image: php:8.3-cli
    working_dir: /var/www
    command: ["php","artisan","queue:work","--tries=3"]
    volumes:
      - .:/var/www
    depends_on:
      - db
volumes:
  db:
```

A matching `docker/nginx.conf` is the same as the Section 2.4 nginx
block but with `fastcgi_pass app:9000`. Run with:

```bash
docker compose up -d
docker compose exec app composer install --no-dev --optimize-autoloader
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force
docker compose exec app npm ci
docker compose exec app npm run build
docker compose exec app php artisan storage:link
```

## 4. Zero-downtime deploys

Once running, future deploys should follow this script (run on the
server or via a CI/CD job):

```bash
cd /var/www/super-market-erp
git fetch origin
git reset --hard origin/main
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo supervisorctl restart smk-worker:*
sudo systemctl reload php8.3-fpm
```

The PHP-FPM reload is graceful; in-flight requests finish before
workers cycle.

## 5. Security checklist

- [ ] `APP_DEBUG=false` in production.
- [ ] `APP_KEY` set (never commit it).
- [ ] Database user has only `SELECT, INSERT, UPDATE, DELETE` (no DDL)
      against the app database.
- [ ] HTTPS-only (HTTP 301 → HTTPS).
- [ ] HSTS header enabled (`add_header Strict-Transport-Security ...`).
- [ ] `php artisan storage:link` ran, but `storage/` is NOT inside
      `public/` other than via the symlink.
- [ ] `.env.production` permissions are 600 and owned by the deploy user.
- [ ] Daily database backup configured (`mysqldump` cron or managed db).
- [ ] Activity log prune scheduled (default 180 days).
- [ ] Admin user has a strong password (not `password`!).

## 6. Smoke test the live app

After deploy:

```bash
curl -sI https://erp.example.com | grep -E 'HTTP|Strict-Transport'
curl -s  https://erp.example.com/login | grep -E '<title>|csrf-token' | head
```

Then in a browser:

1. Log in as `admin` with the production password.
2. Switch to ខ្មែរ.
3. Open the Products list.
4. Open `/admin/pos/register?session=<seeded session>` and ring a
   $0.01 test sale (refund or void after).
5. Confirm the sale shows up in `/admin/activity-logs` immediately.

If all five succeed, the deployment is operational.

## End

Eighteen chapters from zero to a multi-branch supermarket ERP serving
real customers. Next steps to consider:

- **Reporting depth** — add per-customer LTV, per-product velocity,
  ABC inventory classification.
- **Loyalty** — extend `customer_groups` to a points balance, redeemable
  at POS.
- **Multi-currency** — store FX rates per day, convert reports.
- **Hardware** — barcode scanner integration via USB-HID, receipt
  printer via ESC/POS.
- **Background sync** — replicate sales to a central reporting database
  for offline-tolerant branches.

Anything you build on top of this scaffold inherits the same RBAC, i18n,
activity log, and stock-service guarantees. Happy shipping.
