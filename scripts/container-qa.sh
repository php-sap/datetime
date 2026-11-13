#!/bin/sh
set -eu

if [ "${PREPARED:-0}" = 0 ]; then
    export http_proxy="${http_proxy:-${HTTP_PROXY:-}}" https_proxy="${https_proxy:-${HTTPS_PROXY:-}}" no_proxy="${no_proxy:-${NO_PROXY:-}}"
    if [ "${EXTRA_CA:-0}" = 1 ]; then
        cat /etc/ssl/certs/ca-certificates.crt /tmp/extra-ca.crt > /tmp/ca-bundle.pem
        export CURL_CA_BUNDLE=/tmp/ca-bundle.pem SSL_CERT_FILE=/tmp/ca-bundle.pem
    fi
    if [ "$MODE" = install ] || [ "$MODE" = check ]; then
        if [ "${EXTRA_CA:-0}" = 1 ]; then
            export APT_CONFIG=/tmp/apt-proxy-ca.conf
            printf 'Acquire::https::CaInfo "/tmp/ca-bundle.pem";\n' > "$APT_CONFIG"
        fi
        apt-get update -qq
        DEBIAN_FRONTEND=noninteractive apt-get install -y -qq --no-install-recommends git unzip
    fi
    if [ "$MODE" = check ]; then
        pecl install /tmp/xdebug.tgz > /tmp/xdebug-build.log 2>&1 || {
            cat /tmp/xdebug-build.log >&2
            exit 1
        }
    fi
    export PREPARED=1
    exec setpriv --reuid "$HOST_UID" --regid "$HOST_GID" --clear-groups sh "$0"
fi

php -r 'printf("PHP %s\n", PHP_VERSION); if (!in_array(getenv("PHP_VERSION"), [PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION, PHP_VERSION], true)) exit(1);'
php /tmp/composer --version
case "$MODE" in
    validate) php /tmp/composer validate --strict --no-check-lock ;;
    audit) php /tmp/composer audit --no-interaction ;;
    install|check)
        php -r '
            $config = json_decode(file_get_contents(getenv("COMPOSER")), true, 512, JSON_THROW_ON_ERROR);
            $platform = $config["config"]["platform"] ?? [];
            if (array_key_exists("php", $platform) && $platform["php"] !== PHP_VERSION) {
                fwrite(STDERR, "config.platform.php does not match the actual PHP runtime; refusing simulated resolution.\n");
                exit(1);
            }
        '
        if [ "$DEPENDENCIES_LOWEST" = 1 ]; then
            php /tmp/composer update --prefer-lowest --prefer-dist --no-interaction --no-progress
        else
            php /tmp/composer update --prefer-dist --no-interaction --no-progress
        fi
        php /tmp/composer check-platform-reqs
        if [ "$MODE" = check ]; then
            lockfile="${COMPOSER%.json}.lock"
            snapshot=$(sha256sum "$lockfile")
            sh tests/makefile-clean-test.sh
            sh tests/makefile-failure-test.sh
            find src tests -type f -name "*.php" -print0 | xargs -0 -n1 php -l
            php -d zend_extension=xdebug.so -d xdebug.mode=coverage vendor/bin/phpunit --coverage-text --colors=never
            php vendor/bin/phpstan analyse --memory-limit=-1
            php vendor/bin/phpcs
            test "$(sha256sum "$lockfile")" = "$snapshot"
            php /tmp/composer audit --locked --no-interaction
            test "$(sha256sum "$lockfile")" = "$snapshot"
            printf 'Audited dependency snapshot: %s\n' "$snapshot"
        fi
        ;;
esac
