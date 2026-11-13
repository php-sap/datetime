SHELL := /bin/sh
.DEFAULT_GOAL := help

PHP_VERSION ?= 8.1
DEPENDENCIES_LOWEST ?= 0
COMPOSER_IMAGE ?= composer:2
COMPOSER_CACHE_DIR ?= $(HOME)/.cache/composer
export PHP_VERSION DEPENDENCIES_LOWEST COMPOSER_IMAGE COMPOSER_CACHE_DIR CA_CERT_FILE

DOCKER_RUN = docker run --rm --init --user "$$(id -u):$$(id -g)" --volume "$(CURDIR):/app" --workdir /app --env PHP_VERSION="$(PHP_VERSION)" "php:$(PHP_VERSION)-cli"

.PHONY: help clean check-php-version install check lint test analyze sniff beautify audit validate

help: ## Show available targets
	@grep -E '^[a-zA-Z_-]+:.*## ' $(MAKEFILE_LIST) | sort | \
		awk 'BEGIN {FS = ":.*## "}; {printf "  %-18s %s\n", $$1, $$2}'

clean: ## Remove vendor/ and PHPUnit cache, preserving user lockfiles
	flock -x composer.json rm -rf vendor .phpunit.result.cache

check-php-version: ## Show and verify the requested PHP runtime
	@$(DOCKER_RUN) php -r 'printf("PHP %s\n", PHP_VERSION); if (!in_array(getenv("PHP_VERSION"), [PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION, PHP_VERSION], true)) { exit(1); }'

install: ## Resolve dependencies for PHP_VERSION; use DEPENDENCIES_LOWEST=1 for lowest
	@sh scripts/composer-run.sh install

check: ## Install, run QA with coverage, and audit the same dependency snapshot
	@sh scripts/composer-run.sh check

lint: ## Syntax-check source and tests
	@$(DOCKER_RUN) sh -c 'find src tests -type f -name "*.php" -print0 | xargs -0 -n1 php -l'

test: ## Run PHPUnit
	@$(DOCKER_RUN) php vendor/bin/phpunit

analyze: ## Run PHPStan
	@$(DOCKER_RUN) php vendor/bin/phpstan analyse --memory-limit=-1

sniff: ## Check coding style
	@$(DOCKER_RUN) php vendor/bin/phpcs

beautify: ## Fix coding style
	@$(DOCKER_RUN) php vendor/bin/phpcbf

audit: ## Audit installed dependencies, independently of previous runs
	@sh scripts/composer-run.sh audit

validate: ## Validate Composer metadata
	@sh scripts/composer-run.sh validate
