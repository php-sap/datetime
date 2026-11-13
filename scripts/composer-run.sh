#!/bin/sh
set -eu

mode=${1:?expected install, check, audit, or validate}
case "$mode" in install|check|audit|validate) ;; *) echo "Unknown mode: $mode" >&2; exit 1 ;; esac
case "$DEPENDENCIES_LOWEST" in 0|1) ;; *) echo 'DEPENDENCIES_LOWEST must be 0 or 1' >&2; exit 1 ;; esac

exec 9<composer.json
flock -x 9
temporary=$(mktemp -d)
manifest=
cleanup() {
    status=$?
    trap - EXIT
    if [ -n "$manifest" ]; then
        rm -f "$manifest" "${manifest%.json}.lock" || {
            echo 'Failed to remove temporary Composer files' >&2
            [ "$status" -ne 0 ] || status=1
        }
    fi
    rm -rf "$temporary" || {
        echo 'Failed to remove temporary Composer executable' >&2
        [ "$status" -ne 0 ] || status=1
    }
    exit "$status"
}
trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

manifest=$(mktemp .composer-run.XXXXXXXX.json)
cp composer.json "$manifest"
mkdir -p "$COMPOSER_CACHE_DIR"
cache_directory=$(CDPATH= cd -- "$COMPOSER_CACHE_DIR" && pwd -P)
docker run --rm --entrypoint sh "$COMPOSER_IMAGE" -ec 'exec cat "$(command -v composer)"' > "$temporary/composer"

if [ "$mode" = check ]; then
    set -- curl --fail --silent --show-error --location --retry 2
    if [ -n "${CA_CERT_FILE:-}" ]; then
        test -f "$CA_CERT_FILE" && test -r "$CA_CERT_FILE" || {
            echo "CA_CERT_FILE is not a readable file: $CA_CERT_FILE" >&2
            exit 1
        }
        cat /etc/ssl/certs/ca-certificates.crt "$CA_CERT_FILE" > "$temporary/ca-bundle.pem"
        set -- "$@" --cacert "$temporary/ca-bundle.pem"
    fi
    "$@" https://xdebug.org/files/xdebug-3.5.0.tgz -o "$temporary/xdebug.tgz"
    printf '%s  %s\n' f6daf55a5c7adadb07dd2af25ff78b1cc9b1c58d6cc442a463eba015b678aabf "$temporary/xdebug.tgz" | sha256sum -c -
fi

set -- docker run --rm --init \
    --volume "$(pwd):/app" --workdir /app \
    --volume "$temporary/composer:/tmp/composer:ro" \
    --volume "$cache_directory:/tmp/composer-cache" \
    --env COMPOSER_CACHE_DIR=/tmp/composer-cache \
    --env COMPOSER_HOME=/tmp/composer-home \
    --env "COMPOSER=/app/$manifest" \
    --env HTTP_PROXY --env HTTPS_PROXY --env NO_PROXY \
    --env http_proxy --env https_proxy --env no_proxy \
    --env "PHP_VERSION=$PHP_VERSION" --env "DEPENDENCIES_LOWEST=$DEPENDENCIES_LOWEST" \
    --env "HOST_UID=$(id -u)" --env "HOST_GID=$(id -g)" --env "MODE=$mode"
if [ "$mode" = check ]; then
    set -- "$@" --volume "$temporary/xdebug.tgz:/tmp/xdebug.tgz:ro"
fi
if [ -n "${CA_CERT_FILE:-}" ]; then
    test -f "$CA_CERT_FILE" && test -r "$CA_CERT_FILE" || {
        echo "CA_CERT_FILE is not a readable file: $CA_CERT_FILE" >&2
        exit 1
    }
    ca_directory=$(CDPATH= cd -- "$(dirname -- "$CA_CERT_FILE")" && pwd -P)
    set -- "$@" --volume "$ca_directory/$(basename -- "$CA_CERT_FILE"):/tmp/extra-ca.crt:ro" --env EXTRA_CA=1
fi

"$@" "php:$PHP_VERSION-cli" sh /app/scripts/container-qa.sh
