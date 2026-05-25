# Chapter 00 — Prerequisites

Before writing the first line of code, install the runtime, pick a
database, and prepare a working directory. This chapter is short on
purpose: skipping the version checks here is the single most common
source of "it works on my machine" pain in later chapters.

## Required software

| Tool       | Minimum version | Why                                              |
|------------|-----------------|--------------------------------------------------|
| PHP        | 8.2             | Laravel 12 requires PHP 8.2+.                    |
| Composer   | 2.6             | PHP dependency manager.                          |
| Node.js    | 20 LTS          | Vite 7 + React 19 build toolchain.               |
| npm        | 10              | Ships with Node 20.                              |
| Git        | 2.40            | Version control.                                 |
| SQLite     | 3.40            | Default dev database — zero config.              |
| MySQL/MariaDB | 8.0 / 10.6   | Optional, recommended for staging/production.    |

## Verify your toolchain

Run each command and confirm the printed version meets the minimum:

```bash
php --version
composer --version
node --version
npm --version
git --version
sqlite3 --version
```

Example acceptable output:

```text
PHP 8.3.6 (cli)
Composer version 2.7.7
v20.15.0
10.7.0
git version 2.43.0
3.45.1
```

## Optional but recommended

- **A real terminal multiplexer** like tmux or zellij. You will run
  `php artisan serve` and `npm run dev` in parallel during development.
- **A SQL GUI** like TablePlus, DBeaver, or `php artisan tinker` for
  ad-hoc queries. We rely on tinker throughout the tutorial.
- **A modern browser** with stable DevTools — Chrome, Edge, or Firefox.

## Decide on a database

The tutorial defaults to **SQLite** because there is nothing to install
and `php artisan migrate:fresh` runs in under a second. SQLite is
production-ready for small shops; the schema is identical on MySQL.

If you intend to switch to MySQL later, just install it now so you can
verify the connection at the end of Chapter 01:

```bash
sudo apt install -y mysql-server          # Debian / Ubuntu
brew install mysql && brew services start mysql   # macOS

mysql -u root -e "CREATE DATABASE super_market_erp CHARACTER SET utf8mb4;"
mysql -u root -e "CREATE USER 'erp'@'localhost' IDENTIFIED BY 'password';"
mysql -u root -e "GRANT ALL ON super_market_erp.* TO 'erp'@'localhost';"
```

## Pick a working directory

```bash
mkdir -p ~/code && cd ~/code
```

The tutorial creates the project at `~/code/super-market-erp/`. Adjust
that path to whatever you prefer; just keep it consistent.

## Set up git and SSH

If you intend to push to GitHub, configure your identity and a key:

```bash
git config --global user.name "Your Name"
git config --global user.email "you@example.com"
ssh-keygen -t ed25519 -C "you@example.com"   # then upload to GitHub
```

## Verify

You are ready to start Chapter 01 when:

- `php --version` reports 8.2 or newer.
- `composer --version` reports 2.6 or newer.
- `node --version` reports v20 or newer.
- You have chosen a working directory and can `cd` into it.
- `git config --get user.email` prints your email.

If any of those fail, fix the toolchain before moving on. Trying to
install Laravel on PHP 8.1 fails with a hard composer error; trying to
run Vite 7 on Node 18 fails with `crypto.hash is not a function`.
