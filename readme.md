# Bro World Media Backend Environment

This repository provides a fully containerised Symfony 7 environment that demonstrates a production-like JSON REST API stack. It bundles everything needed for local development, quality assurance, and staging rehearsals, combining Docker services, Symfony configuration, and a curated toolchain.

[![Actions Status](https://github.com/systemsdk/docker-symfony-api/workflows/Symfony%20Rest%20API/badge.svg)](https://github.com/systemsdk/docker-symfony-api/actions)
[![CircleCI](https://circleci.com/gh/systemsdk/docker-symfony-api.svg?style=svg)](https://circleci.com/gh/systemsdk/docker-symfony-api)
[![Coverage Status](https://coveralls.io/repos/github/systemsdk/docker-symfony-api/badge.svg)](https://coveralls.io/github/systemsdk/docker-symfony-api)
[![Latest Stable Version](https://poser.pugx.org/systemsdk/docker-symfony-api/v)](https://packagist.org/packages/systemsdk/docker-symfony-api)
[![MIT licensed](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

> **Note** The original template for this environment lives at [systemsdk/docker-symfony-api](https://github.com/systemsdk/docker-symfony-api.git). This fork applies Bro World Media specific configuration and deployment conventions.

---

## Table of contents

1. [Overview](#overview)
2. [Service topology](#service-topology)
3. [Prerequisites](#prerequisites)
4. [Quick start](#quick-start)
5. [Environment configuration](#environment-configuration)
6. [Daily developer workflow](#daily-developer-workflow)
7. [Staging and production parity](#staging-and-production-parity)
8. [Testing and quality gates](#testing-and-quality-gates)
9. [Troubleshooting](#troubleshooting)
10. [Frequently used documentation](#frequently-used-documentation)
11. [License](#license)

---

## Overview

The stack is designed to simulate a realistic backend ecosystem for Bro World Media products. It combines PHP-FPM, MySQL, Elasticsearch, RabbitMQ, Redis, and observability tooling behind an Nginx gateway. All services are orchestrated with Docker Compose and controlled through a rich `Makefile`. JWT authentication, message queues, scheduled commands, and elastic indices are preconfigured, allowing you to focus on application logic rather than infrastructure plumbing.

## Service topology

| Service         | Version | Purpose                                                                    |
|-----------------|---------|----------------------------------------------------------------------------|
| Nginx           | 1.27    | Serves the Symfony application and static assets.                          |
| PHP-FPM         | 8.4     | Runs the Symfony application with Xdebug support for local development.    |
| MySQL           | 8       | Primary relational data store.                                             |
| Symfony         | 7       | Application framework powering the JSON REST API.                          |
| RabbitMQ        | 4       | Handles asynchronous tasks and message queues.                             |
| Elasticsearch   | 7       | Search and analytics engine, paired with Kibana.                           |
| Kibana          | 7       | UI for querying Elasticsearch indices and dashboards.                      |
| Redis           | 7       | Caching layer and messenger transport.                                     |
| Mailpit         | latest  | Local email testing inbox (development only).                              |

## Prerequisites

- Docker Engine **23.0+**
- Docker Compose **2.0+**
- A code editor or IDE (PhpStorm, VS Code, etc.)
- Optional database GUI such as **MySQL Workbench**
- Linux (Ubuntu) is the recommended host OS. macOS is supported through Docker Desktop.

### Docker installation notes

- Follow the official [Docker Engine installation guide](https://docs.docker.com/engine/install/).
- On Linux run `sudo usermod -aG docker $USER` after installation to avoid permission issues.
- On macOS 12.2 or later using Docker Desktop, enable [virtiofs](https://www.docker.com/blog/speed-boost-achievement-unlocked-on-docker-desktop-4-6-for-mac/) for faster volume mounts (enabled by default in Docker Desktop v4.22+).

## Quick start

1. **Clone the repository** (or create a project from Composer):
   ```bash
   git clone git@github.com:bro-world/bro-world-backend-media.git
   # or
   composer create-project systemsdk/docker-symfony-api bro-world-backend-media
   ```
2. **Configure secrets**:
   - Update `APP_SECRET` in `.env.prod` and `.env.staging`.
   - Do **not** keep `.env.local.php` for development or test environments.
3. **Adjust local overrides** (optional):
   - Create `.env.local` to tweak ports, Xdebug, or mail settings.
   - Remove `var/mysql-data` if it exists to ensure a clean database volume.
4. **Map the local domain**:
   ```bash
   echo "127.0.0.1    localhost" | sudo tee -a /etc/hosts
   ```
5. **Tune Xdebug** (optional):
   - Linux / Windows: edit `docker/dev/xdebug-main.ini`
   - macOS: edit `docker/dev/xdebug-osx.ini`
   - Set `xdebug.start_with_request = no` to debug only IDE initiated requests, or `yes` to debug all requests.
6. **Install dependencies and bootstrap services**:
   ```bash
   make build
   make start
   make composer-install
   make generate-jwt-keys
   ```
7. **Provision application data**:
   ```bash
   make migrate
   make create-roles-groups
   make migrate-cron-jobs
   make messenger-setup-transports
   make elastic-create-or-update-template
   ```
8. **Access the stack**:
   - API documentation: http://localhost/api/doc
   - RabbitMQ dashboard: http://localhost:15672
   - Kibana: http://localhost:5601
   - Mailpit inbox: http://localhost:8025

## Environment configuration

### Secrets and environment files

- `.env` contains baseline configuration shared across environments.
- `.env.local` can override settings for your workstation (never commit it).
- `.env.staging` and `.env.prod` define deployable configuration; always rotate `APP_SECRET` before release.
- For production and staging, supply your own secure credentials for Elasticsearch, databases, and JWT keys.

### Xdebug helper

Install the Firefox or Chrome **Xdebug Helper** extension and set the IDE key to `PHPSTORM` (or match your IDE). Combine with `xdebug.start_with_request` for granular debugging control.

### Elasticsearch bootstrap user

Elasticsearch ships with the privileged `elastic` user and default password `changeme`. Update these values in staging and production to maintain security.

## Daily developer workflow

Most tasks are exposed through the `Makefile`. Some highlights:

- `make start` / `make stop` – bring the stack up or down.
- `make ssh` / `make ssh-root` – open interactive shells inside the PHP container.
- `make logs-<service>` – tail logs from Nginx, Supervisord, MySQL, RabbitMQ, Elasticsearch, or Kibana.
- `make phpunit`, `make phpcs`, `make phpstan` – run automated tests and static analysis.
- `make fixtures` – seed the database with development data.

Refer to `make help` for the full catalog of commands.

## Staging and production parity

To emulate staging locally, reuse the staging Compose configuration:

```bash
make down
docker compose -f compose-staging.yaml up -d --build
```

Key differences from development:

- Mailpit is disabled; external SMTP configuration is expected.
- Xdebug is turned off by default.
- Resource limits mirror production sizing.

Always clear `var/mysql-data` when switching environments to avoid mixing datasets.

## Testing and quality gates

The project integrates a comprehensive quality toolchain:

- **Unit & integration tests**: `phpunit`, `dama/doctrine-test-bundle`, Symfony test helpers.
- **Static analysis**: PHPStan, PHP Mess Detector, Easy Coding Standard, Rector, PHP Insights.
- **Code coverage**: `make report-code-coverage` uploads data to Coveralls.
- **Security**: `composer audit`, `Roave/SecurityAdvisories`, local PHP security checker.

CI pipelines (GitHub Actions and CircleCI) ensure all checks must pass before merging into `develop` or release branches.

## Troubleshooting

| Symptom | Resolution |
|---------|------------|
| Containers fail to start with permission denied on volumes | Ensure your user belongs to the `docker` group (Linux) and restart your shell. |
| PHP code changes are not reflected | Verify Docker Desktop file sharing is enabled (macOS/Windows) or restart the PHP container. |
| Xdebug is not triggering | Confirm IDE key is `PHPSTORM`, browser extension is enabled, and `xdebug.start_with_request` is set appropriately. |
| MySQL refuses connection | Remove `var/mysql-data`, then rerun `make start` to recreate the volume. |
| Elasticsearch security warnings | Update passwords and certificates in `.env.staging` / `.env.prod` and rebuild services. |

## Frequently used documentation

- [Commands](docs/commands.md)
- [API Key usage](docs/api-key.md)
- [Development workflow](docs/development.md)
- [Testing guidelines](docs/testing.md)
- [PhpStorm setup](docs/phpstorm.md)
- [Xdebug configuration](docs/xdebug.md)
- [Swagger documentation](docs/swagger.md)
- [Postman collection](docs/postman.md)
- [Redis Desktop Manager](docs/rdm.md)
- [Messenger component](docs/messenger.md)

## License

This project is released under the [MIT License](LICENSE).
