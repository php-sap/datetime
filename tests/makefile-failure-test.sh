#!/bin/sh
set -eu

root=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
directory=$(mktemp -d)
trap 'rm -rf "$directory"' EXIT

cp "$root/Makefile" "$directory/Makefile"
cp -R "$root/scripts" "$directory/scripts"
printf '{"name":"example/example","config":{"platform":{"php":"8.1.34","ext-json":"8.1.34"}}}\n' > "$directory/composer.json"
cp "$directory/composer.json" "$directory/expected.json"
printf 'user-owned lockfile\n' > "$directory/composer.lock"
mkdir "$directory/bin"
printf '#!/bin/sh\nexit 42\n' > "$directory/bin/docker"
chmod +x "$directory/bin/docker"

if PATH="$directory/bin:$PATH" make -s -C "$directory" install \
    PHP_VERSION=8.1 COMPOSER_CACHE_DIR="$directory/cache" >/dev/null 2>&1; then
    echo 'install unexpectedly succeeded after Docker startup failure' >&2
    exit 1
fi

cmp "$directory/expected.json" "$directory/composer.json"
test "$(cat "$directory/composer.lock")" = 'user-owned lockfile'
test -z "$(find "$directory" -maxdepth 1 -name '.composer-run.*' -print)"
